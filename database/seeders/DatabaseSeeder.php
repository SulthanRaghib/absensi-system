<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Jabatan;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // run JabatanSeeder
        $this->call(JabatanSeeder::class);

        // Create Jabatans
        $mentorSDM = Jabatan::where('name', 'Mentor')->first();
        $jabatanPranataKomputer = Jabatan::where('name', 'Pranata Komputer')->first();

        // Create Admin User
        User::firstOrCreate(
            ['email' => 'admin@absensi.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'jabatan_id' => $mentorSDM->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'dimas@mentor.com'],
            [
                'name' => 'Dimas',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'jabatan_id' => $mentorSDM->id,
            ]
        );
        User::firstOrCreate(
            ['email' => 'supeni@mentor.com'],
            [
                'name' => 'Supeni',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'jabatan_id' => $mentorSDM->id,
            ]
        );

        // Create Sample Users
        User::firstOrCreate(
            ['email' => 'tahta@gmail.com'],
            [
                'name' => 'Tahta',
                'password' => Hash::make('123456'),
                'role' => 'user',
                'jabatan_id' => $jabatanPranataKomputer->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'raghib@gmail.com'],
            [
                'name' => 'Raghib',
                'password' => Hash::make('123456'),
                'role' => 'user',
                'jabatan_id' => $jabatanPranataKomputer->id,
            ]
        );

        $this->command->info('✓ Users seeded successfully!');
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('Admin: admin@absensi.com / password');
        $this->command->info('User: tahta@gmail.com / 123456');
        $this->command->info('User: raghib@gmail.com / 123456');
    }
}
