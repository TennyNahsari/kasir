# Panduan Deployment dengan Nginx

Panduan lengkap untuk deploy aplikasi Unified POS menggunakan Nginx di server production.

## 📋 Prerequisites

### Server Requirements
- Ubuntu 20.04/22.04 LTS (atau OS Linux lainnya)
- RAM minimal 2GB
- Storage minimal 20GB
- Akses root/sudo

### Software Requirements
- Nginx
- PHP 8.2+ dengan PHP-FPM
- MySQL 8.0+
- Node.js 18+
- Composer
- Git

## 🚀 Langkah-langkah Deployment

### 1. Install Dependencies

```bash
# Update sistem
sudo apt update && sudo apt upgrade -y

# Install Nginx
sudo apt install nginx -y

# Install PHP 8.2 dan ekstensi yang diperlukan
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath -y

# Install MySQL
sudo apt install mysql-server -y

# Install Node.js 18
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Git
sudo apt install git -y
```

### 2. Setup Database

```bash
# Login ke MySQL
sudo mysql -u root

# Buat database dan user
CREATE DATABASE kasir_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'kasir_user'@'localhost' IDENTIFIED BY 'password_yang_kuat';
GRANT ALL PRIVILEGES ON kasir_pos.* TO 'kasir_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Clone dan Setup Backend

```bash
# Buat direktori untuk aplikasi
sudo mkdir -p /var/www/kasir-pos
sudo chown -R $USER:$USER /var/www/kasir-pos

# Clone repository (atau upload via FTP/SFTP)
cd /var/www/kasir-pos
git clone <your-repo-url> .
# ATAU upload files secara manual

# Setup backend
cd /var/www/kasir-pos/backend

# Install dependencies
composer install --optimize-autoloader --no-dev

# Setup .env
cp .env.example .env
nano .env
```

Edit file `.env` dengan konfigurasi production:
```env
APP_NAME="Unified POS"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kasir_pos
DB_USERNAME=kasir_user
DB_PASSWORD=password_yang_kuat

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true

SANCTUM_STATEFUL_DOMAINS=your-domain.com
SESSION_DOMAIN=.your-domain.com
```

```bash
# Generate application key
php artisan key:generate

# Set permissions
sudo chown -R www-data:www-data /var/www/kasir-pos/backend/storage
sudo chown -R www-data:www-data /var/www/kasir-pos/backend/bootstrap/cache
sudo chmod -R 775 /var/www/kasir-pos/backend/storage
sudo chmod -R 775 /var/www/kasir-pos/backend/bootstrap/cache

# Run migrations dan seeders
php artisan migrate --seed --force

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. Build Frontend

```bash
cd /var/www/kasir-pos/frontend

# Install dependencies
npm install

# Setup .env untuk production
cat > .env << EOF
VITE_API_URL=https://your-domain.com/api
EOF

# Build untuk production
npm run build

# Files hasil build ada di folder dist/
```

### 5. Konfigurasi Nginx

Buat file konfigurasi Nginx:

```bash
sudo nano /etc/nginx/sites-available/kasir-pos
```

Paste konfigurasi berikut:

