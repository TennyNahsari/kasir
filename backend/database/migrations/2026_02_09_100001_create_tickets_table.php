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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 50)->unique(); // TKT-2026-0001
            $table->enum('type', ['INCIDENT', 'MAINTENANCE'])->default('INCIDENT');
            
            // Relations
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            
            // Ticket Details
            $table->string('title', 200);
            $table->text('description');
            $table->enum('priority', ['NORMAL', 'HIGH'])->default('NORMAL');
            $table->enum('status', [
                'OPEN',
                'ASSIGNED',
                'IN_PROGRESS',
                'ON_HOLD',
                'RESOLVED',
                'CLOSED',
                'CANCELLED'
            ])->default('OPEN');
            $table->enum('category', ['HARDWARE', 'SOFTWARE', 'NETWORK', 'FACILITY', 'OTHER'])->nullable();
            
            // Maintenance Specific
            $table->dateTime('scheduled_date')->nullable();
            $table->enum('maintenance_type', ['PREVENTIVE', 'CORRECTIVE', 'PREDICTIVE'])->nullable();
            
            // Resolution
            $table->text('resolution_notes')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            
            // SLA & Tracking
            $table->dateTime('sla_due_date')->nullable();
            $table->dateTime('first_response_at')->nullable();
            $table->dateTime('estimated_completion')->nullable();
            
            // Rating
            $table->tinyInteger('rating')->nullable(); // 1-5
            $table->text('feedback')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('ticket_number');
            $table->index('asset_id');
            $table->index('reported_by');
            $table->index('assigned_to');
            $table->index('status');
            $table->index('type');
            $table->index('priority');
            $table->index('scheduled_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
