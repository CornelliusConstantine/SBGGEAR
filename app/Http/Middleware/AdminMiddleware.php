<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
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
        // Log the request information for debugging
        Log::info('Admin middleware check', [
            'path' => $request->path(),
            'url' => $request->fullUrl(),
            'authenticated' => $request->user() ? true : false,
            'user_id' => $request->user() ? $request->user()->id : null,
            'user_email' => $request->user() ? $request->user()->email : null,
            'user_role' => $request->user() ? $request->user()->role : null,
            'is_admin' => $request->user() ? $request->user()->isAdmin() : false,
        ]);
        
        // Check if user is logged in
        if (!$request->user()) {
            Log::info('User not authenticated, redirecting to login', [
                'redirect' => $request->fullUrl()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Please log in.'], 401);
            }
            
            // Redirect to login with the intended URL
            return redirect()->route('login', ['redirect' => $request->fullUrl()]);
        }
        
        // Check if user is admin
        if (!$request->user()->isAdmin()) {
            Log::info('User not admin, denying access', [
                'user_id' => $request->user()->id,
                'user_email' => $request->user()->email,
                'user_role' => $request->user()->role
            ]);
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
            }
            
            session()->flash('error', 'You do not have admin privileges. Your current role is: ' . $request->user()->role);
            return redirect('/')->with('error', 'You do not have permission to access the admin area.');
        }
        
        Log::info('Admin access granted', [
            'user_id' => $request->user()->id,
            'user_email' => $request->user()->email,
            'user_role' => $request->user()->role
        ]);
        
        return $next($request);
    }
} 