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
        Schema::table('code_summaries', function (Blueprint $table) {
            // Context & Purpose
            $table->string('problem_statement')->nullable()->after('summary'); // One-sentence description of what the code solves
            $table->text('learning_goal')->nullable()->after('problem_statement'); // What skill or concept you practiced
            $table->text('use_case')->nullable()->after('learning_goal'); // Where this code is used (mini-project, exercise, bug fix)
            
            // Proof & Reproducibility
            $table->text('how_to_run')->nullable()->after('use_case'); // Exact commands or environment setup
            $table->text('expected_output')->nullable()->after('how_to_run'); // Result screenshot, console output, or test report
            $table->string('dependencies')->nullable()->after('expected_output'); // Language version, OS, packages
            $table->string('test_status')->nullable()->after('dependencies'); // Test status / Lint status
            
            // Evaluation & Reflection
            $table->text('complexity_notes')->nullable()->after('test_status'); // O(n), runtime, memory, or benchmark summary
            $table->text('security_notes')->nullable()->after('complexity_notes'); // Potential risks, input validation, or error handling
            $table->text('reflection')->nullable()->after('security_notes'); // What worked, what you'd improve
            
            // Traceability
            $table->string('commit_sha')->nullable()->after('reflection'); // Repository commit SHA
            $table->string('license')->nullable()->after('commit_sha'); // License / Ownership
            $table->string('file_path_repo')->nullable()->after('license'); // File path in repo
            
            // Metadata Improvements
            $table->string('framework')->nullable()->after('file_path_repo'); // Framework / Library (Laravel, React, etc.)
            $table->enum('difficulty', ['Beginner', 'Intermediate', 'Advanced'])->nullable()->after('framework');
            $table->integer('time_spent')->nullable()->after('difficulty'); // Minutes
            $table->enum('status', ['completed', 'in_progress'])->default('completed')->after('time_spent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('code_summaries', function (Blueprint $table) {
            $table->dropColumn([
                'problem_statement',
                'learning_goal',
                'use_case',
                'how_to_run',
                'expected_output',
                'dependencies',
                'test_status',
                'complexity_notes',
                'security_notes',
                'reflection',
                'commit_sha',
                'license',
                'file_path_repo',
                'framework',
                'difficulty',
                'time_spent',
                'status',
            ]);
        });
    }
};


