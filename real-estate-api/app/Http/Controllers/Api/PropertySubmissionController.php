<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubmissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertySubmissionRequest;
use App\Http\Requests\UpdatePropertySubmissionRequest;
use App\Http\Resources\PropertySubmissionResource;
use App\Models\PropertySubmission;
use App\Services\ClickUpService;
use App\Services\ClickUpSyncService;
use App\Services\N8nWebhookService;
use App\Services\SubmissionPublisher;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PropertySubmissionController extends Controller
{
    public function __construct(
        protected N8nWebhookService $n8n,
        protected ClickUpService $clickUp,
        protected SubmissionPublisher $publisher,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $submissions = $this->filteredQuery($request)->paginate(10)->withQueryString();

        return PropertySubmissionResource::collection($submissions);
    }

    public function store(StorePropertySubmissionRequest $request): JsonResponse
    {
        $submission = $request->user()->submissions()->create($request->validated());
        $submission->load(['property', 'user']);

        $this->dispatchPipeline($submission);

        return (new PropertySubmissionResource($submission))->response()->setStatusCode(201);
    }

    public function update(UpdatePropertySubmissionRequest $request, PropertySubmission $propertySubmission): PropertySubmissionResource
    {
        abort_if($propertySubmission->user_id !== $request->user()->id, 403, 'You can only edit your own submissions.');
        abort_if(! in_array($propertySubmission->status, SubmissionStatus::editable(), true), 422, 'Only draft or rejected submissions can be edited.');

        $propertySubmission->update($request->validated());
        $propertySubmission->load(['property', 'user']);

        $this->dispatchPipeline($propertySubmission);

        return new PropertySubmissionResource($propertySubmission);
    }

    public function destroy(Request $request, PropertySubmission $propertySubmission): JsonResponse
    {
        abort_if($propertySubmission->user_id !== $request->user()->id, 403, 'You can only delete your own submissions.');
        abort_if(! in_array($propertySubmission->status, SubmissionStatus::editable(), true), 422, 'Only draft or rejected submissions can be deleted.');

        $propertySubmission->delete();

        return response()->json(['message' => 'Submission deleted.']);
    }

    public function export(Request $request): StreamedResponse
    {
        $submissions = $this->filteredQuery($request)->get();

        $columns = [
            'ID', 'Property', 'Location', 'Owner', 'Phone', 'Email', 'Address',
            'Listing Price', 'Status', 'Publish Ready', 'Published At', 'Created At',
            'Description', 'Notes',
        ];

        return response()->streamDownload(function () use ($submissions, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            foreach ($submissions as $submission) {
                fputcsv($handle, [
                    $submission->id,
                    $submission->property?->title,
                    $submission->property?->location,
                    $submission->owner_name,
                    $submission->phone,
                    $submission->email,
                    $submission->address,
                    number_format((float) $submission->listing_price, 2, '.', ''),
                    $submission->status,
                    $submission->publish_ready ? 'Yes' : 'No',
                    $submission->published_at?->toDateTimeString() ?? '',
                    $submission->created_at->toDateTimeString(),
                    $submission->description,
                    $submission->notes ?? '',
                ]);
            }

            fclose($handle);
        }, 'property-submissions.csv', ['Content-Type' => 'text/csv']);
    }

    protected function filteredQuery(Request $request): Builder
    {
        return $request->user()
            ->submissions()
            ->with(['property', 'publishedProperty'])
            ->when($request->integer('related_property_id'), function ($query, $propertyId) {
                $query->where(function ($query) use ($propertyId) {
                    $query->where('property_id', $propertyId)
                        ->orWhere('published_property_id', $propertyId);
                });
            })
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('owner_name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%")
                        ->orWhere('address', 'ilike', "%{$search}%")
                        ->orWhereHas('property', fn ($query) => $query->where('title', 'ilike', "%{$search}%"));
                });
            })
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->orderBy('created_at', $request->query('sort') === 'oldest' ? 'asc' : 'desc');
    }

    public function publish(Request $request, PropertySubmission $propertySubmission): PropertySubmissionResource
    {
        abort_if($propertySubmission->user_id !== $request->user()->id, 403, 'You can only publish your own submissions.');
        abort_if($propertySubmission->status !== SubmissionStatus::Ready->value, 422, 'Only submissions that are ready to publish can be published.');

        $propertySubmission->load('property');
        $this->publisher->publish($propertySubmission);

        return new PropertySubmissionResource($propertySubmission->load(['property', 'publishedProperty']));
    }

    public function syncClickUp(Request $request, ClickUpSyncService $sync): JsonResponse
    {
        $submissions = $request->user()
            ->submissions()
            ->with('property')
            ->awaitingClickUp()
            ->get();

        return response()->json($sync->sync($submissions));
    }

    protected function dispatchPipeline(PropertySubmission $submission): void
    {
        if ($submission->status !== SubmissionStatus::Pending->value) {
            return;
        }

        if ($this->n8n->send($submission)) {
            $submission->update(['status' => SubmissionStatus::AiProcessing->value]);

            return;
        }

        $taskId = $this->clickUp->createTask($submission);

        if ($taskId !== null) {
            $submission->update([
                'status' => SubmissionStatus::ClickUpReview->value,
                'clickup_task_id' => $taskId,
                'clickup_status' => 'to do',
            ]);
        }
    }
}
