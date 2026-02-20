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

### 7. Asset Management (`/inventory/assets`)
- **Asset Registration**: Register new assets with complete details
- **Asset Tracking**: Track asset location, condition, and status
- **Asset List & Search**: Filter by category, location, status, condition
- **Asset Details**: View complete asset information and history
- **QR Code Generation**: Generate QR code for each asset
- **Asset Assignment**: Assign assets to specific locations or users
- **Maintenance Tracking**: Schedule and track maintenance activities
- **Depreciation Calculation**: Auto-calculate depreciation (Straight Line method)
- **Asset Transfer**: Transfer assets between locations
- **Asset Disposal**: Record asset disposal/write-off with reasons

#### Asset Fields
- **Basic Info**: Asset Code (unique), Name, Description, Category
- **Financial**: Purchase Date, Purchase Price, Salvage Value, Useful Life (years)
- **Location**: Current Location, Assigned User (optional)
- **Condition**: New, Good, Fair, Poor, Damaged
- **Status**: Active, In Maintenance, Disposed, Retired
- **Tracking**: QR Code, Serial Number, Manufacturer, Model
- **Images**: Upload asset photos (up to 5 images)

#### Asset Categories
- **IT Equipment**: Computers, Laptops, Servers, Network Devices
- **Furniture**: Desks, Chairs, Cabinets, Meeting Tables
- **Vehicles**: Cars, Trucks, Motorcycles
- **Machinery**: Production Equipment, Tools
- **Electronics**: Printers, Scanners, Projectors, CCTV
- **Others**: Custom categories

#### Asset Workflow
1. **Register Asset**:
   - Input asset details
   - Upload photos
   - Generate QR code
   - Assign initial location
   - Set depreciation parameters

2. **Track & Monitor**:
   - Scan QR code untuk quick view
   - Update location via transfer
   - Update condition status
   - Log maintenance activities
   - View depreciation value

3. **Maintenance**:
   - Schedule preventive maintenance
   - Log maintenance performed
   - Track maintenance costs
   - Maintenance history per asset
   - Upcoming maintenance alerts

4. **Transfer Asset**:
   - Select asset to transfer
   - Choose destination location
   - Add transfer notes
   - Create transfer request
   - Approval workflow (optional)
   - Update location after receive

5. **Disposal**:
   - Mark asset for disposal
   - Select disposal reason (Sold, Scrapped, Donated, Lost, etc.)
   - Record disposal date and notes
   - Update status to DISPOSED
   - Remove from active asset list

#### Asset Reports
- **Asset Register**: Complete list of all assets
- **Asset by Location**: Group assets per location
- **Asset by Category**: Summary per category
- **Depreciation Report**: Current book value vs purchase price
- **Maintenance Schedule**: Upcoming maintenance calendar
- **Asset Valuation**: Total asset value per location/category
- **Disposal History**: Log of disposed assets

#### Features Detail

**Asset Dashboard**:
- Total Assets count
- Total Asset Value (current book value)
- Assets by Status (pie chart)
- Assets by Category (bar chart)
- Recent Registered Assets
- Assets requiring maintenance
- Depreciation this month

**Asset QR Code**:
- Auto-generate unique QR code per asset
- QR contains: Asset Code, Category, Location
- Scan QR to view asset details instantly
- Print QR stickers for asset labeling
- Mobile-friendly QR scanner

**Depreciation**:
- Calculation method: Straight Line
- Formula: `(Purchase Price - Salvage Value) / Useful Life`
- Monthly depreciation auto-calculated
- Current Book Value displayed
- Fully depreciated assets flagged
- Depreciation schedule view

**Asset History**:
- Registration log
- Location transfer history
- Condition changes
- Maintenance records
- Status changes
- Disposal information

**Asset Search & Filter**:
- Quick search: Asset Code, Name, Serial Number
- Filter by:
  - Category (multi-select)
  - Location (multi-select)
  - Status (Active, In Maintenance, Disposed)
  - Condition (New, Good, Fair, Poor, Damaged)
  - Purchase Date range
  - Value range (min-max)
- Sort by: Code, Name, Purchase Date, Value, Category
- Export to Excel/PDF

**Asset Permissions**:
- **View**: All users dapat view asset list
- **Create**: Authorized users dapat register asset
- **Edit**: Authorized users dapat update asset info
- **Transfer**: Manager level untuk approve transfer
- **Dispose**: Manager/Admin only
- **Maintenance**: Authorized users log maintenance

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

