<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== Simulating QR Order API Calls for Location 7 ===\n\n";

$locationId = 7;

// 1. Get location info
$location = \App\Models\Location::find($locationId);
echo "1. GET /public/locations/7\n";
echo "   Location: {$location->name}\n";
echo "   Type: {$location->type}\n\n";

// 2. Get all categories
$allCategories = \App\Models\Category::all();
echo "2. GET /public/categories\n";
echo "   Total categories: {$allCategories->count()}\n";
foreach ($allCategories as $cat) {
    echo "   - ID:{$cat->id} {$cat->name} (slug: {$cat->slug})\n";
}
echo "\n";

// 3. Frontend filters FNB categories (simulated)
$locationType = $location->type;
$fnbCategories = $allCategories->filter(function($cat) {
    $slug = strtolower($cat->slug);
    $name = strtoupper($cat->name);
    return str_contains($slug, 'fnb') || str_contains($name, 'FNB');
});

echo "3. Frontend filter (if location type = FNB):\n";
echo "   Location type is FNB: " . ($locationType === 'FNB' ? 'YES' : 'NO') . "\n";
if ($locationType === 'FNB') {
    echo "   Filtered to FNB categories only:\n";
    foreach ($fnbCategories as $cat) {
        echo "   - ID:{$cat->id} {$cat->name}\n";
    }
} else {
    echo "   Show all categories (not FNB location)\n";
}
echo "\n";

// 4. Get all products
$products = \App\Models\Product::where('is_active', true)->with('category')->get();
echo "4. GET /public/products?location_id=7&per_page=100\n";
echo "   Total active products from API: {$products->count()}\n\n";

// 5. Frontend filters products by allowed categories
$allowedCategoryIds = $locationType === 'FNB' 
    ? $fnbCategories->pluck('id')->toArray() 
    : $allCategories->pluck('id')->toArray();

$filteredProducts = $products->filter(function($p) use ($allowedCategoryIds) {
    return in_array($p->category_id, $allowedCategoryIds);
});

echo "5. Frontend filter products by allowed categories:\n";
echo "   Allowed category IDs: " . implode(', ', $allowedCategoryIds) . "\n";
echo "   Filtered products count: {$filteredProducts->count()}\n";
echo "   Products shown to customer:\n";
foreach ($filteredProducts as $prod) {
    echo "   - {$prod->name} (Category: {$prod->category->name})\n";
}
echo "\n";

// Compare with what POS shows for non-owner user
echo "=== What POS Shows for Non-Owner User at Location 7 ===\n";
echo "isFnbMode = true (location type = FNB)\n";
echo "Categories filtered to FNB: {$fnbCategories->count()} categories\n";
echo "Products filtered to FNB: {$filteredProducts->count()} products\n\n";

echo "=== What POS Shows for Owner at Location 7 ===\n";
echo "Owner exempted from filtering\n";
echo "Shows all categories: {$allCategories->count()} categories\n";
echo "Shows all products: {$products->count()} products\n\n";

echo "Expected Behavior:\n";
echo "✅ QR Order (location 7): {$filteredProducts->count()} FNB products\n";
echo "✅ POS Non-Owner (location 7): {$filteredProducts->count()} FNB products\n";
echo "✅ POS Owner (location 7): {$products->count()} ALL products\n";
