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
        Schema::create('ongoing_progress_items', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->string('unit')->nullable();
            $table->integer('value')->default(0);
            $table->integer('goal')->default(100);
            $table->string('link')->nullable();
            $table->string('eta')->nullable();
            $table->integer('trend_amount')->nullable();
            $table->string('trend_window')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ongoing_progress_items');
    }
};
