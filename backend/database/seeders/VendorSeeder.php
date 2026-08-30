<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendors = [
            [
                'code' => 'VND-001',
                'name' => 'PT Sumber Rezeki Jaya',
                'address' => 'Jl. Gajah Mada No. 100, Jakarta Pusat',
                'phone' => '021-6543-2100',
                'email' => 'sales@sumberrezeki.com',
                'contact_person' => 'Hendra Kusuma',
                'tax_id' => '01.234.567.8-901.000',
                'business_license' => 'NIB-1234567890',
                'payment_term_days' => 30,
                'credit_limit' => 100000000,
                'rating' => 4.5,
                'is_active' => true,
                'notes' => 'Supplier utama untuk kategori elektronik',
            ],
            [
                'code' => 'VND-002',
                'name' => 'CV Maju Bersama',
                'address' => 'Jl. Veteran No. 45, Bandung',
                'phone' => '022-7654-3210',
                'email' => 'purchasing@majubersama.co.id',
                'contact_person' => 'Rina Wulandari',
                'tax_id' => '01.987.654.3-210.000',
                'business_license' => 'NIB-0987654321',
                'payment_term_days' => 45,
                'credit_limit' => 75000000,
                'rating' => 4.0,
                'is_active' => true,
                'notes' => 'Supplier fashion dan textile',
            ],
            [
                'code' => 'VND-003',
                'name' => 'PT Sentosa Makmur',
                'address' => 'Jl. Sudirman No. 234, Jakarta Selatan',
                'phone' => '021-5678-9012',
                'email' => 'orders@sentosamakmur.com',
                'contact_person' => 'Bambang Suryanto',
                'tax_id' => '02.345.678.9-012.000',
                'business_license' => 'NIB-2345678901',
                'payment_term_days' => 30,
                'credit_limit' => 150000000,
                'rating' => 5.0,
                'is_active' => true,
                'notes' => 'Supplier premium - Fast delivery',
            ],
            [
                'code' => 'VND-004',
                'name' => 'Toko Grosir Sukses',
                'address' => 'Jl. Pasar Baru No. 78, Jakarta',
                'phone' => '021-3456-7890',
                'email' => 'info@grosir-sukses.com',
                'contact_person' => 'Lina Handayani',
                'tax_id' => '03.456.789.0-123.000',
                'business_license' => 'NIB-3456789012',
                'payment_term_days' => 14,
                'credit_limit' => 50000000,
                'rating' => 3.5,
                'is_active' => true,
                'notes' => 'Supplier lokal - Harga kompetitif',
            ],
            [
                'code' => 'VND-005',
                'name' => 'PT Global Teknologi Indonesia',
                'address' => 'Jl. HR Rasuna Said Kav C-11, Jakarta',
                'phone' => '021-5200-1000',
                'email' => 'procurement@globaltek.id',
                'contact_person' => 'David Hartono',
                'tax_id' => '04.567.890.1-234.000',
                'business_license' => 'NIB-4567890123',
                'payment_term_days' => 60,
                'credit_limit' => 200000000,
                'rating' => 4.8,
                'is_active' => true,
                'notes' => 'Authorized distributor - IT equipment',
            ],
        ];

        foreach ($vendors as $vendor) {
            Vendor::firstOrCreate(['code' => $vendor['code']], $vendor);
        }

        $this->command->info('Vendors seeded successfully!');
    }
}
