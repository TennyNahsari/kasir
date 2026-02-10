# Ticket Management System - Product Requirements Document (PRD)

**Version:** 2.0  
**Updated:** February 9, 2026  
**Project:** Ticket App (ticket-app)

---

## 📋 Executive Summary

**Ticket Management & Asset Tracking System** - Aplikasi terintegrasi untuk menangani incident reporting, scheduled maintenance, dan full asset lifecycle management. Sebagai modul keempat yang terintegrasi dengan backend Laravel yang sama, aplikasi ini menggabungkan:

1. **Ticket System**: Incident reporting dan maintenance scheduling dengan workflow complete
2. **Asset Management**: Full CRUD asset registry dengan tracking movements, depreciation, dan document management
3. **Integration**: Seamless connection dengan Procurement (PO/GRN), Inventory, dan POS systems

Aplikasi ini menjadi central hub untuk semua aktivitas terkait aset perusahaan dari procurement hingga disposal, dengan kemampuan real-time tracking dan comprehensive reporting.

---

## 🎯 Goals & Objectives

### Primary Goals
1. **Incident Management**: Memungkinkan user melaporkan masalah pada aset yang menjadi tanggung jawab mereka
2. **Maintenance Scheduling**: Otomatis membuat jadwal maintenance berdasarkan data aset (warranty, useful life)
3. **Workflow Tracking**: Memantau progress penyelesaian ticket dari created hingga completed
4. **Asset Management**: Full CRUD dan tracking aset dari procurement hingga disposal
5. **Asset Integration**: Seamless integration dengan existing procurement dan inventory system

### Success Metrics

**Ticket Performance:**
- Response time untuk incident tickets < 24 jam
- Maintenance compliance rate > 90%
- Ticket resolution time tracking
- User satisfaction rating > 4.0/5.0

**Asset Management:**
- Asset registration completion rate > 95%
- Asset tracking accuracy > 98%
- Depreciation calculation accuracy 100%
- Asset movement documentation 100%

**User Adoption:**
- User adoption > 80% dalam 3 bulan
- Daily active users (technicians) > 90%
- Asset data completeness > 95%

---

## ✨ Key Features Overview

### 🎫 Ticket Management
- **Incident Reporting**: User-friendly ticket creation dengan asset selection
- **Maintenance Scheduling**: Auto-generate tickets dari maintenance schedule
- **Priority Levels**: 2-tier priority system (Normal, High)
- **Workflow Engine**: Complete lifecycle tracking (Open → Assigned → In Progress → Resolved → Closed)
- **Worklog & Timeline**: Detailed activity tracking dengan time spent
- **File Attachments**: Upload photos/documents untuk dokumentasi
- **Rating System**: User satisfaction feedback
- **SLA Tracking**: Response time dan resolution time monitoring
- **Notifications**: Email alerts untuk status changes

### 📦 Asset Management (Full CRUD)
- **Asset Registry**: Complete asset database dengan auto-generated asset tags
- **Asset Assignment**: Assign assets ke users dengan history tracking
- **Location Tracking**: Monitor asset movements antar lokasi
- **QR Code Generation**: Print QR labels untuk easy identification
- **Depreciation Calculation**: Automatic depreciation tracking (Straight-line, Declining balance)
- **Document Management**: Upload manuals, invoices, photos per asset
- **Bulk Operations**: Import, export, bulk assign, bulk transfer
- **Asset Conditions**: Track kondisi asset (New, Good, Fair, Poor, Broken)
- **Status Management**: Lifecycle status (Available, Assigned, In Use, Maintenance, Damaged, Disposed)
- **Warranty Tracking**: Monitor warranty expiration
- **Integration**: Seamless link dengan PO/GRN dari Procurement system

### 📊 Reports & Analytics
- **Ticket Reports**: Statistics, trends, technician performance, SLA compliance
- **Asset Reports**: Inventory, movements, depreciation, warranty expiry
- **Dashboard Widgets**: Role-based KPI displays
- **Export Functions**: Excel, PDF, CSV formats
- **Custom Date Ranges**: Flexible reporting periods

### 🔄 Integrations
- **Procurement System**: Auto-populate asset dari GRN
- **Inventory System**: Share location data
- **POS System**: Shared authentication & user management
- **Email Service**: Automated notifications

---

## 👥 User Roles & Permissions

### 1. **End User** (Asset PIC)
- Melihat aset yang di-assign ke mereka
- Membuat incident ticket untuk aset mereka
- Melihat status ticket yang mereka buat
- Memberikan feedback/rating setelah ticket selesai
- View-only access untuk aset mereka

### 2. **Technician** (Role baru)
- Melihat semua ticket yang di-assign ke mereka
- Update status ticket (In Progress, On Hold, Waiting Parts)
- Menambah worklog dan dokumentasi
- Assign ulang ticket jika diperlukan
- Menutup ticket dengan resolution notes
- View all assets untuk referensi
- Update asset condition setelah maintenance

### 3. **Supervisor** (Existing role - enhanced)
- Melihat semua ticket
- Assign ticket ke technician
- Approve/reject ticket closure
- Generate reports
- Manage SLA dan priority
- **Full Asset Management**: CRUD assets, movements, bulk operations
- Assign/reassign assets ke users
- Approve asset disposal

### 4. **Owner** (Existing role - enhanced)
- Full access semua fitur
- View analytics & dashboard
- Approve major maintenance tickets
- Budget approval untuk spare parts
- **Full Asset Management**: All asset operations
- Asset depreciation review
- Asset lifecycle analytics

