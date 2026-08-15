<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Outlet;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first outlet for reference
        $outlet = Outlet::first();

        $locations = [
            // Warehouses
            [
                'code' => 'WH-CENTRAL',
                'name' => 'Central Warehouse',
                'type' => 'WAREHOUSE',
                'address' => 'Jl. Industri Raya No. 123, Jakarta',
                'phone' => '021-5555-1001',
                'person_in_charge' => 'Budi Santoso',
                'is_active' => true,
                'outlet_id' => null,
            ],
            [
                'code' => 'WH-NORTH',
                'name' => 'North Warehouse',
                'type' => 'WAREHOUSE',
                'address' => 'Jl. Sunter Raya No. 45, Jakarta Utara',
                'phone' => '021-5555-1002',
                'person_in_charge' => 'Siti Rahayu',
                'is_active' => true,
                'outlet_id' => null,
            ],
            [
                'code' => 'WH-SOUTH',
                'name' => 'South Warehouse',
                'type' => 'WAREHOUSE',
                'address' => 'Jl. TB Simatupang No. 89, Jakarta Selatan',
                'phone' => '021-5555-1003',
                'person_in_charge' => 'Agus Wijaya',
                'is_active' => true,
                'outlet_id' => null,
            ],
            // Outlets
            [
                'code' => 'OUT-001',
                'name' => 'Main Store - Plaza Senayan',
                'type' => 'OUTLET',
                'address' => 'Plaza Senayan Lt. 2, Jakarta',
                'phone' => '021-5555-2001',
                'person_in_charge' => 'Dewi Lestari',
                'is_active' => true,
                'outlet_id' => $outlet ? $outlet->id : null,
            ],
            [
                'code' => 'OUT-002',
                'name' => 'Store - Grand Indonesia',
                'type' => 'OUTLET',
                'address' => 'Grand Indonesia Lt. 3, Jakarta',
                'phone' => '021-5555-2002',
                'person_in_charge' => 'Rizki Pratama',
                'is_active' => true,
                'outlet_id' => $outlet ? $outlet->id : null,
            ],
            [
                'code' => 'OUT-003',
                'name' => 'Store - Pondok Indah Mall',
                'type' => 'OUTLET',
                'address' => 'PIM Lt. 1, Jakarta Selatan',
                'phone' => '021-5555-2003',
                'person_in_charge' => 'Maya Sari',
                'is_active' => true,
                'outlet_id' => $outlet ? $outlet->id : null,
            ],
            // F&B Locations
            [
                'code' => 'FNB-001',
                'name' => 'Resto & Cafe Utama',
                'type' => 'FNB',
                'address' => 'Jl. Ahmad Yani No. 789, Jakarta',
                'phone' => '021-5555-3001',
                'person_in_charge' => 'Budi Sudarsono',
                'is_active' => true,
                'outlet_id' => Outlet::where('business_type', 'fnb')->first()?->id ?? ($outlet ? $outlet->id : null),
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }

        $this->command->info('Locations seeded successfully!');
    }
}
