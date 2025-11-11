<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Testimonial extends Model
{
    use HasTranslations;
    protected $fillable = [
        'name', 'company', 'title', 'quote', 'photo_url', 'sns_url', 'position', 'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'position' => 'integer',
        // Cast translatable fields as JSON
        'quote' => 'array',
        'company' => 'array',
    ];
    
    /**
     * Get translatable fields
     */
    protected function getTranslatableFields(): array
    {
        return [
            'quote',
            'company',
        ];
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderBy('created_at', 'desc');
    }
}
