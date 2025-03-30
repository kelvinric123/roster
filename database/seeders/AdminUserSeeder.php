<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'drtai@qmed.asia',
            'password' => Hash::make('qmed.asia'),
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);
        
        $this->command->info('Created admin user account (Email: drtai@qmed.asia, Password: qmed.asia)');
    }
}
