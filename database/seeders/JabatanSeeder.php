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
            'Analisis Barang Milik Negara',
            'Pengelola Administrasi Perkantoran',
            'Analisis Pengembangan Kompetensi',
            'Pranata Hubungan Masyarakat',
            'Analisis Bantuan Hukum',
            'Analisis Pengawasan Penyelenggaraan Urusan Pemerintah',
            'Analis Sistem Pembelajaran',
            'Pengelola Administrasi Peraturan',
            'Analis Pengawas Fasilitas Radiasi',
            'Analis Perizinan Fasilitas Radiasi',
            'Analis Pelaporan Keuangan',
            'Analis Perencanaan, Monitoring, dan Evaluasi',
            'Analis Penganggaran',
            'Analis Radiasi',
            'Analis Kajian Fasilitas Radiasi',
            'Statiska',
            'Evaluator'
        ];

        foreach ($jabatans as $name) {
            Jabatan::create(['name' => $name]);
        }

        $this->command->info('✓ Jabatans seeded successfully!');
    }
}
