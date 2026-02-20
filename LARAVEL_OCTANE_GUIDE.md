# Laravel Octane Deployment Guide

Panduan deploy Laravel dengan Octane (Swoole) untuk performance tinggi.

## 📦 Persiapan: Install Laravel Octane

**Di komputer lokal, install Octane dulu:**

```bash
cd backend

# Install Octane package
composer require laravel/octane

# Install dengan Swoole
php artisan octane:install --server=swoole
# Pilih: Yes untuk install Swoole

# Publish config (optional)
php artisan vendor:publish --provider="Laravel\Octane\OctaneServiceProvider"
```

## ⚙️ Konfigurasi Octane

Edit `config/octane.php`:

```php
return [
    // Server: swoole atau roadrunner
    'server' => env('OCTANE_SERVER', 'swoole'),

    'swoole' => [
        'options' => [
            // Swoole bisa serve static files
            'enable_static_handler' => env('OCTANE_STATIC_HANDLER', true),
            'document_root' => public_path(),
            
            // Static file locations
            'static_handler_locations' => ['/css', '/js', '/images', '/fonts', '/favicon.ico'],
            
            // Worker settings
            'worker_num' => swoole_cpu_num() * 2,
            'task_worker_num' => swoole_cpu_num() * 2,
            'max_request' => 500,
            
            // Performance
            'package_max_length' => 10 * 1024 * 1024, // 10MB
        ],
    ],

    // Warm handlers (preload untuk performance)
    'warm' => [
        // ...
    ],
];
```

Edit `.env`:

```env
OCTANE_SERVER=swoole
OCTANE_STATIC_HANDLER=true
```

## 🚀 Method 1: Pure Octane (Tanpa Nginx)

### Deploy Steps:

```powershell
# 1. Build image
docker build -f backend/Dockerfile.octane -t kasir-octane backend/

# 2. Run dengan docker-compose
docker-compose -f docker-compose-octane.yml up -d

# 3. Run migrations
docker exec kasir-octane php artisan migrate --seed

# 4. Create storage link
docker exec kasir-octane php artisan storage:link

# 5. Test akses
# Browser: http://localhost:8000
# Atau: http://IP-SERVER:8000
```

### Akses dari Luar:

```
✅ Local Network:
   http://192.168.1.100:8000

✅ Internet (jika server punya IP public):
   http://IP-PUBLIC:8000
   
✅ Domain (jika DNS sudah setup):
   http://domain.com:8000
```

### Kelebihan:
- ✅ Simple, hanya 1 container
- ✅ Octane dengan Swoole sangat cepat
- ✅ Bisa serve static files

### Kekurangan:
- ⚠️ Port 8000 (bukan standar HTTP 80)
- ⚠️ Static files tidak se-optimal Nginx
- ⚠️ No SSL termination

---

## 🔥 Method 2: Octane + Nginx (Production Ready)

### Deploy Steps:

```powershell
# 1. Build image
docker build -f backend/Dockerfile.octane -t kasir-octane backend/

# 2. Run dengan docker-compose
docker-compose -f docker-compose-octane-nginx.yml up -d

# 3. Run migrations
docker exec kasir-octane-app php artisan migrate --seed

# 4. Create storage link
docker exec kasir-octane-app php artisan storage:link

# 5. Test akses
# Browser: http://localhost (port 80, standar!)
```

### Akses dari Luar:

```
✅ Local Network:
   http://192.168.1.100

✅ Internet:
   http://IP-PUBLIC
   
✅ Domain:
   http://domain.com
   https://domain.com (jika SSL setup)
```

### Arsitektur:

```
Internet/Browser
       ↓
   Port 80/443
       ↓
┌─────────────────────┐
│   Nginx Container   │
│  - Serve static     │  ← CSS, JS, images
│  - Reverse proxy    │
└─────────────────────┘
       ↓ Proxy
┌─────────────────────┐
│  Octane Container   │
│  - Process PHP      │  ← API, dynamic content
│  port 8000          │
└─────────────────────┘
       ↓
┌─────────────────────┐
│   MySQL Container   │
└─────────────────────┘
```

### Kelebihan:
- ✅ Port 80/443 (standar HTTP/HTTPS)
- ✅ Nginx untuk static files (super cepat)
- ✅ Octane untuk PHP (super cepat juga)
- ✅ Bisa setup SSL/TLS
- ✅ Production-ready

### Kekurangan:
- ⚠️ Butuh 2 containers (Nginx + Octane)
- ⚠️ Sedikit lebih kompleks

---

## 📊 Performance Comparison

### Request ke Static File (CSS):

