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
        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            
            $table->decimal('quantity', 12, 4)->default(0);
            $table->decimal('reserved_quantity', 12, 4)->default(0);
            $table->decimal('reorder_level', 12, 4)->default(10);
            
            $table->timestamp('last_stock_in')->nullable();
            $table->timestamp('last_stock_out')->nullable();
            
            $table->timestamps();
            
            // Unique constraint: 1 product per location
            $table->unique(['product_id', 'location_id'], 'product_location_unique');
            
            // Indexes for better performance
            $table->index('product_id');
            $table->index('location_id');
            $table->index('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_stocks');
    }
};
