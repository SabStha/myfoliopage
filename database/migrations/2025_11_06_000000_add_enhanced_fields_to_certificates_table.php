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
        if (!Schema::hasTable('certificates')) {
            return;
        }
        
        Schema::table('certificates', function (Blueprint $table) {
            // Proof & Verification
            $table->date('expiry_date')->nullable()->after('issued_at');
            $table->boolean('has_expiry')->default(false)->after('expiry_date');
            
            // Context & Impact
            $table->string('issued_by')->nullable()->after('provider'); // Instructor/Org
            $table->string('level')->nullable()->after('issued_by'); // Beginner/Intermediate/Advanced
            $table->integer('learning_hours')->nullable()->after('level'); // Hours spent
            $table->text('learning_outcomes')->nullable()->after('learning_hours'); // Key topics learned
            $table->text('reflection')->nullable()->after('learning_outcomes'); // How you applied it
            
            // Traceability & Portfolio Integration
            $table->string('status')->default('completed')->after('reflection'); // completed/in_progress
            $table->unsignedBigInteger('project_id')->nullable()->after('status'); // Link to project
            
            // Add foreign key for project
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if table exists before trying to alter it
        if (!Schema::hasTable('certificates')) {
            return;
        }
        
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn([
                'expiry_date',
                'has_expiry',
                'issued_by',
                'level',
                'learning_hours',
                'learning_outcomes',
                'reflection',
                'status',
                'project_id',
            ]);
        });
    }
};