**Pure Octane:**
```
Browser → Octane (port 8000) → Read file → Return
Time: ~5-10ms
```

**Octane + Nginx:**
```
Browser → Nginx (port 80) → Read file → Return
         (Octane tidak terlibat)
Time: ~1-3ms (3x lebih cepat!)
```

### Request ke API (Dynamic):

**Pure Octane:**
```
Browser → Octane → Laravel Router → Controller → Return
Time: ~20-50ms (sangat cepat dengan Swoole)
```

**Octane + Nginx:**
```
Browser → Nginx → Proxy ke Octane → Laravel → Return
Time: ~25-55ms (tambah ~5ms untuk proxy, masih sangat cepat)
```

---

## 🎯 Folder Public Ter-copy ke Image?

**Ya! Semua ter-copy!**

```dockerfile
# Dockerfile.octane
WORKDIR /var/www
COPY . /var/www  # ← Ini copy SEMUA, termasuk /public
```

**Struktur dalam Container:**

```
/var/www/
├── app/
├── config/
├── database/
├── public/              ← FOLDER PUBLIC ADA DI SINI!
│   ├── index.php       ← Entry point Laravel
│   ├── .htaccess
│   ├── favicon.ico
│   ├── css/            ← Static CSS files
│   │   └── app.css
│   ├── js/             ← Static JS files
│   │   └── app.js
│   └── images/         ← Images
│       └── logo.png
├── routes/
├── storage/
└── vendor/
```

**Cara Kerja:**

1. **Request ke static file** (e.g., `/css/app.css`):
   - Octane check: `public/css/app.css` exist?
   - Yes → Serve directly (jika `enable_static_handler = true`)
   - No → Return 404

2. **Request ke API** (e.g., `/api/products`):
   - Octane pass ke Laravel Router
   - Laravel process normal
   - Return JSON response

---

## ✅ Testing Akses dari Luar

### Test 1: Pure Octane

```powershell
# Start
docker-compose -f docker-compose-octane.yml up -d

# Test dari komputer lain di network yang sama:
# Misal server IP: 192.168.1.100

# Di browser komputer lain:
http://192.168.1.100:8000

# Test API:
curl http://192.168.1.100:8000/api/health

# Test static file:
curl http://192.168.1.100:8000/favicon.ico
```

### Test 2: Octane + Nginx

```powershell
# Start
docker-compose -f docker-compose-octane-nginx.yml up -d

# Test dari komputer lain:
# Di browser:
http://192.168.1.100

# Test API:
curl http://192.168.1.100/api/health

# Test static file:
curl http://192.168.1.100/css/app.css
```

---

## 🛡️ Production Tips

### 1. Disable Octane Static Handler (Biar Nginx yang handle):

```env
# .env
OCTANE_STATIC_HANDLER=false  # Nginx yang handle static
```

### 2. Tune Swoole Workers:

```php
// config/octane.php
'swoole' => [
    'options' => [
        'worker_num' => 4,        // Sesuaikan dengan CPU cores
        'task_worker_num' => 4,
        'max_request' => 1000,    // Restart worker setelah 1000 requests
    ],
],
```

### 3. SSL Setup (untuk Method 2):

Update `nginx/octane.conf`:

```nginx
server {
    listen 443 ssl http2;
    ssl_certificate /etc/letsencrypt/live/domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/domain.com/privkey.pem;
    
    # ... rest of config
}
```

---

## 🎬 Final Answer

### Q: **Jadi kalau pakai Laravel Octane bisa diakses dari luar?**
**A:** ✅ **YA BISA!** Asalkan:
- Port di-expose dengan `-p 8000:8000`
- Octane bind ke `0.0.0.0` (default)
- Firewall allow port tersebut

### Q: **Ini berarti folder public dicopy juga ke dalam image?**
**A:** ✅ **YA!** Semua folder Laravel ter-copy dengan `COPY . /var/www`, termasuk:
- `/public` dengan index.php, CSS, JS, images
- `/app`, `/routes`, `/config`, dll
- Octane bisa serve static files dari `/public`

### Rekomendasi:

🥇 **Production:** Gunakan **Octane + Nginx** (Method 2)
- Nginx handle static files (super cepat)
- Octane handle PHP (super cepat juga)
- Port standar 80/443
- SSL support

🥈 **Simple Deployment:** Gunakan **Pure Octane** (Method 1)
- 1 container only
- Cukup untuk small-medium apps
- Octane handle semua

🥉 **Development:** Gunakan `php artisan octane:start` langsung
- No Docker needed
- Fastest iteration

---

**Ready to deploy? Choose your method and go! 🚀**
