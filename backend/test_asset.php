<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Asset Creation\n";
echo "======================\n\n";

// Create a test product
$product = App\Models\Product::create([
    'sku' => 'TEST-ASSET-' . time(),
    'name' => 'Test Laptop Dell XPS',
    'type' => 'ASSET',
    'category_id' => 1,
    'cost_price' => 15000000,
    'selling_price' => 0,
    'uom' => 'unit',
    'track_inventory' => false,
    'is_active' => true,
]);

echo "✓ Product created: {$product->name} (ID: {$product->id}, Type: {$product->type})\n";

// Create asset using AssetService
$assetService = app(App\Services\AssetService::class);
$asset = $assetService->createAsset([
    'product_id' => $product->id,
    'location_id' => 1,
    'serial_number' => 'SN' . time(),
    'purchase_date' => now(),
    'purchase_price' => 15000000,
    'status' => 'AVAILABLE',
    'condition' => 'NEW',
    'useful_life_months' => 36,
    'warranty_until' => now()->addMonths(12),
], 1);

echo "✓ Asset created: {$asset->asset_tag}\n";
echo "  - Serial: {$asset->serial_number}\n";
echo "  - Status: {$asset->status}\n";
echo "  - Condition: {$asset->condition}\n";
echo "  - Price: Rp " . number_format($asset->purchase_price, 0, ',', '.') . "\n";

// Check asset movements
$movements = $asset->movements()->get();
echo "\n✓ Movement history: {$movements->count()} record(s)\n";
foreach ($movements as $movement) {
    echo "  - {$movement->movement_type} at {$movement->moved_at}\n";
}

// Test assign asset
echo "\n--- Testing Asset Assignment ---\n";
$assetService->assignAsset($asset->id, 1, [
    'location_id' => 1,
    'notes' => 'Test assignment'
], 1);
$asset->refresh();
echo "✓ Asset assigned to user ID: {$asset->assigned_to}\n";
echo "  - Status changed to: {$asset->status}\n";

// Check movements again
$movements = $asset->movements()->get();
echo "✓ Movement history: {$movements->count()} record(s)\n";
foreach ($movements as $movement) {
    echo "  - {$movement->movement_type} at {$movement->moved_at}\n";
}

echo "\n======================\n";
echo "All tests passed! ✓\n";
