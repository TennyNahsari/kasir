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
        Schema::table('products', function (Blueprint $table) {
            // Item type for inventory management
            $table->enum('item_type', ['FINISHED_GOODS', 'RAW_MATERIAL', 'CONSUMABLE'])->default('FINISHED_GOODS')->after('category_id');
            
            // Unit of Measure
            $table->string('uom', 20)->default('PCS')->after('item_type');
            
            // Inventory tracking flag
            $table->boolean('track_inventory')->default(true)->after('uom');
            
            // Stock levels (for reference, actual stock in inventory_stocks)
            $table->decimal('min_stock_level', 12, 4)->default(0)->after('stock');
            $table->decimal('max_stock_level', 12, 4)->default(0)->after('min_stock_level');
            $table->decimal('reorder_level', 12, 4)->default(0)->after('max_stock_level');
            
            // Costing
            $table->decimal('last_purchase_price', 15, 2)->nullable()->after('price');
            $table->decimal('average_cost', 15, 2)->nullable()->after('last_purchase_price');
            
            // Indexes
            $table->index('item_type');
            $table->index('track_inventory');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['item_type']);
            $table->dropIndex(['track_inventory']);
            
            $table->dropColumn([
                'item_type',
                'uom',
                'track_inventory',
                'min_stock_level',
                'max_stock_level',
                'reorder_level',
                'last_purchase_price',
                'average_cost',
            ]);
        });
    }
};
