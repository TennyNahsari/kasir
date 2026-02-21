<?php

// Debug CSRF Token Issue
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CSRF Debug Info ===\n\n";

// 1. Check environment variables
echo "1. Environment Configuration:\n";
echo "   APP_URL: " . env('APP_URL') . "\n";
echo "   FRONTEND_URL: " . env('FRONTEND_URL') . "\n";
echo "   SANCTUM_STATEFUL_DOMAINS: " . env('SANCTUM_STATEFUL_DOMAINS') . "\n";
echo "   SESSION_DOMAIN: " . env('SESSION_DOMAIN') . "\n";
echo "   SESSION_DRIVER: " . env('SESSION_DRIVER') . "\n";
echo "   SESSION_SECURE_COOKIE: " . env('SESSION_SECURE_COOKIE') . "\n";
echo "\n";

// 2. Check actual config values (after cache)
echo "2. Actual Config Values (after cache):\n";
echo "   config('app.url'): " . config('app.url') . "\n";
echo "   config('sanctum.stateful'): " . implode(', ', config('sanctum.stateful')) . "\n";
echo "   config('session.domain'): " . config('session.domain') . "\n";
echo "   config('session.secure'): " . (config('session.secure') ? 'true' : 'false') . "\n";
echo "   config('session.same_site'): " . config('session.same_site') . "\n";
echo "\n";

// 3. Check CORS config
echo "3. CORS Configuration:\n";
echo "   allowed_origins: " . implode(', ', config('cors.allowed_origins')) . "\n";
echo "   supports_credentials: " . (config('cors.supports_credentials') ? 'true' : 'false') . "\n";
echo "\n";

// 4. Check middleware
echo "4. Sanctum Middleware Configuration:\n";
echo "   verify_csrf_token: " . config('sanctum.middleware.verify_csrf_token') . "\n";
echo "   encrypt_cookies: " . config('sanctum.middleware.encrypt_cookies') . "\n";
echo "\n";

// 5. Check if config is cached
echo "5. Cache Status:\n";
$configCached = file_exists(base_path('bootstrap/cache/config.php'));
echo "   Config cached: " . ($configCached ? 'YES (config:cache active)' : 'NO') . "\n";
if ($configCached) {
    echo "   Cache file: bootstrap/cache/config.php\n";
    echo "   WARNING: Changes in .env won't take effect until you run 'php artisan config:clear'\n";
}
echo "\n";

// 6. Check session storage
echo "6. Session Storage:\n";
$sessionPath = storage_path('framework/sessions');
echo "   Session path: $sessionPath\n";
echo "   Path exists: " . (is_dir($sessionPath) ? 'YES' : 'NO') . "\n";
if (is_dir($sessionPath)) {
    echo "   Writable: " . (is_writable($sessionPath) ? 'YES' : 'NO') . "\n";
    $files = glob($sessionPath . '/*');
    echo "   Session files count: " . count($files) . "\n";
}
echo "\n";

echo "=== Recommendations ===\n";
if ($configCached) {
    echo "1. Run: php artisan config:clear\n";
}
echo "2. Run: php artisan cache:clear\n";
echo "3. Run: php artisan route:clear\n";
echo "4. Run: sudo systemctl restart php8.2-fpm\n";
echo "5. Clear browser cookies and test again\n";
