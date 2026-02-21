#!/bin/bash
set -e

echo "=== ULTIMATE FIX - Deploy with Proxy Config ==="
echo ""

cd /var/www/kasir-web

# Stop any running Laravel serve
pkill -f "php artisan serve" || true

# Start Laravel on port 8080
echo "1. Starting Laravel backend on port 8080..."
cd backend
nohup php artisan serve --host=127.0.0.1 --port=8080 > /tmp/laravel-serve.log 2>&1 &
LARAVEL_PID=$!
echo "   Laravel PID: $LARAVEL_PID"
sleep 2

# Test Laravel directly
echo "2. Testing Laravel backend directly..."
curl -s http://127.0.0.1:8080/api/locations | head -5
echo ""

# Deploy nginx config
echo "3. Deploying nginx config..."
cd /var/www/kasir-web
sudo cp nginx-proxy.conf /etc/nginx/sites-available/tazkia-inv

# Test and restart nginx
echo "4. Restarting nginx..."
sudo nginx -t
sudo systemctl restart nginx

# Test through nginx
echo "5. Testing through nginx..."
curl -s http://localhost/api/locations | head -5
echo ""

echo ""
echo "✅ Deploy completed!"
echo "Laravel backend running on PID: $LARAVEL_PID"
echo "Check logs: tail -f /tmp/laravel-serve.log"
echo ""
echo "To make Laravel serve permanent, run:"
echo "sudo systemctl enable kasir-backend.service"
