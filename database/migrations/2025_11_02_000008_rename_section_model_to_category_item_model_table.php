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
     * Renames section_model table to category_item_model to use CategoryItems as Sections
     */
    public function up(): void
    {
        // Check if section_model table exists
        if (Schema::hasTable('section_model')) {
            // For SQLite, we need to recreate the table
            if (config('database.default') === 'sqlite') {
                // Create new table
                Schema::create('category_item_model', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('category_item_id')->constrained('category_items')->onDelete('cascade');
                    $table->morphs('sectionable');
                    $table->unsignedInteger('position')->default(0);
                    $table->timestamps();
                    $table->unique(['category_item_id', 'sectionable_id', 'sectionable_type'], 'category_item_model_unique');
                });
                
                // Copy data if any exists
                if (Schema::hasColumn('section_model', 'section_id')) {
                    DB::statement('INSERT INTO category_item_model (id, category_item_id, sectionable_id, sectionable_type, position, created_at, updated_at) 
                                    SELECT id, section_id, sectionable_id, sectionable_type, position, created_at, updated_at FROM section_model');
                }
                
                // Drop old table
                Schema::dropIfExists('section_model');
            } else {
                // For other databases, use rename
                // First, drop the foreign key before renaming (using original table name)
                try {
                    // Try to find and drop the foreign key constraint
                    $foreignKeys = DB::select("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'section_model' 
                        AND COLUMN_NAME = 'section_id' 
                        AND REFERENCED_TABLE_NAME IS NOT NULL
                    ");
                    
                    if (!empty($foreignKeys)) {
                        $foreignKeyName = $foreignKeys[0]->CONSTRAINT_NAME;
                        Schema::table('section_model', function (Blueprint $table) use ($foreignKeyName) {
                            $table->dropForeign($foreignKeyName);
                        });
                    }
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
                }
                
                // Drop unique constraint before renaming
                try {
                    Schema::table('section_model', function (Blueprint $table) {
                        $table->dropUnique('section_model_unique');
                    });
                } catch (\Exception $e) {
                    // Unique constraint might not exist, continue
                }
                
                // Rename table
                Schema::rename('section_model', 'category_item_model');
                
                // Rename column
                Schema::table('category_item_model', function (Blueprint $table) {
                    $table->renameColumn('section_id', 'category_item_id');
                });
                
                // Add new foreign key
                Schema::table('category_item_model', function (Blueprint $table) {
                    $table->foreign('category_item_id')->references('id')->on('category_items')->onDelete('cascade');
                });
                
                // Add new unique constraint
                Schema::table('category_item_model', function (Blueprint $table) {
                    $table->unique(['category_item_id', 'sectionable_id', 'sectionable_type'], 'category_item_model_unique');
                });
            }
        } else {
            // Table doesn't exist, create it fresh
            Schema::create('category_item_model', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_item_id')->constrained('category_items')->onDelete('cascade');
                $table->morphs('sectionable');
                $table->unsignedInteger('position')->default(0);
                $table->timestamps();
                $table->unique(['category_item_id', 'sectionable_id', 'sectionable_type'], 'category_item_model_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('category_item_model', function (Blueprint $table) {
            $table->dropForeign(['category_item_id']);
            $table->dropUnique('category_item_model_unique');
            $table->renameColumn('category_item_id', 'section_id');
        });
        
        Schema::rename('category_item_model', 'section_model');
        
        Schema::table('section_model', function (Blueprint $table) {
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
            $table->unique(['section_id', 'sectionable_id', 'sectionable_type'], 'section_model_unique');
        });
    }
};

