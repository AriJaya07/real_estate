<?php

namespace App\Models;

use Database\Factories\PropertySubmissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'property_id',
    'owner_name',
    'phone',
    'email',
    'address',
    'listing_price',
    'status',
    'description',
    'notes',
    'publish_ready',
    'published_at',
    'published_property_id',
    'clickup_task_id',
])]
class PropertySubmission extends Model
{
    /** @use HasFactory<PropertySubmissionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'listing_price' => 'decimal:2',
            'publish_ready' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function publishedProperty(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'published_property_id');
    }
}
