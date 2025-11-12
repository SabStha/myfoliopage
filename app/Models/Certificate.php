<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Certificate extends Model
{
    use HasTranslations;
    protected $fillable = [
        'user_id', 'title', 'provider', 'issued_by', 'credential_id', 'verify_url', 
        'issued_at', 'expiry_date', 'has_expiry', 'level', 'learning_hours',
        'learning_outcomes', 'reflection', 'status', 'project_id',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'expiry_date' => 'date',
        'has_expiry' => 'boolean',
        'learning_hours' => 'integer',
        // Cast translatable fields as JSON
        'title' => 'array',
        'provider' => 'array',
        'learning_outcomes' => 'array',
        'reflection' => 'array',
    ];
    
    /**
     * Get translatable fields
     */
    protected function getTranslatableFields(): array
    {
        return [
            'title',
            'provider',
            'learning_outcomes',
            'reflection',
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

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
