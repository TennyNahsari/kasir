<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Outlet;
use App\Models\Location;
use App\Models\User;
use App\Models\Category;

echo "=== FNB Outlet Check ===\n\n";

// Check outlets
$outlets = Outlet::all();
echo "Total outlets: " . $outlets->count() . "\n\n";

if ($outlets->count() > 0) {
    echo "Outlets:\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-5s %-30s %-10s %-15s\n", "ID", "Name", "Code", "Business Type");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($outlets as $outlet) {
        printf(
            "%-5s %-30s %-10s %-15s\n",
            $outlet->id,
            $outlet->name,
            $outlet->code,
            $outlet->business_type
        );
    }
    echo str_repeat("-", 80) . "\n";
}

// Check FNB outlets
echo "\n=== FNB Outlets ===\n";
$fnbOutlets = Outlet::where('business_type', 'fnb')->get();

if ($fnbOutlets->count() > 0) {
    echo "Found " . $fnbOutlets->count() . " FNB outlet(s):\n\n";
    
    foreach ($fnbOutlets as $outlet) {
        echo "Outlet: {$outlet->name} (ID: {$outlet->id}, Code: {$outlet->code})\n";
        
        // Check if outlet has a location
        $location = Location::where('outlet_id', $outlet->id)->first();
        if ($location) {
            echo "  ✓ Location: {$location->name} (ID: {$location->id})\n";
        } else {
            echo "  ✗ No location found\n";
        }
        
        // Check users assigned to this outlet
        $users = User::where('outlet_id', $outlet->id)
            ->orWhere('location_id', $location?->id)
            ->get();
        
        if ($users->count() > 0) {
            echo "  Users assigned:\n";
            foreach ($users as $user) {
                echo "    - {$user->name} ({$user->role})\n";
            }
        } else {
            echo "  ✗ No users assigned\n";
        }
        echo "\n";
    }
} else {
    echo "No FNB outlets found.\n";
    echo "\nYou may need to update an existing outlet to FNB type.\n";
}

// Check FNB categories
echo "\n=== FNB Categories ===\n";
$fnbCategories = Category::where('name', 'like', '%FNB%')->get();

if ($fnbCategories->count() > 0) {
    echo "Found " . $fnbCategories->count() . " FNB categories:\n";
    foreach ($fnbCategories as $cat) {
        echo "  - {$cat->name} (ID: {$cat->id}, Slug: {$cat->slug})\n";
    }
} else {
    echo "No FNB categories found!\n";
}

echo "\n=== Done ===\n";
