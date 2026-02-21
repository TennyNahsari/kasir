<?php

// Debug: Test if Laravel can decrypt session cookie from browser
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Session Cookie Debug ===\n\n";

// This is the actual cookie value from your browser (from the error screenshot)
// Replace this with the LATEST cookie value from your browser
$cookieFromBrowser = "eyJpdiI6ImFqN1hVcFZUSnpIenBEaG1YRzJ5emc9PSIsInZhbHVlIjoiS0R1TzZWbU5ZeWkzcVVUUUgrSi9JcG1CRVNxV1dQSVlEekwyVGF0NVQ3ajFLaFUrOUFrZFk4dUt4ZVhyTHY2ZW8zd2RlWDlWRGtlRnZwYWdkM1FqYzR3SVBqcEJGZ1Urcy82eW55c3lETW1ISHZxV1JTNVRIYlB0TzJnNjdTRlQiLCJtYWMiOiI1OWQ2MjZjMTkzOTZlZjMwNjE2NzM1N2NjZjg4M2FhNGUzOGJiNTlhNDM4MzgzZjZkN2VmYzBjYjdlMDkzMWY2IiwidGFnIjoiIn0%3D";

// URL decode
$cookieDecoded = urldecode($cookieFromBrowser);
echo "1. Cookie value (URL decoded):\n";
echo substr($cookieDecoded, 0, 100) . "...\n\n";

// Try to decrypt using Laravel encrypter
echo "2. Attempting to decrypt with current APP_KEY...\n";
try {
    $encrypter = app('encrypter');
    $decrypted = $encrypter->decrypt($cookieDecoded, false);
    echo "✓ SUCCESS! Decrypted value:\n";
    echo $decrypted . "\n\n";
    
    // Try to get session ID from decrypted value
    echo "3. Session ID from decrypted cookie: " . $decrypted . "\n";
    
    // Check if session file exists
    $sessionPath = storage_path('framework/sessions/' . $decrypted);
    if (file_exists($sessionPath)) {
        echo "✓ Session file EXISTS\n";
        echo "   File: " . basename($sessionPath) . "\n";
        $content = file_get_contents($sessionPath);
        echo "   Content: " . $content . "\n";
    } else {
        echo "✗ Session file NOT FOUND\n";
        echo "   Expected: " . $sessionPath . "\n";
    }
    
} catch (\Exception $e) {
    echo "✗ FAILED to decrypt!\n";
    echo "   Error: " . $e->getMessage() . "\n";
    echo "   This means APP_KEY is wrong or cookie was encrypted with different key\n\n";
    
    echo "Current APP_KEY: " . substr(env('APP_KEY'), 0, 20) . "...\n";
}

echo "\n=== Solution ===\n";
echo "If decryption failed, the cookie was encrypted with a DIFFERENT APP_KEY.\n";
echo "This happens when:\n";
echo "1. APP_KEY changed but old cookies still in browser\n";
echo "2. Multiple servers with different APP_KEY\n";
echo "3. .env file has different APP_KEY than when cookie was created\n\n";
echo "Fix: Clear ALL browser cookies and try again\n";
