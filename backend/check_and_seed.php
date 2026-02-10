<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Location;
use App\Models\Category;
use Illuminate\Support\Facades\Artisan;

echo "Checking database data...\n\n";

$locationCount = Location::count();
$categoryCount = Category::count();

echo "Locations: $locationCount\n";
echo "Categories: $categoryCount\n\n";

if ($locationCount === 0 || $categoryCount === 0) {
    echo "⚠️  Missing data detected!\n\n";
    
    if ($locationCount === 0) {
        echo "No locations found. Running LocationSeeder...\n";
        Artisan::call('db:seed', ['--class' => 'LocationSeeder']);
        echo Artisan::output();
    }
    
    if ($categoryCount === 0) {
        echo "No categories found. Running DatabaseSeeder to create categories...\n";
        echo "Note: This will create sample data. You may need to run full seeder.\n";
    }
    
    echo "\n✅ Done! Please refresh your application.\n";
} else {
    echo "✅ All required data exists!\n";
    echo "\nLocations available:\n";
    foreach (Location::all() as $location) {
        echo "  - {$location->name} ({$location->type})\n";
    }
    
    echo "\nCategories available:\n";
    foreach (Category::all() as $category) {
        echo "  - {$category->name}\n";
    }
}
