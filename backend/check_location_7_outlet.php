<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== Checking Location 7 and Outlet Assignment ===\n\n";

// Check location 7
$location7 = \App\Models\Location::with('outlet')->find(7);

echo "Location 7 Details:\n";
echo "  ID: {$location7->id}\n";
echo "  Name: {$location7->name}\n";
echo "  Code: {$location7->code}\n";
echo "  Type: {$location7->type}\n";
echo "  outlet_id: " . ($location7->outlet_id ?? 'NULL') . "\n";
echo "  Outlet Name: " . ($location7->outlet ? $location7->outlet->name : 'N/A') . "\n\n";

// Check all outlets
echo "=== All Outlets ===\n";
$outlets = \App\Models\Outlet::all();
foreach ($outlets as $outlet) {
    echo "Outlet ID: {$outlet->id}\n";
    echo "  Name: {$outlet->name}\n";
    echo "  Business Type: {$outlet->business_type}\n";
    
    // Get locations for this outlet
    $locations = \App\Models\Location::where('outlet_id', $outlet->id)->get();
    echo "  Locations (" . $locations->count() . "):\n";
    foreach ($locations as $loc) {
        echo "    - {$loc->name} (ID: {$loc->id}, Type: {$loc->type})\n";
    }
    echo "\n";
}

// Check if there's already an outlet named "Warung Makan GDC"
$gdcOutlet = \App\Models\Outlet::where('name', 'Warung Makan GDC')->first();
if ($gdcOutlet) {
    echo "✅ Outlet 'Warung Makan GDC' already exists (ID: {$gdcOutlet->id})\n\n";
} else {
    echo "❌ No outlet named 'Warung Makan GDC' found\n";
    echo "Current setup: Location 'Warung Makan GDC' is assigned to Outlet 'Warung Makan Sedap'\n\n";
}

echo "=== Explanation ===\n";
echo "When customer orders from Location 7 (Warung Makan GDC),\n";
echo "the transaction is created for Outlet " . ($location7->outlet_id ?? 'NULL') . " (" . ($location7->outlet ? $location7->outlet->name : 'N/A') . ")\n";
echo "because Location 7's outlet_id field points to that outlet.\n\n";

echo "Options:\n";
echo "1. Keep current setup: Multiple locations can share one outlet\n";
echo "2. Create new outlet: 'Warung Makan GDC' and assign Location 7 to it\n";
