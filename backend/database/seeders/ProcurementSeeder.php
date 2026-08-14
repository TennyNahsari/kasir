<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProcurementSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Procurement Location
        $procLocation = Location::firstOrCreate(
            ['code' => 'DEPT-PROC'],
            [
                'name' => 'Procurement Department',
                'type' => 'DEPARTMENT',
                'address' => 'HQ Lt. 4, Jakarta',
                'phone' => '021-5555-9000',
                'person_in_charge' => 'Procurement Head',
                'is_active' => true,
            ]
        );

        $whLocation = Location::where('type', 'WAREHOUSE')->first() ?? Location::first();

        // 2. Create Users
        $procUser = User::firstOrCreate(
            ['email' => 'procurement@kasir.app'],
            [
                'name' => 'Procurement Officer',
                'password' => Hash::make('password'),
                'role' => 'procurement',
                'location_id' => $procLocation->id,
                'is_active' => true,
            ]
        );

        $whUser = User::firstOrCreate(
            ['email' => 'warehouse@kasir.app'],
            [
                'name' => 'Warehouse Manager',
                'password' => Hash::make('password'),
                'role' => 'warehouse',
                'location_id' => $whLocation->id,
                'is_active' => true,
            ]
        );

        $owner = User::where('role', 'owner')->first() ?? $procUser;
        $vendor = Vendor::first();
        $products = Product::where('track_stock', true)->take(3)->get();

        if (!$vendor || $products->isEmpty()) {
            $this->command->warn('Vendor or Products missing, skipping PR/PO/GRN seeding.');
            return;
        }

        // 3. Seed Purchase Requests (PR)
        $pr1 = PurchaseRequest::firstOrCreate(
            ['pr_no' => 'PR-2026-0001'],
            [
                'request_date' => now()->subDays(3)->toDateString(),
                'required_date' => now()->addDays(7)->toDateString(),
                'location_id' => $procLocation->id,
                'requested_by' => $procUser->id,
                'status' => 'PENDING_APPROVAL',
                'notes' => 'Pengadaan perlengkapan kantor & IT bulanan',
            ]
        );

        foreach ($products as $idx => $prod) {
            $estPrice = $prod->cost_price > 0 ? $prod->cost_price : 100000;
            PurchaseRequestItem::firstOrCreate(
                [
                    'pr_id' => $pr1->id,
                    'product_id' => $prod->id,
                ],
                [
                    'quantity' => 10 * ($idx + 1),
                    'estimated_price' => $estPrice,
                ]
            );
        }

        $pr2 = PurchaseRequest::firstOrCreate(
            ['pr_no' => 'PR-2026-0002'],
            [
                'request_date' => now()->subDays(5)->toDateString(),
                'required_date' => now()->addDays(2)->toDateString(),
                'location_id' => $whLocation->id,
                'requested_by' => $whUser->id,
                'approved_by' => $owner->id,
                'approved_at' => now()->subDays(4),
                'status' => 'APPROVED',
                'notes' => 'Restock stok gudang utama',
            ]
        );

        foreach ($products as $idx => $prod) {
            $estPrice = $prod->cost_price > 0 ? $prod->cost_price : 50000;
            PurchaseRequestItem::firstOrCreate(
                [
                    'pr_id' => $pr2->id,
                    'product_id' => $prod->id,
                ],
                [
                    'quantity' => 5 * ($idx + 1),
                    'estimated_price' => $estPrice,
                ]
            );
        }

        // 4. Seed Purchase Orders (PO)
        $po1 = PurchaseOrder::firstOrCreate(
            ['po_no' => 'PO-2026-0001'],
            [
                'vendor_id' => $vendor->id,
                'location_id' => $whLocation->id,
                'created_by' => $procUser->id,
                'approved_by' => $owner->id,
                'approved_at' => now()->subDays(2),
                'order_date' => now()->subDays(2)->toDateString(),
                'expected_delivery_date' => now()->toDateString(),
                'status' => 'SENT',
                'notes' => 'Pengiriman barang sesuai kesepakatan PO',
                'subtotal' => 15000000,
                'tax_amount' => 1650000,
                'discount_amount' => 0,
                'shipping_cost' => 100000,
                'total' => 16750000,
            ]
        );

        $poItem1 = null;
        foreach ($products as $idx => $prod) {
            $price = $prod->cost_price > 0 ? $prod->cost_price : 100000;
            $qty = 10;
            $lineTotal = $qty * $price;
            $item = PurchaseOrderItem::firstOrCreate(
                [
                    'po_id' => $po1->id,
                    'product_id' => $prod->id,
                ],
                [
                    'quantity' => $qty,
                    'quantity_received' => 0,
                    'unit_price' => $price,
                    'line_total' => $lineTotal,
                ]
            );
            if ($idx === 0) $poItem1 = $item;
        }

        // 5. Seed Goods Receipts (GRN)
        $grn1 = GoodsReceipt::firstOrCreate(
            ['grn_no' => 'GRN-2026-0001'],
            [
                'po_id' => $po1->id,
                'location_id' => $whLocation->id,
                'received_by' => $whUser->id,
                'receipt_date' => now()->toDateString(),
                'status' => 'QUALITY_CHECK',
                'is_posted' => false,
                'notes' => 'Penerimaan barang dari vendor, menunggu QC',
            ]
        );

        if ($poItem1) {
            $price = $products[0]->cost_price > 0 ? $products[0]->cost_price : 100000;
            GoodsReceiptItem::firstOrCreate(
                [
                    'grn_id' => $grn1->id,
                    'product_id' => $products[0]->id,
                ],
                [
                    'po_item_id' => $poItem1->id,
                    'quantity_ordered' => 10,
                    'quantity_received' => 10,
                    'quantity_rejected' => 0,
                    'unit_price' => $price,
                    'line_total' => 10 * $price,
                    'quality_status' => 'PENDING',
                ]
            );
        }

        $this->command->info('Procurement locations, users, PRs, POs, and GRNs seeded successfully!');
    }
}
