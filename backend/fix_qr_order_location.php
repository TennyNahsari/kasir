<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== Fix QR Order Transaction Location ===\n\n";

// Find the QR Order transaction
$qrOrder = \App\Models\Transaction::where('notes', 'like', '%QR Order%')
    ->orderBy('created_at', 'desc')
    ->first();

if ($qrOrder) {
    echo "Found QR Order transaction:\n";
    echo "  Transaction: {$qrOrder->transaction_no}\n";
    echo "  Current location_id: " . ($qrOrder->location_id ?? 'NULL') . "\n";
    echo "  outlet_id: {$qrOrder->outlet_id}\n";
    echo "  table_id: " . ($qrOrder->table_id ?? 'NULL') . "\n";
    echo "  notes: {$qrOrder->notes}\n\n";
    
    // This order was from http://localhost:5173/order/7/1
    // So it should be location_id = 7, table_id = 1
    if ($qrOrder->table_id == 1) {
        // Check which location has this table
        $table = \App\Models\Table::find($qrOrder->table_id);
        if ($table) {
            echo "Table {$table->table_number} belongs to outlet_id: {$table->outlet_id}\n";
            
            // Find location 7 which is FNB type for outlet 3
            $location7 = \App\Models\Location::find(7);
            if ($location7 && $location7->outlet_id == $table->outlet_id) {
                echo "Setting location_id to 7 (Warung Makan GDC)\n";
                $qrOrder->location_id = 7;
                $qrOrder->save();
                echo "✅ Updated!\n";
            }
        }
    }
} else {
    echo "❌ QR Order transaction not found\n";
}

echo "\n=== Verify Kasir3 Can Now See Transaction ===\n";
$kasir3 = \App\Models\User::where('email', 'kasir3@kasir.app')->first();
$transactions = \App\Models\Transaction::where('location_id', $kasir3->location_id)
    ->orderBy('created_at', 'desc')
    ->get();

echo "Kasir3 (location_id {$kasir3->location_id}) can see:\n";
foreach ($transactions as $trans) {
    echo "  ✅ {$trans->transaction_no} | {$trans->created_at} | {$trans->notes}\n";
}

if ($transactions->isEmpty()) {
    echo "  ❌ No transactions yet. Create a new transaction from Location 7.\n";
}
