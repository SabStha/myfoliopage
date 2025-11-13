<?php

namespace App\Traits;

trait HandlesTranslations
{
    /**
     * Process translation fields from request
     * Converts array format (title[en], title[ja]) to JSON
     * 
     * @param array $data Request data
     * @param array $translatableFields Fields that should be translated
     * @return array Processed data
     */
    protected function processTranslations(array $data, array $translatableFields): array
    {
        foreach ($translatableFields as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                // Normalize array format - ensure en and ja keys exist
                // Don't JSON encode here - let Laravel's array cast handle it
                $translations = [
                    'en' => $data[$field]['en'] ?? '',
                    'ja' => $data[$field]['ja'] ?? '',
                ];
                $data[$field] = $translations;
            } elseif (isset($data[$field]) && is_string($data[$field])) {
                // If it's already a string (legacy), convert to array format
                // Laravel's array cast will JSON encode it automatically
                $data[$field] = [
                    'en' => $data[$field],
                    'ja' => '',
                ];
            }
        }
        
        return $data;
    }
    
    /**
     * Get translatable fields for a model
     * Override in controller if needed
     */
    protected function getTranslatableFields(): array
    {
        return [];
    }
}











