<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds fields to link CategoryItems to specific models (BookPage, CodeSummary, Room, Certificate, Course)
     */
    public function up(): void
    {
        Schema::table('category_items', function (Blueprint $table) {
            $table->string('linked_model_type')->nullable()->after('nav_link_id'); // e.g., 'App\Models\BookPage'
            $table->unsignedBigInteger('linked_model_id')->nullable()->after('linked_model_type'); // ID of the linked model
            $table->index(['linked_model_type', 'linked_model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('category_items', function (Blueprint $table) {
            $table->dropIndex(['linked_model_type', 'linked_model_id']);
            $table->dropColumn(['linked_model_type', 'linked_model_id']);
        });
    }
};