```nginx
# Redirect HTTP ke HTTPS (opsional, jika sudah setup SSL)
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    
    # Jika sudah ada SSL, uncomment baris ini:
    # return 301 https://$server_name$request_uri;
    
    # Jika belum ada SSL, gunakan config di bawah
    root /var/www/kasir-pos/frontend/dist;
    index index.html;
    
    # Frontend - Vue.js SPA
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    # Backend API - Laravel
    location /api {
        alias /var/www/kasir-pos/backend/public;
        try_files $uri $uri/ @backend;
        
        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME /var/www/kasir-pos/backend/public/index.php;
            include fastcgi_params;
        }
    }
    
    location @backend {
        rewrite /api/(.*)$ /index.php?/$1 last;
    }
    
    # Storage files (uploads)
    location /storage {
        alias /var/www/kasir-pos/backend/storage/app/public;
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
    
    # Deny access to hidden files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # Logging
    access_log /var/log/nginx/kasir-pos-access.log;
    error_log /var/log/nginx/kasir-pos-error.log;
}

# HTTPS Configuration (jika sudah setup SSL)
# server {
#     listen 443 ssl http2;
#     listen [::]:443 ssl http2;
#     server_name your-domain.com www.your-domain.com;
#
#     # SSL Certificate (gunakan Let's Encrypt - lihat bagian SSL di bawah)
#     ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
#     ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
#     
#     # SSL Configuration
#     ssl_protocols TLSv1.2 TLSv1.3;
#     ssl_ciphers HIGH:!aNULL:!MD5;
#     ssl_prefer_server_ciphers on;
#     
#     root /var/www/kasir-pos/frontend/dist;
#     index index.html;
#     
#     # Frontend - Vue.js SPA
#     location / {
#         try_files $uri $uri/ /index.html;
#     }
#     
#     # Backend API - Laravel
#     location /api {
#         alias /var/www/kasir-pos/backend/public;
#         try_files $uri $uri/ @backend;
#         
#         location ~ \.php$ {
#             fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
#             fastcgi_index index.php;
#             fastcgi_param SCRIPT_FILENAME /var/www/kasir-pos/backend/public/index.php;
#             include fastcgi_params;
#             fastcgi_param HTTPS on;
#         }
#     }
#     
#     location @backend {
#         rewrite /api/(.*)$ /index.php?/$1 last;
#     }
#     
#     # Storage files
#     location /storage {
#         alias /var/www/kasir-pos/backend/storage/app/public;
#         expires 30d;
#         add_header Cache-Control "public, immutable";
#     }
#     
#     # Security headers
#     add_header X-Frame-Options "SAMEORIGIN" always;
#     add_header X-Content-Type-Options "nosniff" always;
#     add_header X-XSS-Protection "1; mode=block" always;
#     
#     # Deny access to hidden files
#     location ~ /\. {
#         deny all;
#         access_log off;
#         log_not_found off;
#     }
#     
#     # Logging
#     access_log /var/log/nginx/kasir-pos-access.log;
#     error_log /var/log/nginx/kasir-pos-error.log;
# }
```

Aktifkan konfigurasi:

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/kasir-pos /etc/nginx/sites-enabled/

# Test konfigurasi
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx

# Enable Nginx on boot
sudo systemctl enable nginx
```

### 6. Setup SSL dengan Let's Encrypt (Opsional tapi Disarankan)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Generate SSL certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Certbot akan otomatis update konfigurasi Nginx
# Test auto-renewal
sudo certbot renew --dry-run
```

### 7. Setup PHP-FPM untuk Performa Optimal

Edit konfigurasi PHP-FPM:

```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

Sesuaikan pengaturan:
```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
sudo systemctl enable php8.2-fpm
```

### 8. Setup Firewall

```bash
# Install UFW
sudo apt install ufw -y

# Allow SSH
sudo ufw allow OpenSSH

# Allow HTTP and HTTPS
sudo ufw allow 'Nginx Full'

# Enable firewall
sudo ufw enable
```

### 9. Setup Storage Link

```bash
cd /var/www/kasir-pos/backend
php artisan storage:link
```

## 🔧 Konfigurasi Tambahan

### Setup Queue Worker (Jika Diperlukan)

Buat systemd service untuk Laravel queue:

```bash
sudo nano /etc/systemd/system/kasir-queue.service
```

```ini
[Unit]
Description=Kasir POS Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/kasir-pos/backend/artisan queue:work --sleep=3 --tries=3

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable kasir-queue
sudo systemctl start kasir-queue
```

### Setup Cron untuk Task Scheduler

```bash
sudo crontab -e -u www-data
```

Tambahkan:
```
* * * * * cd /var/www/kasir-pos/backend && php artisan schedule:run >> /dev/null 2>&1
```

### Monitoring dan Logs

```bash
# Monitor Nginx logs
sudo tail -f /var/log/nginx/kasir-pos-access.log
sudo tail -f /var/log/nginx/kasir-pos-error.log

# Monitor Laravel logs
sudo tail -f /var/www/kasir-pos/backend/storage/logs/laravel.log

# Monitor PHP-FPM
sudo tail -f /var/log/php8.2-fpm.log
```

## 🔄 Update Aplikasi

Untuk update aplikasi di kemudian hari:

```bash
# Backup database dulu!
mysqldump -u kasir_user -p kasir_pos > backup-$(date +%Y%m%d).sql

# Pull update atau upload files baru
cd /var/www/kasir-pos
git pull origin main

# Update backend
cd backend
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Update frontend
cd ../frontend
npm install
npm run build

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

