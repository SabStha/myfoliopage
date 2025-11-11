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
        Schema::create('nav_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->string('label');
            $table->string('slug')->nullable();
            $table->string('route')->nullable();
            $table->string('url')->nullable();
            $table->string('active_pattern')->nullable();
            $table->text('icon_svg')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('visible')->default(true);
            $table->timestamps();
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nav_items');
    }
};
