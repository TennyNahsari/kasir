#!/bin/bash

echo "=== Checking Active Nginx Config ==="
cat /etc/nginx/sites-available/tazkia-inv.duckdns.org
echo ""
echo "=== Checking Nginx Error Log ==="
sudo tail -20 /var/log/nginx/tazkia-inv_error.log
echo ""
echo "=== Testing PHP-FPM Socket ==="
ls -la /var/run/php/php8.3-fpm.sock
echo ""
echo "=== Test Direct PHP Access ==="
echo '<?php phpinfo(); ?>' | sudo tee /var/www/kasir-web/backend/public/test.php
curl -s http://localhost/test.php | head -20
