-- ==============================================================================
-- INITALIZATION SQL SCRIPT FOR KASIR / POS APPLICATION (POSTGRESQL)
-- ==============================================================================
--
-- DOKUMENTASI AKUN LOGIN BAWAAN (DEFAULT CREDENTIALS):
-- ------------------------------------------------------------------------------
-- | No | Email                  | Password   | Role       | Akses / Keterangan      |
-- ------------------------------------------------------------------------------
-- | 1  | owner@kasir.app        | password   | owner      | Akses Semua Outlet & Setting
-- | 2  | supervisor@kasir.app   | password   | supervisor | Supervisor Outlet Retail
-- | 3  | kasir@kasir.app        | password   | kasir      | Kasir Toko Retail Utama
-- | 4  | kasir2@kasir.app       | password   | kasir      | Kasir Minimarket Sejahtera
-- | 5  | kasir3@kasir.app       | password   | kasir      | Kasir Warung Makan F&B
-- | 6  | kitchen@kasir.app      | password   | kitchen    | Bagian Dapur / Kitchen F&B
-- ------------------------------------------------------------------------------
--
-- CARA SETUP DATABASE DARI CMD / TERMINAL:
-- 1. Buat database di PostgreSQL (misal: kasir_db):
--    psql -U postgres -c "CREATE DATABASE kasir_db;"
--
-- 2. Eksekusi script init.sql ke database kasir_db:
--    psql -U postgres -d kasir_db -f init.sql
-- ==============================================================================

-- Drop tables if exists (Order matters due to foreign keys)
DROP TABLE IF EXISTS transaction_items CASCADE;
DROP TABLE IF EXISTS transactions CASCADE;
DROP TABLE IF EXISTS cash_flows CASCADE;
DROP TABLE IF EXISTS table_bookings CASCADE;
DROP TABLE IF EXISTS tables CASCADE;
DROP TABLE IF EXISTS inventory_stocks CASCADE;
DROP TABLE IF EXISTS products CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS locations CASCADE;
DROP TABLE IF EXISTS outlets CASCADE;
DROP TABLE IF EXISTS activity_logs CASCADE;
DROP TABLE IF EXISTS app_settings CASCADE;
DROP TABLE IF EXISTS personal_access_tokens CASCADE;

