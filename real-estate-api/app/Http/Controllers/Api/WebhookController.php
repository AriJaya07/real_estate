<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Models\PropertySubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function publish(Request $request): JsonResponse
    {
        $secret = config('services.publish.webhook_secret');

        abort_if(! $secret || ! hash_equals($secret, (string) $request->header('X-Webhook-Secret')), 401, 'Invalid webhook secret.');

        $validated = $request->validate([
            'submission_id' => ['required', 'integer', 'exists:property_submissions,id'],
        ]);

        $submission = PropertySubmission::with('property')->findOrFail($validated['submission_id']);

        abort_if($submission->status === 'draft', 422, 'Draft submissions cannot be published.');
        abort_if(! $submission->publish_ready, 422, 'Submission is not marked as publish ready.');
        abort_if($submission->published_at !== null, 409, 'Submission has already been published.');

        $property = Property::create([
            'title' => $submission->property->title,
            'location' => $submission->address,
            'price' => $submission->listing_price,
            'type' => $submission->property->type,
            'image' => $submission->property->image,
            'description' => $submission->description,
        ]);

        $submission->update([
            'published_at' => now(),
            'published_property_id' => $property->id,
        ]);

        return response()->json([
            'message' => 'Submission published to website.',
            'property' => new PropertyResource($property),
        ], 201);
    }
}
