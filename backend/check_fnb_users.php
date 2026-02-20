<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Outlet;
use App\Models\Location;

echo "=== Checking Users Assigned to FNB Outlet ===\n\n";

// Find FNB outlet
$fnbOutlet = Outlet::where('business_type', 'fnb')->first();

if (!$fnbOutlet) {
    echo "No FNB outlet found!\n";
    exit(1);
}

echo "FNB Outlet: {$fnbOutlet->name} (ID: {$fnbOutlet->id}, Type: {$fnbOutlet->business_type})\n";

// Get location for this outlet
$fnbLocation = Location::where('outlet_id', $fnbOutlet->id)->first();

if ($fnbLocation) {
    echo "FNB Location: {$fnbLocation->name} (ID: {$fnbLocation->id})\n\n";
} else {
    echo "Warning: No location found for this outlet!\n\n";
}

// Find users assigned to this outlet (either by outlet_id or location_id)
$users = User::where(function($q) use ($fnbOutlet, $fnbLocation) {
    $q->where('outlet_id', $fnbOutlet->id);
    if ($fnbLocation) {
        $q->orWhere('location_id', $fnbLocation->id);
    }
})->get();

if ($users->count() > 0) {
    echo "Users assigned to FNB outlet:\n";
    echo str_repeat("-", 100) . "\n";
    printf("%-5s %-30s %-30s %-15s %-10s %-10s\n", "ID", "Name", "Email", "Role", "Outlet ID", "Location ID");
    echo str_repeat("-", 100) . "\n";
    
    foreach ($users as $user) {
        printf(
            "%-5s %-30s %-30s %-15s %-10s %-10s\n",
            $user->id,
            $user->name,
            $user->email,
            $user->role,
            $user->outlet_id ?: '-',
            $user->location_id ?: '-'
        );
    }
    echo str_repeat("-", 100) . "\n\n";
    
    echo "Testing Instructions:\n";
    echo "====================\n";
    foreach ($users as $user) {
        echo "\n[User: {$user->name}]\n";
        echo "  Login with:\n";
        echo "  - Email: {$user->email}\n";
        echo "  - Password: (use the password you set for this user)\n";
        echo "  \n";
        echo "  Expected behavior in POS Kasir:\n";
        echo "  ✓ Only show 3 categories: FNB Makanan, FNB Minuman, FNB Snack\n";
        echo "  ✓ Only show FNB products (Nasi Goreng, Mie Ayam, Es Teh, etc.)\n";
        echo "  ✗ Should NOT show other categories (Elektronik, Fashion, etc.)\n";
        echo "  ✗ Should NOT show non-FNB products\n";
    }
} else {
    echo "No users assigned to FNB outlet!\n";
    echo "\nYou need to assign a user to the FNB outlet.\n";
    echo "Update a user with:\n";
    echo "  - outlet_id = {$fnbOutlet->id}\n";
    if ($fnbLocation) {
        echo "  - location_id = {$fnbLocation->id}\n";
    }
}

echo "\n=== Current Filter Logic ===\n";
echo "The POS system will:\n";
echo "1. Load location info to get outlet business_type\n";
echo "2. If business_type = 'fnb', filter categories to only show FNB categories\n";
echo "3. Filter products to only show products from FNB categories\n";
echo "4. Non-FNB outlets will show all categories and products normally\n";

echo "\n=== Done ===\n";
