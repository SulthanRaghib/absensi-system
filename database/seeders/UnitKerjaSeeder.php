<?php

namespace Database\Seeders;

use App\Models\UnitKerja;
use Illuminate\Database\Seeder;

class UnitKerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unitKerjas = [
            'Kepala Badan',
            'Deputi Bidang Pengkajian Keselamatan Nuklir',
            'Deputi Bidang Perizinan dan Inspeksi',
            'Sekretaris Utama',
            'Direktorat Inspeksi Instalasi dan Bahan Nuklir',
            'Direktorat Keteknikan dan Kesiapsiagaan Nuklir',
            'Direktorat Pengaturan Pengawasan Instalasi dan Bahan Nuklir',
            'Direktorat Perizinan Instalasi dan Bahan Nuklir',
            'Biro Hukum, Kerja Sama dan Komunikasi Publik',
            'Biro Perencanaan, Informasi dan Keuangan',
            'Biro Sumber Daya Manusia dan Umum',
            'Biro Organisasi dan Tata Laksana',
            'Inspektorat',
            'Pusat Pengkajian Sistem dan Teknologi Pengawasan Fasilitas Radiasi dan Zat Radioaktif',
            'Pusat Pengkajian Sistem dan Teknologi Pengawasan Instalasi dan Bahan Nuklir',
            'Direktorat Inspeksi Fasilitas Radiasi dan Zat Radioaktif',
            'Direktorat Pengaturan Pengawasan Fasilitas Radiasi dan Zat Radioaktif',
            'Direktorat Perizinan Fasilitas Radiasi dan Zat Radioaktif',
        ];

        foreach ($unitKerjas as $unitKerja) {
            UnitKerja::updateOrCreate(['name' => $unitKerja]);
        }
    }
}
