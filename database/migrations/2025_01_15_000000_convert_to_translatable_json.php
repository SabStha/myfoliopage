<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration converts existing string fields to JSON format
     * Example: "Projects" becomes {"en": "Projects", "ja": ""}
     */
    public function up(): void
    {
        // Convert hero_sections translatable fields
        $this->convertTableToJson('hero_sections', [
            'badge_text',
            'heading_text',
            'subheading_text',
            'button1_text',
            'button2_text',
        ]);
        
        // Convert certificates translatable fields
        $this->convertTableToJson('certificates', [
            'title',
            'provider',
            'learning_outcomes',
            'reflection',
        ]);
        
        // Convert courses translatable fields
        $this->convertTableToJson('courses', [
            'title',
            'provider',
            'key_skills',
            'module_outline',
            'takeaways',
        ]);
        
        // Convert rooms translatable fields
        $this->convertTableToJson('rooms', [
            'title',
            'description',
            'summary',
        ]);
        
        // Convert category_items translatable fields
        $this->convertTableToJson('category_items', [
            'title',
            'summary',
        ]);
        
        // Convert blogs translatable fields
        if (Schema::hasTable('blogs')) {
            $this->convertTableToJson('blogs', [
                'title',
                'excerpt',
                'content',
            ]);
        }
        
        // Convert categories translatable fields
        $this->convertTableToJson('categories', [
            'name',
            'summary',
        ]);
        
        // Convert home_page_sections translatable fields
        if (Schema::hasTable('home_page_sections')) {
            $this->convertTableToJson('home_page_sections', [
                'title',
                'subtitle',
            ]);
        }
        
        // Convert engagement_sections translatable fields
        if (Schema::hasTable('engagement_sections')) {
            $this->convertTableToJson('engagement_sections', [
                'title',
                'hint',
            ]);
        }
        
        // Convert projects translatable fields
        if (Schema::hasTable('projects')) {
            $this->convertTableToJson('projects', [
                'title',
                'summary',
            ]);
        }
        
        // Convert testimonials translatable fields
        if (Schema::hasTable('testimonials')) {
            $this->convertTableToJson('testimonials', [
                'quote',
                'company',
            ]);
        }
        
        // Convert book_pages translatable fields
        if (Schema::hasTable('book_pages')) {
            $this->convertTableToJson('book_pages', [
                'title',
                'content',
                'summary',
            ]);
        }
        
        // Convert code_summaries translatable fields
        if (Schema::hasTable('code_summaries')) {
            $this->convertTableToJson('code_summaries', [
                'title',
                'summary',
            ]);
        }
    }
    
    /**
     * Convert string fields to JSON format
     */
    private function convertTableToJson(string $table, array $fields): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }
        
        $records = DB::table($table)->get();
        
        foreach ($records as $record) {
            $updates = [];
            
            foreach ($fields as $field) {
                if (!Schema::hasColumn($table, $field)) {
                    continue;
                }
                
                $value = $record->$field;
                
                // Skip if already JSON or null
                if (is_null($value) || $this->isJson($value)) {
                    continue;
                }
                
                // Convert to JSON format
                $updates[$field] = json_encode([
                    'en' => $value,
                    'ja' => '', // Empty for now, can be filled later
                ]);
            }
            
            if (!empty($updates)) {
                DB::table($table)
                    ->where('id', $record->id)
                    ->update($updates);
            }
        }
    }
    
    /**
     * Check if string is JSON
     */
    private function isJson($string): bool
    {
        if (!is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    /**
     * Reverse the migrations.
     * 
     * Convert JSON back to English strings (lossy - Japanese data will be lost)
     */
    public function down(): void
    {
        // Convert hero_sections back
        $this->convertJsonToString('hero_sections', [
            'badge_text',
            'heading_text',
            'subheading_text',
            'button1_text',
            'button2_text',
        ]);
        
        // Convert certificates back
        $this->convertJsonToString('certificates', [
            'title',
            'provider',
            'learning_outcomes',
            'reflection',
        ]);
        
        // Convert courses back
        $this->convertJsonToString('courses', [
            'title',
            'provider',
            'key_skills',
            'module_outline',
            'takeaways',
        ]);
        
        // Convert rooms back
        $this->convertJsonToString('rooms', [
            'title',
            'description',
            'summary',
        ]);
        
        // Convert category_items back
        $this->convertJsonToString('category_items', [
            'title',
            'summary',
        ]);
        
        // Convert blogs back
        if (Schema::hasTable('blogs')) {
            $this->convertJsonToString('blogs', [
                'title',
                'excerpt',
                'content',
            ]);
        }
        
        // Convert categories back
        $this->convertJsonToString('categories', [
            'name',
            'summary',
        ]);
        
        // Convert home_page_sections back
        if (Schema::hasTable('home_page_sections')) {
            $this->convertJsonToString('home_page_sections', [
                'title',
                'subtitle',
            ]);
        }
        
        // Convert engagement_sections back
        if (Schema::hasTable('engagement_sections')) {
            $this->convertJsonToString('engagement_sections', [
                'title',
                'hint',
            ]);
        }
        
        // Convert projects back
        if (Schema::hasTable('projects')) {
            $this->convertJsonToString('projects', [
                'title',
                'summary',
            ]);
        }
        
        // Convert testimonials back
        if (Schema::hasTable('testimonials')) {
            $this->convertJsonToString('testimonials', [
                'quote',
                'company',
            ]);
        }
        
        // Convert book_pages back
        if (Schema::hasTable('book_pages')) {
            $this->convertJsonToString('book_pages', [
                'title',
                'content',
                'summary',
            ]);
        }
        
        // Convert code_summaries back
        if (Schema::hasTable('code_summaries')) {
            $this->convertJsonToString('code_summaries', [
                'title',
                'summary',
            ]);
        }
    }
    
    /**
     * Convert JSON fields back to English strings
     */
    private function convertJsonToString(string $table, array $fields): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }
        
        $records = DB::table($table)->get();
        
        foreach ($records as $record) {
            $updates = [];
            
            foreach ($fields as $field) {
                if (!Schema::hasColumn($table, $field)) {
                    continue;
                }
                
                $value = $record->$field;
                
                // If JSON, extract English value
                if ($this->isJson($value)) {
                    $decoded = json_decode($value, true);
                    $updates[$field] = $decoded['en'] ?? $value;
                }
            }
            
            if (!empty($updates)) {
                DB::table($table)
                    ->where('id', $record->id)
                    ->update($updates);
            }
        }
    }
};

