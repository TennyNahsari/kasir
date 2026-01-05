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
        Schema::create('inventory_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_no')->unique();
            
            // Locations
            $table->foreignId('from_location_id')->constrained('locations');
            $table->foreignId('to_location_id')->constrained('locations');
            
            // Dates
            $table->date('transfer_date');
            $table->date('received_date')->nullable();
            
            // Status: DRAFT, PENDING, IN_TRANSIT, RECEIVED, CANCELLED
            $table->enum('status', ['DRAFT', 'PENDING', 'IN_TRANSIT', 'RECEIVED', 'CANCELLED'])->default('DRAFT');
            
            // Approval chain
            $table->foreignId('requested_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users');
            
            // Metadata
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('transfer_no');
            $table->index('from_location_id');
            $table->index('to_location_id');
            $table->index('status');
            $table->index('transfer_date');
        });
        
        Schema::create('inventory_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('inventory_transfers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            
            $table->decimal('quantity_requested', 12, 4);
            $table->decimal('quantity_received', 12, 4)->default(0);
            $table->decimal('quantity_rejected', 12, 4)->default(0);
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('transfer_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transfers');
    }
};
