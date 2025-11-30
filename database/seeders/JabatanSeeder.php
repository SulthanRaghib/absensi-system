<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // jabatan: Pranata Komputer, Sekretaris, Analisis Organisasi, Analisis Sarana dan Prasarana
        $jabatans = [
            'Pranata Komputer',
            'Sekretaris',
            'Analisis Organisasi',
            'Analisis Sarana dan Prasarana',
            'Analis Barang Milik Negara',
            'Pengelola Administrasi Perkantoran',
            'Analisis Pengembangan Kompetensi',
            'Pranata Hubungan Masyarakat',
            'Analis Bantuan Hukum',
            'Analis Pengawasan Penyelenggaraan Urusan Pemerintahan',
            'Analis Sistem Pembelajaran',
            'Pengelola Administrasi Peraturan',
            'Analis Pengawas Fasilitas Radiasi',
            'Analis Perizinan Fasilitas Radiasi',
            'Analis Pelaporan Keuangan',
            'Analis Perencanaan, Monitoring, dan Evaluasi',
            'Analis Penganggaran',
            'Analis Radiasi',
            'Analis Kajian Fasilitas Radiasi',
            'Statistika',
            'Evaluator',
            'Mentor'
        ];

        foreach ($jabatans as $name) {
            Jabatan::create(['name' => $name]);
        }

        $this->command->info('✓ Jabatans seeded successfully!');
    }
}
