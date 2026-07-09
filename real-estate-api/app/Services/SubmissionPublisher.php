<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertySubmission;

class SubmissionPublisher
{
    public function publish(PropertySubmission $submission): Property
    {
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

        return $property;
    }
}
