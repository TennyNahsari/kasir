<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== Update User kasir3 to Outlet 3 (FNB) ===\n\n";

$kasir3 = \App\Models\User::where('email', 'kasir3@kasir.app')->first();

if ($kasir3) {
    echo "Current setup:\n";
    echo "  Name: {$kasir3->name}\n";
    echo "  Email: {$kasir3->email}\n";
    echo "  outlet_id: {$kasir3->outlet_id}\n\n";
    
    // Update to outlet 3
    $kasir3->outlet_id = 3;
    $kasir3->save();
    
    echo "✅ Updated!\n";
    echo "  New outlet_id: {$kasir3->outlet_id}\n";
    echo "  Now kasir3 can access Warung Makan GDC (Location 7)\n";
} else {
    echo "❌ User kasir3@kasir.app not found\n";
}
