<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing GRN to Asset Flow\n";
echo "==========================\n\n";

// Step 1: Check if we have a PO with ASSET type products
$po = App\Models\PurchaseOrder::with('items.product')->where('status', 'APPROVED')->first();

if (!$po) {
    echo "⚠ No approved PO found. Creating test data...\n\n";
    
    // Create asset product
    $product = App\Models\Product::create([
        'sku' => 'LAPTOP-DELL-' . time(),
        'name' => 'Laptop Dell XPS 13',
        'type' => 'ASSET',
        'category_id' => 1,
        'cost_price' => 18000000,
        'selling_price' => 0,
        'uom' => 'unit',
        'track_inventory' => false,
        'is_active' => true,
    ]);
    echo "✓ Created asset product: {$product->name}\n";
    
    // Create PR
    $pr = App\Models\PurchaseRequest::create([
        'pr_no' => 'PR-TEST-' . time(),
        'department_id' => 1,
        'request_date' => now(),
        'required_date' => now()->addDays(7),
        'status' => 'APPROVED',
        'requested_by' => 1,
        'approved_by' => 1,
        'approved_at' => now(),
    ]);
    
    App\Models\PurchaseRequestItem::create([
        'pr_id' => $pr->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'quantity_requested' => 2,
        'unit_price' => 18000000,
        'line_total' => 36000000,
    ]);
    echo "✓ Created PR: {$pr->pr_no}\n";
    
    // Create PO
    $vendor = App\Models\Vendor::first();
    if (!$vendor) {
        $vendor = App\Models\Vendor::create([
            'code' => 'V-TEST-' . time(),
            'name' => 'Test Vendor',
            'email' => 'test@vendor.com',
            'phone' => '081234567890',
            'address' => 'Test Address',
            'is_active' => true,
        ]);
        echo "✓ Created vendor: {$vendor->name}\n";
    }
    
    $po = App\Models\PurchaseOrder::create([
        'po_no' => 'PO-TEST-' . time(),
        'pr_id' => $pr->id,
        'vendor_id' => $vendor->id,
        'location_id' => 1,
        'order_date' => now(),
        'expected_delivery' => now()->addDays(14),
        'status' => 'APPROVED',
        'subtotal' => 36000000,
        'tax' => 0,
        'discount' => 0,
        'total' => 36000000,
        'created_by' => 1,
        'approved_by' => 1,
        'approved_at' => now(),
    ]);
    
    App\Models\PurchaseOrderItem::create([
        'po_id' => $po->id,
        'pr_item_id' => $pr->items->first()->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'quantity_received' => 0,
        'unit_price' => 18000000,
        'line_total' => 36000000,
    ]);
    
    echo "✓ Created PO: {$po->po_no}\n\n";
    $po->load('items.product');
}

echo "Using PO: {$po->po_no}\n";
echo "Items:\n";
foreach ($po->items as $item) {
    echo "  - {$item->product->name} (Type: {$item->product->type}) x {$item->quantity}\n";
}
echo "\n";

// Step 2: Create GRN
$grnService = app(App\Services\GoodsReceiptService::class);

$grnData = [
    'po_id' => $po->id,
    'location_id' => 1,
    'receipt_date' => now(),
    'items' => []
];

foreach ($po->items as $item) {
    $serialNumbers = [];
    // Generate serial numbers for asset products
    if ($item->product->type === 'ASSET') {
        for ($i = 1; $i <= $item->quantity; $i++) {
            $serialNumbers[] = 'SN-' . time() . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);
        }
    }
    
    $grnData['items'][] = [
        'po_item_id' => $item->id,
        'quantity_received' => $item->quantity,
        'quantity_rejected' => 0,
        'serial_numbers' => $serialNumbers,
    ];
}

$grn = $grnService->createGRN($grnData, 1);
echo "✓ Created GRN: {$grn->grn_no}\n";
echo "  Items with serial numbers:\n";
foreach ($grn->items as $item) {
    if ($item->serial_numbers) {
        echo "    - {$item->product->name}: " . implode(', ', $item->serial_numbers) . "\n";
    }
}
echo "\n";

// Step 3: Quality check - mark all as PASSED
$qualityData = [
    'items' => []
];

foreach ($grn->items as $item) {
    $qualityData['items'][] = [
        'id' => $item->id,
        'quality_status' => 'PASSED',
        'quantity_rejected' => 0,
    ];
}

$grn = $grnService->qualityCheck($grn->id, $qualityData, 1);
echo "✓ Quality check completed\n\n";

// Step 4: Approve GRN
$grn = $grnService->approveGRN($grn->id, 1);
echo "✓ GRN approved\n\n";

// Step 5: Post GRN (this should create assets)
echo "--- Posting GRN to create assets ---\n";
$assetCountBefore = App\Models\Asset::count();

$grn = $grnService->postGRN($grn->id, 1);
echo "✓ GRN posted\n\n";

$assetCountAfter = App\Models\Asset::count();
$newAssets = $assetCountAfter - $assetCountBefore;

echo "Assets created: {$newAssets}\n";

// Show created assets
$assets = App\Models\Asset::with('product')
    ->where('grn_id', $grn->id)
    ->get();

echo "\nCreated Assets:\n";
foreach ($assets as $asset) {
    echo "  ✓ {$asset->asset_tag}\n";
    echo "    - Product: {$asset->product->name}\n";
    echo "    - Serial: {$asset->serial_number}\n";
    echo "    - Status: {$asset->status}\n";
    echo "    - Condition: {$asset->condition}\n";
    echo "    - Price: Rp " . number_format($asset->purchase_price, 0, ',', '.') . "\n";
    echo "    - Location ID: {$asset->location_id}\n\n";
}

echo "==========================\n";
echo "GRN to Asset flow test completed! ✓\n";
