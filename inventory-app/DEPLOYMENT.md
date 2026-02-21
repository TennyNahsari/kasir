# Deployment Guide - Inventory App (Full Stack)

Panduan lengkap deployment Inventory Management App (Backend Laravel + Frontend Vue.js) ke production server dengan Nginx dan SSL Let's Encrypt.

## 📋 Prerequisites

### Server Requirements
- **OS**: Ubuntu 20.04/22.04 LTS
- **RAM**: Minimal 2GB (4GB recommended)
- **Storage**: Minimal 20GB
- **CPU**: 2 cores minimum
- **Network**: Public IP atau Domain

### Domain Requirements
- **2 Domains/Subdomains** (atau 1 domain untuk keduanya):
  - Backend API: `api.yourdomain.com` atau `yourdomain.com/api`
  - Frontend: `inventory.yourdomain.com` atau `yourdomain.com`
- DNS A record configured untuk kedua domain
- Port 80 dan 443 accessible dari internet

### Software Requirements
- Nginx (web server)
- PHP 8.2+ & PHP-FPM (untuk Laravel backend)
- PostgreSQL 14+ (database)
- Composer (PHP package manager)
- Node.js 18+ & npm (untuk build frontend)
- Git (untuk clone repository)
- Certbot (untuk SSL)

---

## 🚀 Step-by-Step Deployment

### Step 1: Persiapan Server

#### 1.1. Login ke Server
```bash
ssh root@your-server-ip
# atau
ssh username@your-server-ip
```

#### 1.2. Update System
```bash
sudo apt update && sudo apt upgrade -y
```

#### 1.3. Install Nginx
```bash
sudo apt install nginx -y

# Enable Nginx
sudo systemctl enable nginx
sudo systemctl start nginx

# Check status
sudo systemctl status nginx
```

#### 1.4. Install PHP 8.2 & Extensions
```bash
# Add PHP repository
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.2 dan ekstensi yang diperlukan Laravel
sudo apt install php8.2 php8.2-fpm php8.2-pgsql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl -y

# Verify installation
php -v   # Should show PHP 8.2.x
```

#### 1.5. Install PostgreSQL 14
```bash
# Install PostgreSQL Server
sudo apt install postgresql postgresql-contrib -y

# Enable PostgreSQL
sudo systemctl enable postgresql
sudo systemctl start postgresql

# Check status
sudo systemctl status postgresql
```

**PostgreSQL should be running:**
```
● postgresql.service - PostgreSQL RDBMS
   Active: active (running)
```

#### 1.6. Install Composer
```bash
# Download Composer
curl -sS https://getcomposer.org/installer | php

# Move to global location
sudo mv composer.phar /usr/local/bin/composer

# Verify
composer --version
```

#### 1.7. Install Node.js 18
```bash
# Install Node.js dari NodeSource repository
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y

# Verify installation
node --version   # Should show v18.x.x
npm --version    # Should show 9.x.x or higher
```

#### 1.8. Install Git
```bash
sudo apt install git -y

# Verify
git --version
```

---

### Step 2: Setup Firewall

```bash
# Install UFW (if not installed)
sudo apt install ufw -y

# Allow SSH (IMPORTANT! Jangan skip ini)
sudo ufw allow OpenSSH
sudo ufw allow 22/tcp

# Allow HTTP & HTTPS
sudo ufw allow 'Nginx Full'
# atau
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable

# Check status
sudo ufw status
```

**Output yang diharapkan:**
```
Status: active

To                         Action      From
--                         ------      ----
OpenSSH                    ALLOW       Anywhere
Nginx Full                 ALLOW       Anywhere
```

---

### Step 3: Setup Database

#### 3.1. Create Database and User
```bash
# Switch to postgres user
sudo su - postgres

# Open PostgreSQL prompt
psql
```

**Di PostgreSQL console:**
```sql
-- Create user
CREATE USER inventory_user WITH PASSWORD 'your_strong_password_here';

-- Create database
CREATE DATABASE inventory_db OWNER inventory_user;

-- Grant privileges
GRANT ALL PRIVILEGES ON DATABASE inventory_db TO inventory_user;

-- Exit
\q
```

**Exit dari postgres user:**
```bash
exit
```

#### 3.2. Verify Database
```bash
# Test connection
psql -U inventory_user -d inventory_db -h localhost
# Enter password ketika diminta
# Should login successfully

# Type: \q to exit
```

