# Ticket Management System - MVP

Ticket Management & Asset Tracking System MVP dengan fitur login dan dashboard.

## 🚀 Features (MVP)

### Backend
- ✅ Database migrations (tickets, worklogs, attachments, maintenance_schedules)
- ✅ Models dengan relationships
- ✅ Controllers & API endpoints
- ✅ Dummy data seeders
- ✅ Authentication (Laravel Sanctum)

### Frontend
- ✅ Login page dengan role-based authentication
- ✅ Dashboard (role-specific)
  - Supervisor Dashboard: Ticket stats, asset overview, recent tickets
  - Technician Dashboard: Assigned tickets, today's maintenance, overdue alerts
  - User Dashboard: My tickets, my assets
- ✅ Routing & navigation
- ✅ Responsive design

## 📋 Prerequisites

- PHP 8.1+
- PostgreSQL
- Node.js 18+
- Composer
- Backend already running on http://localhost:8000

## 🔧 Setup Instructions

### 1. Database Setup

```powershell
# Navigate to backend folder
cd backend

# Run migrations
php artisan migrate

# Run seeders for ticket system
php artisan db:seed --class=TicketSystemSeeder
```

This will create:
- 2 Technician users (technician@kasir.test, maintenance@kasir.test)
- 5 Sample tickets (3 incidents + 2 maintenance)
- 5 Maintenance schedules
- Sample worklogs

### 2. Frontend Setup

```powershell
# Navigate to ticket-app folder
cd ticket-app

# Install dependencies
npm install

# Run development server
npm run dev
```

The app will run on **http://localhost:5176**

## 🔑 Demo Accounts

### Technician Users (New)
- Email: `technician@kasir.test`
- Password: `password`

- Email: `maintenance@kasir.test`
- Password: `password`

### Existing Users
- **Owner**: `owner@kasir.test` / `password`
- **Supervisor**: `supervisor@kasir.test` / `password`

## 📱 Application Structure

```
ticket-app/
├── src/
│   ├── views/
│   │   ├── LoginView.vue          ✅ Login page
│   │   ├── DashboardView.vue      ✅ Role-based dashboard
│   │   ├── TicketList.vue         🔜 Coming soon
│   │   ├── TicketDetail.vue       🔜 Coming soon
│   │   └── MyAssets.vue           🔜 Coming soon
│   ├── components/
│   │   ├── SupervisorDashboard.vue
│   │   ├── TechnicianDashboard.vue
│   │   ├── UserDashboard.vue
│   │   ├── StatCard.vue
│   │   └── StatusBar.vue
│   ├── layouts/
│   │   └── MainLayout.vue
│   ├── stores/
│   │   └── auth.js
│   ├── services/
│   │   └── ticketService.js
│   └── router/
│       └── index.js
```

## 🎯 API Endpoints (MVP)

### Authentication
- `POST /api/login` - Login
- `POST /api/logout` - Logout
- `GET /api/me` - Get current user

### Tickets
- `GET /api/tickets` - List tickets (with filters)
- `GET /api/tickets/{id}` - Get ticket detail
- `POST /api/tickets` - Create ticket
- `PUT /api/tickets/{id}` - Update ticket
- `GET /api/tickets/statistics` - Get statistics
- `GET /api/tickets/my-assets` - Get user's assets

### Dashboard
- `GET /api/ticket-dashboard` - Get role-based dashboard data

## 🎨 Dashboard Features

### Supervisor Dashboard
- Total tickets overview
- Tickets by status chart
- Asset statistics
- Recent tickets table
- Technician performance

### Technician Dashboard
- My workload statistics
- Assigned tickets list
- Today's scheduled maintenance
- Overdue tickets alerts

### User Dashboard
- My open tickets
- My assigned assets
- Quick create ticket button
- Ticket status summary

## 🔄 Development Workflow

1. **Backend is already running** on port 8000
2. **Run migrations**: Creates new tables for ticket system
3. **Run seeders**: Populates dummy data
4. **Start frontend**: `npm run dev` in ticket-app folder
5. **Login**: Use demo accounts
6. **Test dashboard**: View role-specific data

## 🐛 Troubleshooting

### CORS Issues
Ensure `config/cors.php` includes:
```php
'allowed_origins' => [
    'http://localhost:5173',
    'http://localhost:5174',
    'http://localhost:5175',
    'http://localhost:5176',  // Ticket app
],
```

### Database Connection
Check `.env` in backend folder:
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=kasir_db
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### Frontend API Connection
The proxy is configured in `vite.config.js`:
```javascript
server: {
  port: 5176,
  proxy: {
    '/api': {
      target: 'http://localhost:8000',
      changeOrigin: true
    }
  }
}
```

## 📈 Next Steps (Phase 2)

- [ ] Ticket List with advanced filters
- [ ] Ticket Detail page with timeline
- [ ] Create Ticket form
- [ ] Worklog functionality
- [ ] File attachments
- [ ] My Assets page
- [ ] Asset management (CRUD)
- [ ] Maintenance scheduling

## 🤝 Contributing

This is the MVP phase. More features will be added in subsequent phases as per the PRD.

---

**Version**: 1.0 MVP  
**Date**: February 9, 2026  
**Port**: 5176
