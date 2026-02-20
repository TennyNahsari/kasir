<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\Category;
use App\Models\Location;
use App\Models\InventoryStock;

echo "=== Creating FNB Products ===\n\n";

// Get FNB categories
$catMakanan = Category::where('slug', 'fnb-makanan')->first();
$catMinuman = Category::where('slug', 'fnb-minuman')->first();
$catSnack = Category::where('slug', 'fnb-snack')->first();

if (!$catMakanan || !$catMinuman || !$catSnack) {
    echo "FNB categories not found!\n";
    exit(1);
}

echo "Found FNB Categories:\n";
echo "  - {$catMakanan->name} (ID: {$catMakanan->id})\n";
echo "  - {$catMinuman->name} (ID: {$catMinuman->id})\n";
echo "  - {$catSnack->name} (ID: {$catSnack->id})\n\n";

// Get FNB location
$fnbLocation = Location::whereHas('outlet', function($q) {
    $q->where('business_type', 'fnb');
})->first();

if (!$fnbLocation) {
    echo "FNB location not found!\n";
    exit(1);
}

echo "Found FNB Location: {$fnbLocation->name} (ID: {$fnbLocation->id})\n\n";

// FNB Products to create
$fnbProducts = [
    // Makanan
    [
        'name' => 'Nasi Goreng Spesial',
        'category_id' => $catMakanan->id,
        'sku' => 'FNB-MKN-001',
        'cost_price' => 15000,
        'selling_price' => 25000,
        'track_stock' => false, // FNB biasanya tidak track stock
        'is_active' => true,
    ],
    [
        'name' => 'Mie Ayam',
        'category_id' => $catMakanan->id,
        'sku' => 'FNB-MKN-002',
        'cost_price' => 10000,
        'selling_price' => 18000,
        'track_stock' => false,
        'is_active' => true,
    ],
    [
        'name' => 'Soto Ayam',
        'category_id' => $catMakanan->id,
        'sku' => 'FNB-MKN-003',
        'cost_price' => 12000,
        'selling_price' => 20000,
        'track_stock' => false,
        'is_active' => true,
    ],
    
    // Minuman
    [
        'name' => 'Es Teh Manis',
        'category_id' => $catMinuman->id,
        'sku' => 'FNB-MIN-001',
        'cost_price' => 2000,
        'selling_price' => 5000,
        'track_stock' => false,
        'is_active' => true,
    ],
    [
        'name' => 'Es Jeruk',
        'category_id' => $catMinuman->id,
        'sku' => 'FNB-MIN-002',
        'cost_price' => 3000,
        'selling_price' => 7000,
        'track_stock' => false,
        'is_active' => true,
    ],
    [
        'name' => 'Kopi Hitam',
        'category_id' => $catMinuman->id,
        'sku' => 'FNB-MIN-003',
        'cost_price' => 2500,
        'selling_price' => 6000,
        'track_stock' => false,
        'is_active' => true,
    ],
    
    // Snack
    [
        'name' => 'Pisang Goreng',
        'category_id' => $catSnack->id,
        'sku' => 'FNB-SNK-001',
        'cost_price' => 3000,
        'selling_price' => 8000,
        'track_stock' => false,
        'is_active' => true,
    ],
    [
        'name' => 'Tahu Isi',
        'category_id' => $catSnack->id,
        'sku' => 'FNB-SNK-002',
        'cost_price' => 2500,
        'selling_price' => 6000,
        'track_stock' => false,
        'is_active' => true,
    ],
];

echo "Creating FNB products...\n\n";

foreach ($fnbProducts as $productData) {
    // Check if product already exists
    $existing = Product::where('sku', $productData['sku'])->first();
    
    if ($existing) {
        echo "  ⊙ {$productData['name']} already exists (SKU: {$productData['sku']})\n";
        
        // Create stock for existing product if not exists
        $stockExists = InventoryStock::where('product_id', $existing->id)
            ->where('location_id', $fnbLocation->id)
            ->exists();
            
        if (!$stockExists && $existing->track_stock) {
            InventoryStock::create([
                'product_id' => $existing->id,
                'location_id' => $fnbLocation->id,
                'quantity' => 100,
                'reserved_quantity' => 0,
            ]);
            echo "    ✓ Stock created\n";
        }
        continue;
    }
    
    $product = Product::create($productData);
    echo "  ✓ Created: {$product->name} (SKU: {$product->sku})\n";
    
    // Create inventory stock for this product at FNB location
    if ($product->track_stock) {
        InventoryStock::create([
            'product_id' => $product->id,
            'location_id' => $fnbLocation->id,
            'quantity' => 100,
            'reserved_quantity' => 0,
        ]);
        echo "    ✓ Stock created: 100 units\n";
    } else {
        echo "    - No stock tracking (typical for FNB)\n";
    }
}

echo "\n=== Done ===\n";
echo "FNB products have been created and are ready to use in the POS system!\n";
