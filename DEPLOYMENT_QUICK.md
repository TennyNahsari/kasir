# Quick Deployment Guide - Nginx

Panduan singkat untuk deploy Unified POS dengan Nginx.

## 🎯 Arsitektur Deployment

```
                    ┌─────────────────┐
                    │   NGINX         │
                    │   Port 80/443   │
                    └────────┬────────┘
                             │
                ┌────────────┴────────────┐
                │                         │
       ┌────────▼─────────┐     ┌────────▼────────┐
       │   Frontend        │     │   Backend API   │
       │   Vue.js (SPA)    │     │   Laravel       │
       │   /dist folder    │     │   PHP-FPM       │
       └───────────────────┘     └────────┬────────┘
                                          │
                                  ┌───────▼────────┐
                                  │   MySQL DB     │
                                  └────────────────┘
```

## 🚀 Cara Kerja Nginx

### 1. **Frontend (Static Files)**
Nginx serve langsung file HTML/CSS/JS dari folder `frontend/dist/`

### 2. **Backend API**
Nginx proxy request `/api/*` ke Laravel melalui PHP-FPM

### 3. **Routing**
- Request ke `/` → Frontend (index.html)
- Request ke `/api/*` → Backend Laravel
- Request ke `/storage/*` → File uploads

## ⚡ Quick Start (Ubuntu)

### 1. Install Software
```bash
sudo apt update
sudo apt install nginx php8.2-fpm mysql-server nodejs npm composer -y
```

### 2. Setup Database
```bash
sudo mysql -u root
CREATE DATABASE kasir_pos;
CREATE USER 'kasir_user'@'localhost' IDENTIFIED BY 'your-password';
GRANT ALL PRIVILEGES ON kasir_pos.* TO 'kasir_user'@'localhost';
EXIT;
```

### 3. Setup Backend
```bash
cd /var/www/kasir-pos/backend
composer install --no-dev
cp .env.example .env
nano .env  # Edit database config
php artisan key:generate
php artisan migrate --seed
sudo chown -R www-data:www-data storage bootstrap/cache
```

### 4. Build Frontend
```bash
cd /var/www/kasir-pos/frontend
npm install
echo "VITE_API_URL=http://your-domain.com/api" > .env
npm run build
```

### 5. Configure Nginx
```bash
sudo nano /etc/nginx/sites-available/kasir-pos
```

**Paste config minimal:**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    
    root /var/www/kasir-pos/frontend/dist;
    index index.html;
    
    # Frontend
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    # Backend API
    location /api {
        alias /var/www/kasir-pos/backend/public;
        try_files $uri @laravel;
        
        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_param SCRIPT_FILENAME /var/www/kasir-pos/backend/public/index.php;
            include fastcgi_params;
        }
    }
    
    location @laravel {
        rewrite /api/(.*)$ /index.php?/$1 last;
    }
    
    # Storage
    location /storage {
        alias /var/www/kasir-pos/backend/storage/app/public;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/kasir-pos /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 6. Test
Buka browser: `http://your-domain.com`

## 🔧 Alternatif: Subdomain Terpisah

**Frontend:** `app.yourdomain.com`
**Backend:** `api.yourdomain.com`

### Config Frontend (app.yourdomain.com)
```nginx
server {
    listen 80;
    server_name app.yourdomain.com;
    root /var/www/kasir-pos/frontend/dist;
    index index.html;
    
    location / {
        try_files $uri $uri/ /index.html;
    }
}
```

### Config Backend (api.yourdomain.com)
```nginx
server {
    listen 80;
    server_name api.yourdomain.com;
    root /var/www/kasir-pos/backend/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

**Update Frontend .env:**
```env
VITE_API_URL=https://api.yourdomain.com/api
```

**Update Backend .env:**
```env
SANCTUM_STATEFUL_DOMAINS=app.yourdomain.com
SESSION_DOMAIN=.yourdomain.com
```

## 📱 Deploy di Local Network

Untuk server lokal tanpa domain:

```nginx
server {
    listen 80;
    server_name 192.168.1.100;  # IP server
    
    root /var/www/kasir-pos/frontend/dist;
    index index.html;
    
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    location /api {
        alias /var/www/kasir-pos/backend/public;
        try_files $uri @laravel;
        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_param SCRIPT_FILENAME /var/www/kasir-pos/backend/public/index.php;
            include fastcgi_params;
        }
    }
    
    location @laravel {
        rewrite /api/(.*)$ /index.php?/$1 last;
    }
}
```

**Frontend .env:**
```env
VITE_API_URL=http://192.168.1.100/api
```

**Backend .env:**
```env
APP_URL=http://192.168.1.100
SANCTUM_STATEFUL_DOMAINS=192.168.1.100
```

## 🔐 Enable SSL (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

Certbot akan otomatis update config nginx dan setup auto-renewal.

## 🔄 Update Aplikasi

```bash
# Backup database
mysqldump -u kasir_user -p kasir_pos > backup.sql

# Pull update code
cd /var/www/kasir-pos
git pull

# Update backend
cd backend
composer install --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache

# Build frontend
cd ../frontend
npm install
npm run build

# Restart services
sudo systemctl restart php8.2-fpm nginx
```

## 🛠️ Automated Deployment Script

Gunakan script yang sudah disediakan:

```bash
# Linux/Mac
chmod +x deploy.sh
./deploy.sh production

# Windows
deploy-windows.bat
```

## 🚨 Troubleshooting

### 502 Bad Gateway
```bash
sudo systemctl restart php8.2-fpm
sudo systemctl status php8.2-fpm
```

### Permission Denied
```bash
sudo chown -R www-data:www-data /var/www/kasir-pos/backend/storage
sudo chmod -R 775 /var/www/kasir-pos/backend/storage
```

### API CORS Error
Cek `backend/.env`:
```env
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
SESSION_DOMAIN=.yourdomain.com
```

### Frontend Blank/White Screen
```bash
# Rebuild frontend
cd /var/www/kasir-pos/frontend
npm run build

# Check nginx config
sudo nginx -t
sudo systemctl restart nginx
```

## 📊 Monitor Logs

```bash
# Nginx error log
sudo tail -f /var/log/nginx/error.log

# Laravel log
sudo tail -f /var/www/kasir-pos/backend/storage/logs/laravel.log

# PHP-FPM log
sudo tail -f /var/log/php8.2-fpm.log
```

## ✅ Production Checklist

- [ ] `APP_DEBUG=false` di backend `.env`
- [ ] `APP_ENV=production` di backend `.env`
- [ ] SSL certificate installed (Let's Encrypt)
- [ ] Firewall enabled (UFW)
- [ ] Database password yang kuat
- [ ] Regular backup database (cron job)
- [ ] Storage permissions correct (775)
- [ ] `SESSION_SECURE_COOKIE=true` (jika pakai HTTPS)
- [ ] Log rotation enabled
- [ ] Monitor disk space

## 📚 Dokumentasi Lengkap

Untuk panduan lengkap, lihat [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)

## 🎉 Selesai!

Aplikasi sudah bisa diakses di domain/IP server Anda!

Login default:
- **Owner**: owner@kasir.app / password
- **Kasir**: kasir@kasir.app / password
