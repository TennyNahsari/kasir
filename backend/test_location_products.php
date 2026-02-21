<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Location;
use App\Models\Product;
use App\Models\InventoryStock;

// Get location_id from command line or default to 5
$locationId = $argv[1] ?? 5;

echo "=== DEBUGGING PRODUCTS-BY-LOCATION for Location ID: {$locationId} ===\n\n";

// Step 1: Check location exists
$location = Location::find($locationId);
if (!$location) {
    echo "❌ Location ID {$locationId} NOT FOUND!\n";
    echo "\nAvailable OUTLET/FNB locations:\n";
    $locs = Location::whereIn('type', ['OUTLET', 'FNB'])->get();
    foreach ($locs as $loc) {
        echo "  - ID: {$loc->id}, Name: {$loc->name}, Type: {$loc->type}\n";
    }
    exit(1);
}

echo "✅ Location found:\n";
echo "  ID: {$location->id}\n";
echo "  Name: {$location->name}\n";
echo "  Type: {$location->type}\n";
echo "  Outlet ID: " . ($location->outlet_id ?? 'NULL') . "\n\n";

// Step 2: Check inventory_stocks for this location
$stocksCount = InventoryStock::where('location_id', $locationId)->count();
echo "📦 Inventory Stocks for this location: {$stocksCount} records\n";

if ($stocksCount === 0) {
    echo "❌ NO STOCKS FOUND for this location!\n";
    echo "\nCreating stock records for all active products...\n";
    
    $activeProducts = Product::where('is_active', true)->get();
    echo "Found {$activeProducts->count()} active products\n";
    
    $created = 0;
    foreach ($activeProducts as $product) {
        InventoryStock::create([
            'product_id' => $product->id,
            'location_id' => $locationId,
            'quantity' => 100, // Set initial quantity for testing
            'reserved_quantity' => 0,
            'min_stock' => 10,
            'max_stock' => 500,
            'reorder_point' => 20
        ]);
        $created++;
    }
    
    echo "✅ Created {$created} stock records with quantity=100 for testing\n\n";
    $stocksCount = $created;
}

// Step 3: Check active products
$activeProductsCount = Product::where('is_active', true)->count();
echo "📋 Active products in system: {$activeProductsCount}\n\n";

// Step 4: Run the same query as ProductController
echo "--- Running same query as ProductController::getByLocation() ---\n";
$query = Product::with(['category'])
    ->join('inventory_stocks', 'products.id', '=', 'inventory_stocks.product_id')
    ->where('inventory_stocks.location_id', $locationId)
    ->where('products.is_active', true)
    ->select('products.*', 'inventory_stocks.quantity as stock', 'inventory_stocks.reserved_quantity');

$products = $query->get();

echo "✅ Query returned: {$products->count()} products\n\n";

if ($products->isEmpty()) {
    echo "❌ NO PRODUCTS RETURNED!\n\n";
    echo "Debugging:\n";
    echo "  - Stocks in location: {$stocksCount}\n";
    echo "  - Active products: {$activeProductsCount}\n";
    echo "  - Query result: 0\n\n";
    
    echo "Checking join condition:\n";
    $stocksDetails = InventoryStock::with('product')
        ->where('location_id', $locationId)
        ->limit(5)
        ->get();
    
    foreach ($stocksDetails as $stock) {
        $isActive = $stock->product->is_active ? 'ACTIVE' : 'INACTIVE';
        echo "  - Product: {$stock->product->name} ({$isActive}), Stock: {$stock->quantity}\n";
    }
    
} else {
    echo "✅ SUCCESS! Products found:\n\n";
    foreach ($products->take(10) as $product) {
        $availableStock = $product->stock - $product->reserved_quantity;
        echo "  - {$product->name}\n";
        echo "    SKU: {$product->sku}\n";
        echo "    Price: {$product->price}\n";
        echo "    Stock: {$product->stock}\n";
        echo "    Reserved: {$product->reserved_quantity}\n";
        echo "    Available: {$availableStock}\n";
        echo "    Category: " . ($product->category->name ?? 'N/A') . "\n\n";
    }
    
    if ($products->count() > 10) {
        echo "  ... and " . ($products->count() - 10) . " more products\n\n";
    }
}

echo "\n=== TEST COMPLETE ===\n";
