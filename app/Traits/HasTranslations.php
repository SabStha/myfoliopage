<?php

namespace App\Traits;

trait HasTranslations
{
    /**
     * Get translated value for a field based on current locale
     * 
     * @param string $field Field name
     * @param string|null $locale Locale (defaults to app locale)
     * @param mixed $default Default value if translation not found
     * @return mixed
     */
    public function getTranslated($field, $locale = null, $default = null)
    {
        $locale = $locale ?? app()->getLocale();
        $value = $this->$field;
        
        // If value is already a string (legacy data), return it for English
        if (is_string($value) && !$this->isJson($value)) {
            return $locale === 'en' ? $value : ($default ?? $value);
        }
        
        // Try to decode JSON
        $translations = is_string($value) ? json_decode($value, true) : $value;
        
        // If not an array, return as-is (fallback)
        if (!is_array($translations)) {
            return $value ?? $default;
        }
        
        // Return translation for current locale, fallback to English, then default
        return $translations[$locale] 
            ?? $translations['en'] 
            ?? $default 
            ?? '';
    }
    
    /**
     * Set translated value for a field
     * 
     * @param string $field Field name
     * @param string $locale Locale
     * @param mixed $value Value to set
     * @return void
     */
    public function setTranslated($field, $locale, $value)
    {
        $current = $this->$field;
        
        // Decode existing JSON or create new array
        $translations = is_string($current) && $this->isJson($current) 
            ? json_decode($current, true) 
            : (is_array($current) ? $current : ['en' => $current ?? '']);
        
        // Update translation
        $translations[$locale] = $value;
        
        // Save as JSON
        $this->$field = json_encode($translations);
    }
    
    /**
     * Get all translations for a field
     * 
     * @param string $field Field name
     * @return array
     */
    public function getTranslations($field)
    {
        $value = $this->$field;
        
        if (is_string($value) && $this->isJson($value)) {
            return json_decode($value, true) ?? [];
        }
        
        if (is_array($value)) {
            return $value;
        }
        
        // Legacy: return as English
        return ['en' => $value ?? ''];
    }
    
    /**
     * Check if string is JSON
     * 
     * @param string $string
     * @return bool
     */
    private function isJson($string)
    {
        if (!is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    /**
     * Accessor: Automatically translate fields when accessed
     * Override in model to specify which fields are translatable
     */
    protected function getTranslatableFields(): array
    {
        return [];
    }
}




