<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@absensi.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'jabatan' => 'Administrator',
        ]);

        User::create([
            'name' => 'Dimas',
            'email' => 'dimas@maganghub.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'jabatan' => 'Staff IT',
        ]);
        // Create Sample Users

        User::create([
            'name' => 'Tahta',
            'email' => 'tahta@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'jabatan' => 'SDM Pranata Komputer',
        ]);

        User::create([
            'name' => 'Raghib',
            'email' => 'raghib@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'jabatan' => 'SDM Pranata Komputer',
        ]);

        $this->command->info('✓ Users seeded successfully!');
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('Admin: admin@absensi.com / password');
        $this->command->info('User: tahta@gmail.com / password');
        $this->command->info('User: raghib@gmail.com / password');
    }
}
