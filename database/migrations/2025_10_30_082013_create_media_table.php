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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('type')->nullable(); // image, pdf, video, link
            $table->string('path')->nullable(); // storage path or URL
            $table->unsignedBigInteger('mediable_id');
            $table->string('mediable_type');
            $table->timestamps();
            $table->index(['mediable_id', 'mediable_type'], 'media_morph_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
