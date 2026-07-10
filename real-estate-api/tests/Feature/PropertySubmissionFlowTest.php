<?php

use App\Models\Property;
use App\Models\PropertySubmission;
use App\Models\User;
use App\Services\SubmissionPublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function validSubmissionPayload(Property $property, array $overrides = []): array
{
    return array_merge([
        'property_id' => $property->id,
        'owner_name' => 'John Doe',
        'phone' => '+62 812 3456 7890',
        'email' => 'owner@example.com',
        'address' => 'Jl. Pantai Berawa, Canggu',
        'listing_price' => 1250000,
        'status' => 'pending',
        'description' => 'Beautiful beachside villa.',
        'notes' => 'Motivated seller.',
        'publish_ready' => false,
    ], $overrides);
}

function withoutIntegrations(): void
{
    config([
        'services.n8n.webhook_url' => null,
        'services.clickup.token' => null,
        'services.clickup.list_id' => null,
    ]);
}

test('a draft submission does not enter the pipeline', function () {
    Http::fake();
    withoutIntegrations();
    Sanctum::actingAs(User::factory()->create());
    $property = Property::factory()->create();

    $this->postJson('/api/property-submissions', validSubmissionPayload($property, ['status' => 'draft']))
        ->assertCreated()
        ->assertJsonPath('data.status', 'draft');

    Http::assertNothingSent();
});

test('a pending submission advances to ai_processing when n8n accepts', function () {
    config(['services.n8n.webhook_url' => 'https://n8n.test/webhook']);
    Http::fake(['n8n.test/*' => Http::response(['ok' => true])]);
    Sanctum::actingAs(User::factory()->create());
    $property = Property::factory()->create();

    $this->postJson('/api/property-submissions', validSubmissionPayload($property))
        ->assertCreated()
        ->assertJsonPath('data.status', 'ai_processing');
});

test('a pending submission falls back to a direct ClickUp task when n8n fails', function () {
    config([
        'services.n8n.webhook_url' => 'https://n8n.test/webhook',
        'services.clickup.token' => 'test-token',
        'services.clickup.list_id' => 'list-1',
    ]);
    Http::fake([
        'n8n.test/*' => Http::response('error', 500),
        'api.clickup.com/api/v2/list/*' => Http::response(['id' => 'task-123']),
    ]);
    Sanctum::actingAs(User::factory()->create());
    $property = Property::factory()->create();

    $this->postJson('/api/property-submissions', validSubmissionPayload($property))
        ->assertCreated()
        ->assertJsonPath('data.status', 'clickup_review')
        ->assertJsonPath('data.clickup_status', 'to do');

    $this->assertDatabaseHas('property_submissions', ['clickup_task_id' => 'task-123']);
});

test('a pending submission stays pending when all integrations are down', function () {
    Http::fake();
    withoutIntegrations();
    Sanctum::actingAs(User::factory()->create());
    $property = Property::factory()->create();

    $this->postJson('/api/property-submissions', validSubmissionPayload($property))
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending');
});

test('submissions in review cannot be edited or deleted', function () {
    withoutIntegrations();
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    $submission = PropertySubmission::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

    $payload = collect(validSubmissionPayload($submission->property, ['status' => 'draft']))->except('property_id')->all();

    $this->putJson("/api/property-submissions/{$submission->id}", $payload)->assertUnprocessable();
    $this->deleteJson("/api/property-submissions/{$submission->id}")->assertUnprocessable();
});

test('a rejected submission can be edited and resubmitted', function () {
    Http::fake();
    withoutIntegrations();
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    $submission = PropertySubmission::factory()->create(['user_id' => $user->id, 'status' => 'rejected']);

    $payload = collect(validSubmissionPayload($submission->property, ['status' => 'pending']))->except('property_id')->all();

    $this->putJson("/api/property-submissions/{$submission->id}", $payload)
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'pending');
});

test('users cannot touch other users submissions', function () {
    Sanctum::actingAs(User::factory()->create());
    $other = PropertySubmission::factory()->create(['status' => 'draft']);

    $payload = collect(validSubmissionPayload($other->property, ['status' => 'draft']))->except('property_id')->all();

    $this->putJson("/api/property-submissions/{$other->id}", $payload)->assertForbidden();
    $this->deleteJson("/api/property-submissions/{$other->id}")->assertForbidden();
    $this->postJson("/api/property-submissions/{$other->id}/publish")->assertForbidden();
});

test('publishing a ready submission creates a live property and closes the ClickUp task', function () {
    config(['services.clickup.token' => 'test-token']);
    Http::fake(['api.clickup.com/api/v2/task/*' => Http::response(['ok' => true])]);
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    $submission = PropertySubmission::factory()->create([
        'user_id' => $user->id,
        'status' => 'ready',
        'clickup_task_id' => 'task-9',
    ]);

    $this->postJson("/api/property-submissions/{$submission->id}/publish")
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'published');

    $submission->refresh();
    expect($submission->published_property_id)->not->toBeNull()
        ->and($submission->published_at)->not->toBeNull()
        ->and($submission->clickup_status)->toBe('closed');

    $published = Property::find($submission->published_property_id);
    expect($published->is_published)->toBeTrue()
        ->and($published->published_at)->not->toBeNull()
        ->and($published->location)->toBe($submission->address);

    Http::assertSent(fn ($request) => $request->method() === 'PUT'
        && str_contains($request->url(), 'task/task-9')
        && $request['status'] === 'closed');
});

