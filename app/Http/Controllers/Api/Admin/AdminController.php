<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('admin');
    }
    
    /**
     * Flush all admin accounts except the current one.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function flushAdminAccounts(Request $request)
    {
        try {
            // Get current user ID
            $currentUserId = auth()->id();
            
            // Delete all admin accounts except the current one
            $deletedCount = User::where('role', 'admin')
                ->where('id', '!=', $currentUserId)
                ->delete();
                
            return response()->json([
                'success' => true,
                'message' => 'Admin accounts flushed successfully',
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error flushing admin accounts: ' . $e->getMessage()
            ], 500);
        }
    }
} 