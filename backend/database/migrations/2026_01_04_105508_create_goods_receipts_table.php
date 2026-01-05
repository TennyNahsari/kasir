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
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('grn_no')->unique();
            
            $table->foreignId('po_id')->constrained('purchase_orders');
            $table->foreignId('location_id')->constrained('locations');
            
            $table->date('receipt_date');
            $table->string('supplier_invoice_no', 100)->nullable();
            $table->date('supplier_invoice_date')->nullable();
            
            // Status: DRAFT, QUALITY_CHECK, APPROVED, POSTED, REJECTED
            $table->enum('status', ['DRAFT', 'QUALITY_CHECK', 'APPROVED', 'POSTED', 'REJECTED'])->default('DRAFT');
            
            // Posting to inventory
            $table->boolean('is_posted')->default(false);
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users');
            
            // Quality check
            $table->foreignId('quality_checked_by')->nullable()->constrained('users');
            $table->timestamp('quality_checked_at')->nullable();
            $table->text('quality_notes')->nullable();
            
            // Approval
            $table->foreignId('received_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            
            // Metadata
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('grn_no');
            $table->index('po_id');
            $table->index('location_id');
            $table->index('status');
            $table->index('receipt_date');
            $table->index('is_posted');
        });
        
        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grn_id')->constrained('goods_receipts')->cascadeOnDelete();
            $table->foreignId('po_item_id')->constrained('purchase_order_items')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            
            $table->decimal('quantity_ordered', 12, 4);
            $table->decimal('quantity_received', 12, 4);
            $table->decimal('quantity_rejected', 12, 4)->default(0);
            $table->decimal('quantity_accepted', 12, 4)->storedAs('quantity_received - quantity_rejected');
            
            $table->decimal('unit_price', 15, 2);
            $table->decimal('line_total', 15, 2);
            
            // Quality status
            $table->enum('quality_status', ['PENDING', 'PASSED', 'FAILED'])->default('PENDING');
            $table->text('quality_notes')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('grn_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipts');
    }
};
