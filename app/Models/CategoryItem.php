<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class CategoryItem extends Model
{
    use HasTranslations;
    protected $fillable = [
        'category_id',
        'nav_link_id',
        'title',
        'slug',
        'image_path',
        'url',
        'summary',
        'download_url',
        'view_url',
        'visit_url',
        'position',
        'show_title',
        'show_description',
        'show_slug',
        'show_buttons',
        'button_settings',
        'linked_model_type',
        'linked_model_id'
    ];
    
    protected $casts = [
        'position' => 'integer',
        'show_title' => 'boolean',
        'show_description' => 'boolean',
        'show_slug' => 'boolean',
        'show_buttons' => 'boolean',
        'button_settings' => 'array',
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
    
    /**
     * Get the category that owns this item
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    
    /**
     * Get the sub-nav (NavLink) this item is linked to
     */
    public function navLink(): BelongsTo
    {
        return $this->belongsTo(NavLink::class);
    }
    
    /**
     * Get the linked model (polymorphic relationship)
     */
    public function linkedModel()
    {
        return $this->morphTo('linked_model');
    }
    
    /**
     * Get the model type name for display
     */
    public function getModelTypeNameAttribute(): ?string
    {
        if (!$this->linked_model_type) return null;
        
        $types = [
            'App\Models\BookPage' => 'book-page',
            'App\Models\CodeSummary' => 'code-summary',
            'App\Models\Room' => 'room',
            'App\Models\Certificate' => 'certificate',
            'App\Models\Course' => 'course',
        ];
        
        return $types[$this->linked_model_type] ?? null;
    }
    
    /**
     * Get all book pages linked to this section
     */
    public function bookPages(): MorphToMany
    {
        return $this->morphedByMany(\App\Models\BookPage::class, 'sectionable', 'category_item_model')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }
    
    /**
     * Get all code summaries linked to this section
     */
    public function codeSummaries(): MorphToMany
    {
        return $this->morphedByMany(\App\Models\CodeSummary::class, 'sectionable', 'category_item_model')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }
    
    /**
     * Get all rooms linked to this section
     */
    public function rooms(): MorphToMany
    {
        return $this->morphedByMany(\App\Models\Room::class, 'sectionable', 'category_item_model')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }
    
    /**
     * Get all certificates linked to this section
     */
    public function certificates(): MorphToMany
    {
        return $this->morphedByMany(\App\Models\Certificate::class, 'sectionable', 'category_item_model')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }
    
    /**
     * Get all courses linked to this section
     */
    public function courses(): MorphToMany
    {
        return $this->morphedByMany(\App\Models\Course::class, 'sectionable', 'category_item_model')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }
}
