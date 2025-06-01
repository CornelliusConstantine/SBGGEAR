<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

// Show all admin users
$adminUsers = User::where('role', 'admin')->get();
echo "Admin users in the system:\n";

foreach ($adminUsers as $admin) {
    echo "ID: {$admin->id}, Name: {$admin->name}, Email: {$admin->email}\n";
}

// Test authentication for the first admin user
if ($adminUsers->isNotEmpty()) {
    $admin = $adminUsers->first();
    
    echo "\nTesting authentication for admin: {$admin->email}\n";
    
    // Create a token for the admin
    $token = $admin->createToken('test-token')->plainTextToken;
    
    echo "Generated token: {$token}\n";
    
    // Parse the token to verify it contains the role
    $tokenParts = explode('|', $token);
    $tokenId = $tokenParts[0];
    
    $accessToken = PersonalAccessToken::find($tokenId);
    if ($accessToken) {
        echo "Token found in database.\n";
        echo "Token belongs to user ID: {$accessToken->tokenable_id}\n";
        
        // Get the user from the token
        $user = $accessToken->tokenable;
        echo "User role: {$user->role}\n";
        echo "Is admin: " . ($user->isAdmin() ? 'Yes' : 'No') . "\n";
    } else {
        echo "Token not found in database.\n";
    }
    
    echo "\nInstructions for testing in frontend:\n";
    echo "1. Open browser console\n";
    echo "2. Run: localStorage.setItem('token', '{$token}');\n";
    echo "3. Run: localStorage.setItem('userRole', '{$admin->role}');\n";
    echo "4. Refresh the page\n";
}

echo "\nDone.\n"; 