---

### Step 4: Deploy Backend (Laravel API)

#### 4.1. Create Backend Directory
```bash
# Buat direktori untuk backend
sudo mkdir -p /var/www/inventory-backend
sudo chown -R $USER:$USER /var/www/inventory-backend
```

#### 4.2. Clone Backend Project

**Opsi A: Clone dari Git**
```bash
cd /var/www/inventory-backend
git clone <your-repo-url> .

# Navigate to backend folder
cd backend
```

**Opsi B: Upload via SFTP**
```bash
# Upload folder backend ke /var/www/inventory-backend/backend
```

**Project structure should be:**
```
/var/www/inventory-backend/
└── backend/
    ├── app/
    ├── config/
    ├── database/
    ├── public/      ← Laravel entry point
    ├── routes/
    ├── storage/
    ├── .env.example
    ├── artisan
    └── composer.json
```

#### 4.3. Install Backend Dependencies
```bash
cd /var/www/inventory-backend/backend

# Install Composer dependencies
composer install --optimize-autoloader --no-dev
```

#### 4.4. Configure Backend Environment
```bash
# Copy environment file
cp .env.example .env

# Edit .env file
nano .env
```

**Configure `.env` for production:**
```env
APP_NAME="Inventory API"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

# Database Configuration
DB_CONNECTION=pgsql       # IMPORTANT: Must be 'pgsql' NOT 'psql'
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=inventory_db
DB_USERNAME=inventory_user
DB_PASSWORD=your_strong_password_here

# Session & Cache
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true

# CORS & Sanctum
SANCTUM_STATEFUL_DOMAINS=inventory.yourdomain.com
SESSION_DOMAIN=.yourdomain.com
```

**Save:** `Ctrl+O`, `Enter`, `Ctrl+X`

#### 4.5. Generate Application Key
```bash
php artisan key:generate
```

#### 4.6. Set Permissions
```bash
# Set correct ownership
sudo chown -R www-data:www-data /var/www/inventory-backend/backend/storage
sudo chown -R www-data:www-data /var/www/inventory-backend/backend/bootstrap/cache

# Set correct permissions
sudo chmod -R 775 /var/www/inventory-backend/backend/storage
sudo chmod -R 775 /var/www/inventory-backend/backend/bootstrap/cache
```

#### 4.7. Run Migrations & Seeders
```bash
# Run migrations
php artisan migrate --force

# Run seeders (if any)
php artisan db:seed --force

# Or run both
php artisan migrate:fresh --seed --force
```

#### 4.8. Create Storage Link
```bash
php artisan storage:link
```

#### 4.9. Optimize Laravel
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### Step 5: Deploy Frontend (Vue.js)

#### 5.1. Create Frontend Directory
```bash
# Buat direktori untuk frontend
sudo mkdir -p /var/www/inventory-frontend
sudo chown -R $USER:$USER /var/www/inventory-frontend
```

#### 5.2. Clone or Upload Project

**Opsi A: Clone dari Git Repository**
```bash
cd /var/www/inventory-frontend
git clone <your-repo-url> .

# Navigate to inventory-app
cd inventory-app
```

**Opsi B: Upload via SFTP/SCP**
```bash
# Upload folder inventory-app ke /var/www/inventory-frontend/
```

#### 5.3. Navigate to Inventory App
```bash
cd /var/www/inventory-frontend/inventory-app
```

#### 5.4. Install Dependencies
```bash
# JANGAN gunakan sudo!
npm install
```

**⚠️ PENTING:** Jangan gunakan `sudo npm install`! Ini akan menyebabkan vite permission error saat build.

**Jika error `EACCES` permission:**
```bash
# Fix ownership dulu, baru install
sudo chown -R $USER:$USER ~/.npm
sudo chown -R $USER:$USER /var/www/inventory-frontend

# Install TANPA sudo
npm install
```

#### 5.5. Configure Environment

**Buat file `.env` untuk production:**
```bash
nano .env
```

**Isi file `.env`:**
```env
# API Backend URL - Point ke backend API
VITE_API_URL=https://api.yourdomain.com/api
```

**Save:** `Ctrl+O`, `Enter`, `Ctrl+X`

#### 5.6. Build for Production
```bash
# JANGAN gunakan sudo!
npm run build
```

