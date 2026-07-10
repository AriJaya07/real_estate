<?php

namespace App\Services;

use App\Enums\SubmissionStatus;
use App\Models\Property;
use App\Models\PropertySubmission;

class SubmissionPublisher
{
    public function __construct(protected ClickUpService $clickUp) {}

    public function publish(PropertySubmission $submission): Property
    {
        if ($submission->status === SubmissionStatus::Published->value && $submission->publishedProperty !== null) {
            return $submission->publishedProperty;
        }

        $property = Property::create([
            'title' => $submission->property->title,
            'location' => $submission->address,
            'price' => $submission->listing_price,
            'type' => $submission->property->type,
            'image' => $submission->property->image,
            'description' => $submission->description,
            'is_published' => true,
        ]);

        $changes = [
            'status' => SubmissionStatus::Published->value,
            'published_at' => now(),
            'published_property_id' => $property->id,
        ];

        if ($submission->clickup_task_id !== null && $this->clickUp->closeTask($submission->clickup_task_id)) {
            $changes['clickup_status'] = 'closed';
        }

        $submission->update($changes);

        return $property;
    }
}
