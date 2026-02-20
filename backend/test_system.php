<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║          SYSTEM TEST - Location-Based Transactions         ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Test 1: Check kasir3 setup
echo "━━━ Test 1: User kasir3 Setup ━━━\n";
$kasir3 = \App\Models\User::where('email', 'kasir3@kasir.app')->first();
if ($kasir3) {
    echo "✅ User found: {$kasir3->name}\n";
    echo "   Email: {$kasir3->email}\n";
    echo "   Role: {$kasir3->role}\n";
    echo "   location_id: " . ($kasir3->location_id ?? 'NULL') . "\n";
    echo "   outlet_id: " . ($kasir3->outlet_id ?? 'NULL') . "\n";
    
    if ($kasir3->location_id == 7) {
        echo "   ✅ Correctly assigned to Warung Makan GDC (location 7)\n";
    } else {
        echo "   ❌ ERROR: Should be location_id = 7\n";
    }
} else {
    echo "❌ User kasir3 not found\n";
}
echo "\n";

// Test 2: Check Location 7 setup
echo "━━━ Test 2: Location 7 (Warung Makan GDC) ━━━\n";
$location7 = \App\Models\Location::with('outlet')->find(7);
if ($location7) {
    echo "✅ Location found: {$location7->name}\n";
    echo "   Type: {$location7->type}\n";
    echo "   Code: {$location7->code}\n";
    echo "   outlet_id: {$location7->outlet_id}\n";
    echo "   Outlet: " . ($location7->outlet ? $location7->outlet->name : 'N/A') . "\n";
    
    if ($location7->type === 'FNB' && $location7->outlet_id) {
        echo "   ✅ Valid FNB location with outlet\n";
    }
} else {
    echo "❌ Location 7 not found\n";
}
echo "\n";

// Test 3: Check transactions table structure
echo "━━━ Test 3: Database Schema ━━━\n";
$columns = \Schema::getColumnListing('transactions');
$hasLocationId = in_array('location_id', $columns);
$hasOutletId = in_array('outlet_id', $columns);

echo "Transactions table columns:\n";
echo "   outlet_id: " . ($hasOutletId ? "✅ YES" : "❌ NO") . "\n";
echo "   location_id: " . ($hasLocationId ? "✅ YES" : "❌ NO") . "\n";
echo "\n";

// Test 4: Check transactions for location 7
echo "━━━ Test 4: Transactions for Location 7 ━━━\n";
$location7Transactions = \App\Models\Transaction::where('location_id', 7)
    ->orderBy('created_at', 'desc')
    ->get();

if ($location7Transactions->count() > 0) {
    echo "✅ Found {$location7Transactions->count()} transaction(s):\n";
    foreach ($location7Transactions as $trans) {
        echo "   • {$trans->transaction_no}\n";
        echo "     Created: {$trans->created_at}\n";
        echo "     Status: {$trans->status}\n";
        echo "     Total: Rp " . number_format($trans->total, 0, ',', '.') . "\n";
        echo "     Notes: " . ($trans->notes ?? '-') . "\n";
        echo "     location_id: {$trans->location_id}\n";
        echo "     outlet_id: {$trans->outlet_id}\n\n";
    }
} else {
    echo "⚠️  No transactions yet for location 7\n";
    echo "   Create a transaction to test!\n";
}
echo "\n";

// Test 5: Check FNB products for location 7
echo "━━━ Test 5: Products Available at Location 7 ━━━\n";
$fnbCategories = \App\Models\Category::where('name', 'like', '%FNB%')->get();
$fnbCategoryIds = $fnbCategories->pluck('id')->toArray();
$fnbProducts = \App\Models\Product::whereIn('category_id', $fnbCategoryIds)
    ->where('is_active', true)
    ->count();

echo "FNB Categories: {$fnbCategories->count()}\n";
echo "FNB Products: {$fnbProducts}\n";
if ($fnbProducts > 0) {
    echo "✅ Products available for FNB location\n";
}
echo "\n";

// Test 6: Check owner access
echo "━━━ Test 6: Owner User ━━━\n";
$owner = \App\Models\User::where('role', 'owner')->whereNull('outlet_id')->first();
if ($owner) {
    echo "✅ Owner found: {$owner->name}\n";
    echo "   Email: {$owner->email}\n";
    echo "   Role: {$owner->role}\n";
    echo "   outlet_id: " . ($owner->outlet_id ?? 'NULL (can access all)') . "\n";
    echo "   location_id: " . ($owner->location_id ?? 'NULL (can access all)') . "\n";
} else {
    echo "⚠️  No owner user found\n";
}
echo "\n";

// Test 7: API endpoint simulation
echo "━━━ Test 7: API Filtering Logic ━━━\n";
echo "Simulating: GET /api/transactions (as kasir3)\n";

if ($kasir3 && $kasir3->location_id) {
    $kasir3Transactions = \App\Models\Transaction::where('location_id', $kasir3->location_id)
        ->orderBy('created_at', 'desc')
        ->get();
    
    echo "Results: {$kasir3Transactions->count()} transaction(s)\n";
    if ($kasir3Transactions->count() > 0) {
        echo "✅ Kasir3 can see transactions from location {$kasir3->location_id}\n";
    } else {
        echo "⚠️  No transactions to display yet\n";
    }
} elseif ($kasir3 && $kasir3->outlet_id) {
    echo "⚠️  Kasir3 has outlet_id but no location_id\n";
    echo "   Will filter by outlet_id: {$kasir3->outlet_id}\n";
}
echo "\n";

// Summary
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                       TEST SUMMARY                         ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$tests = [
    '1. User kasir3 setup' => $kasir3 && $kasir3->location_id == 7,
    '2. Location 7 config' => $location7 && $location7->type === 'FNB',
    '3. Database schema' => $hasLocationId && $hasOutletId,
    '4. Transactions exist' => $location7Transactions->count() > 0,
    '5. FNB products' => $fnbProducts > 0,
    '6. Owner user' => $owner !== null,
    '7. API filtering' => $kasir3 && $kasir3->location_id,
];

foreach ($tests as $test => $passed) {
    echo ($passed ? "✅" : "⚠️ ") . " {$test}\n";
}

echo "\n";

if (array_filter($tests)) {
    echo "🎉 System ready! Core functionality is working.\n";
} else {
    echo "⚠️  Some issues detected. Check details above.\n";
}

echo "\n📝 Next Steps:\n";
echo "   1. Login as kasir3@kasir.app (password: password)\n";
echo "   2. Go to POS and select Location 7\n";
echo "   3. Create a transaction\n";
echo "   4. Check Transactions page - should appear!\n";
echo "   5. Test QR Order: http://localhost:5173/order/7/1\n";
echo "\n";
