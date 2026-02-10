<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== Checking Technician Users ===\n\n";

// Check all users with is_technician flag
$technicians = User::where('is_technician', true)->get();

echo "Users with is_technician = true: " . $technicians->count() . "\n\n";

if ($technicians->count() > 0) {
    foreach ($technicians as $tech) {
        echo "- ID: {$tech->id}\n";
        echo "  Name: {$tech->name}\n";
        echo "  Email: {$tech->email}\n";
        echo "  Role: {$tech->role}\n";
        echo "  is_technician: " . ($tech->is_technician ? 'true' : 'false') . "\n\n";
    }
} else {
    echo "No technician users found!\n";
    echo "\nCreating a test technician user...\n";
    
    try {
        $user = User::create([
            'name' => 'Test Technician',
            'email' => 'technician@kasir.test',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'is_technician' => true,
            'is_active' => true
        ]);
        
        echo "✓ Created technician user:\n";
        echo "  Email: technician@kasir.test\n";
        echo "  Password: password\n";
        echo "  Role: staff\n";
        echo "  is_technician: true\n";
    } catch (\Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Check Complete ===\n";
