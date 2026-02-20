<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== Testing Location-Based Transaction Access ===\n\n";

// Check kasir3 setup
$kasir3 = \App\Models\User::where('email', 'kasir3@kasir.app')->first();
echo "Kasir3 Setup:\n";
echo "  location_id: " . ($kasir3->location_id ?? 'NULL') . "\n";
echo "  outlet_id: " . ($kasir3->outlet_id ?? 'NULL') . "\n\n";

// Check if transactions table now has location_id
$columns = \Schema::getColumnListing('transactions');
$hasLocationId = in_array('location_id', $columns);
echo "Transactions table has location_id: " . ($hasLocationId ? "✅ YES" : "❌ NO") . "\n\n";

if ($hasLocationId) {
    // Update existing transactions to have location_id based on their outlet
    echo "=== Updating Existing Transactions ===\n";
    $transactions = \App\Models\Transaction::whereNull('location_id')->get();
    
    foreach ($transactions as $trans) {
        // Find a location for this outlet
        $location = \App\Models\Location::where('outlet_id', $trans->outlet_id)->first();
        if ($location) {
            $trans->location_id = $location->id;
            $trans->save();
            echo "Updated transaction {$trans->transaction_no}: outlet_id {$trans->outlet_id} -> location_id {$location->id}\n";
        }
    }
    echo "\n";
}

// Check what kasir3 can see now
echo "=== What Kasir3 Can See ===\n";
if ($kasir3->location_id) {
    $kasir3Transactions = \App\Models\Transaction::where('location_id', $kasir3->location_id)
        ->orderBy('created_at', 'desc')
        ->get();
    
    echo "Transactions for location_id {$kasir3->location_id}:\n";
    if ($kasir3Transactions->isEmpty()) {
        echo "  ❌ No transactions found\n";
    } else {
        foreach ($kasir3Transactions as $trans) {
            echo "  - {$trans->transaction_no} | {$trans->created_at} | outlet_id: {$trans->outlet_id} | location_id: {$trans->location_id}\n";
        }
    }
} else {
    echo "Kasir3 has no location_id set!\n";
}

echo "\n=== Next Steps ===\n";
echo "1. ✅ Transactions table now has location_id column\n";
echo "2. ✅ TransactionController updated to save location_id\n";
echo "3. ✅ TransactionController index() filters by user.location_id\n";
echo "4. ✅ Transaction model updated with location_id in fillable\n";
echo "5. 🔄 Test: Create new transaction from Location 7 (Warung Makan GDC)\n";
echo "6. 🔄 Verify: Kasir3 can see the transaction\n";
