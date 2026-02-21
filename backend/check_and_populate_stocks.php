<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Location;
use App\Models\Product;
use App\Models\InventoryStock;

echo "=== CHECK AND POPULATE INVENTORY STOCKS ===\n\n";

// Get all OUTLET and FNB locations
$locations = Location::whereIn('type', ['OUTLET', 'FNB'])
    ->orderBy('name')
    ->get();

echo "Found " . $locations->count() . " OUTLET/FNB locations:\n";
foreach ($locations as $loc) {
    echo "  - ID: {$loc->id}, Name: {$loc->name}, Type: {$loc->type}\n";
}
echo "\n";

// Get all active products
$products = Product::where('is_active', true)->get();
echo "Found " . $products->count() . " active products\n\n";

// Check stocks for each location
foreach ($locations as $location) {
    echo "--- Location: {$location->name} (ID: {$location->id}, Type: {$location->type}) ---\n";
    
    $stocksCount = InventoryStock::where('location_id', $location->id)->count();
    echo "  Current stocks: {$stocksCount} records\n";
    
    if ($stocksCount === 0) {
        echo "  ⚠️  NO STOCKS! Creating initial stocks for all active products...\n";
        
        $created = 0;
        foreach ($products as $product) {
            InventoryStock::create([
                'product_id' => $product->id,
                'location_id' => $location->id,
                'quantity' => 0,
                'reserved_quantity' => 0,
                'reorder_level' => 20
            ]);
            $created++;
        }
        
        echo "  ✅ Created {$created} stock records\n";
    } else {
        // Check if all active products have stocks
        $missingProducts = [];
        foreach ($products as $product) {
            $exists = InventoryStock::where('product_id', $product->id)
                ->where('location_id', $location->id)
                ->exists();
            
            if (!$exists) {
                $missingProducts[] = $product->name;
            }
        }
        
        if (count($missingProducts) > 0) {
            echo "  ⚠️  Missing stocks for " . count($missingProducts) . " products:\n";
            foreach ($missingProducts as $pname) {
                echo "    - {$pname}\n";
            }
            
            echo "  Creating missing stock records...\n";
            $created = 0;
            foreach ($products as $product) {
                $exists = InventoryStock::where('product_id', $product->id)
                    ->where('location_id', $location->id)
                    ->exists();
                
                if (!$exists) {
                    InventoryStock::create([
                        'product_id' => $product->id,
                        'location_id' => $location->id,
                        'quantity' => 0,
                        'reserved_quantity' => 0,
                        'reorder_level' => 20
                    ]);
                    $created++;
                }
            }
            echo "  ✅ Created {$created} missing stock records\n";
        } else {
            echo "  ✅ All active products have stock records\n";
        }
    }
    
    // Show sample stocks with quantity > 0
    $stocksWithQty = InventoryStock::with('product')
        ->where('location_id', $location->id)
        ->where('quantity', '>', 0)
        ->take(5)
        ->get();
    
    if ($stocksWithQty->count() > 0) {
        echo "  Sample stocks with quantity > 0:\n";
        foreach ($stocksWithQty as $stock) {
            echo "    - {$stock->product->name}: {$stock->quantity} units\n";
        }
    } else {
        echo "  ℹ️  No stocks with quantity > 0 (all products have 0 stock)\n";
    }
    
    echo "\n";
}

echo "\n=== SUMMARY ===\n";
$totalStocks = InventoryStock::count();
echo "Total inventory_stocks records: {$totalStocks}\n";

echo "\nStocks by location:\n";
foreach ($locations as $loc) {
    $count = InventoryStock::where('location_id', $loc->id)->count();
    $withQty = InventoryStock::where('location_id', $loc->id)->where('quantity', '>', 0)->count();
    echo "  {$loc->name} (ID: {$loc->id}): {$count} records, {$withQty} with qty > 0\n";
}

echo "\n✅ Done!\n";
echo "⚠️  Note: All stocks are created with quantity=0. Use Inventory app to add actual stock.\n";
