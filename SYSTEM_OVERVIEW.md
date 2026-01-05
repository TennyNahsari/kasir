# Unified System - Complete Documentation

Sistem terintegrasi untuk POS, Inventory Management, dan Procurement Management dengan arsitektur modular.

## 🎯 System Overview

### Architecture
```
┌─────────────────────────────────────────────────────────────┐
│                    Laravel Backend API                       │
│                  (Port 8000 - PostgreSQL)                    │
│                                                              │
│  Authentication: Laravel Sanctum (HTTP-only Cookies)         │
│  - Routes: 80+ API endpoints                                │
│  - Services: Business logic layer                           │
│  - Models: 15 database tables                               │
└─────────────────────────────────────────────────────────────┘
                              │
                              │ Shared Authentication
                              │ Same Database
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
┌───────▼────────┐   ┌────────▼────────┐   ┌───────▼────────┐
│   POS App      │   │  Inventory App  │   │ Procurement    │
│  Port 5173     │   │   Port 5174     │   │ App Port 5175  │
│                │   │                 │   │                │
│ - Transactions │   │ - Stock Levels  │   │ - Purchase     │
│ - Products     │   │ - Transfers     │   │   Requests     │
│ - Tables       │   │ - Adjustments   │   │ - Purchase     │
│ - Reports      │   │ - Locations     │   │   Orders       │
│ - Categories   │   │ - Ledger        │   │ - Goods        │
│                │   │                 │   │   Receipts     │
│                │   │                 │   │ - Vendors      │
└────────────────┘   └─────────────────┘   └────────────────┘
```

## 🚀 Running All Applications

### Backend (Laravel)
```bash
cd backend
php artisan serve
# Running at http://localhost:8000
```

### Frontend Applications

**POS Application**
```bash
cd frontend
npm run dev
# Running at http://localhost:5173
```

**Inventory Management**
```bash
cd inventory-app
npm run dev
# Running at http://localhost:5174
```

**Procurement Management**
```bash
cd procurement-app
npm run dev
# Running at http://localhost:5175
```

## 📊 Database Schema

### Core Tables (6)
1. **outlets** - Outlet/branch information
2. **categories** - Product categories
3. **products** - Product master data
4. **tables** - Restaurant tables
5. **users** - System users dengan roles
6. **transactions** + **transaction_items** - Sales transactions

### Inventory Tables (9)
7. **locations** - Warehouses & outlets
8. **inventory_stocks** - Stock levels per product per location
9. **inventory_ledgers** - All stock movements (audit trail)
10. **inventory_transfers** + **items** - Inter-location transfers
11. **vendors** - Vendor master data
12. **purchase_requests** + **items** - Purchase requisitions
13. **purchase_orders** + **items** - Purchase orders to vendors
14. **goods_receipts** + **items** - Goods receipt notes

## 🔐 Authentication System

### Shared Authentication
- **Method**: HTTP-only Cookies
- **Library**: Laravel Sanctum
- **Cookie**: `laravel_session`
- **CSRF**: `XSRF-TOKEN`
- **Domain**: localhost (development)

### User Roles
- **owner**: Full access semua modul
- **supervisor**: POS, reports, inventory, procurement
- **kasir**: POS only
- **warehouse**: Inventory management
- **procurement**: Procurement management

### CORS Configuration
```php
// backend/config/cors.php
'allowed_origins' => [
    'http://localhost:5173',  // POS
    'http://localhost:5174',  // Inventory
    'http://localhost:5175',  // Procurement
    'http://localhost:3000',
    'http://localhost:8000'
],
'supports_credentials' => true
```

## 📋 API Endpoints Summary

### Authentication (4)
- POST /api/login
- POST /api/logout
- GET /api/user
- GET /api/check-auth

### POS Module (20+)
- Products, Categories, Outlets
- Transactions, Tables
- Reports, Cash Flow

### Inventory Module (30+)
- Locations (6 endpoints)
- Inventory Stocks (4 endpoints)
- Inventory Transfers (9 endpoints)
- Ledger tracking

### Procurement Module (30+)
- Purchase Requests (10 endpoints)
- Purchase Orders (9 endpoints)
- Goods Receipts (10 endpoints)
- Vendors (5 endpoints)

## 🎨 Frontend Technology Stack

