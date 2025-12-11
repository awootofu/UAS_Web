<?php

namespace Database\Seeders;

use App\Models\Prodi;
use Illuminate\Database\Seeder;

class ProdiSeeder extends Seeder
{
    public function run(): void
    {
        $prodis = [
            ['kode_prodi' => 'TI', 'nama_prodi' => 'Teknik Informatika', 'fakultas' => 'Fakultas Teknik'],
            ['kode_prodi' => 'SI', 'nama_prodi' => 'Sistem Informasi', 'fakultas' => 'Fakultas Teknik'],
            ['kode_prodi' => 'TK', 'nama_prodi' => 'Teknik Komputer', 'fakultas' => 'Fakultas Teknik'],
            ['kode_prodi' => 'MI', 'nama_prodi' => 'Manajemen Informatika', 'fakultas' => 'Fakultas Teknik'],
            ['kode_prodi' => 'DKV', 'nama_prodi' => 'Desain Komunikasi Visual', 'fakultas' => 'Fakultas Desain'],
        ];

        foreach ($prodis as $prodi) {
            Prodi::create($prodi);
        }
    }
}
