<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Location;
use App\Models\Outlet;
use App\Models\User;
use App\Models\InventoryStock;

echo "=== Checking Location with Code: FNB-001 ===\n\n";

// Find location by code
$location = Location::where('code', 'FNB-001')->first();

if (!$location) {
    echo "Location with code 'FNB-001' not found in database.\n\n";
    
    echo "Available locations:\n";
    echo str_repeat("-", 100) . "\n";
    printf("%-5s %-30s %-15s %-15s %-10s\n", "ID", "Name", "Code", "Type", "Outlet ID");
    echo str_repeat("-", 100) . "\n";
    
    $allLocations = Location::all();
    foreach ($allLocations as $loc) {
        printf(
            "%-5s %-30s %-15s %-15s %-10s\n",
            $loc->id,
            $loc->name,
            $loc->code,
            $loc->type,
            $loc->outlet_id ?: '-'
        );
    }
    echo str_repeat("-", 100) . "\n";
    exit(0);
}

echo "✓ Location Found!\n";
echo str_repeat("-", 100) . "\n";
echo "Location ID: {$location->id}\n";
echo "Location Name: {$location->name}\n";
echo "Location Code: {$location->code}\n";
echo "Location Type: {$location->type}\n";
echo "Address: " . ($location->address ?: '-') . "\n";
echo "Phone: " . ($location->phone ?: '-') . "\n";
echo "Person in Charge: " . ($location->person_in_charge ?: '-') . "\n";
echo "Active: " . ($location->is_active ? 'Yes' : 'No') . "\n";
echo "Outlet ID: " . ($location->outlet_id ?: '-') . "\n";
echo str_repeat("-", 100) . "\n\n";

// Check if has outlet
if ($location->outlet_id) {
    $outlet = Outlet::find($location->outlet_id);
    if ($outlet) {
        echo "✓ Connected to Outlet:\n";
        echo str_repeat("-", 100) . "\n";
        echo "Outlet ID: {$outlet->id}\n";
        echo "Outlet Name: {$outlet->name}\n";
        echo "Outlet Code: {$outlet->code}\n";
        echo "Business Type: {$outlet->business_type}\n";
        echo "Active: " . ($outlet->is_active ? 'Yes' : 'No') . "\n";
        echo str_repeat("-", 100) . "\n\n";
        
        if ($outlet->business_type === 'fnb') {
            echo "🍽️ This is an F&B outlet - POS will show only FNB categories!\n\n";
        }
    }
} else {
    echo "⊙ Not connected to any outlet\n\n";
}

// Check users assigned to this location
$users = User::where('location_id', $location->id)
    ->orWhere('outlet_id', $location->outlet_id)
    ->get();

if ($users->count() > 0) {
    echo "✓ Users Assigned to this Location ({$users->count()}):\n";
    echo str_repeat("-", 100) . "\n";
    printf("%-5s %-25s %-30s %-15s %-12s %-12s\n", "ID", "Name", "Email", "Role", "Outlet ID", "Location ID");
    echo str_repeat("-", 100) . "\n";
    
    foreach ($users as $user) {
        printf(
            "%-5s %-25s %-30s %-15s %-12s %-12s\n",
            $user->id,
            $user->name,
            $user->email,
            $user->role,
            $user->outlet_id ?: '-',
            $user->location_id ?: '-'
        );
    }
    echo str_repeat("-", 100) . "\n\n";
} else {
    echo "⊙ No users assigned to this location\n\n";
}

// Check inventory stocks
$stockCount = InventoryStock::where('location_id', $location->id)->count();
echo "📦 Inventory Stocks: {$stockCount} product(s) have stock at this location\n\n";

// Summary
echo "=== Summary for Location: {$location->name} ===\n";
echo "Location ID: {$location->id}\n";
echo "Location Code: {$location->code}\n";
if ($location->outlet_id && isset($outlet)) {
    echo "Outlet: {$outlet->name} (Type: {$outlet->business_type})\n";
    if ($outlet->business_type === 'fnb') {
        echo "✓ F&B Mode Active - Will filter to FNB categories only\n";
    }
}
if ($users->count() > 0) {
    echo "Users: {$users->count()} user(s) assigned\n";
}
echo "Stocks: {$stockCount} product(s)\n";

echo "\n=== Done ===\n";
