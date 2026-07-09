<?php

namespace App\Services;

use App\Models\PropertySubmission;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ClickUpService
{
    public function createTask(PropertySubmission $submission): ?string
    {
        $token = config('services.clickup.token');
        $listId = config('services.clickup.list_id');

        if (! $token || ! $listId) {
            return null;
        }

        try {
            $response = Http::withHeaders(['Authorization' => $token])
                ->timeout(5)
                ->post("https://api.clickup.com/api/v2/list/{$listId}/task", [
                    'name' => "Property Submission: {$submission->property->title}",
                    'description' => $this->taskDescription($submission),
                    'status' => 'to do',
                ]);

            return $response->successful() ? $response->json('id') : null;
        } catch (Throwable $exception) {
            Log::warning('ClickUp task creation failed.', [
                'submission_id' => $submission->id,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return array{name: string, type: string}|null
     */
    public function getTaskStatus(string $taskId): ?array
    {
        $token = config('services.clickup.token');

        if (! $token) {
            return null;
        }

        try {
            $response = Http::withHeaders(['Authorization' => $token])
                ->timeout(5)
                ->get("https://api.clickup.com/api/v2/task/{$taskId}");

            if (! $response->successful()) {
                return null;
            }

            return [
                'name' => strtolower((string) $response->json('status.status')),
                'type' => strtolower((string) $response->json('status.type')),
            ];
        } catch (Throwable $exception) {
            Log::warning('ClickUp task status fetch failed.', [
                'task_id' => $taskId,
                'error' => $exception->getMessage(),
            ]);

            return null;
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
