<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Outlet;
use App\Models\Location;
use App\Models\User;

echo "=== Checking Outlet: Warung Makan GDC ===\n\n";

// Find outlet by name
$outlet = Outlet::where('name', 'like', '%Warung Makan GDC%')->first();

if (!$outlet) {
    echo "Outlet 'Warung Makan GDC' not found in database.\n\n";
    
    echo "Available outlets:\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-5s %-30s %-10s %-15s\n", "ID", "Name", "Code", "Business Type");
    echo str_repeat("-", 80) . "\n";
    
    $allOutlets = Outlet::all();
    foreach ($allOutlets as $o) {
        printf(
            "%-5s %-30s %-10s %-15s\n",
            $o->id,
            $o->name,
            $o->code,
            $o->business_type
        );
    }
    echo str_repeat("-", 80) . "\n";
    exit(0);
}

echo "✓ Outlet Found!\n";
echo str_repeat("-", 80) . "\n";
echo "ID: {$outlet->id}\n";
echo "Name: {$outlet->name}\n";
echo "Code: {$outlet->code}\n";
echo "Business Type: {$outlet->business_type}\n";
echo "Address: " . ($outlet->address ?: '-') . "\n";
echo "Phone: " . ($outlet->phone ?: '-') . "\n";
echo "Active: " . ($outlet->is_active ? 'Yes' : 'No') . "\n";
echo "Enable QR Order: " . ($outlet->enable_qr_order ? 'Yes' : 'No') . "\n";
echo str_repeat("-", 80) . "\n\n";

// Check location
$location = Location::where('outlet_id', $outlet->id)->first();

if ($location) {
    echo "✓ Location Found!\n";
    echo str_repeat("-", 80) . "\n";
    echo "Location ID: {$location->id}\n";
    echo "Location Name: {$location->name}\n";
    echo "Location Code: {$location->code}\n";
    echo "Location Type: {$location->type}\n";
    echo "Active: " . ($location->is_active ? 'Yes' : 'No') . "\n";
    echo str_repeat("-", 80) . "\n\n";
} else {
    echo "✗ No Location Found for this outlet!\n";
    echo "This outlet needs a location to be used in POS system.\n\n";
}

// Check users
$users = User::where('outlet_id', $outlet->id)
    ->orWhere('location_id', $location?->id)
    ->get();

if ($users->count() > 0) {
    echo "✓ Users Assigned ({$users->count()}):\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-5s %-25s %-30s %-15s\n", "ID", "Name", "Email", "Role");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($users as $user) {
        printf(
            "%-5s %-25s %-30s %-15s\n",
            $user->id,
            $user->name,
            $user->email,
            $user->role
        );
    }
    echo str_repeat("-", 80) . "\n\n";
} else {
    echo "✗ No Users Assigned to this outlet\n\n";
}

// Summary
echo "=== Summary ===\n";
if ($outlet->business_type === 'fnb') {
    echo "✓ This is an F&B outlet\n";
    echo "✓ POS will filter to show only FNB categories and products\n";
} else {
    echo "⊙ This is a {$outlet->business_type} outlet\n";
    echo "⊙ POS will show all categories and products\n";
}

if ($location) {
    echo "✓ Location configured\n";
} else {
    echo "✗ Location NOT configured - needs to be created\n";
}

if ($users->count() > 0) {
    echo "✓ Has {$users->count()} user(s) assigned\n";
} else {
    echo "✗ No users assigned\n";
}

echo "\n=== Done ===\n";