---

## 📊 Database Schema

### New Tables

#### 1. **tickets**
```sql
- id (PK)
- ticket_number (VARCHAR, UNIQUE) // TKT-2026-0001
- type (ENUM: 'INCIDENT', 'MAINTENANCE')
- asset_id (FK -> assets)
- reported_by (FK -> users) // User yang membuat ticket
- assigned_to (FK -> users, nullable) // Technician yang handle
- location_id (FK -> locations)

// Ticket Details
- title (VARCHAR, 200)
- description (TEXT)
- priority (ENUM: 'NORMAL', 'HIGH') // Simplified to 2 levels
- status (ENUM: 'OPEN', 'ASSIGNED', 'IN_PROGRESS', 'ON_HOLD', 'RESOLVED', 'CLOSED', 'CANCELLED')
- category (ENUM: 'HARDWARE', 'SOFTWARE', 'NETWORK', 'FACILITY', 'OTHER')

// Maintenance Specific
- scheduled_date (DATETIME, nullable) // Untuk maintenance tickets
- maintenance_type (ENUM: 'PREVENTIVE', 'CORRECTIVE', 'PREDICTIVE', nullable)

// Resolution
- resolution_notes (TEXT, nullable)
- resolved_at (DATETIME, nullable)
- resolved_by (FK -> users, nullable)
- closed_at (DATETIME, nullable)
- closed_by (FK -> users, nullable)

// SLA & Tracking
- sla_due_date (DATETIME, nullable)
- first_response_at (DATETIME, nullable)
- estimated_completion (DATETIME, nullable)

// Rating
- rating (TINYINT, 1-5, nullable) // User satisfaction
- feedback (TEXT, nullable)

- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
- deleted_at (TIMESTAMP, nullable)

// Indexes
INDEX (ticket_number)
INDEX (asset_id)
INDEX (reported_by)
INDEX (assigned_to)
INDEX (status)
INDEX (type)
INDEX (scheduled_date)
```

#### 2. **ticket_worklogs**
```sql
- id (PK)
- ticket_id (FK -> tickets)
- user_id (FK -> users) // Who made the entry
- worklog_type (ENUM: 'COMMENT', 'STATUS_CHANGE', 'ASSIGNMENT', 'WORK_DONE', 'ESCALATION')
- description (TEXT)
- time_spent_minutes (INT, nullable) // Untuk tracking man-hours
- is_internal (BOOLEAN) // Internal notes vs public comments
- created_at (TIMESTAMP)

INDEX (ticket_id)
INDEX (user_id)
```

#### 3. **ticket_attachments**
```sql
- id (PK)
- ticket_id (FK -> tickets)
- worklog_id (FK -> ticket_worklogs, nullable)
- uploaded_by (FK -> users)
- file_name (VARCHAR)
- file_path (VARCHAR)
- file_type (VARCHAR) // image, document, video
- file_size (INT) // in bytes
- created_at (TIMESTAMP)

INDEX (ticket_id)
```

#### 4. **maintenance_schedules**
```sql
- id (PK)
- asset_id (FK -> assets)
- maintenance_type (ENUM: 'PREVENTIVE', 'INSPECTION', 'CALIBRATION')
- frequency (ENUM: 'MONTHLY', 'QUARTERLY', 'SEMI_ANNUAL', 'ANNUAL')
- last_maintenance_date (DATE, nullable)
- next_maintenance_date (DATE)
- auto_create_ticket (BOOLEAN, default: true)
- is_active (BOOLEAN, default: true)
- notes (TEXT, nullable)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)

INDEX (asset_id)
INDEX (next_maintenance_date)
```

#### 5. **spare_parts** (Optional - Phase 2)
```sql
- id (PK)
- product_id (FK -> products, nullable) // Link to inventory if available
- part_number (VARCHAR)
- name (VARCHAR)
- description (TEXT, nullable)
- quantity (DECIMAL)
- unit_price (DECIMAL)
- location_id (FK -> locations)
- is_active (BOOLEAN)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)

INDEX (part_number)
INDEX (product_id)
```

#### 6. **ticket_spare_parts** (Optional - Phase 2)
```sql
- id (PK)
- ticket_id (FK -> tickets)
- spare_part_id (FK -> spare_parts)
- quantity_used (DECIMAL)
- notes (TEXT, nullable)
- created_at (TIMESTAMP)

INDEX (ticket_id)
INDEX (spare_part_id)
```

### Existing Tables (No Changes)
- **assets**: Read-only access untuk ticket creation
- **users**: Add new role 'technician'
- **locations**: Reference untuk filtering tickets

---

## 🎨 User Interface & Features

### Application Structure
```
ticket-app/ (Port 5176)
├── src/
│   ├── views/
│   │   ├── Dashboard.vue
│   │   ├── TicketList.vue
│   │   ├── TicketCreate.vue
│   │   ├── TicketDetail.vue
│   │   ├── MyAssets.vue
│   │   ├── MaintenanceSchedule.vue
│   │   ├── Reports.vue
│   │   ├── Settings.vue
│   │   ├── AssetList.vue           // New: Full asset management
│   │   ├── AssetDetail.vue         // New: Asset detail & history
│   │   ├── AssetCreate.vue         // New: Create/register asset
│   │   ├── AssetMovement.vue       // New: Transfer/assign assets
│   │   └── AssetDepreciation.vue   // New: Depreciation report
│   ├── components/
│   │   ├── TicketCard.vue
│   │   ├── TicketTimeline.vue
│   │   ├── WorklogForm.vue
│   │   ├── AssetSelector.vue
│   │   ├── TechnicianAssignment.vue
│   │   ├── StatusBadge.vue
│   │   ├── AssetCard.vue           // New
│   │   ├── AssetForm.vue           // New
│   │   ├── AssetQRCode.vue         // New
│   │   ├── MovementHistory.vue     // New
│   │   └── DepreciationChart.vue   // New
│   ├── stores/
│   │   ├── ticketStore.js
│   │   ├── assetStore.js
│   │   ├── movementStore.js        // New
│   │   └── authStore.js (shared)
│   └── services/
│       ├── ticketService.js
│       ├── assetService.js
│       ├── movementService.js      // New
│       └── maintenanceService.js
```

