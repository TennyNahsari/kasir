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
        Schema::create('service_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number', 50)->unique(); // SVC-2026-001
            
            // References
            $table->foreignId('grn_id')->nullable()->constrained('goods_receipts')->nullOnDelete();
            $table->foreignId('po_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            
            // Contract Details
            $table->enum('contract_type', [
                'RENTAL',           // Sewa (gedung, kendaraan)
                'SUBSCRIPTION',     // Langganan (software, service)
                'MAINTENANCE',      // Kontrak maintenance
                'CONSULTING',       // Jasa konsultasi
                'UTILITY',          // Utilities (listrik, air, internet)
                'OTHER'             // Lainnya
            ])->default('OTHER');
            
            $table->date('start_date');
            $table->date('end_date');
            
            // Financial
            $table->decimal('contract_value', 15, 2)->default(0); // Total contract value
            $table->enum('billing_cycle', [
                'MONTHLY',
                'QUARTERLY',
                'YEARLY',
                'ONE_TIME'
            ])->default('MONTHLY');
            
            // Status
            $table->enum('status', [
                'PENDING',      // Waiting to start
                'ACTIVE',       // Currently active
                'EXPIRED',      // Contract ended
                'TERMINATED'    // Terminated early
            ])->default('PENDING');
            
            // Additional Info
            $table->text('notes')->nullable();
            $table->date('renewal_date')->nullable(); // If renewed
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('contract_number');
            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
            $table->index(['vendor_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_contracts');
    }
};
