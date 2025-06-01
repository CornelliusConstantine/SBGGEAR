<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Check if we have any admin users
$adminUsers = User::where('role', 'admin')->get();
echo "Admin users in the system:\n";

if ($adminUsers->isEmpty()) {
    echo "No admin users found.\n";
    
    // Create an admin user for testing
    echo "Creating a test admin user...\n";
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
        'role' => 'admin'
    ]);
    
    echo "Admin user created with ID: {$admin->id}\n";
    echo "Email: admin@example.com\n";
    echo "Password: password\n";
} else {
    foreach ($adminUsers as $admin) {
        echo "ID: {$admin->id}, Name: {$admin->name}, Email: {$admin->email}\n";
    }
}

// Check if we have any regular users
$regularUsers = User::where('role', '!=', 'admin')->orWhereNull('role')->get();
echo "\nRegular users in the system:\n";

if ($regularUsers->isEmpty()) {
    echo "No regular users found.\n";
    
    // Create a regular user for testing
    echo "Creating a test regular user...\n";
    $user = User::create([
        'name' => 'Regular User',
        'email' => 'user@example.com',
        'password' => Hash::make('password'),
        'role' => 'user'
    ]);
    
    echo "Regular user created with ID: {$user->id}\n";
    echo "Email: user@example.com\n";
    echo "Password: password\n";
} else {
    foreach ($regularUsers as $user) {
        echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: {$user->role}\n";
    }
}

echo "\nDone.\n"; 