<?php

namespace App\Services;

use App\Models\PropertySubmission;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class N8nWebhookService
{
    public function send(PropertySubmission $submission): void
    {
        $url = config('services.n8n.webhook_url');

        if (! $url) {
            return;
        }

        try {
            Http::timeout(5)->post($url, $this->payload($submission));
        } catch (Throwable $exception) {
            Log::warning('n8n webhook delivery failed.', [
                'submission_id' => $submission->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    protected function payload(PropertySubmission $submission): array
    {
        return [
            'submission_id' => $submission->id,
            'publish_callback_url' => url('/api/webhooks/publish'),
            'user' => [
                'id' => $submission->user->id,
                'username' => $submission->user->username,
            ],
            'property' => [
                'id' => $submission->property->id,
                'title' => $submission->property->title,
                'location' => $submission->property->location,
                'type' => $submission->property->type,
                'price' => (float) $submission->property->price,
            ],
            'owner' => $submission->owner_name,
            'phone' => $submission->phone,
            'email' => $submission->email,
            'address' => $submission->address,
            'description' => $submission->description,
            'notes' => $submission->notes,
            'status' => $submission->status,
            'price' => (float) $submission->listing_price,
            'publish_ready' => $submission->publish_ready,
        ];
    }
}
