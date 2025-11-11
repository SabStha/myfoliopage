<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Course extends Model
{
    use HasTranslations;
    protected $fillable = [
        'title', 'provider', 'course_url', 'instructor_organization', 'difficulty',
        'estimated_hours', 'prerequisites', 'key_skills', 'module_outline',
        'assessments_grading', 'artifacts_assignments', 'highlight_project_title',
        'highlight_project_goal', 'highlight_project_link', 'proof_completion_url',
        'takeaways', 'applied_in', 'next_actions', 'status', 'completion_percent',
        'credential_id', 'verify_url', 'issued_at', 'completed_at',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'completed_at' => 'date',
        'completion_percent' => 'integer',
        // Cast translatable fields as JSON
        'title' => 'array',
        'provider' => 'array',
        'key_skills' => 'array',
        'module_outline' => 'array',
        'takeaways' => 'array',
    ];
    
    /**
     * Get translatable fields
     */
    protected function getTranslatableFields(): array
    {
        return [
            'title',
            'provider',
            'key_skills',
            'module_outline',
            'takeaways',
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

    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable', 'category_model')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }

    public function sections(): MorphToMany
    {
        // Sections are CategoryItems
        return $this->morphToMany(CategoryItem::class, 'sectionable', 'category_item_model')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }
}
