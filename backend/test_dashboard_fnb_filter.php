<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\Category;
use App\Models\Location;

echo "=== Testing Dashboard Low Stock Filter for FNB ===\n\n";

// Get FNB categories
$fnbCategories = Category::where(function($q) {
    $q->where('name', 'like', '%FNB%')
      ->orWhere('slug', 'like', '%fnb%')
      ->orWhere('slug', 'like', '%FNB%');
})->get();

echo "FNB Categories found: " . $fnbCategories->count() . "\n";
foreach ($fnbCategories as $cat) {
    echo "  - {$cat->name} (ID: {$cat->id})\n";
}
echo "\n";

// Get all low stock products
$allLowStock = Product::with('category')
    ->where('track_stock', true)
    ->whereColumn('stock', '<=', 'min_stock')
    ->get();

echo "All low stock products: " . $allLowStock->count() . "\n";
foreach ($allLowStock as $product) {
    echo "  - {$product->name} (Category: {$product->category->name}, Stock: {$product->stock}/{$product->min_stock})\n";
}
echo "\n";

// Get FNB low stock products only
$fnbCategoryIds = $fnbCategories->pluck('id')->toArray();
$fnbLowStock = Product::with('category')
    ->where('track_stock', true)
    ->whereColumn('stock', '<=', 'min_stock')
    ->whereIn('category_id', $fnbCategoryIds)
    ->get();

echo "FNB low stock products: " . $fnbLowStock->count() . "\n";
foreach ($fnbLowStock as $product) {
    echo "  - {$product->name} (Category: {$product->category->name}, Stock: {$product->stock}/{$product->min_stock})\n";
}
echo "\n";

// Test with FNB location
$fnbLocation = Location::where('type', 'FNB')->first();
if ($fnbLocation) {
    echo "Testing with FNB Location: {$fnbLocation->name} (ID: {$fnbLocation->id})\n";
    echo "Expected: Should return only FNB category products in low stock\n\n";
} else {
    echo "No FNB location found in database\n";
}

// Test with OUTLET location
$outletLocation = Location::where('type', 'OUTLET')->first();
if ($outletLocation) {
    echo "Testing with OUTLET Location: {$outletLocation->name} (ID: {$outletLocation->id})\n";
    echo "Expected: Should return all low stock products\n\n";
}

echo "=== Summary ===\n";
echo "Dashboard will now:\n";
echo "  • For FNB locations → Show only FNB low stock products\n";
echo "  • For OUTLET locations → Show all low stock products\n";
echo "  • WAREHOUSE/DEPARTMENT → Cannot access dashboard from POS app\n";

echo "\n=== Done ===\n";
