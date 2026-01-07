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
        Schema::create('asset_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            
            // Movement type
            $table->enum('movement_type', [
                'PURCHASED',      // Initial purchase
                'ASSIGNED',       // Assigned to user
                'RETURNED',       // Returned by user
                'TRANSFERRED',    // Location transfer
                'MAINTENANCE',    // Sent to maintenance
                'REPAIRED',       // Returned from maintenance
                'DAMAGED',        // Marked as damaged
                'DISPOSED'        // Disposed/sold
            ]);
            
            // From/To tracking
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('from_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('to_location_id')->nullable()->constrained('locations')->nullOnDelete();
            
            // Condition tracking
            $table->enum('condition_before', ['NEW', 'GOOD', 'FAIR', 'POOR', 'BROKEN'])->nullable();
            $table->enum('condition_after', ['NEW', 'GOOD', 'FAIR', 'POOR', 'BROKEN'])->nullable();
            
            // Notes & metadata
            $table->text('notes')->nullable();
            $table->foreignId('moved_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('moved_at')->useCurrent();
            
            $table->timestamps();
            
            // Indexes
            $table->index('asset_id');
            $table->index('movement_type');
            $table->index('moved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_movements');
    }
};
