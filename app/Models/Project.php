<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Project extends Model
{
    use HasTranslations;
    protected $fillable = [
        'title', 'slug', 'summary', 'tech_stack', 'repo_url', 'demo_url', 'completed_at',
    ];
    
    protected $casts = [
        'completed_at' => 'date',
        // Cast translatable fields as JSON
        'title' => 'array',
        'summary' => 'array',
    ];
    
    /**
     * Get translatable fields
     */
    protected function getTranslatableFields(): array
    {
        return [
            'title',
            'summary',
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
