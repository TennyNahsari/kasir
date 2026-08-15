<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Outlets
        $outlet1 = Outlet::firstOrCreate(
            ['code' => 'RET001'],
            [
                'name' => 'Toko Retail Utama',
                'business_type' => 'retail',
                'address' => 'Jl. Merdeka No. 123',
                'phone' => '081234567890',
                'enable_qr_order' => false,
            ]
        );

        $outlet2 = Outlet::firstOrCreate(
            ['code' => 'MKT001'],
            [
                'name' => 'Minimarket Sejahtera',
                'business_type' => 'minimarket',
                'address' => 'Jl. Sudirman No. 456',
                'phone' => '081234567891',
                'enable_qr_order' => false,
            ]
        );

        $outlet3 = Outlet::firstOrCreate(
            ['code' => 'FNB001'],
            [
                'name' => 'Warung Makan Sedap',
                'business_type' => 'fnb',
                'address' => 'Jl. Ahmad Yani No. 789',
                'phone' => '081234567892',
                'enable_qr_order' => true,
            ]
        );

        // Create Users
        User::firstOrCreate(
            ['email' => 'owner@kasir.app'],
            [
                'name' => 'Owner',
                'password' => bcrypt('password'),
                'role' => 'owner',
                'outlet_id' => null, // Can access all outlets
            ]
        );

        User::firstOrCreate(
            ['email' => 'inventory@kasir.app'],
            [
                'name' => 'Inventory Manager',
                'password' => bcrypt('password'),
                'role' => 'inventory',
                'outlet_id' => null,
            ]
        );

        User::firstOrCreate(
            ['email' => 'supervisor@kasir.app'],
            [
                'name' => 'Supervisor Retail',
                'password' => bcrypt('password'),
                'role' => 'supervisor',
                'outlet_id' => $outlet1->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'kasir@kasir.app'],
            [
                'name' => 'Kasir Retail',
                'password' => bcrypt('password'),
                'role' => 'kasir',
                'outlet_id' => $outlet1->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'kasir2@kasir.app'],
            [
                'name' => 'Kasir Minimarket',
                'password' => bcrypt('password'),
                'role' => 'kasir',
                'outlet_id' => $outlet2->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'kasir3@kasir.app'],
            [
                'name' => 'Kasir F&B',
                'password' => bcrypt('password'),
                'role' => 'kasir',
                'outlet_id' => $outlet3->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'kitchen@kasir.app'],
            [
                'name' => 'Kitchen F&B',
                'password' => bcrypt('password'),
                'role' => 'kitchen',
                'outlet_id' => $outlet3->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'technician@kasir.app'],
            [
                'name' => 'Ahmad Teknisi',
                'password' => bcrypt('password'),
                'role' => 'staff',
                'is_technician' => true,
                'outlet_id' => null,
            ]
        );

        // Create Categories
        $categories = [
            // Retail/Minimarket categories (produk kemasan)
            ['name' => 'Makanan', 'slug' => 'makanan', 'color' => '#EF4444'],
            ['name' => 'Minuman', 'slug' => 'minuman', 'color' => '#3B82F6'],
            ['name' => 'Snack', 'slug' => 'snack', 'color' => '#F59E0B'],
            ['name' => 'Elektronik', 'slug' => 'elektronik', 'color' => '#8B5CF6'],
            ['name' => 'Fashion', 'slug' => 'fashion', 'color' => '#EC4899'],
            ['name' => 'Kebutuhan Rumah', 'slug' => 'kebutuhan-rumah', 'color' => '#10B981'],
            
            // F&B categories (makanan/minuman siap saji)
            ['name' => 'Makanan FNB', 'slug' => 'makanan-fnb', 'color' => '#DC2626'],
            ['name' => 'Minuman FNB', 'slug' => 'minuman-fnb', 'color' => '#2563EB'],
            ['name' => 'Snack FNB', 'slug' => 'snack-fnb', 'color' => '#D97706'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        // Create Products
        $catMakananFnb = Category::where('slug', 'makanan-fnb')->first()?->id ?? 7;
        $catMinumanFnb = Category::where('slug', 'minuman-fnb')->first()?->id ?? 8;
        $catSnackFnb = Category::where('slug', 'snack-fnb')->first()?->id ?? 9;

        $products = [
            // Makanan Retail/Minimarket (produk kemasan)
            ['name' => 'Indomie Goreng', 'category_id' => 1, 'selling_price' => 3500, 'cost_price' => 2500, 'stock' => 100],
            ['name' => 'Roti Tawar', 'category_id' => 1, 'selling_price' => 15000, 'cost_price' => 12000, 'stock' => 50],
            ['name' => 'Mie Sedaap', 'category_id' => 1, 'selling_price' => 3000, 'cost_price' => 2200, 'stock' => 120],
            
            // Minuman Retail/Minimarket (kemasan)
            ['name' => 'Aqua 600ml', 'category_id' => 2, 'selling_price' => 4000, 'cost_price' => 3000, 'stock' => 200],
            ['name' => 'Coca Cola 330ml', 'category_id' => 2, 'selling_price' => 7000, 'cost_price' => 5000, 'stock' => 150],
            ['name' => 'Teh Botol Sosro', 'category_id' => 2, 'selling_price' => 5000, 'cost_price' => 3500, 'stock' => 180],
            
            // Snack Retail/Minimarket (kemasan)
            ['name' => 'Chitato', 'category_id' => 3, 'selling_price' => 10000, 'cost_price' => 7500, 'stock' => 80],
            ['name' => 'Oreo', 'category_id' => 3, 'selling_price' => 8500, 'cost_price' => 6500, 'stock' => 60],
            ['name' => 'Tango', 'category_id' => 3, 'selling_price' => 5000, 'cost_price' => 3500, 'stock' => 100],
            
            // Makanan FNB (siap saji & berstok)
            ['name' => 'Nasi Goreng Spesial', 'category_id' => $catMakananFnb, 'selling_price' => 28000, 'cost_price' => 15000, 'stock' => 80, 'track_stock' => true],
            ['name' => 'Nasi Goreng Seafood', 'category_id' => $catMakananFnb, 'selling_price' => 32000, 'cost_price' => 18000, 'stock' => 60, 'track_stock' => true],
            ['name' => 'Mie Goreng Jawa', 'category_id' => $catMakananFnb, 'selling_price' => 24000, 'cost_price' => 12000, 'stock' => 70, 'track_stock' => true],
            ['name' => 'Mie Goreng Aceh', 'category_id' => $catMakananFnb, 'selling_price' => 26000, 'cost_price' => 13000, 'stock' => 50, 'track_stock' => true],
            ['name' => 'Ayam Goreng Lengkuas', 'category_id' => $catMakananFnb, 'selling_price' => 22000, 'cost_price' => 11000, 'stock' => 90, 'track_stock' => true],
            ['name' => 'Ayam Bakar Madu', 'category_id' => $catMakananFnb, 'selling_price' => 25000, 'cost_price' => 13000, 'stock' => 75, 'track_stock' => true],
            ['name' => 'Sate Ayam Madura (10 tusuk)', 'category_id' => $catMakananFnb, 'selling_price' => 28000, 'cost_price' => 14000, 'stock' => 50, 'track_stock' => true],
            ['name' => 'Soto Ayam Lamongan', 'category_id' => $catMakananFnb, 'selling_price' => 20000, 'cost_price' => 10000, 'stock' => 85, 'track_stock' => true],
            ['name' => 'Sop Buntut Rempah', 'category_id' => $catMakananFnb, 'selling_price' => 48000, 'cost_price' => 28000, 'stock' => 40, 'track_stock' => true],
            ['name' => 'Bebek Goreng Crispy', 'category_id' => $catMakananFnb, 'selling_price' => 35000, 'cost_price' => 20000, 'stock' => 45, 'track_stock' => true],
            ['name' => 'Kwetiau Goreng Seafood', 'category_id' => $catMakananFnb, 'selling_price' => 30000, 'cost_price' => 16000, 'stock' => 55, 'track_stock' => true],
            ['name' => 'Gado-Gado Surabaya', 'category_id' => $catMakananFnb, 'selling_price' => 18000, 'cost_price' => 9000, 'stock' => 65, 'track_stock' => true],
            
            // Minuman FNB (siap saji & berstok)
            ['name' => 'Es Teh Manis', 'category_id' => $catMinumanFnb, 'selling_price' => 5000, 'cost_price' => 1500, 'stock' => 250, 'track_stock' => true],
            ['name' => 'Es Jeruk Peras', 'category_id' => $catMinumanFnb, 'selling_price' => 8000, 'cost_price' => 3500, 'stock' => 200, 'track_stock' => true],
            ['name' => 'Kopi Hitam Tubruk', 'category_id' => $catMinumanFnb, 'selling_price' => 10000, 'cost_price' => 4000, 'stock' => 150, 'track_stock' => true],
            ['name' => 'Kopi Susu Gula Aren', 'category_id' => $catMinumanFnb, 'selling_price' => 18000, 'cost_price' => 8000, 'stock' => 120, 'track_stock' => true],
            ['name' => 'Jus Alpukat Segar', 'category_id' => $catMinumanFnb, 'selling_price' => 15000, 'cost_price' => 7500, 'stock' => 90, 'track_stock' => true],
            ['name' => 'Jus Mangga Arumanis', 'category_id' => $catMinumanFnb, 'selling_price' => 15000, 'cost_price' => 7500, 'stock' => 90, 'track_stock' => true],
            ['name' => 'Matcha Latte Ice', 'category_id' => $catMinumanFnb, 'selling_price' => 22000, 'cost_price' => 10000, 'stock' => 80, 'track_stock' => true],
            ['name' => 'Es Campur Spesial', 'category_id' => $catMinumanFnb, 'selling_price' => 16000, 'cost_price' => 7000, 'stock' => 85, 'track_stock' => true],
            ['name' => 'Chocolate Milkshake', 'category_id' => $catMinumanFnb, 'selling_price' => 20000, 'cost_price' => 9000, 'stock' => 95, 'track_stock' => true],
            ['name' => 'Lemon Tea Warm', 'category_id' => $catMinumanFnb, 'selling_price' => 12000, 'cost_price' => 5000, 'stock' => 110, 'track_stock' => true],
            
            // Snack FNB (siap saji & berstok)
            ['name' => 'Pisang Goreng Keju Coklat', 'category_id' => $catSnackFnb, 'selling_price' => 12000, 'cost_price' => 5000, 'stock' => 100, 'track_stock' => true],
            ['name' => 'Tahu Isi Crispy', 'category_id' => $catSnackFnb, 'selling_price' => 10000, 'cost_price' => 4000, 'stock' => 120, 'track_stock' => true],
            ['name' => 'Cireng Rujak Pedas', 'category_id' => $catSnackFnb, 'selling_price' => 10000, 'cost_price' => 3500, 'stock' => 130, 'track_stock' => true],
            ['name' => 'French Fries (Kentang Goreng)', 'category_id' => $catSnackFnb, 'selling_price' => 15000, 'cost_price' => 7000, 'stock' => 140, 'track_stock' => true],
            ['name' => 'Roti Bakar Coklat Keju', 'category_id' => $catSnackFnb, 'selling_price' => 15000, 'cost_price' => 6500, 'stock' => 90, 'track_stock' => true],
            ['name' => 'Dimsum Ayam (4 pcs)', 'category_id' => $catSnackFnb, 'selling_price' => 18000, 'cost_price' => 9000, 'stock' => 100, 'track_stock' => true],
            ['name' => 'Pempek Palembang Kapal Selam', 'category_id' => $catSnackFnb, 'selling_price' => 22000, 'cost_price' => 11000, 'stock' => 70, 'track_stock' => true],
            ['name' => 'Nachos Cheese Dip', 'category_id' => $catSnackFnb, 'selling_price' => 20000, 'cost_price' => 9500, 'stock' => 80, 'track_stock' => true],
        ];

        foreach ($products as $index => $prod) {
            $sku = 'SKU-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT);
            $barcode = $this->generateEAN13($index + 1);
            
            Product::firstOrCreate(
                ['sku' => $sku],
                array_merge($prod, [
                    'barcode' => $barcode,
                    'stock' => $prod['stock'] ?? 50,
                    'min_stock' => 10,
                    'track_stock' => true,
                ])
            );
        }

        // Create Tables for F&B outlet
        for ($i = 1; $i <= 10; $i++) {
            Table::firstOrCreate(
                [
                    'outlet_id' => $outlet3->id,
                    'table_number' => 'Table ' . $i,
                ],
                [
                    'capacity' => 4,
                    'status' => 'available',
                ]
            );
        }
        
        // Seed inventory & procurement data
        $this->seedInventoryData();
    }

    private function generateEAN13($productId): string
    {
        $code = '200' . str_pad($productId, 9, '0', STR_PAD_LEFT);
        
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$code[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        $checkDigit = (10 - ($sum % 10)) % 10;
        
        return $code . $checkDigit;
    }
    
    // Seed inventory & procurement data
    private function seedInventoryData(): void
    {
        $this->call([
            DepartmentSeeder::class,
            LocationSeeder::class,
            VendorSeeder::class,
            ProductInventorySeeder::class,
            AssetSeeder::class,
            TicketSystemSeeder::class,
            ProcurementSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
