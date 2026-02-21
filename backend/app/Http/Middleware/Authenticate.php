<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // For API requests, return null to get JSON 401 response instead of redirect
        if ($request->is('api/*') || $request->expectsJson()) {
            return null;
        }
        
        // For web requests, would redirect to login page (but no web routes defined yet)
        return null;
    }
}
