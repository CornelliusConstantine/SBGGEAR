<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }
        
        // Log the redirect for debugging
        Log::info('Unauthenticated redirect', [
            'path' => $request->path(),
            'url' => $request->fullUrl()
        ]);
        
        // Store the current URL in the session for redirection after login
        session(['url.intended' => $request->fullUrl()]);
        
        // Add the current URL as a redirect parameter
        return route('login', ['redirect' => $request->fullUrl()]);
    }
}
