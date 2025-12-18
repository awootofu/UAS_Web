<?php

namespace Database\Seeders;

use App\Models\Fakultas;
use Illuminate\Database\Seeder;

class FakultasSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Hapus data lama terlebih dahulu
        Fakultas::truncate();
        
        // Enable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $fakultas = [
            [
                'kode_fakultas' => 'FSKOM',
                'nama_fakultas' => 'Fakultas Sains dan Komputer',
                'deskripsi' => 'Fakultas yang menaungi program studi Fisika Medis, Informatika, dan Data Science'
            ],
            [
                'kode_fakultas' => 'FBPAR',
                'nama_fakultas' => 'Fakultas Bisnis, Pariwisata dan Akuntansi',
                'deskripsi' => 'Fakultas yang menaungi program studi Manajemen Bisnis, Pariwisata, dan Akuntansi'
            ],
            [
                'kode_fakultas' => 'FDKKA',
                'nama_fakultas' => 'Fakultas Desain, K3 dan Arsitektur',
                'deskripsi' => 'Fakultas yang menaungi program studi Keselamatan & Kesehatan Kerja (K3), Desain Komunikasi Visual, dan Arsitektur'
            ],
        ];

        foreach ($fakultas as $fak) {
            Fakultas::create($fak);
        }
    }
}