**⚠️ PENTING:** Jangan gunakan `sudo npm run build`! Bisa menyebabkan permission errors.

**Output yang diharapkan:**
```
vite v4.x.x building for production...
✓ 1234 modules transformed.
dist/index.html                   0.45 kB
dist/assets/index-abc123.css     45.67 kB
dist/assets/index-xyz789.js     234.56 kB
✓ built in 12.34s
```

**Hasil build ada di folder `dist/`:**
```bash
ls -la dist/
```

---

### Step 6: Configure Nginx

#### 6.1. Backend API Configuration

**Create backend Nginx config:**
```bash
sudo nano /etc/nginx/sites-available/inventory-backend
```

**Paste konfigurasi berikut:**

```nginx
# Backend API - Laravel
server {
    listen 80;
    listen [::]:80;
    server_name api.yourdomain.com;
    
    # Laravel public directory
    root /var/www/inventory-backend/backend/public;
    index index.php index.html;
    
    # Logging
    access_log /var/log/nginx/inventory-api-access.log;
    error_log /var/log/nginx/inventory-api-error.log;
    
    # Laravel routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP-FPM configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_index index.php;
        include fastcgi_params;
        
        # Additional FastCGI settings
        fastcgi_buffer_size 128k;
        fastcgi_buffers 256 16k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_temp_file_write_size 256k;
    }
    
    # Deny access to hidden files
    location ~ /\.(?!well-known).* {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
}
```

**⚠️ IMPORTANT:** Ganti `api.yourdomain.com` dengan domain API Anda!

**Save:** `Ctrl+O`, `Enter`, `Ctrl+X`

#### 6.2. Frontend Configuration

**Create frontend Nginx config:**
```bash
sudo nano /etc/nginx/sites-available/inventory-frontend
```

**Paste konfigurasi berikut:**

```nginx
# Frontend - Vue.js SPA
server {
    listen 80;
    listen [::]:80;
    server_name inventory.yourdomain.com;
    
    # Root directory (hasil build Vue.js)
    root /var/www/inventory-frontend/inventory-app/dist;
    index index.html;
    
    # Logging
    access_log /var/log/nginx/inventory-frontend-access.log;
    error_log /var/log/nginx/inventory-frontend-error.log;
    
    # Vue.js SPA - All routes fallback to index.html
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    # Static files caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    # Deny access to hidden files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
}
```

**⚠️ IMPORTANT:** Ganti `inventory.yourdomain.com` dengan domain frontend Anda!

**Save:** `Ctrl+O`, `Enter`, `Ctrl+X`

#### 6.3. Enable Both Sites
```bash
# Enable backend
sudo ln -s /etc/nginx/sites-available/inventory-backend /etc/nginx/sites-enabled/

# Enable frontend
sudo ln -s /etc/nginx/sites-available/inventory-frontend /etc/nginx/sites-enabled/

# Test Nginx configuration
sudo nginx -t
```

**Output yang diharapkan:**
```
nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
nginx: configuration file /etc/nginx/nginx.conf test is successful
```

#### 6.4. Configure PHP-FPM (for Laravel)

**Edit PHP-FPM pool configuration:**
```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

**Adjust settings for better performance:**
```ini
; Find and update these settings
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

**Save:** `Ctrl+O`, `Enter`, `Ctrl+X`

**Restart PHP-FPM:**
```bash
sudo systemctl restart php8.2-fpm
sudo systemctl enable php8.2-fpm
```

#### 6.5. Restart Nginx
```bash
sudo systemctl restart nginx
```

---

### Step 7: Configure DNS

**Sebelum lanjut ke SSL, pastikan DNS sudah pointing!**

#### 7.1. Add DNS A Records

Di DNS provider Anda (Cloudflare, Namecheap, GoDaddy, dll):

**Record 1 - Backend API:**
```
Type: A
Name: api
Value: YOUR_SERVER_IP
TTL: Auto atau 3600
```

**Record 2 - Frontend:**
```
Type: A
Name: inventory
Value: YOUR_SERVER_IP
TTL: Auto atau 3600
```

**Contoh:**
```
A     api           203.0.113.45     Auto
A     inventory     203.0.113.45     Auto
```

#### 7.2. Verify DNS
```bash
# Check backend API DNS
nslookup api.yourdomain.com
ping api.yourdomain.com

# Check frontend DNS
nslookup inventory.yourdomain.com
ping inventory.yourdomain.com
```

