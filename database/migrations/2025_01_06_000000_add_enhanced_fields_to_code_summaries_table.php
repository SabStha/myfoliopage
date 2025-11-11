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
        // Check if table exists before trying to alter it
        if (!Schema::hasTable('code_summaries')) {
            return;
        }
        
        Schema::table('code_summaries', function (Blueprint $table) {
            // Context & Purpose
            if (!Schema::hasColumn('code_summaries', 'problem_statement')) {
                $table->string('problem_statement')->nullable()->after('summary');
            }
            if (!Schema::hasColumn('code_summaries', 'learning_goal')) {
                $table->text('learning_goal')->nullable()->after('problem_statement');
            }
            if (!Schema::hasColumn('code_summaries', 'use_case')) {
                $table->text('use_case')->nullable()->after('learning_goal');
            }
            
            // Proof & Reproducibility
            if (!Schema::hasColumn('code_summaries', 'how_to_run')) {
                $table->text('how_to_run')->nullable()->after('use_case');
            }
            if (!Schema::hasColumn('code_summaries', 'expected_output')) {
                $table->text('expected_output')->nullable()->after('how_to_run');
            }
            if (!Schema::hasColumn('code_summaries', 'dependencies')) {
                $table->string('dependencies')->nullable()->after('expected_output');
            }
            if (!Schema::hasColumn('code_summaries', 'test_status')) {
                $table->string('test_status')->nullable()->after('dependencies');
            }
            
            // Evaluation & Reflection
            if (!Schema::hasColumn('code_summaries', 'complexity_notes')) {
                $table->text('complexity_notes')->nullable()->after('test_status');
            }
            if (!Schema::hasColumn('code_summaries', 'security_notes')) {
                $table->text('security_notes')->nullable()->after('complexity_notes');
            }
            if (!Schema::hasColumn('code_summaries', 'reflection')) {
                $table->text('reflection')->nullable()->after('security_notes');
            }
            
            // Traceability
            if (!Schema::hasColumn('code_summaries', 'commit_sha')) {
                $table->string('commit_sha')->nullable()->after('reflection');
            }
            if (!Schema::hasColumn('code_summaries', 'license')) {
                $table->string('license')->nullable()->after('commit_sha');
            }
            if (!Schema::hasColumn('code_summaries', 'file_path_repo')) {
                $table->string('file_path_repo')->nullable()->after('license');
            }
            
            // Metadata Improvements
            if (!Schema::hasColumn('code_summaries', 'framework')) {
                $table->string('framework')->nullable()->after('file_path_repo');
            }
            if (!Schema::hasColumn('code_summaries', 'difficulty')) {
                $table->enum('difficulty', ['Beginner', 'Intermediate', 'Advanced'])->nullable()->after('framework');
            }
            if (!Schema::hasColumn('code_summaries', 'time_spent')) {
                $table->integer('time_spent')->nullable()->after('difficulty');
            }
            if (!Schema::hasColumn('code_summaries', 'status')) {
                $table->enum('status', ['completed', 'in_progress'])->default('completed')->after('time_spent');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if table exists before trying to alter it
        if (!Schema::hasTable('code_summaries')) {
            return;
        }
        
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




