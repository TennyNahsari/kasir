<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\Location;
use App\Models\Outlet;
use App\Models\User;
use App\Models\Table;
use App\Models\InventoryStock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        if ($products->isEmpty()) {
            $this->command->warn('No products found to seed transactions.');
            return;
        }

        $fnbLocation = Location::where('code', 'FNB-001')->orWhere('type', 'FNB')->first();
        $retailLocation = Location::where('code', 'OUT-001')->orWhere('type', 'OUTLET')->first();
        
        $fnbOutlet = Outlet::where('business_type', 'fnb')->first();
        $retailOutlet = Outlet::where('business_type', 'retail')->first();

        $kasirFnb = User::where('role', 'kasir')->where('outlet_id', $fnbOutlet?->id)->first() ?? User::where('role', 'owner')->first();
        $kasirRetail = User::where('role', 'kasir')->where('outlet_id', $retailOutlet?->id)->first() ?? User::where('role', 'owner')->first();
        $owner = User::where('role', 'owner')->first();

        $tables = Table::all();

        $customerNames = [
            'Budi Santoso', 'Siti Rahmawati', 'Ahmad Hidayat', 'Dewi Lestari', 'Eko Prasetyo',
            'Rina Marlina', 'Rian Ardianto', 'Indah Permata', 'Hendra Gunawan', 'Maya Sari',
            'Driver GoFood - Andi', 'Driver GrabFood - Rizky', 'Driver ShopeeFood - Doni',
            'Customer Table 1', 'Customer Table 3', 'Customer Table 5', 'Meja VVIP 2',
            'Agus Setiawan', 'Fajar Ramadhan', 'Nita Wijaya', 'Bambang Hartono', 'Putri Ayu'
        ];

        $paymentMethods = ['cash', 'qris', 'transfer', 'ewallet'];
        $orderTypes = ['dine_in', 'take_away', 'online'];
        $statuses = ['completed', 'completed', 'completed', 'completed', 'processed', 'delivered', 'pending'];

        $startDate = Carbon::now()->subMonths(6);
        $endDate = Carbon::now();

        // Seed 65 realistic transactions across 6 months
        $count = 65;
        
        // Generate random timestamps spread evenly over 180 days
        $timestamps = [];
        for ($i = 0; $i < $count; $i++) {
            $randomDays = rand(0, 180);
            $randomHours = rand(8, 22); // Operating hours 08:00 - 22:00
            $randomMinutes = rand(0, 59);
            $randomSeconds = rand(0, 59);
            $timestamps[] = Carbon::now()->subDays($randomDays)->setHour($randomHours)->setMinute($randomMinutes)->setSecond($randomSeconds);
        }
        
        // Sort timestamps ascending
        usort($timestamps, fn($a, $b) => $a->timestamp <=> $b->timestamp);

        foreach ($timestamps as $index => $timestamp) {
            $isFnb = ($index % 4 !== 0); // 75% F&B, 25% Retail
            
            $businessType = $isFnb ? 'fnb' : 'retail';
            $location = $isFnb ? $fnbLocation : $retailLocation;
            $outlet = $isFnb ? $fnbOutlet : $retailOutlet;
            $user = $isFnb ? $kasirFnb : $kasirRetail;
            
            $orderType = $isFnb ? $orderTypes[array_rand($orderTypes)] : null;
            $table = ($orderType === 'dine_in' && $tables->isNotEmpty()) ? $tables->random() : null;
            
            $status = $statuses[array_rand($statuses)];
            // Recent transactions in last 2 days might be pending or have addon
            $isRecent = $timestamp->diffInDays(Carbon::now()) <= 2;
            $hasAddon = $isRecent && ($orderType === 'dine_in') && (rand(1, 4) === 1);
            
            if ($hasAddon) {
                $status = 'pending';
            }

            $customerName = null;
            if ($orderType === 'online') {
                $customerName = 'Online - ' . $customerNames[array_rand($customerNames)];
            } elseif ($orderType === 'dine_in' || $orderType === 'take_away') {
                $customerName = $customerNames[array_rand($customerNames)];
            }

            // Pick 1 to 4 random products
            $itemCount = rand(1, 4);
            $selectedProducts = $products->random(min($itemCount, $products->count()));

            $subtotal = 0;
            $itemsData = [];

            foreach ($selectedProducts as $prod) {
                $qty = rand(1, 3);
                $price = $prod->selling_price > 0 ? $prod->selling_price : 20000;
                $itemSubtotal = $price * $qty;
                $subtotal += $itemSubtotal;

                $itemsData[] = [
                    'product_id' => $prod->id,
                    'product_name' => $prod->name,
                    'price' => $price,
                    'quantity' => $qty,
                    'discount' => 0,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $discount = (rand(1, 10) === 1) ? 5000 : 0;
            $tax = round($subtotal * 0.1); // 10% tax
            $total = max(0, $subtotal - $discount + $tax);
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
            $paidAmount = ($paymentMethod === 'cash') ? (ceil($total / 10000) * 10000) : $total;
            $changeAmount = max(0, $paidAmount - $total);

            $trxNo = 'TRX-' . $timestamp->format('YmdHis') . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

            $addonSummary = null;
            $notes = null;
            if ($hasAddon) {
                $notes = '[Order Tambahan]';
                $addonSummary = '+1x Es Teh Manis, +1x Extra Rice';
            }

            $trx = Transaction::create([
                'transaction_no' => $trxNo,
                'outlet_id' => $outlet?->id,
                'location_id' => $location?->id,
                'business_type' => $businessType,
                'user_id' => ($orderType === 'online' && rand(1, 2) === 1) ? null : $user?->id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $paymentMethod,
                'customer_name' => $customerName,
                'table_id' => $table?->id,
                'order_type' => $orderType,
                'status' => $status,
                'has_unconfirmed_addon' => $hasAddon,
                'addon_summary' => $addonSummary,
                'notes' => $notes,
                'completed_at' => ($status === 'completed') ? $timestamp : null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            foreach ($itemsData as $item) {
                $item['transaction_id'] = $trx->id;
                $item['created_at'] = $timestamp;
                $item['updated_at'] = $timestamp;
                TransactionItem::create($item);
            }
        }

        $this->command->info('TransactionSeeder executed: 65 transactions created over the last 6 months!');
    }
}
