#!/bin/bash

echo "=== Deploy Fix untuk Auth Issue ==="
echo ""

echo "1. Deploying nginx config..."
sudo cp nginx-inventory-only.conf /etc/nginx/sites-available/tazkia-inv.duckdns.org

echo "2. Testing nginx config..."
sudo nginx -t

if [ $? -eq 0 ]; then
    echo "3. Reloading nginx..."
    sudo systemctl reload nginx
    echo "✅ Nginx reloaded successfully"
else
    echo "❌ Nginx config error! Not reloading."
    exit 1
fi

echo ""
echo "4. Updating Laravel files..."
cd /var/www/kasir-web
git pull origin master

echo ""
echo "5. Clearing Laravel cache..."
cd backend
php artisan config:clear
php artisan route:clear
php artisan cache:clear

echo ""
echo "=== Testing API endpoints ==="
echo "Testing GET /api/locations (should return 401 JSON, not 404):"
curl -s -w "\nHTTP Code: %{http_code}\n" http://localhost/api/locations | head -5

echo ""
echo "✅ Deploy completed!"
echo "Try accessing https://tazkia-inv.duckdns.org now"
