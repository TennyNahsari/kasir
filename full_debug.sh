#!/bin/bash

echo "=== NGINX CONFIG DEBUG ==="
echo ""

echo "1. Current nginx config in sites-available:"
echo "---"
cat /etc/nginx/sites-available/tazkia-inv.duckdns.org
echo ""

echo "2. Symlink in sites-enabled:"
ls -la /etc/nginx/sites-enabled/ | grep tazkia
echo ""

echo "3. All configs in sites-enabled:"
ls -la /etc/nginx/sites-enabled/
echo ""

echo "4. Testing which server_name nginx will use for localhost:"
curl -sI http://localhost/ | grep -i server
echo ""

echo "5. Check if PHP-FPM is running:"
ps aux | grep php-fpm | grep -v grep
echo ""

echo "6. Test direct PHP file:"
echo '<?php echo "PHP Works! Laravel Path: "; echo realpath("/var/www/kasir-web/backend/public"); ?>' > /tmp/test.php
sudo cp /tmp/test.php /var/www/kasir-web/backend/public/test.php
curl -s http://localhost/test.php 2>&1
echo ""

echo "7. Nginx error log (last 5 lines):"
sudo tail -5 /var/log/nginx/error.log
