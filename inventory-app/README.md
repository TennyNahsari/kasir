# Inventory Management System - Frontend

Aplikasi frontend untuk mengelola inventory yang berjalan terpisah dari aplikasi POS utama, namun tetap menggunakan backend API dan autentikasi yang sama.

## 🚀 Quick Start

### Installation
```bash
cd inventory-app
npm install
```

### Development
```bash
npm run dev
```
Aplikasi akan berjalan di: **http://localhost:5174**

### Build Production
```bash
npm run build
```

## 📋 Features

### 1. Dashboard
- **Quick Stats**: Total products, low stock count, locations, pending transfers
- **Low Stock Alerts**: Real-time list produk dengan stok menipis
- **Quick Actions**: Akses cepat ke fitur-fitur utama

### 2. Stock Levels (`/inventory/stocks`)
- List semua stok di berbagai lokasi
- Filter berdasarkan location, product name/SKU
- Show low stock only option
- Stock adjustment dengan notes
- View history ledger per product
- Status badge: In Stock / Low Stock / Out of Stock
- Kolom: Product info, Location, Quantity, Reserved, Available, Actions

### 3. Inventory Transfers (`/inventory/transfers`)
- Create transfer between locations
- Multi-item transfer dengan product search
- Workflow: DRAFT → SUBMITTED → APPROVED → COMPLETED
- Filter by status, from/to location
- Transfer detail view dengan action buttons
- Auto stock update saat received

### 4. Transfer Detail (`/inventory/transfers/:id`)
- View complete transfer information
- Action buttons based on status:
  - **DRAFT**: Submit for Approval, Cancel
  - **SUBMITTED**: Approve, Cancel
  - **APPROVED**: Receive Transfer
- Transfer items table
- Audit trail (created by, approved by, received by)

### 5. Inventory Ledger (`/inventory/ledger`)
- Complete movement history
- Filter by:
  - Product (searchable)
  - Location
  - Movement Type (Stock In/Out, Transfer In/Out, Adjustment, Reserved, Released)
  - Date range (from - to)
- Columns: DateTime, Product, Location, Movement Type, Quantity (+/-), Balance After, Reference, Notes

### 6. Locations Management (`/inventory/locations`)
- CRUD untuk Warehouse dan Outlet
- Location info: Name, Type, Address, Phone
- Stock summary per location (Total SKUs, Low Stock Count)
- Quick view stock per location
- Active/Inactive status

## 🎨 UI Components

### Layout
- Responsive design dengan mobile menu
- Header dengan app title dan user info
- Navigation bar (Desktop & Mobile)
- Main content area

