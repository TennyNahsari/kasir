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
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('pr_no')->unique();
            
            $table->date('request_date');
            $table->date('required_date')->nullable();
            
            // Status: DRAFT, PENDING_APPROVAL, APPROVED, REJECTED, PARTIALLY_ORDERED, FULLY_ORDERED, CANCELLED
            $table->enum('status', ['DRAFT', 'PENDING_APPROVAL', 'APPROVED', 'REJECTED', 'PARTIALLY_ORDERED', 'FULLY_ORDERED', 'CANCELLED'])->default('DRAFT');
            
            // Location for delivery
            $table->foreignId('location_id')->nullable()->constrained('locations');
            
            // Approval chain
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Metadata
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('pr_no');
            $table->index('status');
            $table->index('request_date');
            $table->index('requested_by');
        });
        
        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pr_id')->constrained('purchase_requests')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            
            $table->decimal('quantity', 12, 4);
            $table->decimal('quantity_ordered', 12, 4)->default(0);
            $table->decimal('estimated_price', 15, 2)->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('pr_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
