<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update existing products with inventory fields
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->warn('No products found. Please seed products first.');
            return;
        }

        $itemTypes = ['FINISHED_GOODS', 'RAW_MATERIAL', 'CONSUMABLE'];
        $uoms = ['PCS', 'BOX', 'SET', 'KG', 'LITER', 'PACK'];

        foreach ($products as $product) {
            $product->update([
                'item_type' => $itemTypes[array_rand($itemTypes)],
                'uom' => $uoms[array_rand($uoms)],
                'track_inventory' => true,
                'min_stock_level' => rand(5, 20),
                'max_stock_level' => rand(100, 500),
                'reorder_level' => rand(10, 50),
                'last_purchase_price' => $product->cost_price ?? rand(10000, 500000),
                'average_cost' => $product->cost_price ?? rand(10000, 500000),
            ]);
        }

        // Create new inventory-specific products
        $category = Category::first();
        
        if (!$category) {
            $this->command->warn('No category found. Creating default category.');
            $category = Category::create([
                'name' => 'Inventory Items',
                'description' => 'Items for inventory management',
            ]);
        }

        $newProducts = [
            [
                'sku' => 'INV-RAW-001',
                'barcode' => '8991234567890',
                'name' => 'Bahan Baku Plastik PP - Grade A',
                'description' => 'Polypropylene plastic raw material, industrial grade',
                'category_id' => $category->id,
                'item_type' => 'RAW_MATERIAL',
                'uom' => 'KG',
                'track_inventory' => true,
                'cost_price' => 25000,
                'selling_price' => 0, // Raw material tidak dijual
                'stock' => 0,
                'min_stock_level' => 100,
                'max_stock_level' => 1000,
                'reorder_level' => 200,
                'last_purchase_price' => 25000,
                'average_cost' => 25000,
                'track_stock' => true,
                'is_active' => true,
            ],
            [
                'sku' => 'INV-FIN-001',
                'barcode' => '8991234567891',
                'name' => 'Laptop Dell Latitude 3520 - i5/8GB/256GB',
                'description' => 'Business laptop with warranty',
                'category_id' => $category->id,
                'item_type' => 'FINISHED_GOODS',
                'uom' => 'PCS',
                'track_inventory' => true,
                'cost_price' => 8500000,
                'selling_price' => 11000000,
                'stock' => 0,
                'min_stock_level' => 5,
                'max_stock_level' => 50,
                'reorder_level' => 10,
                'last_purchase_price' => 8500000,
                'average_cost' => 8500000,
                'track_stock' => true,
                'is_active' => true,
            ],
            [
                'sku' => 'INV-CON-001',
                'barcode' => '8991234567892',
                'name' => 'Tinta Printer Epson 664 Black',
                'description' => 'Original printer ink consumable',
                'category_id' => $category->id,
                'item_type' => 'CONSUMABLE',
                'uom' => 'BOT',
                'track_inventory' => true,
                'cost_price' => 75000,
                'selling_price' => 95000,
                'stock' => 0,
                'min_stock_level' => 20,
                'max_stock_level' => 200,
                'reorder_level' => 30,
                'last_purchase_price' => 75000,
                'average_cost' => 75000,
                'track_stock' => true,
                'is_active' => true,
            ],
            [
                'sku' => 'INV-FIN-002',
                'barcode' => '8991234567893',
                'name' => 'Mouse Wireless Logitech M280',
                'description' => 'Wireless mouse with 2.4GHz connectivity',
                'category_id' => $category->id,
                'item_type' => 'FINISHED_GOODS',
                'uom' => 'PCS',
                'track_inventory' => true,
                'cost_price' => 150000,
                'selling_price' => 199000,
                'stock' => 0,
                'min_stock_level' => 10,
                'max_stock_level' => 100,
                'reorder_level' => 20,
                'last_purchase_price' => 150000,
                'average_cost' => 150000,
                'track_stock' => true,
                'is_active' => true,
            ],
            [
                'sku' => 'INV-FIN-003',
                'barcode' => '8991234567894',
                'name' => 'Keyboard Mechanical Gaming RGB',
                'description' => 'Mechanical keyboard with RGB backlight',
                'category_id' => $category->id,
                'item_type' => 'FINISHED_GOODS',
                'uom' => 'PCS',
                'track_inventory' => true,
                'cost_price' => 450000,
                'selling_price' => 650000,
                'stock' => 0,
                'min_stock_level' => 5,
                'max_stock_level' => 50,
                'reorder_level' => 10,
                'last_purchase_price' => 450000,
                'average_cost' => 450000,
                'track_stock' => true,
                'is_active' => true,
            ],
        ];

        foreach ($newProducts as $product) {
            Product::firstOrCreate(['sku' => $product['sku']], $product);
        }

        // Seed initial InventoryStock & InventoryLedger entries across locations
        $locations = \App\Models\Location::all();
        $allProducts = Product::where('track_stock', true)->get();

        if ($locations->isNotEmpty() && $allProducts->isNotEmpty()) {
            foreach ($allProducts as $p) {
                foreach ($locations as $loc) {
                    $qty = rand(5, 100);
                    $reorder = rand(10, 25);
                    $stock = \App\Models\InventoryStock::firstOrCreate(
                        [
                            'product_id' => $p->id,
                            'location_id' => $loc->id,
                        ],
                        [
                            'quantity' => $qty,
                            'reserved_quantity' => 0,
                            'reorder_level' => $reorder,
                            'last_stock_in' => now(),
                        ]
                    );

                    \App\Models\InventoryLedger::firstOrCreate(
                        [
                            'product_id' => $p->id,
                            'location_id' => $loc->id,
                            'reference_type' => 'INITIAL_SEED',
                        ],
                        [
                            'movement_type' => 'STOCK_IN',
                            'quantity' => $qty,
                            'balance_before' => 0,
                            'balance_after' => $qty,
                            'reference_id' => $stock->id,
                            'notes' => 'Initial inventory seed',
                            'created_by' => 1,
                        ]
                    );
                }
            }
        }

        $this->command->info('Products updated with inventory fields!');
        $this->command->info('New inventory products & initial stocks created successfully!');
    }
}

