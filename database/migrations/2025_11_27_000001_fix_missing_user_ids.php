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
        // Fix BookPages table
        if (Schema::hasTable('book_pages')) {
            Schema::table('book_pages', function (Blueprint $table) {
                if (!Schema::hasColumn('book_pages', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
                }
                
                // Ensure enhanced fields exist (safety check)
                if (!Schema::hasColumn('book_pages', 'key_objectives')) {
                    $table->text('key_objectives')->nullable()->after('summary');
                }
                if (!Schema::hasColumn('book_pages', 'reflection')) {
                    $table->text('reflection')->nullable()->after('key_objectives');
                }
                if (!Schema::hasColumn('book_pages', 'applied_snippet')) {
                    $table->text('applied_snippet')->nullable()->after('reflection');
                }
                if (!Schema::hasColumn('book_pages', 'references')) {
                    $table->string('references')->nullable()->after('applied_snippet');
                }
                if (!Schema::hasColumn('book_pages', 'how_to_run')) {
                    $table->text('how_to_run')->nullable()->after('references');
                }
                if (!Schema::hasColumn('book_pages', 'result_evidence')) {
                    $table->text('result_evidence')->nullable()->after('how_to_run');
                }
                if (!Schema::hasColumn('book_pages', 'difficulty')) {
                    $table->enum('difficulty', ['Beginner', 'Intermediate', 'Advanced'])->nullable()->after('result_evidence');
                }
                if (!Schema::hasColumn('book_pages', 'time_spent')) {
                    $table->integer('time_spent')->nullable()->after('difficulty');
                }
                if (!Schema::hasColumn('book_pages', 'status')) {
                    $table->enum('status', ['completed', 'in_progress'])->default('completed')->after('time_spent');
                }
            });
        }

        // Fix CodeSummaries table
        if (Schema::hasTable('code_summaries')) {
            Schema::table('code_summaries', function (Blueprint $table) {
                if (!Schema::hasColumn('code_summaries', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
                }
                
                // Ensure enhanced fields exist (safety check)
                if (!Schema::hasColumn('code_summaries', 'problem_statement')) {
                    $table->text('problem_statement')->nullable()->after('file_path');
                }
                if (!Schema::hasColumn('code_summaries', 'learning_goal')) {
                    $table->text('learning_goal')->nullable()->after('problem_statement');
                }
                if (!Schema::hasColumn('code_summaries', 'use_case')) {
                    $table->text('use_case')->nullable()->after('learning_goal');
                }
                if (!Schema::hasColumn('code_summaries', 'how_to_run')) {
                    $table->text('how_to_run')->nullable()->after('use_case');
                }
                if (!Schema::hasColumn('code_summaries', 'expected_output')) {
                    $table->text('expected_output')->nullable()->after('how_to_run');
                }
                if (!Schema::hasColumn('code_summaries', 'dependencies')) {
                    $table->text('dependencies')->nullable()->after('expected_output');
                }
                if (!Schema::hasColumn('code_summaries', 'test_status')) {
                    $table->string('test_status')->nullable()->after('dependencies');
                }
                if (!Schema::hasColumn('code_summaries', 'complexity_notes')) {
                    $table->text('complexity_notes')->nullable()->after('test_status');
                }
                if (!Schema::hasColumn('code_summaries', 'security_notes')) {
                    $table->text('security_notes')->nullable()->after('complexity_notes');
                }
                if (!Schema::hasColumn('code_summaries', 'reflection')) {
                    $table->text('reflection')->nullable()->after('security_notes');
                }
                if (!Schema::hasColumn('code_summaries', 'commit_sha')) {
                    $table->string('commit_sha')->nullable()->after('reflection');
                }
                if (!Schema::hasColumn('code_summaries', 'license')) {
                    $table->string('license')->nullable()->after('commit_sha');
                }
                if (!Schema::hasColumn('code_summaries', 'file_path_repo')) {
                    $table->string('file_path_repo')->nullable()->after('license');
                }
                if (!Schema::hasColumn('code_summaries', 'framework')) {
                    $table->string('framework')->nullable()->after('file_path_repo');
                }
                if (!Schema::hasColumn('code_summaries', 'difficulty')) {
                    $table->string('difficulty')->nullable()->after('framework');
                }
                if (!Schema::hasColumn('code_summaries', 'time_spent')) {
                    $table->integer('time_spent')->nullable()->after('difficulty');
                }
                if (!Schema::hasColumn('code_summaries', 'status')) {
                    $table->string('status')->nullable()->after('time_spent');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('book_pages')) {
            Schema::table('book_pages', function (Blueprint $table) {
                if (Schema::hasColumn('book_pages', 'user_id')) {
                    $table->dropForeign(['user_id']);
                    $table->dropColumn('user_id');
                }
            });
        }

        if (Schema::hasTable('code_summaries')) {
            Schema::table('code_summaries', function (Blueprint $table) {
                if (Schema::hasColumn('code_summaries', 'user_id')) {
                    $table->dropForeign(['user_id']);
                    $table->dropColumn('user_id');
                }
            });
        }
    }
};
