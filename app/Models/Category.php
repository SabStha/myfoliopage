<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Category extends Model
{
    use HasTranslations;
    protected $fillable = [
        'name',
        'slug',
        'color',
        'position',
        'image_path',
        'document_path',
        'summary',
        'animation_style',
        'download_url',
        'view_url',
        'visit_url'
    ];
    
    protected $casts = [
        'position' => 'integer',
        // Cast translatable fields as JSON
        'name' => 'array',
        'summary' => 'array',
    ];
    
    /**
     * Get translatable fields
     */
    protected function getTranslatableFields(): array
    {
        return [
            'name',
            'summary',
        ];
    }
    
    // DEPRECATED: Single category - kept for backward compatibility
    public function navLinks(): HasMany
    {
        return $this->hasMany(NavLink::class);
    }
    
    // NEW: Multiple categories relationship
    public function navLinksMany(): BelongsToMany
    {
        return $this->belongsToMany(NavLink::class)->withTimestamps();
    }
    
    // NEW: Multiple items per category
    public function items(): HasMany
    {
        return $this->hasMany(CategoryItem::class)->orderBy('position');
    }
    
    // Sections are CategoryItems - alias for backward compatibility
    public function sections(): HasMany
    {
        return $this->items(); // CategoryItems ARE sections
    }
    
    // Polymorphic many-to-many relationships for various models
    public function bookPages(): MorphToMany
    {
        return $this->morphedByMany(BookPage::class, 'categorizable', 'category_model')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }
    
    public function codeSummaries(): MorphToMany
    {
        return $this->morphedByMany(CodeSummary::class, 'categorizable', 'category_model')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }
    
    public function rooms(): MorphToMany
    {
        return $this->morphedByMany(Room::class, 'categorizable', 'category_model')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }
    
    public function certificates(): MorphToMany
    {
        return $this->morphedByMany(Certificate::class, 'categorizable', 'category_model')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }
    
    public function courses(): MorphToMany
    {
        return $this->morphedByMany(Course::class, 'categorizable', 'category_model')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }
}
