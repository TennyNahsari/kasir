#!/bin/bash

echo "=== Checking Nginx Error Log ==="
sudo tail -50 /var/log/nginx/tazkia-inv_error.log
echo ""

echo "=== Checking Nginx Access Log (last 20 API calls) ==="
sudo tail -100 /var/log/nginx/tazkia-inv_access.log | grep "/api/"
echo ""

echo "=== Checking Laravel Error Log ==="
sudo tail -50 /var/www/kasir-web/backend/storage/logs/laravel.log
echo ""

echo "=== Testing API directly with curl ==="
echo "1. GET /api/locations:"
curl -v http://localhost/api/locations 2>&1 | head -30
echo ""

echo "2. GET /api/me:"
curl -v http://localhost/api/me 2>&1 | head -30
echo ""

echo "=== Checking nginx config syntax ==="
sudo nginx -t
