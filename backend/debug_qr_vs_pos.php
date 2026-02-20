<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== Testing QR Order Location 7 ===\n\n";

// Check location 7
$location = \App\Models\Location::with('outlet')->find(7);

if (!$location) {
    echo "❌ Location 7 not found!\n";
    exit;
}

echo "Location ID: {$location->id}\n";
echo "Name: {$location->name}\n";
echo "Type: {$location->type}\n";
echo "Code: {$location->code}\n";
echo "outlet_id: " . ($location->outlet_id ?? 'NULL') . "\n";
echo "Outlet: " . ($location->outlet ? $location->outlet->name : 'N/A') . "\n";
echo "Outlet Business Type: " . ($location->outlet ? $location->outlet->business_type : 'N/A') . "\n\n";

if (!$location->outlet_id) {
    echo "❌ ERROR: Location 7 does not have outlet_id!\n";
    echo "This will cause 'Location does not have an associated outlet' error\n\n";
} else {
    echo "✅ Location 7 has outlet_id: {$location->outlet_id}\n\n";
}

// Check FNB categories
echo "=== FNB Categories ===\n";
$fnbCategories = \App\Models\Category::where(function($q) {
    $q->where('name', 'like', '%FNB%')
      ->orWhere('slug', 'like', '%fnb%');
})->get();

foreach ($fnbCategories as $cat) {
    echo "ID: {$cat->id}, Name: {$cat->name}, Slug: {$cat->slug}\n";
}
echo "\n";

// Check FNB products
echo "=== FNB Products ===\n";
$fnbCategoryIds = $fnbCategories->pluck('id')->toArray();
$fnbProducts = \App\Models\Product::whereIn('category_id', $fnbCategoryIds)
    ->where('is_active', true)
    ->get();

echo "Total FNB products: " . $fnbProducts->count() . "\n";
foreach ($fnbProducts as $prod) {
    $category = $fnbCategories->firstWhere('id', $prod->category_id);
    echo "  - {$prod->name} ({$category->name})\n";
}
echo "\n";

// Check ALL active products
echo "=== ALL Active Products ===\n";
$allProducts = \App\Models\Product::where('is_active', true)->with('category')->get();
echo "Total active products: " . $allProducts->count() . "\n";

$nonFnbProducts = $allProducts->filter(function($prod) use ($fnbCategoryIds) {
    return !in_array($prod->category_id, $fnbCategoryIds);
});

echo "Non-FNB products: " . $nonFnbProducts->count() . "\n";
foreach ($nonFnbProducts as $prod) {
    echo "  - {$prod->name} ({$prod->category->name})\n";
}
echo "\n";

// Simulate what QR Order sees
echo "=== What QR Order Should Show (location_id=7) ===\n";
echo "Location type: {$location->type}\n";
echo "Should filter to FNB categories: " . ($location->type === 'FNB' ? 'YES' : 'NO') . "\n";
echo "FNB Category IDs: " . implode(', ', $fnbCategoryIds) . "\n";
echo "Products in FNB categories: {$fnbProducts->count()}\n\n";

// Simulate what POS sees for owner selecting location 7
echo "=== What POS Shows for Owner (location_id=7) ===\n";
echo "Owner should see all categories and products\n";
echo "But if non-owner with FNB location, should only see FNB\n";
echo "Total categories: " . \App\Models\Category::count() . "\n";
echo "Total active products: " . $allProducts->count() . "\n";
