<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    protected $fillable = [
        'section_id',
        'name',
        'page',
        'order',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];
    
    /**
     * Get all active sections for a specific page, ordered
     */
    public static function getActiveForPage(string $page = 'home')
    {
        return static::where('page', $page)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }
    
    /**
     * Get sections as array for dropdowns (id => name)
     */
    public static function getForDropdown(string $page = 'home')
    {
        return static::getActiveForPage($page)
            ->mapWithKeys(function ($section) {
                return [$section->section_id => $section->name];
            })
            ->toArray();
    }
}
