<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'location' => $this->location,
            'price' => (float) $this->price,
            'type' => $this->type,
            'image' => $this->image,
            'description' => $this->description,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
        ];
    }
}
