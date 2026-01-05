# Procurement Management System - Frontend

Aplikasi frontend untuk mengelola procurement (pengadaan barang) yang berjalan terpisah dari aplikasi POS dan Inventory, namun tetap menggunakan backend API dan autentikasi yang sama.

## 🚀 Quick Start

### Installation
```bash
cd procurement-app
npm install
```

### Development
```bash
npm run dev
```
Aplikasi akan berjalan di: **http://localhost:5175**

### Build Production
```bash
npm run build
```

## 📋 Features

### 1. Dashboard (`/`)
- **Quick Stats**:
  - Pending Purchase Requests
  - Active Purchase Orders
  - Pending Goods Receipt Notes
  - Active Vendors
- **Recent Purchase Requests**: List 5 PR terbaru dengan akses cepat
- **Quick Actions**: Links ke fitur-fitur utama

### 2. Purchase Requests (`/procurement/purchase-requests`)
- List semua purchase requests
- Filter by:
  - Status (Draft, Submitted, Approved, Rejected, Cancelled)
  - Department
  - Date range
- Create new PR dengan multi-item
- View PR details dengan action buttons
- Workflow: DRAFT → SUBMITTED → APPROVED → Create PO

### 3. Purchase Request Detail (`/procurement/purchase-requests/:id`)
- Complete PR information
- Request items table dengan estimated prices
- Total estimated calculation
- Action buttons based on status:
  - **DRAFT**: Submit for Approval, Cancel
  - **SUBMITTED**: Approve, Reject, Cancel
  - **APPROVED**: Create Purchase Order
- Audit trail (requested by, approved by)

### 4. Purchase Orders (`/procurement/purchase-orders`)
- List all purchase orders
- Filter by:
  - Status (Draft, Submitted, Approved, Sent, Cancelled)
  - Vendor
  - Date range
- View PO details
- Link to related PR
- Total amount display
- Workflow: DRAFT → SUBMITTED → APPROVED → SENT to Vendor

### 5. Goods Receipt Notes (`/procurement/goods-receipts`)
- List all goods receipts
- Filter by:
  - Status (Draft, QC Pending, QC Approved, Posted, Rejected)
  - PO Number
  - Date range
- Create GRN from PO
- Quality check workflow
- Post to inventory setelah approval
- Workflow: DRAFT → QC_PENDING → QC_APPROVED → POSTED

### 6. Vendors (`/procurement/vendors`)
- Grid view of all vendors
- Vendor information:
  - Vendor Code & Name
  - Contact Person
  - Email, Phone, Fax
  - Address
  - Payment Terms
  - Lead Time (days)
  - Active/Inactive status
- CRUD operations (Create, Edit)
- Quick stats per vendor

## 🎨 UI Components

