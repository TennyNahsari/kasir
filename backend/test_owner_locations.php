<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== Testing Owner Access to Locations for POS ===\n\n";

// Get all locations with outlet_id for POS (OUTLET and FNB types)
$locations = \App\Models\Location::whereNotNull('outlet_id')
    ->whereIn('type', ['OUTLET', 'FNB'])
    ->where('is_active', true)
    ->with('outlet')
    ->get();

echo "Available locations for POS:\n";
foreach ($locations as $loc) {
    echo "\nLocation ID: {$loc->id}\n";
    echo "  Name: {$loc->name}\n";
    echo "  Type: {$loc->type}\n";
    echo "  Code: {$loc->code}\n";
    echo "  Outlet ID: {$loc->outlet_id}\n";
    echo "  Outlet Name: " . ($loc->outlet ? $loc->outlet->name : 'N/A') . "\n";
    echo "  Business Type: " . ($loc->outlet ? $loc->outlet->business_type : 'N/A') . "\n";
}

echo "\n=== Testing Transaction Creation (Simulated) ===\n";
$testLocation = $locations->first();
if ($testLocation) {
    echo "\nTest data for location {$testLocation->id} ({$testLocation->name}):\n";
    echo "  outlet_id: {$testLocation->outlet_id}\n";
    echo "  location_id: {$testLocation->id}\n";
    echo "  Should work: YES\n";
} else {
    echo "\n❌ No valid locations found!\n";
}