// Assets
GET    /api/assets
POST   /api/assets
GET    /api/assets/{id}
PUT    /api/assets/{id}
DELETE /api/assets/{id}
GET    /api/assets/{id}/qr-code
POST   /api/assets/{id}/upload-image
DELETE /api/assets/{id}/images/{imageId}
GET    /api/assets/categories
GET    /api/assets/by-location/{locationId}
GET    /api/assets/by-category/{category}
POST   /api/assets/{id}/transfer
GET    /api/assets/{id}/history
POST   /api/assets/{id}/maintenance
GET    /api/assets/{id}/maintenance-history
POST   /api/assets/{id}/dispose
GET    /api/assets/dashboard-stats
GET    /api/assets/depreciation-report
GET    /api/assets/export (Excel/PDF)

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
    │   ├── AssetCard.vue        # Asset card component
    │   ├── AssetQRCode.vue      # QR code display/scanner
    │   ├── AssetForm.vue        # Asset registration/edit form
    │   ├── AssetFilters.vue     # Asset search filters
    │   └── MaintenanceLog.vue   # Maintenance history component
    ├── layouts/
    │   └── MainLayout.vue       # Main app layout
    ├── router/
    │   └── index.js             # Route definitions
    ├── services/
    │   └── api.js               # Axios instance
    ├── stores/
    │   └── auth.js              # Pinia auth store
    ├── utils/                   # Utility functions
    │   ├── qrCodeGenerator.js   # QR code generation
    │   └── depreciationCalc.js  # Depreciation calculator
    └── views/
        ├── InventoryDashboard.vue    # Dashboard view
        ├── StockList.vue             # Stock levels view
        ├── TransferList.vue          # Transfer list view
        ├── TransferDetail.vue        # Transfer detail view
        ├── InventoryLedger.vue       # Ledger history view
        ├── LocationsView.vue         # Locations management
        ├── AssetList.vue             # Asset list view
        ├── AssetDetail.vue           # Asset detail view
        ├── AssetRegister.vue         # Asset registration view
        ├── AssetTransfer.vue         # Asset transfer view
        ├── AssetMaintenance.vue      # Maintenance schedule view
        ├── AssetReports.vue          # Asset reports view
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

## � Asset Management Workflow

### Register New Asset
1. User klik "Register Asset" button
2. Form registration terbuka:
   - **Basic Info Tab**:
     - Asset Code (auto-generated or manual)
     - Asset Name (required)
     - Description
     - Category dropdown
     - Serial Number
     - Manufacturer & Model
   - **Financial Tab**:
     - Purchase Date (required)
     - Purchase Price (required)
     - Salvage Value (optional, default 0)
     - Useful Life in years (required for depreciation)
   - **Location Tab**:
     - Location dropdown (required)
     - Assigned User (optional)
   - **Images Tab**:
     - Upload up to 5 images
     - Set primary image
3. Submit POST `/api/assets`
4. Backend:
   - Validate data
   - Auto-generate QR code
   - Calculate initial depreciation
   - Save asset record
   - Create asset registration log
5. Frontend redirect ke Asset Detail
6. Display success message + QR code

### View Asset Details
1. User klik asset dari list atau scan QR code
2. GET `/api/assets/{id}`
3. Display asset information:
   - **Header Section**:
     - Asset Code, Name, Category
     - Status badge, Condition badge
     - QR Code display
     - Action buttons (Edit, Transfer, Maintenance, Dispose)
   - **Details Tab**:
     - All asset information
     - Current location
     - Assigned user
     - Financial data
   - **Depreciation Tab**:
     - Purchase Price
     - Current Book Value
     - Accumulated Depreciation
     - Depreciation per year/month
     - Remaining useful life
     - Depreciation schedule table
   - **History Tab**:
     - Registration log
     - Transfer history
     - Maintenance records
     - Status/condition changes
     - Disposal info (if any)
   - **Images Tab**:
     - Asset photos gallery
     - Upload more images
     - Delete images

### Asset Transfer
1. User pada Asset Detail klik "Transfer Asset"
2. Transfer modal opens:
   - Current Location (disabled)
   - Destination Location dropdown
   - Transfer Date (default today)
   - Transfer Reason/Notes textarea
   - Assigned User at destination (optional)
3. Submit POST `/api/assets/{id}/transfer`
4. Backend:
   - Validate destination location
   - Create transfer record
   - Update asset location
   - Create history log
   - Notify destination manager (optional)
5. Frontend reload asset detail
6. Show success notification

### Schedule Maintenance
1. User pada Asset Detail klik "Schedule Maintenance"
2. Maintenance form modal:
   - Maintenance Type: Preventive / Corrective / Emergency
   - Scheduled Date (required)
   - Description/Work to be done
   - Estimated Cost
   - Assigned Technician (optional)
   - Priority: Low / Medium / High
3. Submit POST `/api/assets/{id}/maintenance`
4. Backend:
   - Create maintenance record
   - Status = SCHEDULED
   - Create reminder/notification
