<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class EngagementSection extends Model
{
    use HasTranslations;
    protected $fillable = [
        'title',
        'video_path',
        'poster_path',
    ];

    protected $casts = [
        // Cast translatable fields as JSON
        'title' => 'array',
    ];
    
    /**
     * Get translatable fields
     */
    protected function getTranslatableFields(): array
    {
        return [
            'title',
        ];
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }
}