## 🎯 Alternatif: Deploy di Subdomain

Jika ingin backend dan frontend di subdomain terpisah:

### Frontend: `pos.your-domain.com`
### Backend: `api.your-domain.com`

Buat 2 file konfigurasi terpisah:

**Frontend (/etc/nginx/sites-available/kasir-pos-frontend):**
```nginx
server {
    listen 80;
    server_name pos.your-domain.com;
    
    root /var/www/kasir-pos/frontend/dist;
    index index.html;
    
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    access_log /var/log/nginx/kasir-pos-frontend-access.log;
    error_log /var/log/nginx/kasir-pos-frontend-error.log;
}
```

**Backend (/etc/nginx/sites-available/kasir-pos-backend):**
```nginx
server {
    listen 80;
    server_name api.your-domain.com;
    
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
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    access_log /var/log/nginx/kasir-pos-backend-access.log;
    error_log /var/log/nginx/kasir-pos-backend-error.log;
}
```

Jangan lupa update `VITE_API_URL` di frontend:
```env
VITE_API_URL=https://api.your-domain.com/api
```

Dan `SANCTUM_STATEFUL_DOMAINS` di backend:
```env
SANCTUM_STATEFUL_DOMAINS=pos.your-domain.com
SESSION_DOMAIN=.your-domain.com
```

## 📱 Deploy untuk Local Network (Tanpa Domain)

Jika deploy di server lokal untuk toko/outlet:

```nginx
server {
    listen 80;
    server_name 192.168.1.100;  # IP server lokal
    
    root /var/www/kasir-pos/frontend/dist;
    index index.html;
    
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    location /api {
        alias /var/www/kasir-pos/backend/public;
        try_files $uri $uri/ @backend;
        
        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME /var/www/kasir-pos/backend/public/index.php;
            include fastcgi_params;
        }
    }
    
    location @backend {
        rewrite /api/(.*)$ /index.php?/$1 last;
    }
}
```

Update frontend `.env`:
```env
VITE_API_URL=http://192.168.1.100/api
```

Update backend `.env`:
```env
APP_URL=http://192.168.1.100
SANCTUM_STATEFUL_DOMAINS=192.168.1.100
SESSION_DOMAIN=192.168.1.100
```

## 🛡️ Security Checklist

- [ ] Set `APP_DEBUG=false` di production
- [ ] Gunakan password database yang kuat
- [ ] Setup firewall (UFW)
- [ ] Install SSL certificate (Let's Encrypt)
- [ ] Set proper file permissions (775 untuk storage, 755 untuk lainnya)
- [ ] Disable directory listing di Nginx
- [ ] Regular backup database
- [ ] Update sistem dan dependencies secara berkala
- [ ] Monitor logs secara rutin
- [ ] Gunakan `SESSION_SECURE_COOKIE=true` jika pakai HTTPS

## 🚨 Troubleshooting

### Masalah: 502 Bad Gateway
```bash
# Cek PHP-FPM status
sudo systemctl status php8.2-fpm

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

### Masalah: Permission Denied
```bash
# Fix permissions
sudo chown -R www-data:www-data /var/www/kasir-pos/backend/storage
sudo chmod -R 775 /var/www/kasir-pos/backend/storage
```

### Masalah: CSS/JS tidak load
```bash
# Rebuild frontend
cd /var/www/kasir-pos/frontend
npm run build

# Clear Nginx cache
sudo systemctl restart nginx
```

### Masalah: CORS Error
Pastikan konfigurasi CORS di backend sudah benar:
```bash
# Edit config/cors.php
# Atau set di .env:
SANCTUM_STATEFUL_DOMAINS=your-domain.com
```

## 📊 Performance Tuning

### Enable Gzip Compression
Edit `/etc/nginx/nginx.conf`:
```nginx
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json;
```

### Enable Browser Caching
Tambah di konfigurasi Nginx:
```nginx
location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

### Optimize MySQL
```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```
Tambahkan:
```ini
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
max_connections = 200
```

## 📞 Support

Jika ada masalah, cek:
1. Nginx error log: `/var/log/nginx/kasir-pos-error.log`
2. Laravel log: `/var/www/kasir-pos/backend/storage/logs/laravel.log`
3. PHP-FPM log: `/var/log/php8.2-fpm.log`

---

**Selamat! Aplikasi Unified POS sudah berhasil di-deploy! 🎉**
