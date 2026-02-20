<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Location;
use App\Models\Product;
use App\Models\InventoryStock;
use App\Models\Category;

echo "=== Checking QR Order for Location 7 (Warung Makan GDC) ===\n\n";

// Get location 7
$location = Location::find(7);

if (!$location) {
    echo "Location ID 7 not found!\n";
    exit(1);
}

echo "Location: {$location->name}\n";
echo "Type: {$location->type}\n";
echo "Code: {$location->code}\n\n";

// Get FNB categories
$fnbCategories = Category::where(function($q) {
    $q->where('name', 'like', '%FNB%')
      ->orWhere('slug', 'like', '%fnb%');
})->get();

echo "FNB Categories:\n";
foreach ($fnbCategories as $cat) {
    echo "  - {$cat->name} (ID: {$cat->id}, Slug: {$cat->slug})\n";
}
echo "\n";

// Get FNB products
$fnbCategoryIds = $fnbCategories->pluck('id')->toArray();
$fnbProducts = Product::whereIn('category_id', $fnbCategoryIds)
    ->where('is_active', true)
    ->get();

echo "FNB Products: " . $fnbProducts->count() . "\n";
foreach ($fnbProducts as $product) {
    $stock = InventoryStock::where('product_id', $product->id)
        ->where('location_id', $location->id)
        ->first();
    
    $stockQty = $stock ? $stock->quantity : 0;
    $hasStock = $stock ? 'Yes' : 'No';
    
    echo "  - {$product->name} (Track Stock: " . ($product->track_stock ? 'Yes' : 'No') . ", Stock: {$stockQty}, Has Record: {$hasStock})\n";
}
echo "\n";

// Check which products need stock records
$productsNeedingStock = [];
foreach ($fnbProducts as $product) {
    if ($product->track_stock) {
        $hasStock = InventoryStock::where('product_id', $product->id)
            ->where('location_id', $location->id)
            ->exists();
        
        if (!$hasStock) {
            $productsNeedingStock[] = $product;
        }
    }
}

if (count($productsNeedingStock) > 0) {
    echo "Products needing stock records: " . count($productsNeedingStock) . "\n\n";
    echo "Creating stock records...\n";
    
    foreach ($productsNeedingStock as $product) {
        InventoryStock::create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'quantity' => 100,
            'reserved_quantity' => 0,
        ]);
        echo "  ✓ Created stock for: {$product->name}\n";
    }
} else {
    echo "All products have stock records or don't track stock.\n";
}

echo "\n=== QR Order URL Test ===\n";
echo "URL: http://localhost:5173/order/{$location->id}/1\n";
echo "Expected behavior:\n";
echo "  ✓ Should show location name: {$location->name}\n";
echo "  ✓ Should show " . $fnbCategories->count() . " FNB categories\n";
echo "  ✓ Should show " . $fnbProducts->count() . " FNB products\n";
echo "  ✗ Should NOT show non-FNB products\n";

echo "\n=== Done ===\n";
