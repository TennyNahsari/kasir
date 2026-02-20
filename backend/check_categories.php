<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Category;

echo "=== Checking Categories in Database ===\n\n";

$categories = Category::orderBy('sort_order')->orderBy('name')->get();

echo "Total categories: " . $categories->count() . "\n\n";

if ($categories->count() > 0) {
    echo "Existing categories:\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-5s %-30s %-20s %-10s %-10s\n", "ID", "Name", "Slug", "Active", "Sort");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($categories as $cat) {
        printf(
            "%-5s %-30s %-20s %-10s %-10s\n",
            $cat->id,
            $cat->name,
            $cat->slug,
            $cat->is_active ? 'Yes' : 'No',
            $cat->sort_order
        );
    }
    echo str_repeat("-", 80) . "\n";
} else {
    echo "No categories found in database.\n";
}

// Check for FNB categories specifically
echo "\n=== FNB Categories ===\n";
$fnbCategories = Category::where('name', 'like', '%FNB%')->get();

if ($fnbCategories->count() > 0) {
    echo "Found " . $fnbCategories->count() . " FNB categories:\n";
    foreach ($fnbCategories as $cat) {
        echo "  - {$cat->name} (ID: {$cat->id}, Active: " . ($cat->is_active ? 'Yes' : 'No') . ")\n";
    }
} else {
    echo "No FNB categories found.\n";
    echo "\nCreating FNB categories...\n";
    
    $fnbCats = [
        ['name' => 'FNB Makanan', 'slug' => 'fnb-makanan', 'color' => '#DC2626', 'is_active' => true],
        ['name' => 'FNB Minuman', 'slug' => 'fnb-minuman', 'color' => '#2563EB', 'is_active' => true],
        ['name' => 'FNB Snack', 'slug' => 'fnb-snack', 'color' => '#D97706', 'is_active' => true],
    ];
    
    foreach ($fnbCats as $cat) {
        $created = Category::create($cat);
        echo "  ✓ Created: {$created->name} (ID: {$created->id})\n";
    }
    
    echo "\nFNB categories created successfully!\n";
}

echo "\n=== Done ===\n";