**Pastikan kedua domain resolve ke IP server Anda!**

**⏰ Note:** DNS propagation bisa memakan waktu 5 menit - 48 jam (biasanya 5-30 menit).

---

### Step 8: Setup SSL with Let's Encrypt

#### 8.1. Install Certbot
```bash
sudo apt install certbot python3-certbot-nginx -y
```

#### 8.2. Generate SSL for Backend API
```bash
sudo certbot --nginx -d api.yourdomain.com
```

**Anda akan ditanya:**

**1. Email address (untuk renewal notifications):**
```
Enter email address: admin@yourdomain.com
```

**2. Agree to Terms of Service:**
```
(A)gree/(C)ancel: A
```

**3. Share email with EFF (optional):**
```
(Y)es/(N)o: N
```

**4. Redirect HTTP to HTTPS:**
```
Please choose whether or not to redirect HTTP traffic to HTTPS
1: No redirect
2: Redirect - Make all requests redirect to secure HTTPS access
Select the appropriate number [1-2]: 2
```

**Output sukses:**
```
Successfully received certificate.
Certificate is saved at: /etc/letsencrypt/live/api.yourdomain.com/fullchain.pem
Key is saved at: /etc/letsencrypt/live/api.yourdomain.com/privkey.pem
Congratulations! Your certificate and chain have been saved.
```

#### 8.3. Generate SSL for Frontend
```bash
sudo certbot --nginx -d inventory.yourdomain.com
```

**Akan menanyakan hal yang sama, pilih opsi yang sama seperti backend.**

**Output sukses:**
```
Successfully received certificate.
Certificate is saved at: /etc/letsencrypt/live/inventory.yourdomain.com/fullchain.pem
Key is saved at: /etc/letsencrypt/live/inventory.yourdomain.com/privkey.pem
Congratulations!
```

#### 8.4. Verify Auto-Renewal
```bash
# Test renewal untuk kedua certificates
sudo certbot renew --dry-run
```

**Output sukses:**
```
Congratulations, all simulated renewals succeeded:
  /etc/letsencrypt/live/api.yourdomain.com/fullchain.pem (success)
  /etc/letsencrypt/live/inventory.yourdomain.com/fullchain.pem (success)
```

**Certbot otomatis setup cron job untuk auto-renewal setiap 12 jam.**

#### 8.5. Check Certificates Status
```bash
sudo certbot certificates
```

**Output:**
```
Found the following certs:
  Certificate Name: api.yourdomain.com
    Domains: api.yourdomain.com
    Expiry Date: 2024-05-21
    
  Certificate Name: inventory.yourdomain.com
    Domains: inventory.yourdomain.com
    Expiry Date: 2024-05-21
```

---

### Step 9: Verify Configuration

#### 9.1. Check Nginx Configs After SSL

Certbot otomatis update konfigurasi Nginx. Verify:

**Backend config:**
```bash
sudo nano /etc/nginx/sites-available/inventory-backend
```

**Seharusnya ada redirect dan HTTPS block:**
```nginx
# HTTP - Redirect to HTTPS
server {
    listen 80;
    server_name api.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

# HTTPS - Main config
server {
    listen 443 ssl http2;
    server_name api.yourdomain.com;
    
    ssl_certificate /etc/letsencrypt/live/api.yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.yourdomain.com/privkey.pem;
    # ... rest of config
}
```

**Frontend config:**
```bash
sudo nano /etc/nginx/sites-available/inventory-frontend
```

**Seharusnya juga ada redirect dan HTTPS block.**

#### 9.2. Restart Nginx
```bash
sudo systemctl restart nginx
```

---

### Step 10: Testing

#### 10.1. Test Backend API (HTTPS)

**Test HTTP → HTTPS Redirect:**
```bash
curl -I http://api.yourdomain.com
```

**Output yang diharapkan:**
```
HTTP/1.1 301 Moved Permanently
Location: https://api.yourdomain.com/
```

**Test API Health Check:**
```bash
curl https://api.yourdomain.com/api/health
```

**Atau test user endpoint:**
```bash
curl https://api.yourdomain.com/api/user
```

**Output diharapkan:**
```json
{"message": "Unauthenticated."}
```
*(Ini normal karena belum login, artinya API berjalan dengan baik)*

#### 10.2. Test Frontend (HTTPS)

