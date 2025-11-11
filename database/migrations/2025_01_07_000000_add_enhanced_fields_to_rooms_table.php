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
        Schema::table('rooms', function (Blueprint $table) {
            // Learning & Purpose
            $table->text('objective_goal')->nullable()->after('summary'); // What you intended to learn
            $table->text('key_techniques_used')->nullable()->after('objective_goal'); // Concrete skills gained
            $table->text('tools_commands_used')->nullable()->after('key_techniques_used'); // Tools/commands for reproducibility
            $table->text('attack_vector_summary')->nullable()->after('tools_commands_used'); // Thought process (enumeration → exploit → priv-esc)
            $table->text('flag_evidence_proof')->nullable()->after('attack_vector_summary'); // user.txt and root.txt hashes or screenshots
            $table->integer('time_spent')->nullable()->after('flag_evidence_proof'); // Minutes
            $table->text('reflection_takeaways')->nullable()->after('time_spent'); // Why it mattered / what you'd do differently
            $table->string('difficulty_confirmation')->nullable()->after('reflection_takeaways'); // Self-rated + official room rating
            
            // Reproducibility
            $table->text('walkthrough_summary_steps')->nullable()->after('difficulty_confirmation'); // Main steps in attack chain
            $table->text('tools_environment')->nullable()->after('walkthrough_summary_steps'); // OS, VPN, browser extensions, notes software
            $table->text('command_log_snippet')->nullable()->after('tools_environment'); // Top 5-10 lines showing key commands
            $table->string('room_id_author')->nullable()->after('command_log_snippet'); // Official name or link for reference
            $table->string('completion_screenshot_report_link')->nullable()->after('room_id_author'); // Attach or link proof
            
            // Traceability & Meta
            $table->string('platform_username')->nullable()->after('completion_screenshot_report_link'); // Your handle for validation
            $table->string('platform_profile_link')->nullable()->after('platform_username'); // Profile URL
            $table->enum('status', ['completed', 'in_progress', 'retired'])->default('in_progress')->after('platform_profile_link');
            $table->integer('score_points_earned')->nullable()->after('status'); // e.g., TryHackMe 20 pts, HTB 30 pts
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
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


