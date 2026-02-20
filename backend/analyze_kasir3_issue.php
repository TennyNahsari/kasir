<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== Checking User kasir3 Current Setup ===\n\n";

$kasir3 = \App\Models\User::where('email', 'kasir3@kasir.app')->first();

if ($kasir3) {
    echo "User: {$kasir3->name}\n";
    echo "Email: {$kasir3->email}\n";
    echo "Role: {$kasir3->role}\n";
    echo "outlet_id: " . ($kasir3->outlet_id ?? 'NULL') . "\n";
    
    // Check if user has location_id field
    $columns = \Schema::getColumnListing('users');
    echo "\nUser table columns:\n";
    foreach ($columns as $col) {
        if (in_array($col, ['id', 'name', 'email', 'role', 'outlet_id', 'location_id'])) {
            $value = $kasir3->$col ?? 'NULL';
            echo "  - {$col}: {$value}\n";
        }
    }
}

echo "\n=== Checking Transactions Table ===\n";
$transColumns = \Schema::getColumnListing('transactions');
echo "Transaction table has these location/outlet columns:\n";
foreach ($transColumns as $col) {
    if (str_contains($col, 'outlet') || str_contains($col, 'location')) {
        echo "  - {$col}\n";
    }
}

echo "\n=== Recent Transactions ===\n";
$transactions = \App\Models\Transaction::orderBy('created_at', 'desc')->take(3)->get();
foreach ($transactions as $trans) {
    echo "\nTransaction: {$trans->transaction_no}\n";
    echo "  outlet_id: {$trans->outlet_id}\n";
    
    // Check if transaction has location_id
    if (isset($trans->location_id)) {
        echo "  location_id: {$trans->location_id}\n";
    } else {
        echo "  location_id: NOT SET\n";
    }
    echo "  notes: {$trans->notes}\n";
}

echo "\n=== Problem Analysis ===\n";
echo "When kasir3 creates transaction from Location 7:\n";
echo "  1. Frontend sends: location_id = 7\n";
echo "  2. Backend converts: location_id 7 -> outlet_id 3 (from locations.outlet_id)\n";
echo "  3. Transaction saved with: outlet_id = 3\n";
echo "  4. Kasir3 (outlet_id 1) cannot see it because backend filters by user.outlet_id\n\n";

echo "Solutions:\n";
echo "  A) Add location_id to transactions table and filter by that\n";
echo "  B) Change user access to be location-based instead of outlet-based\n";
echo "  C) Update kasir3.outlet_id to match location 7's outlet_id (3)\n";
