<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Jabatan;
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
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@absensi.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'jabatan_id' => $mentorSDM->id,
        ]);

        User::create([
            'name' => 'Dimas',
            'email' => 'dimas@mentor.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'jabatan_id' => $mentorSDM->id,
        ]);
        User::create([
            'name' => 'Supeni',
            'email' => 'supeni@mentor.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'jabatan_id' => $mentorSDM->id,
        ]);
        // Create Sample Users

        User::create([
            'name' => 'Tahta',
            'email' => 'tahta@gmail.com',
            'password' => Hash::make('123456'),
            'role' => 'user',
            'jabatan_id' => $jabatanPranataKomputer->id,
        ]);

        User::create([
            'name' => 'Raghib',
            'email' => 'raghib@gmail.com',
            'password' => Hash::make('123456'),
            'role' => 'user',
            'jabatan_id' => $jabatanPranataKomputer->id,
        ]);

        $this->command->info('✓ Users seeded successfully!');
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('Admin: admin@absensi.com / password');
        $this->command->info('User: tahta@gmail.com / 123456');
        $this->command->info('User: raghib@gmail.com / 123456');
    }
}
