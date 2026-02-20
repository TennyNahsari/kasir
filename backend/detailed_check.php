<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== DETAILED CHECK: Locations, Outlets, and Users ===\n\n";

// Check location 7
echo "=== LOCATION 7 (Warung Makan GDC) ===\n";
$location7 = \App\Models\Location::with('outlet')->find(7);
if ($location7) {
    echo "ID: {$location7->id}\n";
    echo "Name: {$location7->name}\n";
    echo "Code: {$location7->code}\n";
    echo "Type: {$location7->type}\n";
    echo "outlet_id: " . ($location7->outlet_id ?? 'NULL') . "\n";
    echo "Outlet: " . ($location7->outlet ? $location7->outlet->name : 'N/A') . "\n";
    echo "Outlet ID: " . ($location7->outlet ? $location7->outlet->id : 'N/A') . "\n";
} else {
    echo "❌ Location 7 not found!\n";
}
echo "\n";

// Check location 12
echo "=== LOCATION 12 (Warung Makan Sedap) ===\n";
$location12 = \App\Models\Location::with('outlet')->find(12);
if ($location12) {
    echo "ID: {$location12->id}\n";
    echo "Name: {$location12->name}\n";
    echo "Code: {$location12->code}\n";
    echo "Type: {$location12->type}\n";
    echo "outlet_id: " . ($location12->outlet_id ?? 'NULL') . "\n";
    echo "Outlet: " . ($location12->outlet ? $location12->outlet->name : 'N/A') . "\n";
    echo "Outlet ID: " . ($location12->outlet ? $location12->outlet->id : 'N/A') . "\n";
} else {
    echo "❌ Location 12 not found!\n";
}
echo "\n";

// Check user kasir3
echo "=== USER: kasir3 ===\n";
$kasir3 = \App\Models\User::where('email', 'like', '%kasir3%')
    ->orWhere('name', 'like', '%kasir3%')
    ->first();

if ($kasir3) {
    echo "ID: {$kasir3->id}\n";
    echo "Name: {$kasir3->name}\n";
    echo "Email: {$kasir3->email}\n";
    echo "Role: {$kasir3->role}\n";
    echo "outlet_id: " . ($kasir3->outlet_id ?? 'NULL') . "\n";
    
    if ($kasir3->outlet_id) {
        $outlet = \App\Models\Outlet::find($kasir3->outlet_id);
        echo "Outlet: " . ($outlet ? $outlet->name : 'N/A') . "\n";
        
        // Check locations for this outlet
        $locations = \App\Models\Location::where('outlet_id', $kasir3->outlet_id)->get();
        echo "Locations for this outlet:\n";
        foreach ($locations as $loc) {
            echo "  - {$loc->name} (ID: {$loc->id})\n";
        }
    }
} else {
    echo "❌ User kasir3 not found!\n";
    echo "\nSearching for users with 'kasir' in name/email:\n";
    $kasirs = \App\Models\User::where('name', 'like', '%kasir%')
        ->orWhere('email', 'like', '%kasir%')
        ->get();
    foreach ($kasirs as $k) {
        echo "  - {$k->name} ({$k->email}), outlet_id: " . ($k->outlet_id ?? 'NULL') . "\n";
    }
}
echo "\n";

// Check all FNB-related users and locations
echo "=== ALL FNB USERS ===\n";
$fnbUsers = \App\Models\User::whereHas('outlet', function($q) {
    $q->where('business_type', 'fnb');
})->get();

foreach ($fnbUsers as $user) {
    $outlet = \App\Models\Outlet::find($user->outlet_id);
    echo "{$user->name} ({$user->email})\n";
    echo "  Role: {$user->role}\n";
    echo "  Outlet ID: {$user->outlet_id}\n";
    echo "  Outlet: " . ($outlet ? $outlet->name : 'N/A') . "\n\n";
}

// Check transactions from location 7
echo "=== TRANSACTIONS FROM LOCATION 7 ===\n";
if ($location7 && $location7->outlet_id) {
    $transactions = \App\Models\Transaction::where('outlet_id', $location7->outlet_id)
        ->whereHas('items', function($q) use ($location7) {
            // Try to identify if this transaction came from location 7
            // by checking notes or other indicators
        })
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();
    
    echo "Recent transactions for outlet " . $location7->outlet_id . ":\n";
    foreach ($transactions as $trans) {
        echo "  - {$trans->transaction_no} | {$trans->created_at} | {$trans->notes}\n";
    }
}