5. Asset status changed to IN_MAINTENANCE (if immediately)
6. Add to maintenance calendar

### Complete Maintenance
1. User buka maintenance record (status SCHEDULED)
2. Click "Complete Maintenance"
3. Completion form:
   - Actual Date Performed
   - Work Actually Done (notes)
   - Actual Cost
   - Parts Replaced (optional)
   - Update Asset Condition dropdown
   - Mark Complete checkbox
4. Submit PUT `/api/assets/maintenance/{id}`
5. Backend:
   - Update maintenance status = COMPLETED
   - Update asset condition if changed
   - Asset status back to ACTIVE
   - Calculate total maintenance cost
   - Create history log
6. Refresh maintenance list

### Asset Disposal
1. User pada Asset Detail klik "Dispose Asset"
2. Disposal confirmation modal:
   - Disposal Reason dropdown:
     - Sold
     - Scrapped
     - Donated
     - Lost/Stolen
     - Damaged Beyond Repair
     - End of Life
   - Disposal Date (default today)
   - Disposal Value (if sold)
   - Disposal Notes textarea
   - Confirm checkbox
3. Submit POST `/api/assets/{id}/dispose`
4. Backend:
   - Update asset status = DISPOSED
   - Record disposal information
   - Calculate final book value
   - Create disposal log
   - Remove from active assets
   - Archive asset record
5. Frontend redirect to Asset List
6. Asset moved to "Disposed Assets" filter

### Scan QR Code
1. User open mobile app atau QR scanner
2. Klik "Scan QR" button
3. Camera opens
4. Scan asset QR code
5. App reads QR data (asset_id)
6. GET `/api/assets/{id}`
7. Display quick view modal:
   - Asset image
   - Asset Code & Name
   - Current Location
   - Status & Condition
   - Last Maintenance Date
   - Next Maintenance (if scheduled)
   - Quick Actions:
     - View Full Details
     - Transfer
     - Log Maintenance
     - Report Issue
8. User can take quick action or view full details

### Asset Depreciation Calculation
1. **On Asset Registration**:
   - Input: Purchase Price, Salvage Value, Useful Life
   - Calculate: Annual Depreciation = (Purchase Price - Salvage Value) / Useful Life
   - Calculate: Monthly Depreciation = Annual Depreciation / 12
   - Store in asset record

2. **Monthly Auto-Calculation** (via scheduled job):
   - For each active asset with depreciation
   - Current Date - Purchase Date = Months Elapsed
   - Accumulated Depreciation = Monthly Depreciation × Months Elapsed
   - Book Value = Purchase Price - Accumulated Depreciation
   - If Book Value <= Salvage Value: Mark as Fully Depreciated

3. **Display on Asset Detail**:
   ```
   Purchase Price:        $10,000
   Salvage Value:         $1,000
   Useful Life:           5 years
   Annual Depreciation:   $1,800
   Monthly Depreciation:  $150
   
   Purchase Date:         Jan 1, 2024
   Current Date:          Dec 1, 2024
   Months Elapsed:        11 months
   
   Accumulated Depreciation: $1,650
   Current Book Value:       $8,350
   Remaining Life:           4 years 1 month
   ```

4. **Depreciation Report**:
   - List all assets with depreciation data
   - Group by category/location
   - Total original value
   - Total current book value
   - Total depreciation
   - Filter by date range
   - Export to Excel/PDF

## �📊 Data Flow

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