**Test HTTP → HTTPS Redirect:**
```bash
curl -I http://inventory.yourdomain.com
```

**Output yang diharapkan:**
```
HTTP/1.1 301 Moved Permanently
Location: https://inventory.yourdomain.com/
```

**Test HTTPS:**
```bash
curl -I https://inventory.yourdomain.com
```

**Output yang diharapkan:**
```
HTTP/2 200
server: nginx
content-type: text/html
```

#### 10.3. Test di Browser

**1. Buka Backend API:**
```
https://api.yourdomain.com/api/user
```

**Check:**
- ✅ Padlock icon (🔒) di address bar
- ✅ Muncul JSON response: `{"message": "Unauthenticated."}`
- ✅ Certificate valid (klik padlock → Certificate)

**2. Buka Frontend App:**
```
https://inventory.yourdomain.com
```

**Check:**
- ✅ Padlock icon (🔒) di address bar
- ✅ Certificate valid
- ✅ Inventory App loads properly
- ✅ No console errors (F12 → Console tab)
- ✅ No network errors (F12 → Network tab)
- ✅ Can see login page
- ✅ Frontend dapat connect ke backend API

**3. Test Login Functionality:**
- Login dengan user yang sudah ada di database
- Check apakah berhasil login
- Check apakah data dari backend tampil (products, assets, dll)

#### 10.4. SSL Grade Test

**Check SSL configuration quality:**

https://www.ssllabs.com/ssltest/analyze.html?d=api.yourdomain.com
https://www.ssllabs.com/ssltest/analyze.html?d=inventory.yourdomain.com

**Seharusnya mendapat Grade A atau A+**

#### 10.5. Check Services Status

**All services should be running:**
```bash
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status postgresql
```

**Output diharapkan:**
```
● nginx.service - A high performance web server...
   Active: active (running)

● php8.2-fpm.service - The PHP 8.2 FastCGI Process Manager
   Active: active (running)

● postgresql.service - PostgreSQL RDBMS
   Active: active (running)
```

#### 10.6. Check Logs (Jika Ada Error)

**Nginx logs:**
```bash
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log
```

**PHP-FPM logs:**
```bash
sudo tail -f /var/log/php8.2-fpm.log
```

**Laravel logs:**
```bash
sudo tail -f /var/www/inventory-backend/backend/storage/logs/laravel.log
```

---

## 🔄 Update Application

### Update Backend (Laravel API)

```bash
cd /var/www/inventory-backend/backend

# Backup database first (IMPORTANT!)
pg_dump -U inventory_user -d inventory_db -h localhost > ~/backup_$(date +%Y%m%d_%H%M%S).sql

# Pull latest code
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev

# Run migrations (if any)
php artisan migrate --force

# Clear and rebuild cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

### Update Frontend (Vue.js)

```bash
cd /var/www/inventory-frontend/inventory-app

# Pull latest code
git pull origin main

# Install new dependencies (if any)
npm install

# Rebuild production bundle
npm run build

# No restart needed, Nginx serves static files
# Browser cache might need hard refresh: Ctrl+Shift+R
```

---

## 🛠️ Troubleshooting

### Issue 1: Backend 502 Bad Gateway

**Cause:** PHP-FPM tidak berjalan atau socket path salah

**Solution:**
```bash
# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Restart if needed
sudo systemctl restart php8.2-fpm

# Check socket exists
ls -la /var/run/php/php8.2-fpm.sock

# Check Nginx error log
sudo tail -f /var/log/nginx/error.log
```

### Issue 2: Backend CORS Error

**Error:** "CORS policy: No 'Access-Control-Allow-Origin' header"

**Solution:** Check `backend/config/cors.php`:
```php
'allowed_origins' => ['https://inventory.yourdomain.com'],
```

### Issue 3: Frontend 403 Forbidden

**Cause:** Permission issues

**Solution:**
```bash
sudo chown -R www-data:www-data /var/www/inventory-frontend
sudo chmod -R 755 /var/www/inventory-frontend
```

### Issue 4: Frontend 404 for Routes (Vue Router)

**Cause:** Missing `try_files` directive

**Solution:** Pastikan ada di Nginx config frontend:
```nginx
location / {
    try_files $uri $uri/ /index.html;
}
```

### Issue 5: Database Connection Not Configured

**Error:** "Database connection [psql] not configured" atau "Database connection [pgsql] not configured"

**Cause:** Typo di `.env` file - harus `pgsql` bukan `psql`

**Solution:**
```bash
cd /var/www/inventory-backend/backend
nano .env

