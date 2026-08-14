<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Product;
use App\Models\Location;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $locations = Location::all();

        if ($products->isEmpty() || $locations->isEmpty()) {
            $this->command->warn('Products or Locations missing for AssetSeeder.');
            return;
        }

        $laptopProduct = $products->firstWhere('sku', 'INV-FIN-001') ?? $products->first();
        $inkProduct = $products->firstWhere('sku', 'INV-CON-001') ?? $products->first();
        $mouseProduct = $products->firstWhere('sku', 'INV-FIN-002') ?? $products->first();
        $keyboardProduct = $products->firstWhere('sku', 'INV-FIN-003') ?? $products->first();
        
        $whLocation = $locations->firstWhere('type', 'WAREHOUSE') ?? $locations->first();
        $storeLocation = $locations->firstWhere('type', 'OUTLET') ?? $locations->last();

        $assets = [
            [
                'product_id' => $laptopProduct->id,
                'asset_tag' => 'AST-2026-0001',
                'serial_number' => 'SN-DELL-88001',
                'location_id' => $storeLocation->id,
                'pic' => 'Ahmad Teknisi',
                'assigned_date' => now()->subMonths(3),
                'status' => 'IN_USE',
                'condition' => 'GOOD',
                'purchase_date' => now()->subMonths(6)->toDateString(),
                'purchase_price' => 8500000,
                'useful_life_months' => 36,
                'depreciation_method' => 'STRAIGHT_LINE',
                'current_value' => 7000000,
                'warranty_until' => now()->addMonths(18)->toDateString(),
                'notes' => 'Laptop operasional kasir toko utama',
            ],
            [
                'product_id' => $inkProduct->id,
                'asset_tag' => 'AST-2026-0002',
                'serial_number' => 'SN-EPSON-77002',
                'location_id' => $storeLocation->id,
                'pic' => 'Budi Maintenance',
                'assigned_date' => now()->subMonths(2),
                'status' => 'IN_USE',
                'condition' => 'FAIR',
                'purchase_date' => now()->subMonths(5)->toDateString(),
                'purchase_price' => 2500000,
                'useful_life_months' => 24,
                'depreciation_method' => 'STRAIGHT_LINE',
                'current_value' => 1800000,
                'warranty_until' => now()->addMonths(6)->toDateString(),
                'notes' => 'Printer cetak struk & dokumen',
            ],
            [
                'product_id' => $mouseProduct->id,
                'asset_tag' => 'AST-2026-0003',
                'serial_number' => 'SN-LOGI-55003',
                'location_id' => $whLocation->id,
                'pic' => 'Ahmad Teknisi',
                'assigned_date' => now()->subMonth(),
                'status' => 'IN_USE',
                'condition' => 'GOOD',
                'purchase_date' => now()->subMonths(2)->toDateString(),
                'purchase_price' => 450000,
                'useful_life_months' => 12,
                'depreciation_method' => 'STRAIGHT_LINE',
                'current_value' => 375000,
                'warranty_until' => now()->addMonths(10)->toDateString(),
                'notes' => 'Mouse wireless kantor gudang',
            ],
            [
                'product_id' => $keyboardProduct->id,
                'asset_tag' => 'AST-2026-0004',
                'serial_number' => 'SN-KEY-99004',
                'location_id' => $whLocation->id,
                'pic' => 'Admin Gudang',
                'assigned_date' => null,
                'status' => 'AVAILABLE',
                'condition' => 'NEW',
                'purchase_date' => now()->subWeeks(2)->toDateString(),
                'purchase_price' => 650000,
                'useful_life_months' => 24,
                'depreciation_method' => 'STRAIGHT_LINE',
                'current_value' => 650000,
                'warranty_until' => now()->addMonths(12)->toDateString(),
                'notes' => 'Keyboard cadangan di gudang central',
            ],
            [
                'product_id' => $laptopProduct->id,
                'asset_tag' => 'AST-2026-0005',
                'serial_number' => 'SN-DELL-88005',
                'location_id' => $storeLocation->id,
                'pic' => 'Kasir Toko',
                'assigned_date' => now()->subMonths(4),
                'status' => 'MAINTENANCE',
                'condition' => 'POOR',
                'purchase_date' => now()->subYear()->toDateString(),
                'purchase_price' => 8500000,
                'useful_life_months' => 36,
                'depreciation_method' => 'STRAIGHT_LINE',
                'current_value' => 5500000,
                'warranty_until' => now()->addYear()->toDateString(),
                'notes' => 'Unit dalam perbaikan akibat power supply bermasalah',
            ],
        ];

        foreach ($assets as $assetData) {
            Asset::firstOrCreate(
                ['asset_tag' => $assetData['asset_tag']],
                $assetData
            );
        }

        $this->command->info('Assets seeded successfully!');
    }
}
