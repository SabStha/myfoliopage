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
        Schema::table('book_pages', function (Blueprint $table) {
            // Learning outcomes & proof
            $table->text('key_objectives')->nullable()->after('summary'); // Bullet list of objectives
            $table->text('reflection')->nullable()->after('key_objectives'); // 2-4 sentences reflection
            $table->text('applied_snippet')->nullable()->after('reflection'); // Code or screenshot link
            $table->string('references')->nullable()->after('applied_snippet'); // ISBN / Page Range
            
            // Reproducibility
            $table->text('how_to_run')->nullable()->after('references'); // Exact commands or steps
            $table->text('result_evidence')->nullable()->after('how_to_run'); // Expected output, screenshot, or link
            $table->enum('difficulty', ['Beginner', 'Intermediate', 'Advanced'])->nullable()->after('result_evidence');
            $table->integer('time_spent')->nullable()->after('difficulty'); // Minutes
            
            // Status
            $table->enum('status', ['completed', 'in_progress'])->default('completed')->after('time_spent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_pages', function (Blueprint $table) {
            $table->dropColumn([
                'key_objectives',
                'reflection',
                'applied_snippet',
                'references',
                'how_to_run',
                'result_evidence',
                'difficulty',
                'time_spent',
                'status',
            ]);
        });
    }
};