### Navigation Menu Structure

**Main Navigation** (Role-based visibility)

**For End Users:**
- 🏠 Dashboard
- 🎫 My Tickets
- 📦 My Assets
- ➕ Create Ticket

**For Technicians:**
- 🏠 Dashboard
- 🎫 Tickets
  - All Tickets
  - Assigned to Me
  - My Tickets (created by me)
- 📦 Assets (View Only)
- 🗓️ Maintenance Schedule
- ➕ Create Ticket

**For Supervisors & Owners:**
- 🏠 Dashboard
- 🎫 Tickets
  - All Tickets
  - By Status
  - By Priority
  - Maintenance Schedule
- 📦 **Assets** (with submenu)
  - Asset List
  - Create Asset
  - Asset Movements
  - Depreciation Report
- 📊 Reports
  - Ticket Reports
  - Asset Reports
- ⚙️ Settings
  - Maintenance Schedules
  - SLA Configuration
  - Users & Roles

---

### Screen Descriptions

### 1. **Dashboard** (`/`)
**For End Users:**
- My Open Tickets (widget)
- My Assets Status (widget)
- Quick Create Ticket button
- Recent Activity feed

**For Technicians:**
- Assigned to Me (grouped by priority)
- Today's Scheduled Maintenance
- Overdue Tickets alert
- My Workload stats (open/in-progress/resolved this week)

**For Supervisors:**
- Overall Ticket Statistics
  - By Status (pie chart)
  - By Priority (bar chart)
  - By Type (Incident vs Maintenance)
- SLA Compliance meter
- Response Time trends (line chart)
- Top 5 problematic assets
- Team Performance metrics
- **Asset Statistics** (NEW)
  - Total Assets Count
  - Assets by Status (donut chart)
  - Assets by Condition (bar chart)
  - Total Asset Value
  - Warranty Expiring Soon (alert badge)
  - Recent Asset Movements (last 7 days)

### 2. **My Assets** (`/my-assets`)
**Purpose:** List aset yang di-assign ke logged-in user (End User view)

**Features:**
- Card/Grid view dengan asset image
- Asset information:
  - Asset Tag + Serial Number
  - Product Name
  - Location
  - Status badge
  - Condition badge
  - Warranty status (Active/Expired)
  - Depreciation value
- Quick "Report Issue" button per asset
- Filter: Location, Status, Condition
- Search: Asset tag, serial number, product name
- View asset detail & history

### 2B. **Asset Management** (`/assets`) - **NEW MAJOR FEATURE**
**Purpose:** Full asset lifecycle management (Supervisor & Owner only)

**Sub-pages:**

#### **2B.1 Asset List** (`/assets`)
**Similar to Inventory-app stock management**

**View Modes:**
- Table view (default)
- Card/Grid view
- Export to Excel/PDF

**Table Columns:**
- Asset Tag (clickable)
- Product Name + Image
- Serial Number
- Status badge (AVAILABLE, ASSIGNED, IN_USE, MAINTENANCE, DAMAGED, DISPOSED)
- Condition badge (NEW, GOOD, FAIR, POOR, BROKEN)
- Location
- Assigned To (user name)
- Purchase Date
- Current Value (with depreciation %)
- Warranty Status
- Actions dropdown

**Actions:**
- View Detail
- Edit Asset
- Assign to User
- Move to Location
- Change Status
- Update Condition
- Print QR Code
- Create Ticket
- View History
- Mark as Disposed

**Filters:** (Sidebar or top filters)
- Product Category
- Status (multiple select)
- Condition (multiple select)
- Location (dropdown)
- Assigned To (user dropdown)
- Warranty Status: All, Active, Expired, No Warranty
- Purchase Date Range
- Value Range (min-max)
- Search: Asset tag, serial number, product name

**Bulk Operations:** (Select multiple assets)
- Bulk assign to location
- Bulk assign to user
- Bulk status change
- Bulk export
- Print QR codes

**Quick Stats Cards:**
- Total Assets
- Available Assets
- Assigned Assets
- Under Maintenance
- Damaged Assets
- Total Asset Value

#### **2B.2 Asset Detail** (`/assets/:id`)
**Comprehensive asset information page**

**Information Tabs:**

**Tab 1: Overview**
- Asset Photo
- QR Code (for scanning)
- Basic Info:
  - Asset Tag
  - Serial Number
  - Product Name + Category
  - Status & Condition
  - Location
  - Assigned To (with avatar)
  - Assigned Date
- Financial Info:
  - Purchase Date
  - Purchase Price
  - Current Value
  - Depreciation (amount & %)
  - Useful Life (months)
  - Warranty Until
- Procurement References:
  - PO Number (link)
  - GRN Number (link)
  - Vendor (if available)
- Notes

