<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Section extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'position',
        'description',
    ];
    
    protected $casts = [
        'position' => 'integer',
    ];
    
    /**
     * Get the category that owns this section
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    
    /**
     * Get all book pages in this section
     */
    public function bookPages(): MorphToMany
    {
        return $this->morphToMany(BookPage::class, 'sectionable', 'section_model')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }
    
    /**
     * Get all code summaries in this section
     */
    public function codeSummaries(): MorphToMany
    {
        return $this->morphToMany(CodeSummary::class, 'sectionable', 'section_model')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }
    
    /**
     * Get all rooms in this section
     */
    public function rooms(): MorphToMany
    {
        return $this->morphToMany(Room::class, 'sectionable', 'section_model')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }
    
    /**
     * Get all certificates in this section
     */
    public function certificates(): MorphToMany
    {
        return $this->morphToMany(Certificate::class, 'sectionable', 'section_model')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }
    
    /**
     * Get all courses in this section
     */
    public function courses(): MorphToMany
    {
        return $this->morphToMany(Course::class, 'sectionable', 'section_model')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position');
    }
}