### Design System
- **Primary Color**: Blue (#2563eb)
- **Status Colors**:
  - Draft: Gray
  - Submitted: Blue
  - Approved: Purple
  - Completed: Green
  - Cancelled: Red
  - Low Stock: Orange

### Components Pattern
- Modal dialogs untuk forms
- Data tables dengan hover effects
- Status badges dengan warna konsisten
- Action buttons dengan confirmation
- Search input dengan autocomplete dropdown
- Filter sections dengan grid layout

## 🔌 API Integration

### Base URL
```javascript
// vite.config.js
proxy: {
  '/api': {
    target: 'http://localhost:8000',
    changeOrigin: true,
    credentials: 'include'
  }
}
```

### Authentication
- Shared authentication dengan POS app via HTTP-only cookies
- Menggunakan Laravel Sanctum
- Session cookie: `laravel_session`
- CSRF protection dengan `XSRF-TOKEN`

### API Endpoints Used
```javascript
// Locations
GET    /api/locations
POST   /api/locations
PUT    /api/locations/{id}
GET    /api/locations/{id}/stock-summary

// Inventory Stocks
GET    /api/inventory-stocks
GET    /api/inventory-stocks/low-stock
POST   /api/inventory-stocks/adjust
GET    /api/inventory-stocks/ledger

// Transfers
GET    /api/inventory-transfers
POST   /api/inventory-transfers
GET    /api/inventory-transfers/{id}
POST   /api/inventory-transfers/{id}/submit
POST   /api/inventory-transfers/{id}/approve
POST   /api/inventory-transfers/{id}/receive
POST   /api/inventory-transfers/{id}/cancel

// Products
GET    /api/products (for search)
```

## 📁 Directory Structure

```
inventory-app/
├── index.html                    # App entry point
├── package.json                  # Dependencies
├── vite.config.js               # Vite config (port 5174)
├── tailwind.config.js           # Tailwind config
├── postcss.config.js            # PostCSS config
└── src/
    ├── main.js                  # App initialization
    ├── App.vue                  # Root component
    ├── assets/                  # Static assets
    ├── components/              # Reusable components
    ├── layouts/
    │   └── MainLayout.vue       # Main app layout
    ├── router/
    │   └── index.js             # Route definitions
    ├── services/
    │   └── api.js               # Axios instance
    ├── stores/
    │   └── auth.js              # Pinia auth store
    ├── utils/                   # Utility functions
    └── views/
        ├── InventoryDashboard.vue    # Dashboard view
        ├── StockList.vue             # Stock levels view
        ├── TransferList.vue          # Transfer list view
        ├── TransferDetail.vue        # Transfer detail view
        ├── InventoryLedger.vue       # Ledger history view
        ├── LocationsView.vue         # Locations management
        └── LoginView.vue             # Login page
```

## 🔐 Authentication Flow

1. User login melalui `/login`
2. Backend set HTTP-only cookie (`laravel_session`)
3. Setiap request otomatis include cookie via `credentials: 'include'`
4. Protected routes check `authStore.isAuthenticated`
5. Logout clear cookie dan redirect ke `/login`

## 🚦 Route Guards

```javascript
router.beforeEach(async (to, from, next) => {
  // Initialize auth
  if (!authStore.initialized) {
    await authStore.checkAuth()
  }

  // Guest routes (login)
  if (to.meta.guest && authStore.isAuthenticated) {
    return next('/')
  }

  // Protected routes
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next('/login')
  }

  next()
})
```

## 🔄 Stock Adjustment Workflow

1. User klik "Adjust Stock" atau tombol "Adjust" pada row
2. Modal terbuka dengan form:
   - Location selector
   - Product search autocomplete
   - New Quantity input
   - Notes textarea
3. Submit POST `/api/inventory-stocks/adjust`
4. Backend:
   - Validate stock di location tersebut
   - Hitung difference (new - old)
   - Create ledger entry dengan movement type ADJUSTMENT
   - Update stock quantity
5. Frontend reload stock list

## 🔄 Transfer Workflow

### Create Transfer
1. User klik "Create Transfer"
2. Modal form dengan:
   - From Location & To Location selectors
   - Transfer Date
   - Items table (product search, quantity, notes)
   - Add/Remove item rows
3. Submit dengan option:
   - **Save Draft**: Status = DRAFT
   - **Create & Submit**: Create + auto submit

### Submit Transfer
1. User buka transfer detail (status DRAFT)
2. Klik "Submit for Approval"
3. Status berubah ke SUBMITTED
4. Cannot edit lagi

### Approve Transfer
1. User dengan authority buka transfer (status SUBMITTED)
2. Klik "Approve Transfer"
3. Status berubah ke APPROVED
4. Stock reserved di source location (reserved_quantity naik)

### Receive Transfer
1. User di destination location buka transfer (status APPROVED)
2. Klik "Receive Transfer"
3. Status berubah ke COMPLETED
4. Stock movements:
   - Source location: quantity turun, reserved_quantity turun
   - Destination location: quantity naik
5. Ledger entries created:
   - TRANSFER_OUT di source
   - TRANSFER_IN di destination

### Cancel Transfer
1. User buka transfer (status DRAFT or SUBMITTED)
2. Klik "Cancel Transfer"
3. Status berubah ke CANCELLED
4. Jika ada reserved stock, release kembali

## 📊 Data Flow

### Stock Level Display
```
Backend: InventoryStock model
- quantity (total stock)
- reserved_quantity (reserved for transfers/orders)
- available_quantity = quantity - reserved_quantity (computed)

Frontend: StockList.vue
- Fetch GET /api/inventory-stocks
- Display dengan filter location/search
- Badge status berdasarkan available_quantity vs reorder_level
```

### Ledger Tracking
```
Every stock movement creates ledger entry:
- movement_type: STOCK_IN, STOCK_OUT, TRANSFER_IN, TRANSFER_OUT, ADJUSTMENT, RESERVED, RELEASED
- quantity: positive (in) or negative (out)
- balance_after: quantity setelah movement
- reference_type & reference_id: source document
- notes: additional information

Frontend: InventoryLedger.vue
- Fetch GET /api/inventory-stocks/ledger with filters
- Display chronological dengan +/- indicators
```

## 🎯 Best Practices

### Component Structure
- Use Composition API (`<script setup>`)
- Reactive data dengan `ref()`
- Lifecycle hooks: `onMounted()`
- Computed properties untuk derived data

### API Calls
- Always use try-catch untuk error handling
- Show user-friendly error messages
- Reload data setelah successful mutation
- Loading states untuk better UX

### Form Handling
- Validate before submit
- Clear form after success
- Confirm destructive actions (cancel, delete)
- Disable buttons saat processing

### State Management
- Auth state di Pinia store
- Component local state untuk form data
- Fetch data on component mount
- Refresh data setelah changes

## 🔧 Configuration

### Port Configuration
File: `vite.config.js`
```javascript
server: {
  port: 5174,  // Inventory App port
  proxy: {
    '/api': {
      target: 'http://localhost:8000',
      changeOrigin: true,
      credentials: 'include'
    }
  }
}
```

### Tailwind Configuration
File: `tailwind.config.js`
- Configured untuk scan semua `.vue` files
- Custom color palette jika perlu
- Responsive breakpoints

## 🐛 Troubleshooting

### Port Already in Use
```bash
# Kill process on port 5174
netstat -ano | findstr :5174
taskkill /PID <PID> /F

# Or change port di vite.config.js
```

### CORS Issues
- Pastikan backend CORS config includes `http://localhost:5174`
- Set `credentials: 'include'` di axios config

### Authentication Not Working
- Check cookie di browser DevTools
- Verify backend session driver = `cookie`
- Check SANCTUM_STATEFUL_DOMAINS include `localhost:5174`

### API 404 Errors
- Verify Laravel routes registered: `php artisan route:list --path=api`
- Check proxy config di vite.config.js
- Verify backend running di port 8000

## 📝 Notes

- Inventory App berjalan **terpisah** dari POS App (port 5173)
- Sharing authentication via HTTP-only cookies
- Backend API sama untuk semua frontend apps
- Responsive design untuk mobile & desktop
- Real-time stock updates saat transfers received

## 🚀 Next Steps

1. ✅ Inventory Management Frontend (COMPLETED)
2. ⏳ Procurement Management Frontend (port 5175)
   - Purchase Requests
   - Purchase Orders
   - Goods Receipt Notes
   - Vendor Management
3. ⏳ Integrate POS with Inventory
   - Auto stock out saat transaction
   - Real-time stock level display
   - Low stock warnings di POS