# PASTIKAN menggunakan 'pgsql' bukan 'psql'
DB_CONNECTION=pgsql

# Clear config cache
php artisan config:clear
php artisan config:cache

# Test migration lagi
php artisan migrate --force
```

### Issue 6: Database Connection Refused

**Error:** "SQLSTATE[HY000] [2002] Connection refused"

**Solution:**
```bash
# Check PostgreSQL running
sudo systemctl status postgresql

# Check .env database config
cd /var/www/inventory-backend/backend
nano .env

# Verify PostgreSQL is listening
sudo netstat -plnt | grep 5432

# Test database connection
php artisan tinker
> DB::connection()->getPDO();
```

### Issue 7: Laravel Storage Permission

**Error:** "The stream or file could not be opened: Permission denied"

**Solution:**
```bash
cd /var/www/inventory-backend/backend
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Issue 8: SSL Certificate Error

**Error:** "NET::ERR_CERT_AUTHORITY_INVALID"

**Solution:**
```bash
# Pastikan DNS sudah resolve
nslookup api.yourdomain.com
nslookup inventory.yourdomain.com

# Regenerate certificate jika perlu
sudo certbot --nginx -d api.yourdomain.com --force-renewal
sudo certbot --nginx -d inventory.yourdomain.com --force-renewal
```

### Issue 9: DNS Not Resolving

**Solution:**
```bash
# Check DNS dari server
nslookup api.yourdomain.com
nslookup inventory.yourdomain.com

# Jika tidak resolve, check DNS provider settings
# Tunggu DNS propagation (up to 48 hours, biasanya 5-30 menit)
```

### Issue 10: Port 80/443 Not Accessible

**Solution:**
```bash
# Check firewall
sudo ufw status

# Allow jika belum
sudo ufw allow 'Nginx Full'

# Check apakah Nginx listening
sudo netstat -tlnp | grep nginx
```

### Issue 11: Vite Permission Denied saat Build

**Error:** "sh: 1: vite: Permission denied" atau "ls: cannot open directory '.': Permission denied"

**Cause:** node_modules di-install dengan `sudo` sehingga owner menjadi `root:root`

**Solution (Step-by-Step):**

**Step 1: Fix ownership ke user Anda**
```bash
cd /var/www/kasir-web/inventory-app  # atau path aplikasi Anda

# Ganti SELURUH ownership ke user Anda (inventory)
sudo chown -R $USER:$USER /var/www/kasir-web/inventory-app
sudo chown -R $USER:$USER ~/.npm

# Verify ownership sudah benar
ls -la node_modules/.bin/vite
# Seharusnya: inventory inventory (bukan root root)
```

**Step 2: Reinstall node_modules (RECOMMENDED)**
```bash
cd /var/www/kasir-web/inventory-app

# Hapus node_modules yang corrupt
rm -rf node_modules package-lock.json

# Install ulang (TANPA sudo!)
npm install

# Build
npm run build
```

**Alternatif: Fix permissions saja (jika tidak mau reinstall)**
```bash
cd /var/www/kasir-web/inventory-app

# Berikan execute permission
chmod -R +x node_modules/.bin

# Coba build lagi
npm run build
```

**Step 3: Verify build berhasil**
```bash
# Check folder dist sudah ada
ls -la dist/

# Seharusnya ada:
# - index.html
# - assets/
# - dll
```

**⚠️ PENTING:** 
- **NEVER** use `sudo npm install` or `sudo npm run build`
- Always run npm commands as regular user
- Only use `sudo` for system operations (chown, service restart, etc.)
- Jika Anda accidentally run `sudo npm install`, HARUS reinstall ulang tanpa sudo

### Issue 12: Blank Page / White Screen

**Cause:** Wrong API URL atau build error

**Solution:**
```bash
cd /var/www/inventory-frontend/inventory-app

# Check browser console (F12) untuk error
# Common issue: Wrong VITE_API_URL

# Fix .env.production
nano .env.production
# VITE_API_URL=https://api.yourdomain.com

# Rebuild
npm run build

# Hard refresh browser: Ctrl+Shift+R
```

---

## 📊 Monitoring & Maintenance

