<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('category_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('nav_link_id')->nullable()->constrained('nav_links')->onDelete('set null'); // Link to subcategory (sub-nav)
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->string('image_path')->nullable();
            $table->string('url')->nullable();
            $table->text('summary')->nullable();
            $table->string('download_url')->nullable();
            $table->string('view_url')->nullable();
            $table->string('visit_url')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
            
            // Indexes for performance
            $table->index('user_id');
            $table->index('category_id');
            $table->index('nav_link_id');
            $table->index('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
            Schema::dropIfExists('category_items');
    }
};
