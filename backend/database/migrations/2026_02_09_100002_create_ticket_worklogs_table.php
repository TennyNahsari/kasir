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
        Schema::create('ticket_worklogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            $table->enum('worklog_type', [
                'COMMENT',
                'STATUS_CHANGE',
                'ASSIGNMENT',
                'WORK_DONE',
                'ESCALATION'
            ])->default('COMMENT');
            
            $table->text('description');
            $table->integer('time_spent_minutes')->nullable(); // For tracking man-hours
            $table->boolean('is_internal')->default(false); // Internal notes vs public comments
            
            $table->timestamps();
            
            // Indexes
            $table->index('ticket_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_worklogs');
    }
};
