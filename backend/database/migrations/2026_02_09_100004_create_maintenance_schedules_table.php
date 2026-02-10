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
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            
            $table->enum('maintenance_type', ['PREVENTIVE', 'INSPECTION', 'CALIBRATION'])->default('PREVENTIVE');
            $table->enum('frequency', ['MONTHLY', 'QUARTERLY', 'SEMI_ANNUAL', 'ANNUAL'])->default('QUARTERLY');
            
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date');
            
            $table->boolean('auto_create_ticket')->default(true);
            $table->boolean('is_active')->default(true);
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('asset_id');
            $table->index('next_maintenance_date');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};
