<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Outlet;
use App\Models\Location;
use Illuminate\Support\Facades\Hash;

echo "=== Creating FNB User ===\n\n";

// Get FNB outlet and location
$fnbOutlet = Outlet::where('business_type', 'fnb')->first();
if (!$fnbOutlet) {
    echo "No FNB outlet found!\n";
    exit(1);
}

$fnbLocation = Location::where('outlet_id', $fnbOutlet->id)->first();
if (!$fnbLocation) {
    echo "No FNB location found!\n";
    exit(1);
}

echo "Found FNB Outlet: {$fnbOutlet->name} (ID: {$fnbOutlet->id})\n";
echo "Found FNB Location: {$fnbLocation->name} (ID: {$fnbLocation->id})\n\n";

// Check if kasir FNB user exists
$existingUser = User::where('email', 'kasir.fnb@test.com')->first();

if ($existingUser) {
    echo "Updating existing user: {$existingUser->name}\n";
    $existingUser->update([
        'outlet_id' => $fnbOutlet->id,
        'location_id' => $fnbLocation->id,
        'role' => 'kasir',
        'is_active' => true,
    ]);
    echo "✓ User updated and assigned to FNB outlet/location\n";
    $user = $existingUser;
} else {
    echo "Creating new FNB kasir user...\n";
    $user = User::create([
        'name' => 'Kasir FNB',
        'email' => 'kasir.fnb@test.com',
        'password' => Hash::make('password'),
        'role' => 'kasir',
        'outlet_id' => $fnbOutlet->id,
        'location_id' => $fnbLocation->id,
        'is_active' => true,
    ]);
    echo "✓ User created\n";
}

echo "\n=== FNB User Details ===\n";
echo "Name: {$user->name}\n";
echo "Email: {$user->email}\n";
echo "Password: password\n";
echo "Role: {$user->role}\n";
echo "Outlet: {$fnbOutlet->name} (Business Type: {$fnbOutlet->business_type})\n";
echo "Location: {$fnbLocation->name}\n";

echo "\n=== Login Instructions ===\n";
echo "1. Login dengan:\n";
echo "   Email: kasir.fnb@test.com\n";
echo "   Password: password\n";
echo "2. Buka menu POS Kasir\n";
echo "3. Seharusnya hanya muncul kategori: FNB Makanan, FNB Minuman, FNB Snack\n";
echo "4. Produk yang muncul hanya produk FNB\n";

echo "\n=== Done ===\n";
