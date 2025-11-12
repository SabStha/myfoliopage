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
                // Convert array to JSON
                $translations = [
                    'en' => $data[$field]['en'] ?? '',
                    'ja' => $data[$field]['ja'] ?? '',
                ];
                $data[$field] = json_encode($translations);
            } elseif (isset($data[$field]) && is_string($data[$field])) {
                // If it's already a string (legacy), convert to JSON
                $data[$field] = json_encode([
                    'en' => $data[$field],
                    'ja' => '',
                ]);
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





