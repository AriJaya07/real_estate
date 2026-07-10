<?php

use App\Models\Property;
use App\Models\PropertySubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.publish.webhook_secret' => 'test-secret',
        'services.clickup.token' => null,
    ]);
    Http::fake();
});

function webhookHeaders(): array
{
    return ['X-Webhook-Secret' => 'test-secret'];
}

test('webhooks reject a missing or invalid secret', function () {
    $submission = PropertySubmission::factory()->create(['status' => 'pending']);

    $this->postJson('/api/webhooks/status', ['submission_id' => $submission->id, 'status' => 'ready'])
        ->assertUnauthorized();

    $this->postJson('/api/webhooks/status', ['submission_id' => $submission->id, 'status' => 'ready'], ['X-Webhook-Secret' => 'wrong'])
        ->assertUnauthorized();
});

test('webhooks are rejected when no secret is configured', function () {
    config(['services.publish.webhook_secret' => null]);
    $submission = PropertySubmission::factory()->create(['status' => 'pending']);

    $this->postJson('/api/webhooks/status', ['submission_id' => $submission->id, 'status' => 'ready'], webhookHeaders())
        ->assertUnauthorized();
});

test('the status webhook advances a pipeline submission', function () {
    $submission = PropertySubmission::factory()->create(['status' => 'pending']);

    $this->postJson('/api/webhooks/status', [
        'submission_id' => $submission->id,
        'status' => 'clickup_review',
        'clickup_task_id' => 'task-77',
    ], webhookHeaders())->assertSuccessful();

    $submission->refresh();
    expect($submission->status)->toBe('clickup_review')
        ->and($submission->clickup_task_id)->toBe('task-77');
});

test('the status webhook refuses submissions outside the pipeline', function () {
    $submission = PropertySubmission::factory()->draft()->create();

    $this->postJson('/api/webhooks/status', [
        'submission_id' => $submission->id,
        'status' => 'ready',
    ], webhookHeaders())->assertUnprocessable();
});

test('the reject webhook marks a submission rejected', function () {
    $submission = PropertySubmission::factory()->create(['status' => 'clickup_review']);

    $this->postJson('/api/webhooks/reject', ['submission_id' => $submission->id], webhookHeaders())
        ->assertSuccessful();

    expect($submission->refresh()->status)->toBe('rejected');
});

test('the publish webhook publishes a publish-ready submission', function () {
    $submission = PropertySubmission::factory()->create([
        'status' => 'ready',
        'publish_ready' => true,
        'clickup_task_id' => null,
    ]);

    $this->postJson('/api/webhooks/publish', ['submission_id' => $submission->id], webhookHeaders())
        ->assertCreated();

    $submission->refresh();
    expect($submission->status)->toBe('published')
        ->and(Property::find($submission->published_property_id)->is_published)->toBeTrue();
});

test('the publish webhook refuses submissions that are not publish ready', function () {
    $submission = PropertySubmission::factory()->create([
        'status' => 'ready',
        'publish_ready' => false,
    ]);

    $this->postJson('/api/webhooks/publish', ['submission_id' => $submission->id], webhookHeaders())
        ->assertUnprocessable();
});
