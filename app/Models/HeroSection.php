<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class HeroSection extends Model
{
    use HasTranslations;
    protected $fillable = [
        'user_id', 'background_color',
        'badge_text',
        'badge_color',
        'badge_size_mobile',
        'badge_size_tablet',
        'badge_size_desktop',
        'badge_text_color',
        'heading_text',
        'heading_size_mobile',
        'heading_size_tablet',
        'heading_size_desktop',
        'heading_text_color',
        'subheading_text',
        'subheading_text_color',
        'button1_text',
        'button1_link',
        'button1_bg_color',
        'button1_text_color',
        'button1_visible',
        'button2_text',
        'button2_link',
        'button2_bg_color',
        'button2_text_color',
        'button2_border_color',
        'button2_visible',
        'nav_visible',
        'navigation_text_color',
        'navigation_links',
        'blob_color',
        'blob_visible',
        'image_rotation_interval',
        'layout_reversed',
        'text_horizontal_offset',
        'image_horizontal_offset',
        'badge_horizontal_offset',
        'blob_media_horizontal_offset',
        'blob_media_vertical_offset',
    ];

    protected $casts = [
        'button1_visible' => 'boolean',
        'button2_visible' => 'boolean',
        'nav_visible' => 'boolean',
        'blob_visible' => 'boolean',
        'layout_reversed' => 'boolean',
        'image_rotation_interval' => 'integer',
        'text_horizontal_offset' => 'integer',
        'image_horizontal_offset' => 'integer',
        'badge_horizontal_offset' => 'integer',
        'blob_media_horizontal_offset' => 'integer',
        'blob_media_vertical_offset' => 'integer',
        'navigation_links' => 'array',
        // Cast translatable fields as JSON
        'badge_text' => 'array',
        'heading_text' => 'array',
        'subheading_text' => 'array',
        'button1_text' => 'array',
        'button2_text' => 'array',
    ];
    
    /**
     * Get translatable fields
     */
    protected function getTranslatableFields(): array
    {
        return [
            'badge_text',
            'heading_text',
            'subheading_text',
            'button1_text',
            'button2_text',
        ];
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }
    
    /**
     * Get translated navigation links
     */
    public function getTranslatedNavigationLinks($locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        $links = $this->navigation_links ?? [];
        
        if (!is_array($links)) {
            return [];
        }
        
        // Handle translation of navigation link texts
        return array_map(function($link) use ($locale) {
            if (!is_array($link)) {
                return $link;
            }
            
            if (isset($link['text'])) {
                // If text is an array (new format), use the appropriate language key
                if (is_array($link['text'])) {
                    // For the requested locale, prefer that language, fallback to English, then Japanese
                    if ($locale === 'ja') {
                        $link['text'] = $link['text']['ja'] ?? $link['text']['en'] ?? '';
                    } else {
                        $link['text'] = $link['text']['en'] ?? $link['text']['ja'] ?? '';
                    }
                }
                // If text is a string (old format), keep it as is for backward compatibility
                // The old format will be normalized when editing in admin
            }
            return $link;
        }, $links);
    }
}