### Check All Services Status
```bash
# One command untuk check semua
sudo systemctl status nginx php8.2-fpm postgresql
```

### View Real-time Logs

**Backend API logs:**
```bash
# Laravel logs
sudo tail -f /var/www/inventory-backend/backend/storage/logs/laravel.log

# PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log

# Nginx backend logs
sudo tail -f /var/log/nginx/error.log | grep api.yourdomain.com
```

**Frontend logs:**
```bash
# Nginx frontend access
sudo tail -f /var/log/nginx/access.log | grep inventory.yourdomain.com

# Nginx frontend errors
sudo tail -f /var/log/nginx/error.log | grep inventory.yourdomain.com
```

### Check SSL Certificate Expiry
```bash
sudo certbot certificates
```

**Output:**
```
Certificate Name: api.yourdomain.com
  Expiry Date: 2024-05-21 (VALID: 89 days)

Certificate Name: inventory.yourdomain.com
  Expiry Date: 2024-05-21 (VALID: 89 days)
```

### Monitor System Resources
```bash
# Disk space
df -h

# Memory usage
free -h

# CPU and process
htop

# Database size
sudo -u postgres psql -c "SELECT pg_size_pretty(pg_database_size('inventory_db'));"
```

### Database Backup Schedule

**Create backup script:**
```bash
sudo nano /usr/local/bin/backup-inventory-db.sh
```

**Script content:**
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/postgresql"
mkdir -p $BACKUP_DIR

export PGPASSWORD='YOUR_DB_PASSWORD'
pg_dump -U inventory_user -h localhost inventory_db > $BACKUP_DIR/inventory_db_$DATE.sql
unset PGPASSWORD

# Keep only last 7 days
find $BACKUP_DIR -name "inventory_db_*.sql" -mtime +7 -delete
```

**Make executable:**
```bash
sudo chmod +x /usr/local/bin/backup-inventory-db.sh
```

**Add to cron (daily at 2 AM):**
```bash
sudo crontab -e
```

**Add line:**
```
0 2 * * * /usr/local/bin/backup-inventory-db.sh
```

---

## 🛡️ Security Best Practices

### 1. Update Nginx Configs dengan Security Headers

**Add to both backend and frontend Nginx configs:**
```nginx
# Security headers
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "no-referrer-when-downgrade" always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
```

### 2. Rate Limiting untuk Backend API

**Edit `/etc/nginx/sites-available/inventory-backend`:**
```nginx
# Add before server block
limit_req_zone $binary_remote_addr zone=api_limit:10m rate=10r/s;

server {
    # ... existing config
    
    location /api {
        limit_req zone=api_limit burst=20 nodelay;
        # ... rest of location config
    }
}
```

### 3. Disable Unused HTTP Methods
```nginx
# Add to server block
if ($request_method !~ ^(GET|POST|PUT|DELETE|HEAD|OPTIONS)$ ) {
    return 405;
}
```

### 4. Hide Server Information
```nginx
# In /etc/nginx/nginx.conf
http {
    server_tokens off;
    # ...
}
```

### 5. PostgreSQL Security

**Allow local connections only:**
```bash
sudo nano /etc/postgresql/14/main/postgresql.conf
```

**Find and ensure:**
```ini
listen_addresses = 'localhost'
```

**Edit pg_hba.conf untuk local authentication:**
```bash
sudo nano /etc/postgresql/14/main/pg_hba.conf
```

**Ensure these lines:**
```
# TYPE  DATABASE        USER            ADDRESS                 METHOD
local   all             postgres                                peer
local   all             all                                     md5
host    all             all             127.0.0.1/32            md5
host    all             all             ::1/128                 md5
```

**Restart PostgreSQL:**
```bash
sudo systemctl restart postgresql
```

### 6. Regular System Updates
```bash
# Update system secara berkala
sudo apt update && sudo apt upgrade -y

# Check for security updates
sudo unattended-upgrades --dry-run
```

### 7. Laravel Security (.env)

**Production .env should have:**
```env
APP_DEBUG=false
APP_ENV=production
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

---

## 📈 Performance Optimization

### 1. Enable Gzip Compression

**Edit `/etc/nginx/nginx.conf`:**
```nginx
http {
    # ...
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;
}
```

### 2. Browser Caching untuk Frontend

**Add to frontend Nginx config:**
```nginx
location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

### 3. PHP-FPM Optimization

**Edit `/etc/php/8.2/fpm/pool.d/www.conf`:**
```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

