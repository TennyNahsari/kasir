<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== Locations WITHOUT outlet_id ===\n";
$locationsWithoutOutlet = \App\Models\Location::whereNull('outlet_id')->get();
foreach ($locationsWithoutOutlet as $loc) {
    echo "ID: {$loc->id}, Name: {$loc->name}, Type: {$loc->type}, Code: {$loc->code}\n";
}

echo "\n=== Locations WITH outlet_id ===\n";
$locationsWithOutlet = \App\Models\Location::whereNotNull('outlet_id')->get();
foreach ($locationsWithOutlet as $loc) {
    echo "ID: {$loc->id}, Name: {$loc->name}, Type: {$loc->type}, Code: {$loc->code}, Outlet ID: {$loc->outlet_id}\n";
}

echo "\n=== Outlets ===\n";
$outlets = \App\Models\Outlet::all();
foreach ($outlets as $outlet) {
    echo "ID: {$outlet->id}, Name: {$outlet->name}, Business Type: {$outlet->business_type}\n";
}
