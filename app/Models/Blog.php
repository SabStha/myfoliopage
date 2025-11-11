<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Blog extends Model
{
    use HasTranslations;
    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'category', 'published_at', 'is_published',
        'linkedin_post_id', 'linkedin_url', 'auto_sync_from_linkedin', 'auto_post_to_linkedin',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_published' => 'boolean',
        'auto_sync_from_linkedin' => 'boolean',
        'auto_post_to_linkedin' => 'boolean',
        // Cast translatable fields as JSON
        'title' => 'array',
        'excerpt' => 'array',
        'content' => 'array',
    ];
    
    /**
     * Get translatable fields
     */
    protected function getTranslatableFields(): array
    {
        return [
            'title',
            'excerpt',
            'content',
        ];
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}