test('a submission that is not ready cannot be published', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    $submission = PropertySubmission::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

    $this->postJson("/api/property-submissions/{$submission->id}/publish")->assertUnprocessable();
});

test('publishing twice does not create a duplicate property', function () {
    withoutIntegrations();
    $submission = PropertySubmission::factory()->create(['status' => 'ready', 'clickup_task_id' => null]);

    $publisher = app(SubmissionPublisher::class);
    $first = $publisher->publish($submission);
    $second = $publisher->publish($submission->refresh()->load('publishedProperty'));

    expect($second->id)->toBe($first->id)
        ->and(Property::count())->toBe(2); // the source property + one published copy
});

test('the index exposes the published listing and filters by related property', function () {
    withoutIntegrations();
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $submission = PropertySubmission::factory()->create(['user_id' => $user->id, 'status' => 'ready', 'clickup_task_id' => null]);
    PropertySubmission::factory()->create(['user_id' => $user->id, 'status' => 'draft']);
    $published = app(SubmissionPublisher::class)->publish($submission);

    // Filter by the source property.
    $this->getJson("/api/property-submissions?related_property_id={$submission->property_id}")
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $submission->id)
        ->assertJsonPath('data.0.published_property.id', $published->id)
        ->assertJsonPath('data.0.published_property.is_published', true);

    // Filter by the published copy finds the same submission.
    $this->getJson("/api/property-submissions?related_property_id={$published->id}")
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $submission->id);

    // A non-numeric filter value is ignored instead of erroring.
    $this->getJson('/api/property-submissions?related_property_id=abc')
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

test('sync records the ClickUp status name while the task is in progress', function () {
    config(['services.clickup.token' => 'test-token']);
    Http::fake(['api.clickup.com/api/v2/task/*' => Http::response([
        'status' => ['status' => 'In Progress', 'type' => 'custom'],
    ])]);
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    $submission = PropertySubmission::factory()->create([
        'user_id' => $user->id,
        'status' => 'clickup_review',
        'clickup_task_id' => 'task-1',
    ]);

    $this->postJson('/api/property-submissions/sync-clickup')
        ->assertSuccessful()
        ->assertJson(['updated' => 1, 'checked' => 1]);

    expect($submission->refresh()->clickup_status)->toBe('in progress');

    // Second sync with no change reports nothing updated.
    $this->postJson('/api/property-submissions/sync-clickup')
        ->assertSuccessful()
        ->assertJson(['updated' => 0, 'checked' => 1]);
});

test('sync moves a done task to ready when not publish ready', function () {
    config(['services.clickup.token' => 'test-token']);
    Http::fake(['api.clickup.com/api/v2/task/*' => Http::response([
        'status' => ['status' => 'Done', 'type' => 'done'],
    ])]);
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    $submission = PropertySubmission::factory()->create([
        'user_id' => $user->id,
        'status' => 'clickup_review',
        'clickup_task_id' => 'task-2',
        'publish_ready' => false,
    ]);

    $this->postJson('/api/property-submissions/sync-clickup')->assertSuccessful();

    expect($submission->refresh()->status)->toBe('ready')
        ->and($submission->clickup_status)->toBe('done');
});

test('sync auto-publishes a done task that is publish ready', function () {
    config(['services.clickup.token' => 'test-token']);
    Http::fake(['api.clickup.com/api/v2/task/*' => Http::response([
        'status' => ['status' => 'Closed', 'type' => 'closed'],
    ])]);
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    $submission = PropertySubmission::factory()->create([
        'user_id' => $user->id,
        'status' => 'clickup_review',
        'clickup_task_id' => 'task-3',
        'publish_ready' => true,
    ]);

    $this->postJson('/api/property-submissions/sync-clickup')->assertSuccessful();

    $submission->refresh();
    expect($submission->status)->toBe('published')
        ->and($submission->published_property_id)->not->toBeNull();
});

test('the clickup:sync artisan command syncs submissions across all users', function () {
    config(['services.clickup.token' => 'test-token']);
    Http::fake(['api.clickup.com/api/v2/task/*' => Http::response([
        'status' => ['status' => 'Done', 'type' => 'done'],
    ])]);

    $submissions = PropertySubmission::factory()->count(2)->create([
        'status' => 'clickup_review',
        'publish_ready' => false,
    ])->each(fn ($submission, $index) => $submission->update(['clickup_task_id' => "task-{$index}"]));

    $this->artisan('clickup:sync')
        ->expectsOutputToContain('Checked 2 submissions, updated 2.')
        ->assertSuccessful();

    $submissions->each(fn ($submission) => expect($submission->refresh()->status)->toBe('ready'));
});
