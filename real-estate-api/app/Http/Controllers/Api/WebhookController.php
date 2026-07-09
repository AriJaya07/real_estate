<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\PropertySubmissionResource;
use App\Models\Property;
use App\Models\PropertySubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    protected const PIPELINE_STATUSES = ['pending', 'ai_processing', 'clickup_review', 'ready'];

    public function publish(Request $request): JsonResponse
    {
        $submission = $this->resolveSubmission($request);

        abort_if(! in_array($submission->status, self::PIPELINE_STATUSES, true), 422, 'Only submissions in the review pipeline can be published.');
        abort_if(! $submission->publish_ready, 422, 'Submission is not marked as publish ready.');

        $property = Property::create([
            'title' => $submission->property->title,
            'location' => $submission->address,
            'price' => $submission->listing_price,
            'type' => $submission->property->type,
            'image' => $submission->property->image,
            'description' => $submission->description,
            'is_published' => true,
        ]);

        $submission->update([
            'status' => 'published',
            'published_at' => now(),
            'published_property_id' => $property->id,
        ]);

        return response()->json([
            'message' => 'Submission published to website.',
            'property' => new PropertyResource($property),
        ], 201);
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $submission = $this->resolveSubmission($request);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:ai_processing,clickup_review,ready,rejected'],
        ]);

        abort_if(! in_array($submission->status, self::PIPELINE_STATUSES, true), 422, 'Only submissions in the review pipeline can change status.');

        $submission->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Submission status updated.',
            'submission' => new PropertySubmissionResource($submission->load('property')),
        ]);
    }

    public function reject(Request $request): JsonResponse
    {
        $submission = $this->resolveSubmission($request);

        abort_if(! in_array($submission->status, self::PIPELINE_STATUSES, true), 422, 'Only submissions in the review pipeline can be rejected.');

        $submission->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Submission rejected.',
            'submission' => new PropertySubmissionResource($submission->load('property')),
        ]);
    }

    protected function resolveSubmission(Request $request): PropertySubmission
    {
        $secret = config('services.publish.webhook_secret');

        abort_if(! $secret || ! hash_equals($secret, (string) $request->header('X-Webhook-Secret')), 401, 'Invalid webhook secret.');

        $validated = $request->validate([
            'submission_id' => ['required', 'integer', 'exists:property_submissions,id'],
        ]);

        return PropertySubmission::with('property')->findOrFail($validated['submission_id']);
    }
}
