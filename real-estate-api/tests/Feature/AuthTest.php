<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('register creates a user and returns an auth token', function () {
    $response = $this->postJson('/api/register', [
        'username' => 'newuser',
        'password' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['user' => ['id', 'username'], 'token'])
        ->assertJsonPath('user.username', 'newuser');

    $this->assertDatabaseHas('users', ['username' => 'newuser']);

    $token = $response->json('token');

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/me')
        ->assertSuccessful()
        ->assertJsonPath('data.username', 'newuser');
});

test('register rejects invalid input', function (array $payload, string $errorField) {
    $this->postJson('/api/register', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($errorField);
})->with([
    'missing username' => [['password' => 'password123'], 'username'],
    'short username' => [['username' => 'ab', 'password' => 'password123'], 'username'],
    'short password' => [['username' => 'validname', 'password' => 'short'], 'password'],
]);

test('register rejects a duplicate username', function () {
    User::factory()->create(['username' => 'taken']);

    $this->postJson('/api/register', ['username' => 'taken', 'password' => 'password123'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('username');
});

test('login returns a token for valid credentials', function () {
    $user = User::factory()->create(['password' => 'password123']);

    $this->postJson('/api/login', ['username' => $user->username, 'password' => 'password123'])
        ->assertSuccessful()
        ->assertJsonStructure(['user' => ['id', 'username'], 'token'])
        ->assertJsonPath('user.username', $user->username);
});

test('login rejects invalid credentials', function () {
    $user = User::factory()->create(['password' => 'password123']);

    $this->postJson('/api/login', ['username' => $user->username, 'password' => 'wrong-password'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('username');
});

test('logout revokes the token', function () {
    $user = User::factory()->create(['password' => 'password123']);

    $token = $this->postJson('/api/login', [
        'username' => $user->username,
        'password' => 'password123',
    ])->json('token');

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/logout')
        ->assertSuccessful();

    expect($user->tokens()->count())->toBe(0);
});

test('guests are blocked from protected routes', function () {
    $this->getJson('/api/me')->assertUnauthorized();
    $this->getJson('/api/property-submissions')->assertUnauthorized();
});
