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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_no')->unique();
            
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            
            // Status: DRAFT, PENDING_APPROVAL, APPROVED, SENT, PARTIALLY_RECEIVED, FULLY_RECEIVED, CANCELLED
            $table->enum('status', ['DRAFT', 'PENDING_APPROVAL', 'APPROVED', 'SENT', 'PARTIALLY_RECEIVED', 'FULLY_RECEIVED', 'CANCELLED'])->default('DRAFT');
            
            // Location for delivery
            $table->foreignId('location_id')->constrained('locations');
            
            // Financials
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            
            // Approval
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            
            // Metadata
            $table->text('notes')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('po_no');
            $table->index('vendor_id');
            $table->index('status');
            $table->index('order_date');
        });
        
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('pr_item_id')->nullable()->constrained('purchase_request_items');
            
            $table->decimal('quantity', 12, 4);
            $table->decimal('quantity_received', 12, 4)->default(0);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2);
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('po_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
