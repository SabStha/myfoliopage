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
        Schema::create('nav_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nav_item_id')->constrained('nav_items')->cascadeOnDelete();
            $table->string('title');
            $table->string('url')->nullable();
            $table->string('proof_url')->nullable();
            $table->unsignedInteger('progress')->nullable();
            $table->date('issued_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nav_links');
    }
};
