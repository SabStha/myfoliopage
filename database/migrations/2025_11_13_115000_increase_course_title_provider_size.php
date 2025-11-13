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
        Schema::table('courses', function (Blueprint $table) {
            // Change title and provider from string (VARCHAR 255) to text to accommodate JSON translations
            $table->text('title')->change();
            $table->text('provider')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Revert back to string (VARCHAR 255)
            $table->string('title')->change();
            $table->string('provider')->nullable()->change();
        });
    }
};

