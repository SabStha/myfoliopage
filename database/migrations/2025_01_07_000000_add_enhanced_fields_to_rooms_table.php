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
        if (!Schema::hasTable('rooms')) {
            return;
        }
        
        Schema::table('rooms', function (Blueprint $table) {
            // Learning & Purpose
            if (!Schema::hasColumn('rooms', 'objective_goal')) {
                $table->text('objective_goal')->nullable()->after('summary');
            }
            if (!Schema::hasColumn('rooms', 'key_techniques_used')) {
                $table->text('key_techniques_used')->nullable()->after('objective_goal');
            }
            if (!Schema::hasColumn('rooms', 'tools_commands_used')) {
                $table->text('tools_commands_used')->nullable()->after('key_techniques_used');
            }
            if (!Schema::hasColumn('rooms', 'attack_vector_summary')) {
                $table->text('attack_vector_summary')->nullable()->after('tools_commands_used');
            }
            if (!Schema::hasColumn('rooms', 'flag_evidence_proof')) {
                $table->text('flag_evidence_proof')->nullable()->after('attack_vector_summary');
            }
            if (!Schema::hasColumn('rooms', 'time_spent')) {
                $table->integer('time_spent')->nullable()->after('flag_evidence_proof');
            }
            if (!Schema::hasColumn('rooms', 'reflection_takeaways')) {
                $table->text('reflection_takeaways')->nullable()->after('time_spent');
            }
            if (!Schema::hasColumn('rooms', 'difficulty_confirmation')) {
                $table->string('difficulty_confirmation')->nullable()->after('reflection_takeaways');
            }
            
            // Reproducibility
            if (!Schema::hasColumn('rooms', 'walkthrough_summary_steps')) {
                $table->text('walkthrough_summary_steps')->nullable()->after('difficulty_confirmation');
            }
            if (!Schema::hasColumn('rooms', 'tools_environment')) {
                $table->text('tools_environment')->nullable()->after('walkthrough_summary_steps');
            }
            if (!Schema::hasColumn('rooms', 'command_log_snippet')) {
                $table->text('command_log_snippet')->nullable()->after('tools_environment');
            }
            if (!Schema::hasColumn('rooms', 'room_id_author')) {
                $table->string('room_id_author')->nullable()->after('command_log_snippet');
            }
            if (!Schema::hasColumn('rooms', 'completion_screenshot_report_link')) {
                $table->string('completion_screenshot_report_link')->nullable()->after('room_id_author');
            }
            
            // Traceability & Meta
            if (!Schema::hasColumn('rooms', 'platform_username')) {
                $table->string('platform_username')->nullable()->after('completion_screenshot_report_link');
            }
            if (!Schema::hasColumn('rooms', 'platform_profile_link')) {
                $table->string('platform_profile_link')->nullable()->after('platform_username');
            }
            if (!Schema::hasColumn('rooms', 'status')) {
                $table->enum('status', ['completed', 'in_progress', 'retired'])->default('in_progress')->after('platform_profile_link');
            }
            if (!Schema::hasColumn('rooms', 'score_points_earned')) {
                $table->integer('score_points_earned')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if table exists before trying to alter it
        if (!Schema::hasTable('rooms')) {
            return;
        }
        
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn([
                'objective_goal',
                'key_techniques_used',
                'tools_commands_used',
                'attack_vector_summary',
                'flag_evidence_proof',
                'time_spent',
                'reflection_takeaways',
                'difficulty_confirmation',
                'walkthrough_summary_steps',
                'tools_environment',
                'command_log_snippet',
                'room_id_author',
                'completion_screenshot_report_link',
                'platform_username',
                'platform_profile_link',
                'status',
                'score_points_earned',
            ]);
        });
    }
};