### Common Stack
- **Framework**: Vue.js 3 (Composition API)
- **Router**: Vue Router 4
- **State**: Pinia
- **Styling**: Tailwind CSS
- **Build**: Vite
- **HTTP**: Axios

### Color Themes
- **POS**: Primary Blue (#3b82f6)
- **Inventory**: Blue (#2563eb)
- **Procurement**: Orange (#ea580c)

## 🔄 Integration Workflows

### 1. Sales to Inventory (Future)
```
Transaction Created (POS)
  ↓
Stock Out (Inventory Service)
  ↓
Inventory Ledger Entry (STOCK_OUT)
  ↓
Update Stock Levels
```

### 2. Procurement to Inventory
```
Purchase Request Created
  ↓
Purchase Request Approved
  ↓
Purchase Order Created & Sent to Vendor
  ↓
Goods Receipt Note Created
  ↓
QC Check & Approval
  ↓
Post GRN to Inventory
  ↓
Stock In (Inventory Service)
  ↓
Inventory Ledger Entry (STOCK_IN)
  ↓
Update Stock Levels & Average Cost
```

### 3. Inventory Transfer
```
Transfer Created (From Location)
  ↓
Submit for Approval
  ↓
Approve Transfer (Reserve Stock)
  ↓
Receive Transfer (To Location)
  ↓
Stock Movements:
  - Transfer Out (From Location)
  - Transfer In (To Location)
  ↓
Update Stock Levels
```

## 📁 Project Structure

```
kasir-web/
├── backend/                          # Laravel Backend
│   ├── app/
│   │   ├── Http/Controllers/Api/    # 15+ Controllers
│   │   ├── Models/                   # 15 Models
│   │   └── Services/                 # 5 Service Classes
│   ├── database/
│   │   ├── migrations/               # 17 Migration Files
│   │   └── seeders/                  # 6 Seeders
│   └── routes/
│       └── api.php                   # 80+ API Routes
│
├── frontend/                         # POS Application (Port 5173)
│   ├── src/
│   │   ├── views/                   # 8 Views
│   │   ├── components/              # Reusable Components
│   │   ├── stores/                  # Pinia Stores
│   │   └── services/                # API Service
│   └── README.md                    # POS Documentation
│
├── inventory-app/                    # Inventory App (Port 5174)
│   ├── src/
│   │   ├── views/                   # 6 Views
│   │   ├── layouts/                 # MainLayout
│   │   └── router/                  # Routes Config
│   └── README.md                    # Inventory Documentation
│
└── procurement-app/                  # Procurement App (Port 5175)
    ├── src/
    │   ├── views/                   # 6 Views
    │   ├── layouts/                 # MainLayout
    │   └── router/                  # Routes Config
    └── README.md                    # Procurement Documentation
```

## 🎯 Feature Matrix

| Feature | POS | Inventory | Procurement |
|---------|-----|-----------|-------------|
| Dashboard | ✅ | ✅ | ✅ |
| Authentication | ✅ | ✅ | ✅ |
| Products | ✅ | View | View |
| Categories | ✅ | - | - |
| Outlets | ✅ | - | - |
| Tables | ✅ | - | - |
| Transactions | ✅ | - | - |
| Reports | ✅ | - | - |
| Stock Levels | - | ✅ | View |
| Stock Adjustments | - | ✅ | - |
| Transfers | - | ✅ | - |
| Locations | - | ✅ | - |
| Ledger | - | ✅ | - |
| Purchase Requests | - | - | ✅ |
| Purchase Orders | - | - | ✅ |
| Goods Receipts | - | View | ✅ |
| Vendors | - | - | ✅ |

## 🔧 Environment Setup

### Backend (.env)
```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173,http://localhost:5174,http://localhost:5175

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=kasir_db

SESSION_DRIVER=cookie
SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost:5173,localhost:5174,localhost:5175,localhost:3000
```

### Frontend (vite.config.js)
Each app configured dengan port berbeda dan proxy sama:
```javascript
server: {
  port: 5173, // 5174, 5175
  proxy: {
    '/api': {
      target: 'http://localhost:8000',
      changeOrigin: true,
      credentials: 'include'
    }
  }
}
```

## 📊 Development Status

### ✅ Completed
1. **Backend Infrastructure**
   - Database migrations (17 tables)
   - Models dengan relationships (15 models)
   - Service layer (5 services)
   - API controllers (15 controllers)
   - API routes (80+ endpoints)
   - Seeders (6 seeders)
   - Authentication system

2. **POS Application**
   - Complete POS functionality
   - Transaction management
   - Products & categories
   - Reports & analytics
   - Table management
   - HTTP-only cookie authentication

3. **Inventory Management**
   - Stock levels monitoring
   - Inventory transfers
   - Stock adjustments
   - Locations management
   - Complete ledger tracking
   - Dashboard dengan stats

4. **Procurement Management**
   - Purchase requests workflow
   - Purchase orders management
   - Goods receipt notes
   - Vendor management
   - Dashboard dengan stats
   - Complete approval workflow

### ⏳ Pending / Future Enhancements

1. **POS Integration with Inventory**
   - Auto stock out saat transaction
   - Real-time stock level display
   - Low stock warnings di POS

2. **Create Forms**
   - Create PR form (modal/page)
   - Create PO form (from PR or direct)
   - Create GRN form (from PO)
   - Create Transfer form (enhanced)

3. **Detail Views**
   - PO detail view dengan workflow actions
   - GRN detail view dengan QC workflow
   - Enhanced transfer detail

4. **Reports & Analytics**
   - Inventory reports (stock movement, valuation)
   - Procurement reports (vendor performance, purchase trends)
   - Advanced POS reports

5. **Advanced Features**
   - Email notifications
   - PDF exports (PR, PO, GRN, invoices)
   - Barcode scanning
   - Multi-level approval routing
   - Asset management module
   - Budget tracking

## 🐛 Known Limitations

1. Create forms belum diimplementasi (PR, PO, GRN)
2. POS belum terintegrasi dengan inventory stock out
3. Email notifications belum ada
4. PDF export belum tersedia
5. Barcode scanning belum diimplementasi

## 🎓 Usage Guidelines

### For Developers

**Starting Development**:
1. Start PostgreSQL database
2. Run backend: `php artisan serve`
3. Run desired frontend app(s)
4. Use shared authentication

**Making Changes**:
- Backend changes: Update migrations, models, services, controllers
- Frontend changes: Update views, components, routes per app
- Shared components: Consider creating component library

### For Users

**Login Flow**:
1. Access any app (POS/Inventory/Procurement)
2. Login dengan credentials
3. Cookie di-set, berlaku untuk semua apps
4. Navigate antar apps tanpa login ulang

**Procurement Workflow**:
1. Create Purchase Request
2. Submit for approval
3. Approve PR
4. Create PO from approved PR
5. Send PO to vendor
6. Receive goods → Create GRN
7. QC check → Approve GRN
8. Post to inventory

**Inventory Workflow**:
1. Monitor stock levels
2. Create transfer jika perlu redistribution
3. Adjust stock jika discrepancy
4. Track all movements di ledger

## 🚀 Deployment Considerations

### Production Setup
1. Change APP_URL ke production domain
2. Update CORS allowed origins
3. Set SESSION_DOMAIN ke production domain
4. Configure SANCTUM_STATEFUL_DOMAINS
5. Use HTTPS untuk production
6. Set proper cookie security flags

### Scaling
- Backend dapat di-scale horizontal dengan load balancer
- Frontend apps dapat di-deploy separate (CDN)
- Database read replicas untuk reporting
- Redis untuk session & cache

## 📖 Documentation Links

- [Backend API Documentation](API_DOCUMENTATION.md)
- [POS Frontend README](frontend/README.md)
- [Inventory Frontend README](inventory-app/README.md)
- [Procurement Frontend README](procurement-app/README.md)
- [Installation Guide](INSTALL_BACKEND.md)
- [Quick Guide](QUICK_GUIDE.md)

## 🎉 Summary

System ini menyediakan:
- ✅ Complete POS functionality
- ✅ Comprehensive inventory management
- ✅ Full procurement workflow
- ✅ Modular architecture
- ✅ Shared authentication
- ✅ RESTful API
- ✅ Responsive design
- ✅ Real-time updates

**Total Achievement**:
- 3 Frontend Applications
- 80+ API Endpoints
- 15 Database Tables
- 15 Controllers
- 5 Service Classes
- 20+ Views
- Complete Workflows
