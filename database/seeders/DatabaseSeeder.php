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
            ]
        );

        $this->call([
            CategorySeeder::class,
        ]);
    }
}
