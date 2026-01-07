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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('asset_tag', 50)->unique(); // AST-2026-001
            $table->string('serial_number', 100)->nullable()->unique();
            
            // Location & Assignment
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_date')->nullable();
            
            // Status & Condition
            $table->enum('status', [
                'AVAILABLE',     // Ready to assign
                'ASSIGNED',      // Assigned to user but not yet in use
                'IN_USE',        // Currently being used
                'MAINTENANCE',   // Under maintenance
                'DAMAGED',       // Broken/damaged
                'DISPOSED'       // Disposed/sold
            ])->default('AVAILABLE');
            
            $table->enum('condition', [
                'NEW',
                'GOOD',
                'FAIR',
                'POOR',
                'BROKEN'
            ])->default('NEW');
            
            // Financial
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->integer('useful_life_months')->nullable(); // for depreciation
            $table->enum('depreciation_method', ['STRAIGHT_LINE', 'DECLINING_BALANCE'])->nullable();
            $table->decimal('current_value', 12, 2)->default(0);
            $table->date('warranty_until')->nullable();
            
            // Reference to procurement
            $table->foreignId('po_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('grn_id')->nullable()->constrained('goods_receipts')->nullOnDelete();
            
            // Notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('product_id');
            $table->index('location_id');
            $table->index('assigned_to');
            $table->index('status');
            $table->index('asset_tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
