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
        if (!Schema::hasTable('courses')) {
            return;
        }
        
        Schema::table('courses', function (Blueprint $table) {
            // Learning & Scope
            $table->string('course_url')->nullable()->after('provider'); // Provider Course URL
            $table->string('instructor_organization')->nullable()->after('course_url'); // Instructor / Organization
            $table->string('difficulty')->nullable()->after('instructor_organization'); // Beginner/Intermediate/Advanced
            $table->string('estimated_hours')->nullable()->after('difficulty'); // e.g., "12-15 hours"
            $table->text('prerequisites')->nullable()->after('estimated_hours'); // What you knew going in
            
            // Skills & Syllabus
            $table->text('key_skills')->nullable()->after('prerequisites'); // Bulleted, 3-8 items
            $table->text('module_outline')->nullable()->after('key_skills'); // 5-10 short items
            $table->text('assessments_grading')->nullable()->after('module_outline'); // Quizzes, labs, capstone, score
            
            // Evidence & Reproducibility
            $table->text('artifacts_assignments')->nullable()->after('assessments_grading'); // Links to repos, gists, reports, demos
            $table->string('highlight_project_title')->nullable()->after('artifacts_assignments'); // Highlight Project Title
            $table->text('highlight_project_goal')->nullable()->after('highlight_project_title'); // 1-sentence goal
            $table->string('highlight_project_link')->nullable()->after('highlight_project_goal'); // Repo/demo link
            $table->string('proof_completion_url')->nullable()->after('highlight_project_link'); // Public badge link or certificate URL
            
            // Reflection & Impact
            $table->text('takeaways')->nullable()->after('proof_completion_url'); // 2-4 sentences
            $table->text('applied_in')->nullable()->after('takeaways'); // Where you used it with link
            $table->text('next_actions')->nullable()->after('applied_in'); // Precise follow-ups
            
            // Traceability & Admin
            $table->string('status')->default('in_progress')->after('next_actions'); // In Progress / Completed / Retired
            $table->integer('completion_percent')->nullable()->after('status'); // 0-100 if in progress
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if table exists before trying to alter it
        if (!Schema::hasTable('courses')) {
            return;
        }
        
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'course_url',
                'instructor_organization',
                'difficulty',
                'estimated_hours',
                'prerequisites',
                'key_skills',
                'module_outline',
                'assessments_grading',
                'artifacts_assignments',
                'highlight_project_title',
                'highlight_project_goal',
                'highlight_project_link',
                'proof_completion_url',
                'takeaways',
                'applied_in',
                'next_actions',
                'status',
                'completion_percent',
            ]);
        });
    }
};