**Tab 2: Movement History**
- Timeline of all movements
- Columns: Date, Type (Assignment/Transfer), From, To, By User, Notes
- Filter by date range, type

**Tab 3: Ticket History**
- All tickets created for this asset
- Quick stats: Total tickets, Open, Resolved, Avg resolution time
- List with: Ticket#, Type, Title, Status, Priority, Created, Resolved
- Click to open ticket detail

**Tab 4: Maintenance History**
- All maintenance performed
- List: Date, Type, Performed By, Notes, Time Spent
- Next scheduled maintenance date

**Tab 5: Documents**
- Upload documents (manual, invoice, photos, etc)
- List files with preview
- Download/delete actions

**Action Buttons:** (Top right)
- Edit Asset
- Create Ticket
- Assign to User
- Move Location
- Print QR Code
- Mark as Disposed
- View Depreciation Chart

#### **2B.3 Create/Register Asset** (`/assets/create`)
**Form to register new asset**

**Step 1: Product Selection**
- Search & Select Product from master data
- Show product info: Name, Category, Image
- If from GRN: Auto-populate from goods receipt

**Step 2: Asset Details**
- Asset Tag: Auto-generated (AST-YYYY-NNNN) or manual input
- Serial Number: Text input (optional, but recommended)
- Status: Default AVAILABLE
- Condition: Default NEW
- Location: Dropdown (required)
- Assigned To: User dropdown (optional)
- Assigned Date: Auto if assigned

**Step 3: Financial Information**
- Purchase Date: Date picker
- Purchase Price: Currency input
- Useful Life (months): Number input (for depreciation)
- Depreciation Method: STRAIGHT_LINE / DECLINING_BALANCE
- Warranty Until: Date picker (optional)
- PO Reference: Link to PO (if applicable)
- GRN Reference: Link to GRN (if applicable)

**Step 4: Additional Info**
- Upload Photo
- Notes: Text area
- Documents: Multiple file upload

**Step 5: Review & Submit**
- Summary of all data
- Generate QR Code preview
- Save & Print QR button
- Save button

**Bulk Registration:** (For multiple similar assets)
- Upload Excel template
- Map columns
- Preview & validate
- Import all

#### **2B.4 Asset Movement** (`/assets/movements`)
**Track all asset transfers and assignments**

**Movement Types:**
1. **Assign to User**: Asset → User
2. **Transfer Location**: Asset → Location
3. **Return**: Asset back to available
4. **Mass Transfer**: Multiple assets → Location

**Movement List:**
- Recent movements (last 30 days)
- Columns: Date, Asset Tag, Type, From, To, By User, Notes
- Filter: Date range, Type, Location, Asset
- Export to Excel

**Create Movement:**
- Select Assets (single or multiple)
- Select Movement Type
- Destination (User or Location)
- Effective Date (default today)
- Notes
- Submit

**Pending Movements:** (Optional Phase 2)
- Require approval for certain movements
- Request → Approval → Executed workflow

#### **2B.5 Asset Depreciation** (`/assets/depreciation`)
**Financial reporting for asset values**

**Dashboard:**
- Total Asset Value (original purchase)
- Current Total Value (after depreciation)
- Total Depreciation Amount
- Avg Depreciation Rate

**Chart:**
- Asset Value Over Time (line chart)
- Depreciation by Category (bar chart)
- Assets by Condition (pie chart)

**Depreciation Table:**
- Filter by: Date, Category, Location
- Columns:
  - Asset Tag
  - Product Name
  - Purchase Date
  - Purchase Price
  - Useful Life
  - Age (months)
  - Depreciation Method
  - Current Value
  - Depreciation Amount
  - Depreciation %
- Export to Excel for accounting

**Recalculate Depreciation:**
- Button to manually trigger recalculation for all assets
- Show last calculation date

### 3. **Ticket List** (`/tickets`)
**For All Users:**

**Tabs:**
- All Tickets (for supervisors)
- My Tickets (tickets I created)
- Assigned to Me (for technicians)
- Maintenance Schedule

**Columns:**
- Ticket Number
- Type badge (Incident/Maintenance)
- Title
- Asset (with tag)
- Priority badge
- Status badge
- Assigned To
- Created Date
- Due Date (SLA)
- Actions (View, Edit, Close)

**Filters:**
- Type: All, Incident, Maintenance
- Status: Multiple select
- Priority: Normal, High
- Date Range: Created, Due Date, Scheduled
- Location: Dropdown
- Asset: Auto-complete search
- Assigned To: User dropdown

**Sorting:**
- Priority (High to Low)
- Created Date (Newest first)
- Due Date (Urgent first)
- Status

**Bulk Actions:** (Supervisor only)
- Assign to Technician
- Change Priority
- Export to Excel

### 4. **Create Ticket** (`/tickets/create`)
**Step 1: Basic Information**
- Type: 
  - [ ] Incident (default)
  - [ ] Scheduled Maintenance