### Asset Tracking
```
Backend: Asset model
- Basic Fields: code, name, description, category, serial_number
- Financial: purchase_price, salvage_value, useful_life_years
- Current Value: book_value (computed), accumulated_depreciation (computed)
- Location: location_id, assigned_user_id
- Status: ACTIVE, IN_MAINTENANCE, DISPOSED, RETIRED
- Condition: NEW, GOOD, FAIR, POOR, DAMAGED
- QR Code: qr_code_path (auto-generated)
- Timestamps: purchased_at, disposed_at

Related Models:
- AssetImage: Multiple images per asset
- AssetTransfer: Transfer history between locations
- AssetMaintenance: Maintenance schedule and logs
- AssetHistory: Audit trail for all changes

Frontend: AssetList.vue
- Fetch GET /api/assets with filters
- Display grid/list view with cards
- Filter by category, location, status, condition
- Search by code, name, serial number
- QR code display/scanner integration
- Badge indicators for status and condition

Asset Detail Flow:
1. User clicks asset or scans QR
2. GET /api/assets/{id}
3. Display tabs:
   - Details: All asset information
   - Depreciation: Financial calculations
   - History: All changes and activities
   - Maintenance: Schedule and logs
   - Images: Photo gallery
4. Action buttons based on status:
   - ACTIVE: Edit, Transfer, Maintenance, Dispose
   - IN_MAINTENANCE: View Maintenance, Complete Maintenance
   - DISPOSED: View Only (archived)

Depreciation Calculation (Real-time):
- Method: Straight Line
- Formula: (Purchase Price - Salvage Value) / Useful Life (years)
- Monthly Depreciation = Annual / 12
- Months Since Purchase = Current Date - Purchase Date (in months)
- Accumulated Depreciation = Monthly Depreciation × Months Since Purchase
- Book Value = Purchase Price - Accumulated Depreciation
- If Book Value < Salvage Value: Book Value = Salvage Value (floor)

QR Code System:
- Format: JSON string with asset_id, code, category
- Generation: On asset creation via QR library (qrcode.js)
- Storage: Save as PNG in /storage/qr-codes/
- Scanning: Mobile camera or dedicated scanner
- Quick View: Modal with essential info + quick actions
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

### Asset Management Best Practices
- **QR Code**: Generate immediately on asset creation
- **Images**: Compress before upload, max 2MB per image
- **Depreciation**: Auto-calculate monthly via scheduled job
- **Validation**: Prevent disposal if asset has pending maintenance
- **Transfer**: Require approval for high-value assets (configurable threshold)
- **Maintenance**: Auto-create preventive maintenance schedule based on asset type
- **Search**: Index asset_code and serial_number for fast lookup
- **Audit**: Log all changes (who, what, when) in asset_history table
- **QR Scanning**: Use mobile-optimized scanner, support both camera and file upload
- **Permissions**: Restrict disposal and high-value transfers to managers only

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

### Asset QR Code Issues
```bash
# QR Code not generating
- Check QR library installed: npm list qrcode
- Verify storage/qr-codes directory writable
- Check GD extension enabled di PHP

# QR Code not scanning
- Ensure good lighting for camera
- QR code size minimum 200x200px
- Check camera permissions in browser
- Try file upload if camera fails
```

### Asset Image Upload Issues
```bash
# Image upload fails
- Check file size < 2MB
- Verify mime type (jpg, png, gif only)
- Check storage disk configuration
- Verify public/storage symlink exists: php artisan storage:link

# Image not displaying
- Check image path in response
- Verify storage URL configured correctly
- Check CORS allow storage domain
```

### Depreciation Calculation Wrong
```bash
# Book value negative
- Check salvage_value < purchase_price
- Verify useful_life_years > 0
- Check purchase_date not in future

# Depreciation not updating
- Verify scheduled job running: php artisan schedule:work
- Check asset status = ACTIVE
- Manually trigger: php artisan assets:calculate-depreciation
```

### Asset Transfer Not Working
```bash
# Transfer fails validation
- Check destination location exists and active
- Verify asset status = ACTIVE
- Ensure not already in transit

# Transfer approved but location not updated
- Check database transaction committed
- Verify receive transfer API called
- Check asset_history table for logs
```

## 📝 Notes

- Inventory App berjalan **terpisah** dari POS App (port 5173)
- Sharing authentication via HTTP-only cookies
- Backend API sama untuk semua frontend apps
- Responsive design untuk mobile & desktop
- Real-time stock updates saat transfers received
- **Asset Management Features**:
  - QR code auto-generated untuk setiap asset
  - Depreciation calculation otomatis setiap bulan
  - Asset transfer dengan approval workflow (optional)
  - Maintenance scheduling dengan reminder notifications
  - Asset disposal dengan audit trail lengkap
  - Support multiple images per asset (max 5)
  - Mobile-friendly QR scanner untuk quick asset lookup
  - Export asset reports ke Excel/PDF

## 🚀 Next Steps

1. ✅ Inventory Management Frontend (COMPLETED)
   - Stock Levels & Adjustments
   - Inventory Transfers
   - Inventory Ledger
   - Locations Management
   - **Asset Management (NEW)**
     - Asset Registration & Tracking
     - QR Code Generation & Scanning
     - Depreciation Calculation
     - Maintenance Scheduling
     - Asset Transfer & Disposal
     - Asset Reports & Analytics
2. ⏳ Procurement Management Frontend (port 5175)
   - Purchase Requests
   - Purchase Orders
   - Goods Receipt Notes
   - Vendor Management
3. ⏳ Integrate POS with Inventory
   - Auto stock out saat transaction
   - Real-time stock level display
   - Low stock warnings di POS
4. ⏳ Asset Management Enhancements
   - Barcode support (alongside QR)
   - Asset insurance tracking
   - Asset warranty management
   - Predictive maintenance (AI-based)
   - Asset utilization analytics
   - Integration with IoT sensors (temperature, location tracking)
