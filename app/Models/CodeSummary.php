<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class CodeSummary extends Model
{
    use HasTranslations;
    protected $fillable = [
        'title', 'slug', 'code', 'summary', 'language', 'repository_url', 'file_path',
        // Context & Purpose
        'problem_statement', 'learning_goal', 'use_case',
        // Proof & Reproducibility
        'how_to_run', 'expected_output', 'dependencies', 'test_status',
        // Evaluation & Reflection
        'complexity_notes', 'security_notes', 'reflection',
        // Traceability
        'commit_sha', 'license', 'file_path_repo',
        // Metadata
        'framework', 'difficulty', 'time_spent', 'status',
    ];

    protected $casts = [
        'time_spent' => 'integer',
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

    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable', 'category_model')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function sections(): MorphToMany
    {
        // Sections are CategoryItems
        return $this->morphToMany(CategoryItem::class, 'sectionable', 'category_item_model')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}

