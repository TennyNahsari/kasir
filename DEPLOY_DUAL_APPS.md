# Deployment Guide: Dual Apps on Single Domain

## Overview
Deploy 2 aplikasi Vue.js dalam 1 domain dengan path berbeda:
- **Inventory App**: https://tazkia-inv.duckdns.org/
- **POS/Kasir App**: https://tazkia-inv.duckdns.org/kasir

## Prerequisites
- Inventory app sudah ter-deploy di server
- Nginx sudah configured untuk inventory app
- Backend API sudah running
- SSL certificate sudah installed

## Deployment Steps

### 1. Build Frontend dengan Base Path

```bash
# Di local machine
cd frontend
npm install
npm run build
```

Build akan menghasilkan folder `dist/` dengan assets yang sudah dikonfigurasi untuk path `/kasir`.

### 2. Upload Build ke Server

```bash
# Buat directory di server (jika belum ada)
ssh user@server
sudo mkdir -p /var/www/tazkia-inv/frontend/dist
sudo chown -R www-data:www-data /var/www/tazkia-inv/frontend

# Upload dari local
# Option A: Menggunakan SCP
scp -r frontend/dist/* user@server:/tmp/frontend-dist/
ssh user@server "sudo mv /tmp/frontend-dist/* /var/www/tazkia-inv/frontend/dist/"

# Option B: Menggunakan rsync (lebih efficient)
rsync -avz --delete frontend/dist/ user@server:/tmp/frontend-dist/
ssh user@server "sudo rsync -av --delete /tmp/frontend-dist/ /var/www/tazkia-inv/frontend/dist/"

# Option C: Pull dari Git di server (recommended)
ssh user@server
cd /var/www/tazkia-inv
git pull origin master
cd frontend
npm install
npm run build
```

### 3. Set Permissions

```bash
# Di server
sudo chown -R www-data:www-data /var/www/tazkia-inv/frontend/dist
sudo chmod -R 755 /var/www/tazkia-inv/frontend/dist
```

### 4. Update Nginx Configuration

```bash
# Backup existing config
sudo cp /etc/nginx/sites-available/tazkia-inv.conf /etc/nginx/sites-available/tazkia-inv.conf.backup

# Upload new config atau edit manual
sudo nano /etc/nginx/sites-available/tazkia-inv.conf

# Paste isi dari nginx-dual-apps.conf

# Test config
sudo nginx -t

# Jika test OK, reload nginx
sudo systemctl reload nginx
```

### 5. Verify Deployment

```bash
# Test via curl
curl -I https://tazkia-inv.duckdns.org/
curl -I https://tazkia-inv.duckdns.org/kasir
curl -I https://tazkia-inv.duckdns.org/api/user

# Check nginx logs jika ada error
sudo tail -f /var/log/nginx/tazkia-inv-error.log
```

### 6. Browser Testing

Buka di browser:
- https://tazkia-inv.duckdns.org/ → Inventory App
- https://tazkia-inv.duckdns.org/kasir → POS/Kasir App

Test:
- ✅ Login page loads
- ✅ Static assets (CSS/JS) loaded correctly
- ✅ API calls working (check Network tab)
- ✅ Page refresh tidak 404
- ✅ Navigation between pages

## File Structure di Server

```
/var/www/tazkia-inv/
├── inventory-app/
│   └── dist/              # Build dari inventory-app
│       ├── index.html
│       ├── assets/
│       └── ...
├── frontend/
│   └── dist/              # Build dari frontend (POS)
│       ├── index.html
│       ├── assets/
│       └── ...
└── backend/
    ├── public/
    │   └── index.php      # API entry point
    ├── app/
    ├── .env               # Shared database config
    └── ...
```

## Troubleshooting

### 404 on Page Refresh

**Problem**: Refresh page di `/kasir/transactions` return 404

**Solution**:
```nginx
# Pastikan try_files include index.html
location /kasir {
    alias /var/www/tazkia-inv/frontend/dist/;
    try_files $uri $uri/ /kasir/index.html;  # ← Penting!
}
```

### Assets 404 (CSS/JS tidak load)

**Problem**: Browser console show 404 for CSS/JS files

**Solution**:
1. Verify `base: '/kasir/'` di `vite.config.js`
2. Rebuild: `npm run build`
3. Check file permissions: `sudo chown -R www-data:www-data /var/www/tazkia-inv/frontend/dist`
4. Check nginx error log: `sudo tail -f /var/log/nginx/tazkia-inv-error.log`

### API Calls Failed

**Problem**: API calls return CORS error atau 404

**Solution**:
1. Check `baseURL: '/api'` di `frontend/src/services/api.js`
2. Verify nginx @laravel rewrite working
3. Test API directly: `curl https://tazkia-inv.duckdns.org/api/user`
4. Check Laravel logs: `tail -f /var/www/tazkia-inv/backend/storage/logs/laravel.log`

### Login Redirect Loop

**Problem**: After login, redirect ke `/kasir/login` terus

**Solution**:
1. Clear browser cookies untuk domain `tazkia-inv.duckdns.org`
2. Check `withCredentials: true` di axios config
3. Verify Laravel session domain di `.env`:
   ```
   SESSION_DOMAIN=.tazkia-inv.duckdns.org
   ```

### Blank White Page

**Problem**: Page loads tapi blank/white screen

**Solution**:
1. Open browser console, check for errors
2. Verify all assets loaded (Network tab)
3. Check base path di router: `createWebHistory('/kasir')`
4. Check Vue app mounting di `main.js`

## Rollback Procedure

Jika deployment gagal, rollback:

```bash
# Restore nginx config
sudo cp /etc/nginx/sites-available/tazkia-inv.conf.backup /etc/nginx/sites-available/tazkia-inv.conf
sudo nginx -t
sudo systemctl reload nginx

# Remove frontend deployment
sudo rm -rf /var/www/tazkia-inv/frontend/dist
```

## Updates & Maintenance

### Update POS/Kasir App

```bash
# Di local
cd frontend
git pull
npm install
npm run build

# Upload ke server (gunakan option C dari step 2)
```

### Monitor Logs

```bash
# Nginx access log
sudo tail -f /var/log/nginx/tazkia-inv-access.log

# Nginx error log
sudo tail -f /var/log/nginx/tazkia-inv-error.log

# Laravel log
sudo tail -f /var/www/tazkia-inv/backend/storage/logs/laravel.log
```

## Performance Optimization

### Enable Gzip Compression

Add to nginx config:
```nginx
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json;
```

### Browser Caching

Already configured in `nginx-dual-apps.conf`:
- HTML: no-cache
- Static assets (JS/CSS/images): 1 year cache

## Security Checklist

- ✅ HTTPS enabled
- ✅ SSL certificate valid
- ✅ Sensitive files blocked (.env, .git)
- ✅ CSRF protection enabled (Laravel)
- ✅ XSS headers configured
- ✅ File upload size limited (20MB)

## Support

Jika ada masalah:
1. Check nginx error logs
2. Check Laravel logs
3. Check browser console
4. Test API endpoints dengan curl
5. Verify file permissions

---

**Last Updated**: February 2026
