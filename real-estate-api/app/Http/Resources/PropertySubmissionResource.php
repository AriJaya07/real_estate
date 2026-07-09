<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertySubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'property' => new PropertyResource($this->whenLoaded('property')),
            'owner_name' => $this->owner_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'listing_price' => (float) $this->listing_price,
            'status' => $this->status,
            'description' => $this->description,
            'notes' => $this->notes,
            'publish_ready' => $this->publish_ready,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
        ];
    }
}