### Layout
- Responsive design dengan mobile & desktop menu
- Orange color theme (#ea580c)
- Header dengan app title dan user info
- Navigation bar

### Design System
- **Primary Color**: Orange (#ea580c)
- **Status Colors**:
  - **Purchase Requests**:
    - Draft: Gray
    - Submitted: Blue
    - Approved: Green
    - Rejected: Red
    - Cancelled: Red
    - Partially Ordered: Yellow
    - Fully Ordered: Purple
  - **Purchase Orders**:
    - Draft: Gray
    - Submitted: Blue
    - Approved: Green
    - Sent: Purple
    - Cancelled: Red
  - **Goods Receipts**:
    - Draft: Gray
    - QC Pending: Yellow
    - QC Approved: Blue
    - Posted: Green
    - Rejected: Red

### Components Pattern
- Modal dialogs untuk forms
- Data tables dengan filters
- Status badges konsisten
- Grid layout untuk vendors
- Action buttons dengan confirmation
- Responsive cards

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
- Shared authentication dengan POS & Inventory apps via HTTP-only cookies
- Menggunakan Laravel Sanctum
- Session cookie: `laravel_session`

### API Endpoints Used
```javascript
// Purchase Requests
GET    /api/purchase-requests
POST   /api/purchase-requests
GET    /api/purchase-requests/{id}
POST   /api/purchase-requests/{id}/submit
POST   /api/purchase-requests/{id}/approve
POST   /api/purchase-requests/{id}/reject
POST   /api/purchase-requests/{id}/cancel

// Purchase Orders
GET    /api/purchase-orders
POST   /api/purchase-orders
GET    /api/purchase-orders/{id}
POST   /api/purchase-orders/{id}/submit
POST   /api/purchase-orders/{id}/approve
POST   /api/purchase-orders/{id}/send
POST   /api/purchase-orders/{id}/cancel

// Goods Receipts
GET    /api/goods-receipts
POST   /api/goods-receipts
GET    /api/goods-receipts/{id}
POST   /api/goods-receipts/{id}/qc-check
POST   /api/goods-receipts/{id}/approve
POST   /api/goods-receipts/{id}/post
POST   /api/goods-receipts/{id}/reject

// Vendors
GET    /api/vendors
POST   /api/vendors
GET    /api/vendors/{id}
PUT    /api/vendors/{id}
DELETE /api/vendors/{id}

// Products
GET    /api/products (for search)
```

## 📁 Directory Structure

```
procurement-app/
├── index.html                    # App entry point
├── package.json                  # Dependencies
├── vite.config.js               # Vite config (port 5175)
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
        ├── ProcurementDashboard.vue       # Dashboard
        ├── PurchaseRequestList.vue        # PR list
        ├── PurchaseRequestDetail.vue      # PR detail
        ├── PurchaseOrderList.vue          # PO list
        ├── GoodsReceiptList.vue           # GRN list
        ├── VendorList.vue                 # Vendors
        └── LoginView.vue                  # Login page
```

## 🔐 Authentication Flow

Same as Inventory App - menggunakan HTTP-only cookies yang di-share dengan POS dan Inventory apps.

## 🔄 Purchase Request Workflow

### Create PR
1. User klik "Create PR"
2. Form dengan:
   - Request Date & Required Date
   - Department
   - Items table (product search, quantity, estimated price)
   - Notes
3. Save as DRAFT atau Submit langsung

### Submit PR
1. User buka PR (status DRAFT)
2. Review items & total
3. Klik "Submit for Approval"
4. Status → SUBMITTED

### Approve/Reject PR
1. Approver buka PR (status SUBMITTED)
2. Review details
3. **Approve**: Status → APPROVED, ready untuk create PO
4. **Reject**: Input rejection reason, Status → REJECTED

### Create PO from PR
1. User buka approved PR
2. Klik "Create Purchase Order"
3. System navigate ke PO create form dengan PR items pre-filled

## 🔄 Purchase Order Workflow

### Create PO
1. User klik "Create PO"
2. Option:
   - Create from PR (auto-fill items)
   - Create direct (manual input)
3. Form:
   - Vendor selection
   - PO Date & Expected Delivery Date
   - Items table (product, quantity, unit price)
   - Automatic total calculation
   - Payment terms, shipping info
4. Save as DRAFT atau Submit

### Submit & Approve PO
1. Submit: DRAFT → SUBMITTED
2. Approve: SUBMITTED → APPROVED
3. Send to Vendor: APPROVED → SENT

### Send PO
1. Status APPROVED
2. Klik "Send to Vendor"
3. System dapat trigger:
   - Email to vendor
   - Export PDF
   - Update status → SENT

## 🔄 Goods Receipt Workflow

### Create GRN
1. User klik "Create GRN"
2. Select PO
3. System load PO items
4. Input:
   - Receipt Date
   - Invoice Number & Date
   - Delivery Note Number
   - Items dengan received quantity

### QC Check
1. GRN dibuat dengan status DRAFT
2. QC team review:
   - Physical inspection
   - Quantity verification
   - Quality check
3. Update status:
   - **Pass**: QC_PENDING → QC_APPROVED
   - **Fail**: Input rejection notes → REJECTED

### Approve & Post GRN
1. Supervisor approve GRN (QC_APPROVED)
2. Klik "Approve GRN"
3. Klik "Post to Inventory"
4. Backend:
   - Create inventory stock in movements
   - Update stock levels di location
   - Create ledger entries
   - Update PO received quantities
   - Status → POSTED

## 📊 Data Flow

### PR to PO Conversion
```
1. PR Created & Approved
2. User select PR items to order
3. Create PO with vendor selection
4. PO items linked to PR items
5. PR item ordered_quantity updated
6. PR status: PARTIALLY_ORDERED or FULLY_ORDERED
```

### PO to GRN to Inventory
```
1. PO Sent to Vendor
2. Goods Received → Create GRN
3. QC Check → QC_APPROVED
4. Approve & Post GRN
5. Inventory Stock Updated:
   - InventoryStock.quantity increased
   - InventoryLedger entry (STOCK_IN)
   - Product.average_cost updated
6. PO item received_quantity updated
```

### Vendor Management
```
- Vendor master data
- Used in PO creation
- Lead time affects expected delivery
- Payment terms untuk referensi
- Can deactivate vendor (prevent new POs)
```

## 🎯 Best Practices

### Workflow Management
- Always submit PR before approval
- Approval requires proper authorization
- Cannot edit after submission
- Cancel only allowed for DRAFT/SUBMITTED

### Data Validation
- Check stock availability before PR
- Verify vendor lead times
- Validate received quantities vs ordered
- QC approval before inventory posting

### Financial Controls
- Estimated prices di PR untuk budgeting
- Actual prices di PO untuk commitment
- Match invoice amount with PO amount
- Track payment terms compliance

## 🔧 Configuration

### Port Configuration
File: `vite.config.js`
```javascript
server: {
  port: 5175,  // Procurement App port
  proxy: {
    '/api': {
      target: 'http://localhost:8000',
      changeOrigin: true,
      credentials: 'include'
    }
  }
}
```

## 🐛 Troubleshooting

### Port Already in Use
```bash
# Kill process on port 5175
netstat -ano | findstr :5175
taskkill /PID <PID> /F
```

### CORS Issues
- Pastikan backend CORS config includes `http://localhost:5175`
- Check SANCTUM_STATEFUL_DOMAINS

### API 404 Errors
- Verify routes: `php artisan route:list --path=api/purchase`
- Check backend running di port 8000

## 📝 Integration Points

### With Inventory App
- GRN posting creates stock movements
- Inventory ledger tracking
- Location-based stock receiving

### With POS App (Future)
- View product stock levels
- Check procurement status
- Reorder suggestions based on sales

## 🚀 Running Status

```bash
Backend:      http://localhost:8000  ✅ RUNNING
POS App:      http://localhost:5173  ✅ RUNNING
Inventory:    http://localhost:5174  ✅ RUNNING
Procurement:  http://localhost:5175  ✅ RUNNING
```

## 📝 Notes

- Procurement App independent dari POS & Inventory
- Sharing authentication via cookies
- Same backend API untuk semua apps
- Responsive design
- Complete procurement workflow dari request sampai receipt

## 🎓 Next Steps

1. ✅ Procurement Management Frontend (COMPLETED)
2. ⏳ Enhanced Features:
   - Create PR form implementation
   - Create PO from PR flow
   - GRN detail view dengan QC actions
   - PO detail view dengan send email
3. ⏳ Reports & Analytics:
   - Procurement performance metrics
   - Vendor performance analysis
   - Purchase trends
   - Budget vs actual analysis
4. ⏳ Advanced Features:
   - Purchase requisition approval workflow
   - Multi-level approval routing
   - Email notifications
   - PDF export (PR, PO, GRN)
   - Barcode scanning untuk GRN
