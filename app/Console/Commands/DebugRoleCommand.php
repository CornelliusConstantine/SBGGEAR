<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class DebugRoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display all users and their roles for debugging';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== User Role Debug Information ===');
        $this->info('');
        
        $users = User::all();
        
        $headers = ['ID', 'Name', 'Email', 'Role', 'Is Admin?'];
        $rows = [];
        
        foreach ($users as $user) {
            $rows[] = [
                $user->id,
                $user->name,
                $user->email,
                $user->role,
                $user->isAdmin() ? 'YES' : 'NO'
            ];
        }
        
        $this->table($headers, $rows);
        
        $this->info('');
        $this->info('=== Admin Authentication Check ===');
        $this->info('User with role "admin": ' . User::where('role', 'admin')->count());
        
        $adminUsers = User::where('role', 'admin')->get();
        
        foreach ($adminUsers as $admin) {
            $this->info("Admin user: {$admin->email} - isAdmin() returns: " . ($admin->isAdmin() ? 'true' : 'false'));
        }
        
        return 0;
    }
} 