<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== Simulating Owner Checkout Process ===\n\n";

// Test each valid location
$validLocations = \App\Models\Location::whereNotNull('outlet_id')
    ->whereIn('type', ['OUTLET', 'FNB'])
    ->where('is_active', true)
    ->with('outlet')
    ->get();

echo "Testing checkout for each valid location:\n\n";

foreach ($validLocations as $loc) {
    echo "Location ID: {$loc->id} - {$loc->name}\n";
    echo "  Type: {$loc->type}\n";
    echo "  outlet_id: {$loc->outlet_id}\n";
    echo "  Outlet: " . ($loc->outlet ? $loc->outlet->name : 'N/A') . "\n";
    
    // Simulate what backend does
    if (!$loc->outlet_id) {
        echo "  ❌ ERROR: Location does not have an associated outlet\n";
    } else {
        echo "  ✅ OK: Can create transaction with outlet_id = {$loc->outlet_id}\n";
    }
    echo "\n";
}

echo "\n=== Testing Invalid Cases ===\n\n";

// Test warehouse (should not be in dropdown)
$warehouse = \App\Models\Location::where('type', 'WAREHOUSE')->first();
if ($warehouse) {
    echo "Warehouse ID: {$warehouse->id} - {$warehouse->name}\n";
    echo "  outlet_id: " . ($warehouse->outlet_id ?? 'NULL') . "\n";
    echo "  ❌ Should NOT appear in Owner dropdown\n";
    if (!$warehouse->outlet_id) {
        echo "  ❌ Would cause error: Location does not have an associated outlet\n";
    }
    echo "\n";
}
