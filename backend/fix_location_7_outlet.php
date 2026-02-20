<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Assign location 7 (Warung Makan GDC) to outlet 3 (Warung Makan Sedap)
$location = \App\Models\Location::find(7);
if ($location) {
    $location->outlet_id = 3;
    $location->save();
    echo "✅ Location 7 (Warung Makan GDC) assigned to Outlet 3 (Warung Makan Sedap)\n";
    echo "   Type: {$location->type}\n";
    echo "   Code: {$location->code}\n";
    echo "   Outlet ID: {$location->outlet_id}\n";
} else {
    echo "❌ Location 7 not found\n";
}