- Select Asset: Dropdown with search (filter by user's assigned assets for end users)
- Title: Max 200 chars
- Description: Rich text editor
- Priority: Auto-suggested based on asset condition, user can override
  - **High**: Asset completely down, critical functionality lost, production impact
  - **Normal**: Minor issue, workaround available, scheduled maintenance

**Step 2: Additional Details** (conditional)
- Category: Hardware/Software/Network/Facility/Other
- Attachments: Upload photos/documents (max 5 files, 10MB each)
- For Maintenance:
  - Scheduled Date
  - Maintenance Type: Preventive/Corrective

**Step 3: Review & Submit**
- Summary of ticket details
- Estimated SLA due date (auto-calculated)
- Submit button
- Save as Draft button

**Validation:**
- Asset must be assigned to user (for end users)
- Title & description required
- Scheduled date must be future date (for maintenance)

### 5. **Ticket Detail** (`/tickets/:id`)
**Header Section:**
- Ticket Number (large, bold)
- Status badge
- Priority badge
- Type badge
- Action buttons (context-aware):
  - Assign (Supervisor)
  - Take (Technician, if unassigned)
  - Start Work (Technician)
  - Add Worklog (Technician)
  - Resolve (Technician)
  - Close (Supervisor/Owner)
  - Cancel (Supervisor/Owner)
  - Reopen (Supervisor)

**Information Panel:**
- Asset Details (clickable link to asset):
  - Asset Tag
  - Product Name
  - Serial Number
  - Location
  - Current Condition
- Ticket Details:
  - Created By + Date
  - Assigned To
  - Location
  - Priority
  - Category
  - Due Date (with countdown timer)
- Time Tracking:
  - Created At
  - First Response At
  - Resolved At
  - Total Time Spent
  - SLA Status (Met/Breached)

**Description Section:**
- Full description
- Attachments (images shown inline, documents as download links)

**Timeline/Worklog Section:**
- Chronological list of all activities:
  - Status changes (with old → new status)
  - Comments
  - Work done entries
  - Assignment changes
  - Attachments added
  - Time spent per entry
- Each entry shows:
  - User avatar + name
  - Timestamp
  - Entry type icon
  - Content
  - Internal/Public indicator

**Add Worklog Form:** (Technician & above)
- Type: Comment / Work Done / Status Update
- Description: Text area
- Time Spent: Input in hours/minutes
- Is Internal: Checkbox
- Attachments: Upload
- Submit button

**Resolution Section:** (if resolved/closed)
- Resolution Notes
- Resolved By + Date
- User Rating (if provided)
- User Feedback

**Rating & Feedback:** (End Users, after ticket closed)
- Star rating (1-5)
- Feedback text area
- Submit button

### 6. **Maintenance Schedule** (`/maintenance`)
**Purpose:** Manage scheduled maintenance for assets

**Features:**
- Calendar view (primary)
  - Monthly view
  - Color-coded by maintenance type
  - Click date to see all maintenance that day
  - Click item to view/edit

- List view (secondary)
  - Upcoming maintenance (next 30 days)
  - Overdue maintenance (highlighted red)
  - Columns: Asset, Type, Last Date, Next Date, Status, Actions

**Create Schedule:**
- Select Asset
- Maintenance Type: Preventive/Inspection/Calibration
- Frequency: Monthly/Quarterly/Semi-Annual/Annual
- Start Date
- Auto-create Ticket: Yes/No
- Notes

**Auto-Ticket Creation:**
- Cron job runs daily
- Creates ticket 7 days before scheduled date
- Ticket:
  - Type: Maintenance
  - Priority: Normal (configurable)
  - Status: Open
  - Description: Auto-generated with maintenance details

### 7. **Reports** (`/reports`)
**For Supervisors & Owners:**

**Ticket Statistics:**
- Summary Cards:
  - Total Tickets (This Month/Year)
  - Open Tickets
  - Avg Resolution Time
  - SLA Compliance Rate %
  
**Charts:**
1. Tickets by Status (Pie Chart)
2. Tickets by Priority (Bar Chart)
3. Tickets by Type (Donut Chart)
4. Ticket Trends (Line Chart - last 6 months)
5. Top 5 Problematic Assets (Bar Chart)
6. Technician Performance (Table with metrics)
   - Name
   - Assigned Tickets
   - Resolved Tickets
   - Avg Resolution Time
   - Rating

**Filters:**
- Date Range
- Type
- Priority
- Status
- Location
- Technician
- Asset

**Export:**
- Excel
- PDF
- CSV

**Asset Reports:** (NEW)
- Summary Cards:
  - Total Assets
  - Total Asset Value
  - Available Assets
  - Assets Under Maintenance
  - Damaged Assets Count
  - Avg Asset Age (months)

**Asset Charts:**
1. Assets by Status (Donut Chart)
2. Assets by Condition (Bar Chart)
3. Assets by Location (Bar Chart)
4. Asset Value Distribution (Pie Chart)
5. Depreciation Over Time (Line Chart)
6. Top 10 Most Expensive Assets (Table)

**Asset Reports List:**
- Asset Inventory Report (full list with values)
- Asset Movement Report (transfers & assignments)
- Depreciation Report (financial summary)
- Warranty Expiry Report (upcoming & expired)
- Asset Utilization Report (assigned vs available)
- Asset Maintenance History Report
- Disposed Assets Report

**Export Options:**
- Excel (with multiple sheets)
- PDF (formatted report)
- CSV (raw data)

**Custom Reports:** (Phase 2)
- Asset Downtime Report
- Maintenance Compliance Report
- Cost Analysis Report (with spare parts)
- User Satisfaction Report
- Asset ROI Analysis
- Total Cost of Ownership (TCO)

---

## 🔄 Workflows

### Incident Ticket Workflow

```
┌─────────────────────────────────────────────────────────────────┐
│                    INCIDENT TICKET LIFECYCLE                     │
└─────────────────────────────────────────────────────────────────┘

1. OPEN (Initial State)
   - User creates ticket
   - System assigns ticket number
   - SLA due date calculated
   - Notification to supervisors
   ↓
   
2. ASSIGNED
   - Supervisor assigns to technician
   - OR Technician claims ticket
   - Notification to technician
   ↓
   
3. IN_PROGRESS
   - Technician starts working
   - Add worklogs
   - Upload progress photos
   - Can add time spent
   ↓
   
4. ON_HOLD (Optional)
   - Waiting for spare parts
   - Waiting for vendor
   - Waiting for approval
   - Must add reason in worklog
   ↓ (back to IN_PROGRESS when ready)
   
5. RESOLVED
   - Technician marks as resolved
   - Add resolution notes
   - Update asset condition
   - Notification to reporter
   ↓
   
6. CLOSED
   - User confirms resolution
   - OR Supervisor closes
   - User can rate & give feedback
   - Ticket locked (read-only)

Alternative Paths:
- CANCELLED (any state → cancelled by supervisor)
- CLOSED can be REOPENED if issue persists (within 7 days)
```

### Maintenance Ticket Workflow

```
┌─────────────────────────────────────────────────────────────────┐
│                  MAINTENANCE TICKET LIFECYCLE                    │
└─────────────────────────────────────────────────────────────────┘

1. OPEN (Auto-created or Manual)
   - Created 7 days before scheduled date
   - OR Manually created by supervisor
   - Status: OPEN
   ↓
   
2. ASSIGNED
   - Assigned to technician
   - Scheduled date visible
   ↓
   
3. IN_PROGRESS
   - Technician performs maintenance
   - Follow checklist (if available)
   - Document findings
   - Record time spent
   ↓
   
4. RESOLVED
   - Maintenance completed
   - Update asset:
     - Last maintenance date
     - Next maintenance date (auto-calculated)
     - Condition (if changed)
   - Add completion notes
   ↓
   
5. CLOSED
   - Supervisor reviews & closes
   - Maintenance schedule updated
   - Next maintenance auto-scheduled
```

---

## 🔔 Notifications

### Email Notifications
1. **New Ticket Created**
   - To: Supervisors
   - Content: Ticket#, Asset, Priority, Reporter

2. **Ticket Assigned**
   - To: Assigned Technician
   - Content: Ticket#, Asset, Priority, Due Date

3. **Ticket Resolved**
   - To: Reporter
   - Content: Ticket#, Resolution, Request for feedback

4. **SLA Breach Alert**
   - To: Supervisors, Assigned Technician
   - Content: Ticket#, How long overdue

5. **Maintenance Due**
   - To: Supervisors
   - Content: List of assets due in next 7 days

### In-App Notifications
- Bell icon with badge count
- Real-time updates using WebSocket (Phase 2) or polling
- Notification types:
  - Assignment
  - Status changes
  - New comments
  - Due date reminders
  - Resolution

---

## 🔐 Security & Permissions Matrix

| Feature | End User | Technician | Supervisor | Owner |
|---------|----------|------------|------------|-------|
| **Tickets** |
| View own tickets | ✅ | ✅ | ✅ | ✅ |
| View all tickets | ❌ | ✅ (assigned) | ✅ | ✅ |
| Create incident ticket | ✅ (own assets) | ✅ | ✅ | ✅ |
| Create maintenance ticket | ❌ | ❌ | ✅ | ✅ |
| Assign tickets | ❌ | ❌ | ✅ | ✅ |
| Add worklog | ❌ | ✅ (assigned) | ✅ | ✅ |
| Resolve ticket | ❌ | ✅ (assigned) | ✅ | ✅ |
| Close ticket | ❌ | ❌ | ✅ | ✅ |
| Cancel ticket | ❌ | ❌ | ✅ | ✅ |
| Reopen ticket | ❌ | ❌ | ✅ | ✅ |
| Rate ticket | ✅ (own) | ❌ | ❌ | ❌ |
| View reports | ❌ | ✅ (own stats) | ✅ | ✅ |
| Manage schedules | ❌ | ❌ | ✅ | ✅ |
| **Assets** |
| View own assets | ✅ | ✅ | ✅ | ✅ |
| View all assets | ❌ | ✅ (read-only) | ✅ | ✅ |
| Create asset | ❌ | ❌ | ✅ | ✅ |
| Edit asset | ❌ | ❌ | ✅ | ✅ |
| Delete asset | ❌ | ❌ | ✅ | ✅ |
| Assign asset to user | ❌ | ❌ | ✅ | ✅ |
| Transfer asset location | ❌ | ❌ | ✅ | ✅ |
| Update asset condition | ❌ | ✅ (after repair) | ✅ | ✅ |
| Mark as disposed | ❌ | ❌ | ✅ | ✅ |
| View movement history | ✅ (own) | ✅ | ✅ | ✅ |
| Bulk operations | ❌ | ❌ | ✅ | ✅ |
| Import assets | ❌ | ❌ | ✅ | ✅ |
| Export reports | ❌ | ❌ | ✅ | ✅ |
| View depreciation | ❌ | ❌ | ✅ | ✅ |

---

## 📱 API Endpoints

### Tickets
```php
// Ticket CRUD
GET    /api/tickets                    // List with filters
GET    /api/tickets/{id}                // Detail
POST   /api/tickets                    // Create
PUT    /api/tickets/{id}                // Update
DELETE /api/tickets/{id}                // Soft delete

// Ticket Actions
POST   /api/tickets/{id}/assign         // Assign to technician
POST   /api/tickets/{id}/take           // Technician claims ticket
POST   /api/tickets/{id}/start          // Start working
POST   /api/tickets/{id}/hold           // Put on hold
POST   /api/tickets/{id}/resume         // Resume from hold
POST   /api/tickets/{id}/resolve        // Mark as resolved
POST   /api/tickets/{id}/close          // Close ticket
POST   /api/tickets/{id}/cancel         // Cancel ticket
POST   /api/tickets/{id}/reopen         // Reopen closed ticket

// Ticket Worklogs
GET    /api/tickets/{id}/worklogs       // Get all worklogs
POST   /api/tickets/{id}/worklogs       // Add worklog
PUT    /api/worklogs/{id}               // Update worklog
DELETE /api/worklogs/{id}               // Delete worklog

// Ticket Attachments
POST   /api/tickets/{id}/attachments    // Upload file
DELETE /api/attachments/{id}            // Delete file
GET    /api/attachments/{id}/download   // Download file

// Ticket Rating
POST   /api/tickets/{id}/rate           // Rate ticket
```

### Assets (Read-only for tickets)
```php
GET    /api/assets                      // List all assets
GET    /api/assets/my-assets            // Assets assigned to me
GET    /api/assets/{id}                 // Asset detail
GET    /api/assets/{id}/tickets         // Tickets for asset
```

### Asset Management (CRUD - Supervisor & Owner)
```php
// Asset CRUD
GET    /api/assets                      // List with advanced filters
GET    /api/assets/{id}                 // Detail with full info
POST   /api/assets                      // Create/register asset
PUT    /api/assets/{id}                 // Update asset
DELETE /api/assets/{id}                 // Soft delete
POST   /api/assets/bulk-import          // Bulk import from Excel
POST   /api/assets/bulk-assign          // Bulk assign to location/user

// Asset Actions
POST   /api/assets/{id}/assign-user     // Assign to user
POST   /api/assets/{id}/transfer        // Transfer location
POST   /api/assets/{id}/return          // Return to available
POST   /api/assets/{id}/update-status   // Change status
POST   /api/assets/{id}/update-condition // Change condition
POST   /api/assets/{id}/dispose         // Mark as disposed
GET    /api/assets/{id}/qr-code         // Generate QR code
GET    /api/assets/{id}/depreciation    // Calculate depreciation

// Asset Movements
GET    /api/asset-movements             // List all movements
GET    /api/asset-movements/{id}        // Movement detail
POST   /api/asset-movements             // Create movement
GET    /api/assets/{id}/movements       // Movement history for asset

// Asset Documents
GET    /api/assets/{id}/documents       // List documents
POST   /api/assets/{id}/documents       // Upload document
DELETE /api/documents/{id}              // Delete document
GET    /api/documents/{id}/download     // Download document

// Statistics & Reports
GET    /api/assets/statistics           // Overall stats
GET    /api/assets/by-status            // Group by status
GET    /api/assets/by-location          // Group by location
GET    /api/assets/low-warranty         // Assets with expiring warranty
GET    /api/assets/depreciation-report  // Depreciation summary
POST   /api/assets/export               // Export to Excel/PDF
```

### Maintenance
```php
GET    /api/maintenance-schedules       // List schedules
GET    /api/maintenance-schedules/{id}  // Schedule detail
POST   /api/maintenance-schedules       // Create schedule
PUT    /api/maintenance-schedules/{id}  // Update schedule
DELETE /api/maintenance-schedules/{id}  // Delete schedule
POST   /api/maintenance-schedules/{id}/execute  // Manually trigger
```

### Reports & Analytics
```php
GET    /api/reports/ticket-statistics   // Stats summary
GET    /api/reports/ticket-trends        // Trend data
GET    /api/reports/asset-performance    // Asset downtime etc
GET    /api/reports/technician-performance  // Technician metrics
GET    /api/reports/sla-compliance       // SLA metrics
POST   /api/reports/export               // Export report
```

### Dashboard
```php
GET    /api/dashboard/user-dashboard     // End user dashboard
GET    /api/dashboard/technician-dashboard  // Technician dashboard
GET    /api/dashboard/supervisor-dashboard  // Supervisor dashboard
```

---

## 🚀 Implementation Phases

### Phase 1: MVP (4-6 weeks)
**Week 1-2: Backend Foundation**
- [ ] Database migrations (tickets, worklogs, attachments)
- [ ] Models & relationships
- [ ] Basic CRUD API endpoints
- [ ] Authentication & authorization

**Week 3-4: Core Features**
- [ ] Ticket creation (incident only)
- [ ] Ticket listing & filtering
- [ ] Ticket detail view
- [ ] Basic workflow (open → assigned → resolved → closed)
- [ ] Worklog functionality
- [ ] Asset CRUD operations
- [ ] Asset listing & search

**Week 5-6: Frontend Development**
- [ ] Setup ticket-app (Vue 3 + Vite)
- [ ] Dashboard (basic)
- [ ] My Assets page
- [ ] Asset Management pages (List, Detail, Create)
- [ ] Create Ticket page
- [ ] Ticket List page
- [ ] Ticket Detail page
- [ ] Integration with backend APIs

### Phase 2: Enhanced Features (3-4 weeks)
- [ ] Maintenance scheduling
- [ ] Auto-ticket creation from schedules
- [ ] Calendar view for maintenance
- [ ] Email notifications
- [ ] File attachments
- [ ] Rating & feedback system
- [ ] Basic reports
- [ ] Asset movement tracking
- [ ] Asset QR code generation & printing
- [ ] Asset documents upload

### Phase 3: Advanced Features (3-4 weeks)
- [ ] Advanced reporting & analytics
- [ ] SLA management & alerts
- [ ] Spare parts management
- [ ] Cost tracking
- [ ] Mobile-responsive improvements
- [ ] In-app real-time notifications
- [ ] Bulk operations
- [ ] Asset depreciation tracking & reports
- [ ] Asset bulk import/export (Excel)
- [ ] Advanced asset filtering & search

### Phase 4: Optimization (2-3 weeks)
- [ ] Performance optimization
- [ ] Advanced filters & search
- [ ] Export functionality (Excel, PDF)
- [ ] User training materials
- [ ] System documentation

---

## 🧪 Testing Requirements

### Unit Tests
- Model validations
- Business logic in services
- Permission checks
- SLA calculations

### Integration Tests
- API endpoints
- Authentication flows
- Workflow state transitions
- Email notifications

### UI Tests
- Form validations
- Ticket creation flow
- Status updates
- File uploads

### User Acceptance Testing
- End user: Create & track tickets
- Technician: Process tickets efficiently
- Supervisor: Manage team & reports
- Business: Meet SLA requirements

---

## 📈 Success Criteria

### Technical
- [ ] All API endpoints functional
- [ ] Response time < 500ms
- [ ] 95% uptime
- [ ] Mobile responsive
- [ ] Cross-browser compatible

### Business
- [ ] 80% user adoption in 3 months
- [ ] Avg ticket resolution < 3 days
- [ ] User satisfaction > 4.0/5.0
- [ ] Maintenance compliance > 90%

### User Experience
- [ ] Intuitive UI requiring minimal training
- [ ] Quick ticket creation (< 2 minutes)
- [ ] Clear status visibility
- [ ] Easy handover between technicians

---

## 🔮 Future Enhancements (Backlog)

1. **Mobile App** (Native or PWA)
   - Quick ticket creation via camera
   - Push notifications
   - Offline mode

2. **AI/ML Features**
   - Auto-categorization of tickets
   - Predictive maintenance (based on historical data)
   - Smart assignment (match ticket to best technician)

3. **Integration**
   - IoT sensor integration for auto-incident detection
   - Vendor portal for external technicians
   - Procurement integration for auto spare parts ordering

4. **Advanced Analytics**
   - Asset reliability analysis
   - MTBF (Mean Time Between Failures)
   - MTTR (Mean Time To Repair)
   - Cost per asset analysis

5. **Collaboration**
   - Team chat per ticket
   - Video call integration
   - Knowledge base / FAQ
   - Ticket templates

6. **Automation**
   - Auto-assign based on location/expertise
   - Auto-escalation if SLA breach imminent
   - Automated status updates from IoT
   - Chatbot for common queries

---

## 📞 Support & Maintenance

### Documentation
- API documentation (Swagger/OpenAPI)
- User manual
- Admin guide
- Developer documentation

### Training
- Video tutorials
- Quick start guide
- FAQ section
- In-app tooltips

### Monitoring
- Application performance monitoring
- Error tracking (Sentry)
- Usage analytics
- SLA compliance tracking

---

## 💰 Estimated Resources

### Development Team
- 1 Backend Developer (Laravel)
- 1 Frontend Developer (Vue.js)
- 1 UI/UX Designer (part-time)
- 1 QA Engineer (part-time)
- 1 Project Manager (part-time)

### Timeline
- Phase 1 (MVP - Tickets + Basic Assets): 6 weeks
- Phase 2 (Full Asset Management): 4-5 weeks
- Phase 3 (Advanced Features): 4-5 weeks
- Phase 4 (Optimization & Reports): 3-4 weeks
- **Total: ~20-22 weeks (5+ months)**

**Note:** Timeline extended due to comprehensive asset management features including movements, depreciation, bulk operations, and advanced reporting.

### Infrastructure
- Same backend server (already provisioned)
- Additional storage for asset photos & documents (~50GB recommended)
- CloudFlare for frontend hosting (or same VPS)
- Email service (existing)
- File storage: Local or S3 (for asset images/documents)
- QR Code generation library
- PDF generation for reports & labels

---

## 🎨 Design Principles

1. **Consistency**: Follow existing app design patterns
2. **Simplicity**: Minimize clicks to complete tasks
3. **Clarity**: Clear status indicators & CTAs
4. **Responsiveness**: Mobile-first approach
5. **Accessibility**: WCAG 2.1 AA compliance

---

## 📝 Appendix

### A. Ticket Number Format
- Format: `TKT-YYYY-NNNN`
- Example: `TKT-2026-0001`
- Auto-increment per year

### B. SLA Definitions
| Priority | First Response | Resolution Target |
|----------|----------------|-------------------|
| High | 2 hours | 24 hours |
| Normal | 8 hours | 72 hours |

### C. Status Color Codes
- OPEN: Blue `#3B82F6`
- ASSIGNED: Cyan `#06B6D4`
- IN_PROGRESS: Yellow `#EAB308`
- ON_HOLD: Orange `#F97316`
- RESOLVED: Green `#22C55E`
- CLOSED: Gray `#6B7280`
- CANCELLED: Red `#EF4444`

### D. Priority Icons & Colors
- HIGH: 🔴 Red `#EF4444`
- NORMAL: 🟡 Yellow `#EAB308`

---

**Document Status:** Draft v1.0  
**Next Review:** After stakeholder feedback  
**Approved By:** _Pending_

---

*This PRD is a living document and will be updated as requirements evolve.*
