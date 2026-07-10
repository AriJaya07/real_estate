<?php

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function validPropertyPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Modern Family House',
        'location' => 'Canggu, Bali',
        'price' => 450000,
        'type' => 'House',
        'image' => 'https://images.test/house.jpg',
        'description' => 'Spacious family house with pool.',
        'is_published' => true,
    ], $overrides);
}

test('public listing only returns published properties', function () {
    Property::factory()->create(['is_published' => true, 'title' => 'Visible Villa']);
    Property::factory()->create(['is_published' => false, 'title' => 'Hidden House']);

    $this->getJson('/api/properties')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Visible Villa');
});

test('authenticated users can list unpublished properties with all flag', function () {
    Sanctum::actingAs(User::factory()->create());
    Property::factory()->create(['is_published' => true]);
    Property::factory()->create(['is_published' => false]);

    $this->getJson('/api/properties?all=1')
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

test('guests cannot use the all flag', function () {
    Property::factory()->create(['is_published' => false]);

    $this->getJson('/api/properties?all=1')
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

test('show returns a single property', function () {
    $property = Property::factory()->create();

    $this->getJson("/api/properties/{$property->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.id', $property->id)
        ->assertJsonPath('data.title', $property->title);
});

test('show returns 404 for a missing property', function () {
    $this->getJson('/api/properties/999999')->assertNotFound();
});

test('create stores a property and stamps published_at', function () {
    Http::fake(['*' => Http::response('', 200, ['Content-Type' => 'image/jpeg'])]);
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/properties', validPropertyPayload())
        ->assertCreated()
        ->assertJsonPath('data.title', 'Modern Family House')
        ->assertJsonPath('data.is_published', true);

    $property = Property::firstWhere('title', 'Modern Family House');
    expect($property->published_at)->not->toBeNull();
});

test('create validates required fields', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/properties', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'location', 'price', 'type', 'description']);
});

test('create rejects an image URL that points to a web page', function () {
    Http::fake(['*' => Http::response('<html></html>', 200, ['Content-Type' => 'text/html'])]);
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/properties', validPropertyPayload([
        'image' => 'https://www.magnific.com/idn/foto-vektor-gratis/simple-home',
    ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('image');
});

test('create accepts an unreachable image URL (fail-soft)', function () {
    Http::fake(fn () => throw new ConnectionException('timeout'));
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/properties', validPropertyPayload([
        'image' => 'https://unreachable.test/photo.jpg',
    ]))->assertCreated();
});

test('update modifies a property', function () {
    Http::fake(['*' => Http::response('', 200, ['Content-Type' => 'image/png'])]);
    Sanctum::actingAs(User::factory()->create());
    $property = Property::factory()->create();

    $this->putJson("/api/properties/{$property->id}", validPropertyPayload([
        'title' => 'Renamed Residence',
    ]))
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'Renamed Residence');

    $this->assertDatabaseHas('properties', ['id' => $property->id, 'title' => 'Renamed Residence']);
});

test('publish toggle updates status and published_at', function () {
    Sanctum::actingAs(User::factory()->create());
    $property = Property::factory()->create(['is_published' => false]);

    expect($property->published_at)->toBeNull();

    $this->patchJson("/api/properties/{$property->id}/publish", ['is_published' => true])
        ->assertSuccessful()
        ->assertJsonPath('data.is_published', true);

    expect($property->refresh()->published_at)->not->toBeNull();

    $this->patchJson("/api/properties/{$property->id}/publish", ['is_published' => false])
        ->assertSuccessful()
        ->assertJsonPath('data.is_published', false);

    expect($property->refresh()->is_published)->toBeFalse();
});

test('delete removes a property', function () {
    Sanctum::actingAs(User::factory()->create());
    $property = Property::factory()->create();

    $this->deleteJson("/api/properties/{$property->id}")->assertSuccessful();

    $this->assertDatabaseMissing('properties', ['id' => $property->id]);
});

test('guests cannot create, update, publish, or delete properties', function () {
    $property = Property::factory()->create();

    $this->postJson('/api/properties', validPropertyPayload())->assertUnauthorized();
    $this->putJson("/api/properties/{$property->id}", validPropertyPayload())->assertUnauthorized();
    $this->patchJson("/api/properties/{$property->id}/publish", ['is_published' => false])->assertUnauthorized();
    $this->deleteJson("/api/properties/{$property->id}")->assertUnauthorized();
});
