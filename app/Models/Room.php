<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Room extends Model
{
    use HasTranslations;
    protected $fillable = [
        'user_id', 'title', 'slug', 'description', 'summary', 'platform', 'room_url', 'difficulty', 'completed_at',
        // Learning & Purpose
        'objective_goal', 'key_techniques_used', 'tools_commands_used', 'attack_vector_summary',
        'flag_evidence_proof', 'time_spent', 'reflection_takeaways', 'difficulty_confirmation',
        // Reproducibility
        'walkthrough_summary_steps', 'tools_environment', 'command_log_snippet',
        'room_id_author', 'completion_screenshot_report_link',
        // Traceability & Meta
        'platform_username', 'platform_profile_link', 'status', 'score_points_earned',
    ];

    protected $casts = [
        'completed_at' => 'date',
        'time_spent' => 'integer',
        'score_points_earned' => 'integer',
        // Cast translatable fields as JSON
        'title' => 'array',
        'description' => 'array',
        'summary' => 'array',
    ];
    
    /**
     * Get translatable fields
     */
    protected function getTranslatableFields(): array
    {
        return [
            'title',
            'description',
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

