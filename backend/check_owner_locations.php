<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== Checking All Locations for Owner POS ===\n\n";

// Check all locations
$allLocations = \App\Models\Location::where('is_active', true)
    ->with('outlet')
    ->orderBy('id')
    ->get();

echo "ALL ACTIVE LOCATIONS:\n";
foreach ($allLocations as $loc) {
    $hasOutlet = $loc->outlet_id ? "✅" : "❌";
    $validType = in_array($loc->type, ['OUTLET', 'FNB']) ? "✅" : "❌";
    $shouldShow = ($loc->outlet_id && in_array($loc->type, ['OUTLET', 'FNB'])) ? "✅ SHOW" : "❌ HIDE";
    
    echo "\nLocation ID: {$loc->id}\n";
    echo "  Name: {$loc->name}\n";
    echo "  Type: {$loc->type} {$validType}\n";
    echo "  outlet_id: " . ($loc->outlet_id ?? 'NULL') . " {$hasOutlet}\n";
    echo "  Outlet: " . ($loc->outlet ? $loc->outlet->name : 'N/A') . "\n";
    echo "  => {$shouldShow}\n";
}

echo "\n=== SHOULD APPEAR IN OWNER DROPDOWN (outlet_id AND type OUTLET/FNB) ===\n";
$validLocations = \App\Models\Location::whereNotNull('outlet_id')
    ->whereIn('type', ['OUTLET', 'FNB'])
    ->where('is_active', true)
    ->with('outlet')
    ->orderBy('id')
    ->get();

foreach ($validLocations as $loc) {
    echo "✅ ID: {$loc->id}, Name: {$loc->name}, Type: {$loc->type}, Outlet: {$loc->outlet->name}\n";
}

echo "\nTotal valid locations: " . $validLocations->count() . "\n";
