<?php

namespace App\Models;

use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'location', 'price', 'type', 'image', 'description', 'is_published'])]
class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Property $property): void {
            if ($property->is_published && ($property->isDirty('is_published') || ! $property->exists)) {
                $property->published_at = now();
            }
        });
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(PropertySubmission::class);
    }
}
