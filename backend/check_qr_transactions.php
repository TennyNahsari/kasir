<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== Testing QR Order Transaction Creation ===\n\n";

// Check location 7
$location = \App\Models\Location::find(7);
echo "Location 7: {$location->name}\n";
echo "outlet_id: {$location->outlet_id}\n";
echo "Type: {$location->type}\n\n";

// Check recent transactions for outlet 3 (where location 7 is assigned)
echo "=== Recent Transactions for Outlet 3 ===\n";
$transactions = \App\Models\Transaction::where('outlet_id', 3)
    ->orderBy('created_at', 'desc')
    ->take(10)
    ->get();

if ($transactions->isEmpty()) {
    echo "❌ No transactions found for outlet 3\n\n";
} else {
    echo "Found {$transactions->count()} recent transactions:\n\n";
    foreach ($transactions as $trans) {
        echo "Transaction #{$trans->transaction_no}\n";
        echo "  Created: {$trans->created_at}\n";
        echo "  Status: {$trans->status}\n";
        echo "  Total: Rp " . number_format($trans->total, 0, ',', '.') . "\n";
        echo "  Paid: Rp " . number_format($trans->paid_amount ?? 0, 0, ',', '.') . "\n";
        echo "  Notes: " . ($trans->notes ?? '-') . "\n";
        echo "  Table: " . ($trans->table_id ? "Table {$trans->table_id}" : '-') . "\n";
        echo "\n";
    }
}

// Check pending transactions (QR orders)
echo "=== Pending Transactions (QR Orders) ===\n";
$pendingTransactions = \App\Models\Transaction::where('status', 'pending')
    ->where('outlet_id', 3)
    ->orderBy('created_at', 'desc')
    ->get();

if ($pendingTransactions->isEmpty()) {
    echo "❌ No pending transactions found\n";
    echo "QR orders might not be creating transactions successfully\n\n";
} else {
    echo "Found {$pendingTransactions->count()} pending transactions:\n\n";
    foreach ($pendingTransactions as $trans) {
        echo "Transaction #{$trans->transaction_no}\n";
        echo "  Created: {$trans->created_at}\n";
        echo "  Total: Rp " . number_format($trans->total, 0, ',', '.') . "\n";
        echo "  Notes: {$trans->notes}\n";
        echo "  Table: " . ($trans->table_id ? "Table {$trans->table_id}" : '-') . "\n";
        echo "\n";
    }
}

// Check tables
echo "=== Tables ===\n";
$tables = \App\Models\Table::all();
foreach ($tables as $table) {
    echo "Table ID: {$table->id}, Number: {$table->table_number}, Outlet: {$table->outlet_id}\n";
}
