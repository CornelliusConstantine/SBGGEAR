<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@sbggear.com'],
            [
                'name' => 'Admin',
                'email' => 'admin@sbggear.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '1234567890',
                'address' => '123 Admin Street',
                'city' => 'Admin City',
                'postal_code' => '12345',
            ]
        );

        // Create regular user
        User::updateOrCreate(
            ['email' => 'user@sbggear.com'],
            [
                'name' => 'User',
                'email' => 'user@sbggear.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '0987654321',
                'address' => '456 User Avenue',
                'city' => 'User City',
                'postal_code' => '54321',
            ]
        );

        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