-- 1. OUTLETS TABLE
CREATE TABLE outlets (
    id BIGSERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    business_type VARCHAR(50) DEFAULT 'retail' NOT NULL, -- 'retail', 'minimarket', 'fnb'
    address TEXT NULL,
    phone VARCHAR(50) NULL,
    enable_qr_order BOOLEAN DEFAULT FALSE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 2. LOCATIONS TABLE (Outlets & FNB Outlets)
CREATE TABLE locations (
    id BIGSERIAL PRIMARY KEY,
    outlet_id BIGINT NULL REFERENCES outlets(id) ON DELETE SET NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) DEFAULT 'OUTLET' NOT NULL, -- 'OUTLET', 'FNB', 'WAREHOUSE'
    fnb_type VARCHAR(50) NULL,
    department_type VARCHAR(50) NULL,
    address TEXT NULL,
    phone VARCHAR(50) NULL,
    person_in_charge VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 3. USERS TABLE
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'kasir' NOT NULL, -- 'owner', 'supervisor', 'inventory', 'kasir', 'kitchen', 'staff'
    staff_role VARCHAR(50) NULL,
    is_technician BOOLEAN DEFAULT FALSE NOT NULL,
    outlet_id BIGINT NULL REFERENCES outlets(id) ON DELETE SET NULL,
    location_id BIGINT NULL REFERENCES locations(id) ON DELETE SET NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 4. CATEGORIES TABLE
CREATE TABLE categories (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    color VARCHAR(50) DEFAULT '#3B82F6' NOT NULL,
    is_active BOOLEAN DEFAULT TRUE NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 5. PRODUCTS TABLE
CREATE TABLE products (
    id BIGSERIAL PRIMARY KEY,
    category_id BIGINT NOT NULL REFERENCES categories(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) UNIQUE NOT NULL,
    barcode VARCHAR(100) NULL,
    selling_price DECIMAL(15, 2) NOT NULL,
    cost_price DECIMAL(15, 2) DEFAULT 0 NOT NULL,
    stock INT DEFAULT 0 NOT NULL,
    min_stock INT DEFAULT 10 NOT NULL,
    track_stock BOOLEAN DEFAULT TRUE NOT NULL,
    image VARCHAR(255) NULL,
    type VARCHAR(50) DEFAULT 'PRODUCT' NOT NULL,
    unit VARCHAR(50) DEFAULT 'Pcs' NOT NULL,
    is_active BOOLEAN DEFAULT TRUE NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 6. INVENTORY STOCKS TABLE (Stock level per location)
CREATE TABLE inventory_stocks (
    id BIGSERIAL PRIMARY KEY,
    location_id BIGINT NOT NULL REFERENCES locations(id) ON DELETE CASCADE,
    product_id BIGINT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    quantity INT DEFAULT 0 NOT NULL,
    min_stock INT DEFAULT 0 NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_location_product UNIQUE (location_id, product_id)
);

-- 7. TABLES (MEJA RESTO) TABLE
CREATE TABLE tables (
    id BIGSERIAL PRIMARY KEY,
    outlet_id BIGINT NOT NULL REFERENCES outlets(id) ON DELETE CASCADE,
    table_number VARCHAR(50) NOT NULL,
    capacity INT DEFAULT 4 NOT NULL,
    status VARCHAR(50) DEFAULT 'available' NOT NULL, -- 'available', 'occupied', 'reserved'
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 8. TABLE BOOKINGS (RESERVASI MEJA) TABLE
CREATE TABLE table_bookings (
    id BIGSERIAL PRIMARY KEY,
    booking_code VARCHAR(50) UNIQUE NOT NULL,
    outlet_id BIGINT NOT NULL REFERENCES outlets(id) ON DELETE CASCADE,
    table_id BIGINT NULL REFERENCES tables(id) ON DELETE SET NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50) NOT NULL,
    customer_email VARCHAR(255) NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    guest_count INT DEFAULT 1 NOT NULL,
    status VARCHAR(50) DEFAULT 'pending' NOT NULL, -- 'pending', 'confirmed', 'cancelled', 'completed'
    notes TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 9. TRANSACTIONS TABLE
CREATE TABLE transactions (
    id BIGSERIAL PRIMARY KEY,
    transaction_no VARCHAR(100) UNIQUE NOT NULL,
    booking_code VARCHAR(255) NULL,
    outlet_id BIGINT NULL REFERENCES outlets(id) ON DELETE SET NULL,
    location_id BIGINT NULL REFERENCES locations(id) ON DELETE SET NULL,
    user_id BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    table_id BIGINT NULL REFERENCES tables(id) ON DELETE SET NULL,
    business_type VARCHAR(50) DEFAULT 'retail' NOT NULL,
    order_type VARCHAR(50) NULL, -- 'dine_in', 'take_away', 'online'
    customer_name VARCHAR(255) NULL,
    subtotal DECIMAL(15, 2) NOT NULL,
    discount DECIMAL(15, 2) DEFAULT 0 NOT NULL,
    tax DECIMAL(15, 2) DEFAULT 0 NOT NULL,
    total_amount DECIMAL(15, 2) NOT NULL,
    payment_method VARCHAR(50) NULL, -- 'cash', 'qris', 'transfer', 'ewallet', 'multiple'
    payment_details JSONB NULL,
    paid_amount DECIMAL(15, 2) DEFAULT 0 NOT NULL,
    change_amount DECIMAL(15, 2) DEFAULT 0 NOT NULL,
    payment_proof TEXT NULL,
    payment_due_at TIMESTAMP NULL,
    status VARCHAR(50) DEFAULT 'completed' NOT NULL, -- 'pending', 'processed', 'delivered', 'completed', 'void', 'refund'
    has_unconfirmed_addon BOOLEAN DEFAULT FALSE NOT NULL,
    addon_summary TEXT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL
);

-- 10. TRANSACTION ITEMS TABLE
CREATE TABLE transaction_items (
    id BIGSERIAL PRIMARY KEY,
    transaction_id BIGINT NOT NULL REFERENCES transactions(id) ON DELETE CASCADE,
    product_id BIGINT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(15, 2) NOT NULL,
    cost_price DECIMAL(15, 2) DEFAULT 0 NOT NULL,
    discount DECIMAL(15, 2) DEFAULT 0 NOT NULL,
    subtotal DECIMAL(15, 2) NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 11. CASH FLOWS TABLE
CREATE TABLE cash_flows (
    id BIGSERIAL PRIMARY KEY,
    outlet_id BIGINT NOT NULL REFERENCES outlets(id) ON DELETE CASCADE,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL, -- 'in', 'out'
    amount DECIMAL(15, 2) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT NULL,
    reference_id VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 12. ACTIVITY LOGS TABLE
CREATE TABLE activity_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR(255) NOT NULL,
    description TEXT NULL,
    ip_address VARCHAR(50) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 13. APP SETTINGS TABLE
CREATE TABLE app_settings (
    id BIGSERIAL PRIMARY KEY,
    key VARCHAR(100) UNIQUE NOT NULL,
    value TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 14. PERSONAL ACCESS TOKENS TABLE (Laravel Sanctum)
CREATE TABLE personal_access_tokens (
    id BIGSERIAL PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);


-- ==============================================================================
-- INITIAL SEED DATA (DATA AWAL SISTEM KASIR)
-- ==============================================================================

-- 1. SEED OUTLETS
INSERT INTO outlets (id, code, name, business_type, address, phone, enable_qr_order, is_active) VALUES
(1, 'RET001', 'Toko Retail Utama', 'retail', 'Jl. Merdeka No. 123', '081234567890', false, true),
(2, 'MKT001', 'Minimarket Sejahtera', 'minimarket', 'Jl. Sudirman No. 456', '081234567891', false, true),
(3, 'FNB001', 'Warung Makan Sedap', 'fnb', 'Jl. Ahmad Yani No. 789', '081234567892', true, true);
SELECT setval('outlets_id_seq', (SELECT MAX(id) FROM outlets));

-- 2. SEED LOCATIONS
INSERT INTO locations (id, outlet_id, code, name, type, fnb_type, address, phone, person_in_charge, is_active) VALUES
(1, 1, 'OUT-001', 'Main Store - Plaza Senayan', 'OUTLET', NULL, 'Plaza Senayan Lt. 2, Jakarta', '021-5555-2001', 'Dewi Lestari', true),
(2, 2, 'OUT-002', 'Store - Grand Indonesia', 'OUTLET', NULL, 'Grand Indonesia Lt. 3, Jakarta', '021-5555-2002', 'Rizki Pratama', true),
(3, 3, 'FNB-001', 'Resto & Cafe Utama', 'FNB', 'RESTAURANT', 'Jl. Ahmad Yani No. 789, Jakarta', '021-5555-3001', 'Budi Sudarsono', true);
SELECT setval('locations_id_seq', (SELECT MAX(id) FROM locations));

-- 3. SEED USERS (Password default hash bcrypt untuk 'password')
-- Hash bcrypt untuk 'password': $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO users (id, name, email, password, role, outlet_id, location_id) VALUES
(1, 'Owner App', 'owner@kasir.app', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', NULL, NULL),
(2, 'Supervisor Retail', 'supervisor@kasir.app', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supervisor', 1, 1),
(3, 'Kasir Retail Utama', 'kasir@kasir.app', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kasir', 1, 1),
(4, 'Kasir Minimarket', 'kasir2@kasir.app', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kasir', 2, 2),
(5, 'Kasir F&B', 'kasir3@kasir.app', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kasir', 3, 3),
(6, 'Kitchen F&B', 'kitchen@kasir.app', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kitchen', 3, 3);
SELECT setval('users_id_seq', (SELECT MAX(id) FROM users));

-- 4. SEED CATEGORIES
INSERT INTO categories (id, name, slug, color, is_active) VALUES
(1, 'Makanan', 'makanan', '#EF4444', true),
(2, 'Minuman', 'minuman', '#3B82F6', true),
(3, 'Snack', 'snack', '#F59E0B', true),
(4, 'Makanan FNB', 'makanan-fnb', '#DC2626', true),
(5, 'Minuman FNB', 'minuman-fnb', '#2563EB', true),
(6, 'Snack FNB', 'snack-fnb', '#D97706', true);
SELECT setval('categories_id_seq', (SELECT MAX(id) FROM categories));

-- 5. SEED PRODUCTS
INSERT INTO products (id, category_id, name, sku, barcode, selling_price, cost_price, stock, min_stock, track_stock, unit, type, is_active) VALUES
(1, 1, 'Indomie Goreng', 'SKU-000001', '2000000000018', 3500.00, 2500.00, 100, 10, true, 'Pcs', 'PRODUCT', true),
(2, 1, 'Roti Tawar', 'SKU-000002', '2000000000025', 15000.00, 12000.00, 50, 10, true, 'Pcs', 'PRODUCT', true),
(3, 2, 'Aqua 600ml', 'SKU-000003', '2000000000032', 4000.00, 3000.00, 200, 10, true, 'Pcs', 'PRODUCT', true),
(4, 2, 'Teh Botol Sosro', 'SKU-000004', '2000000000049', 5000.00, 3500.00, 180, 10, true, 'Pcs', 'PRODUCT', true),
(5, 3, 'Chitato', 'SKU-000005', '2000000000056', 10000.00, 7500.00, 80, 10, true, 'Pcs', 'PRODUCT', true),
(6, 4, 'Nasi Goreng Spesial', 'SKU-000006', '2000000000063', 28000.00, 15000.00, 80, 10, true, 'Porsi', 'PRODUCT', true),
(7, 4, 'Mie Goreng Jawa', 'SKU-000007', '2000000000070', 24000.00, 12000.00, 70, 10, true, 'Porsi', 'PRODUCT', true),
(8, 5, 'Es Teh Manis', 'SKU-000008', '2000000000087', 5000.00, 1500.00, 250, 10, true, 'Gelas', 'PRODUCT', true),
(9, 5, 'Kopi Susu Gula Aren', 'SKU-000009', '2000000000094', 18000.00, 8000.00, 120, 10, true, 'Gelas', 'PRODUCT', true),
(10, 6, 'Pisang Goreng Keju Coklat', 'SKU-000010', '2000000000100', 12000.00, 5000.00, 100, 10, true, 'Porsi', 'PRODUCT', true);
SELECT setval('products_id_seq', (SELECT MAX(id) FROM products));

-- 6. SEED INVENTORY STOCKS (Stok produk per lokasi)
INSERT INTO inventory_stocks (location_id, product_id, quantity, min_stock) VALUES
(1, 1, 100, 10),
(1, 2, 50, 10),
(1, 3, 200, 10),
(1, 4, 180, 10),
(1, 5, 80, 10),
(2, 1, 150, 10),
(2, 3, 250, 10),
(3, 6, 80, 10),
(3, 7, 70, 10),
(3, 8, 250, 10),
(3, 9, 120, 10),
(3, 10, 100, 10);

-- 7. SEED TABLES (Meja Resto FNB001)
INSERT INTO tables (id, outlet_id, table_number, capacity, status) VALUES
(1, 3, 'Meja 01', 4, 'available'),
(2, 3, 'Meja 02', 4, 'available'),
(3, 3, 'Meja 03', 2, 'available'),
(4, 3, 'Meja 04', 2, 'available'),
(5, 3, 'Meja 05', 6, 'available'),
(6, 3, 'Meja VIP 1', 8, 'available');
SELECT setval('tables_id_seq', (SELECT MAX(id) FROM tables));

-- 8. SEED APP SETTINGS
INSERT INTO app_settings (key, value) VALUES
('whatsapp_api_url', ''),
('whatsapp_api_key', ''),
('payment_qris_active', 'true'),
('payment_cash_active', 'true');

-- ==============================================================================
-- FINISH INITALIZATION
-- ==============================================================================
