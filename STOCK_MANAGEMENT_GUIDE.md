# Stock Management Module - Setup Guide

## Prerequisites

Pastikan aplikasi Anda sudah memiliki:
1. ✅ **Locations** - Minimal 1 location (warehouse/outlet/department)
2. ✅ **Categories** - Kategori produk (optional untuk filter)
3. ✅ **Products** - Produk yang akan di-manage stocknya

## Cara Menggunakan Stock Management

### 1. Persiapan Data

Jika belum ada data locations dan categories, jalankan seeder:

```bash
cd backend
php artisan db:seed --class=LocationSeeder
```

Atau cek data dengan:
```bash
php check_and_seed.php
```

### 2. Menambah Stock Baru

1. Pilih **Location** dari dropdown
2. Klik tombol **"Add Stock"**
3. Pilih produk yang ingin ditambahkan
4. Masukkan jumlah stock awal (Initial Quantity)
5. Set reorder level (opsional) - batas minimum stock sebelum perlu restock
6. Klik **"Add Stock"**

### 3. Adjust Stock (Menambah/Mengurangi Stock)

1. Pilih location yang stocknya ingin di-adjust
2. Klik tombol **"Adjust"** pada produk yang diinginkan
3. Pilih tipe adjustment:
   - **Add Stock**: Menambah stock (misalnya: barang datang dari supplier)
   - **Reduce Stock**: Mengurangi stock (misalnya: barang rusak/expired)
   - **Set to Specific Amount**: Set ke jumlah tertentu (untuk koreksi stock)
4. Masukkan jumlah dan reason (opsional)
5. Klik **"Save"**

### 4. Filter Stock

Gunakan filter untuk mempermudah pencarian:
- **Search**: Cari berdasarkan nama produk atau SKU
- **Category**: Filter berdasarkan kategori produk

## Informasi Stock

Setiap stock menampilkan:
- **Product**: Nama produk
- **SKU**: Kode produk
- **Category**: Kategori produk
- **Quantity**: Total stock fisik
- **Reserved**: Stock yang direserve untuk order
- **Available**: Stock yang tersedia untuk dijual (Quantity - Reserved)

## Troubleshooting

### "No locations found"
- Pastikan sudah ada data locations di database
- Jalankan: `php artisan db:seed --class=LocationSeeder`
- Atau tambah manual di menu Settings > Locations

### "No available products to add"
- Semua produk sudah ada stock di location tersebut
- Atau belum ada produk sama sekali di sistem
- Tambahkan produk baru di Settings > Products

### Dropdown location/category tidak muncul
- Cek koneksi API backend (pastikan backend running di http://localhost:8000)
- Buka browser console untuk melihat error message
- Pastikan user sudah login dengan role 'owner'

## API Endpoints

Stock Management menggunakan endpoints berikut:
- `GET /api/locations` - List semua locations
- `GET /api/categories` - List semua categories
- `GET /api/products` - List semua products
- `GET /api/inventory-stocks` - List stocks per location
- `POST /api/inventory-stocks` - Tambah stock baru
- `POST /api/inventory-stocks/adjust` - Adjust stock quantity

## Features

✅ Multi-location stock management
✅ Stock adjustment dengan tracking
✅ Reserved quantity tracking
✅ Category filtering
✅ Search by product name/SKU
✅ Reorder level setting
✅ Real-time available stock calculation
✅ Loading states & error handling
✅ Responsive design (mobile & desktop)