**Restart PHP-FPM:**
```bash
sudo systemctl restart php8.2-fpm
```

### 4. Laravel Optimization (Already Done)

Pastikan sudah running:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. PostgreSQL Tuning (Basic)

**Edit `/etc/postgresql/14/main/postgresql.conf`:**
```ini
# Memory settings (for 2GB RAM server)
shared_buffers = 512MB              # 25% of RAM
effective_cache_size = 1536MB       # 75% of RAM
maintenance_work_mem = 128MB
work_mem = 16MB

# Connection settings
max_connections = 100

# Performance
random_page_cost = 1.1              # For SSD
effective_io_concurrency = 200      # For SSD
```

**Restart PostgreSQL:**
```bash
sudo systemctl restart postgresql
```

---

## ✅ Deployment Checklist

Sebelum go-live, pastikan semua sudah di-check:

**Server Setup:**
- [ ] Ubuntu 20.04/22.04 dengan minimal 2GB RAM
- [ ] Nginx installed dan configured
- [ ] PHP 8.2-FPM installed dan running
- [ ] PostgreSQL 14+ installed dan secured
- [ ] Composer installed
- [ ] Node.js 18+ installed
- [ ] UFW firewall configured (22, 80, 443)

**DNS & SSL:**
- [ ] DNS A record untuk `api.yourdomain.com` → Server IP
- [ ] DNS A record untuk `inventory.yourdomain.com` → Server IP
- [ ] DNS sudah propagated (nslookup berhasil)
- [ ] SSL certificate untuk backend (api.yourdomain.com)
- [ ] SSL certificate untuk frontend (inventory.yourdomain.com)
- [ ] Auto-renewal enabled dan tested

**Backend (Laravel):**
- [ ] Code deployed ke `/var/www/inventory-backend/backend`
- [ ] Database `inventory_db` created
- [ ] User `inventory_user` created dengan privileges
- [ ] `.env` configured untuk production
- [ ] `composer install --no-dev` completed
- [ ] `php artisan migrate` completed
- [ ] Storage permissions set (www-data:www-data, 775)
- [ ] Laravel optimized (config/route/view cached)
- [ ] Nginx backend config created dan enabled
- [ ] PHP-FPM listening on unix socket

**Frontend (Vue.js):**
- [ ] Code deployed ke `/var/www/inventory-frontend/inventory-app`
- [ ] `.env.production` dengan `VITE_API_URL=https://api.yourdomain.com`
- [ ] `npm install` completed
- [ ] `npm run build` completed
- [ ] dist/ directory generated
- [ ] Nginx frontend config created dan enabled
- [ ] Static files permissions correct

**Testing:**
- [ ] https://api.yourdomain.com/api/user returns JSON
- [ ] https://inventory.yourdomain.com loads successfully
- [ ] SSL padlock shows on both domains
- [ ] SSL Labs Grade A/A+ on both domains
- [ ] Login functionality works
- [ ] Frontend dapat call backend API (no CORS error)
- [ ] All services running (nginx, php-fpm, postgresql)

**Security:**
- [ ] `.env` tidak accessible via web
- [ ] Security headers added
- [ ] Rate limiting configured pada API
- [ ] PostgreSQL listen ke localhost only
- [ ] Unused methods disabled
- [ ] Server tokens hidden

**Monitoring:**
- [ ] SSL auto-renewal tested
- [ ] Database backup script created
- [ ] Cron job untuk backup scheduled
- [ ] Log monitoring setup

---

## 🎉 Deployment Complete!

Aplikasi Inventory Management sekarang sudah live di:

- **Frontend:** https://inventory.yourdomain.com
- **Backend API:** https://api.yourdomain.com

**Happy Managing! 📦**

---

## 📞 Support

Jika menemukan issue:

1. Check logs first (Nginx, PHP-FPM, Laravel)
2. Lihat troubleshooting section
3. Check services status
4. Verify DNS dan SSL

**Common Commands:**
```bash
# Restart all services
sudo systemctl restart nginx php8.2-fpm postgresql

# Check all logs
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/www/inventory-backend/backend/storage/logs/laravel.log

# Test configuration
sudo nginx -t
php artisan config:clear
```

---

*Last updated: January 2024*
*Full Stack Deployment Guide - Laravel API + Vue.js Frontend*
