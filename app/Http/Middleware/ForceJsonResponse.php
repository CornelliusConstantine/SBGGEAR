<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Force Accept header to be application/json
        $request->headers->set('Accept', 'application/json');
        
        // Get the response
        $response = $next($request);
        
        // If we're returning HTML accidentally for an API call, force JSON
        if ($response->headers->get('Content-Type') === 'text/html; charset=UTF-8') {
            // Log this as it shouldn't happen
            \Illuminate\Support\Facades\Log::warning('API endpoint returning HTML instead of JSON', [
                'path' => $request->path(),
                'method' => $request->method(),
            ]);
            
            // Create a new JSON response
            return response()->json([
                'error' => 'The API endpoint returned HTML instead of JSON',
                'data' => [],
                'debug_info' => [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'headers' => $request->headers->all(),
                ]
            ], 500);
        }
        
        return $response;
    }
} 