<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Outlet;
use App\Models\Location;

echo "=== Creating Location for FNB Outlet ===\n\n";

// Find FNB outlet
$fnbOutlet = Outlet::where('business_type', 'fnb')->first();

if (!$fnbOutlet) {
    echo "No FNB outlet found!\n";
    exit(1);
}

echo "Found FNB Outlet: {$fnbOutlet->name} (ID: {$fnbOutlet->id})\n";

// Check if location already exists
$existingLocation = Location::where('outlet_id', $fnbOutlet->id)->first();

if ($existingLocation) {
    echo "Location already exists: {$existingLocation->name} (ID: {$existingLocation->id})\n";
} else {
    echo "Creating new location for FNB outlet...\n";
    
    $location = Location::create([
        'code' => $fnbOutlet->code,
        'name' => $fnbOutlet->name,
        'type' => 'OUTLET',
        'address' => $fnbOutlet->address,
        'phone' => $fnbOutlet->phone,
        'is_active' => true,
        'outlet_id' => $fnbOutlet->id,
    ]);
    
    echo "✓ Location created: {$location->name} (ID: {$location->id})\n";
    echo "\nNow you can use this location in the POS system!\n";
}

echo "\n=== Done ===\n";
