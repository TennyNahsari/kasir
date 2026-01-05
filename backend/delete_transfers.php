<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Before delete:\n";
echo "Transfers: " . DB::table('inventory_transfers')->count() . "\n";
echo "Transfer Items: " . DB::table('inventory_transfer_items')->count() . "\n\n";

DB::table('inventory_transfer_items')->truncate();
DB::table('inventory_transfers')->truncate();

echo "After delete:\n";
echo "Transfers: " . DB::table('inventory_transfers')->count() . "\n";
echo "Transfer Items: " . DB::table('inventory_transfer_items')->count() . "\n";

echo "\nAll transfer records deleted successfully!\n";
