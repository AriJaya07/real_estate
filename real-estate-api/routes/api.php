<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\PropertySubmissionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/properties', [PropertyController::class, 'index']);
Route::get('/properties/{property}', [PropertyController::class, 'show']);

Route::post('/webhooks/publish', [WebhookController::class, 'publish']);
Route::post('/webhooks/status', [WebhookController::class, 'updateStatus']);
Route::post('/webhooks/reject', [WebhookController::class, 'reject']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/properties/import', [PropertyController::class, 'import']);
    Route::patch('/properties/{property}/publish', [PropertyController::class, 'publish']);
    Route::apiResource('properties', PropertyController::class)->except(['index', 'show']);

    Route::get('/property-submissions/export', [PropertySubmissionController::class, 'export']);
    Route::post('/property-submissions/sync-clickup', [PropertySubmissionController::class, 'syncClickUp']);
    Route::get('/property-submissions', [PropertySubmissionController::class, 'index']);
    Route::post('/property-submissions', [PropertySubmissionController::class, 'store']);
    Route::put('/property-submissions/{propertySubmission}', [PropertySubmissionController::class, 'update']);
    Route::post('/property-submissions/{propertySubmission}/publish', [PropertySubmissionController::class, 'publish']);
    Route::delete('/property-submissions/{propertySubmission}', [PropertySubmissionController::class, 'destroy']);

    Route::get('/users', [UserController::class, 'index']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});
