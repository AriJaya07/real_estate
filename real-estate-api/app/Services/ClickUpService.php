<?php

namespace App\Services;

use App\Models\PropertySubmission;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ClickUpService
{
    public function createTask(PropertySubmission $submission): bool
    {
        $token = config('services.clickup.token');
        $listId = config('services.clickup.list_id');

        if (! $token || ! $listId) {
            return false;
        }

        try {
            return Http::withHeaders(['Authorization' => $token])
                ->timeout(5)
                ->post("https://api.clickup.com/api/v2/list/{$listId}/task", [
                    'name' => "Property Submission: {$submission->property->title}",
                    'description' => $this->taskDescription($submission),
                    'status' => 'to do',
                ])
                ->successful();
        } catch (Throwable $exception) {
            Log::warning('ClickUp task creation failed.', [
                'submission_id' => $submission->id,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    protected function taskDescription(PropertySubmission $submission): string
    {
        return implode("\n", [
            "Submission ID: {$submission->id}",
            "Property: {$submission->property->title}",
            "Location: {$submission->property->location}",
            "Type: {$submission->property->type}",
            "Listing Price: {$submission->listing_price}",
            "Owner: {$submission->owner_name}",
            "Phone: {$submission->phone}",
            "Email: {$submission->email}",
            "Address: {$submission->address}",
            "Status: {$submission->status}",
            'Publish Ready: '.($submission->publish_ready ? 'Yes' : 'No'),
            '',
            'Description:',
            $submission->description,
            '',
            'Notes:',
            $submission->notes ?? '-',
        ]);
    }
}
