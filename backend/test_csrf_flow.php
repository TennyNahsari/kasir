<?php

// Test CSRF Flow - simulate what browser does
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

echo "=== Testing CSRF Flow ===\n\n";

// Step 1: Get CSRF Cookie (simulate /sanctum/csrf-cookie)
echo "Step 1: GET /sanctum/csrf-cookie\n";
$request1 = \Illuminate\Http\Request::create('/sanctum/csrf-cookie', 'GET');
$response1 = $kernel->handle($request1);
echo "Status: " . $response1->getStatusCode() . "\n";

// Check cookies set
$cookies = $response1->headers->getCookies();
echo "Cookies set: " . count($cookies) . "\n";
foreach ($cookies as $cookie) {
    echo "  - " . $cookie->getName() . ": " . substr($cookie->getValue(), 0, 50) . "...\n";
}
echo "\n";

// Step 2: Extract XSRF-TOKEN from cookie (like browser would)
$xsrfToken = null;
$sessionCookie = null;
foreach ($cookies as $cookie) {
    if ($cookie->getName() === 'XSRF-TOKEN') {
        $xsrfToken = $cookie->getValue();
        echo "XSRF-TOKEN cookie value (first 50 chars): " . substr($xsrfToken, 0, 50) . "...\n";
    }
    if (strpos($cookie->getName(), 'session') !== false) {
        $sessionCookie = $cookie->getName() . '=' . $cookie->getValue();
    }
}
echo "\n";

// Step 3: Check if XSRF-TOKEN is encrypted
echo "Step 2: Check if XSRF-TOKEN is encrypted\n";
$encryptCookies = new \App\Http\Middleware\EncryptCookies(app('encrypter'));
$reflection = new \ReflectionClass($encryptCookies);
$property = $reflection->getProperty('except');
$property->setAccessible(true);
$except = $property->getValue($encryptCookies);
echo "Cookies NOT encrypted: " . implode(', ', $except) . "\n";
$isXsrfExcluded = in_array('XSRF-TOKEN', $except);
echo "XSRF-TOKEN excluded from encryption: " . ($isXsrfExcluded ? 'YES ✓' : 'NO ✗') . "\n";
echo "\n";

// Step 4: Try to decrypt XSRF-TOKEN (if it's encrypted, this will work. If not, it will fail)
if ($xsrfToken && !$isXsrfExcluded) {
    echo "Step 3: XSRF-TOKEN is encrypted, trying to decrypt...\n";
    try {
        $decrypted = app('encrypter')->decrypt($xsrfToken, false);
        echo "Decrypted value: " . $decrypted . "\n";
    } catch (\Exception $e) {
        echo "Cannot decrypt: " . $e->getMessage() . "\n";
    }
} else {
    echo "Step 3: XSRF-TOKEN is NOT encrypted (correct for JavaScript access)\n";
    if ($xsrfToken) {
        echo "Raw token value: " . $xsrfToken . "\n";
    }
}
echo "\n";

echo "=== Diagnosis ===\n";
if (!$isXsrfExcluded) {
    echo "❌ PROBLEM: XSRF-TOKEN is being encrypted!\n";
    echo "   JavaScript cannot read encrypted cookies.\n";
    echo "   Solution: XSRF-TOKEN must be in EncryptCookies \$except array\n";
} else {
    echo "✓ XSRF-TOKEN is excluded from encryption (correct)\n";
}

echo "\n=== Check App\\Http\\Middleware\\EncryptCookies ===\n";
$file = file_get_contents(__DIR__ . '/app/Http/Middleware/EncryptCookies.php');
echo $file;
