<?php

namespace Database\Seeders;

use App\Models\Prodi;
use App\Models\Fakultas;
use Illuminate\Database\Seeder;

class ProdiSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Hapus data lama terlebih dahulu
        Prodi::truncate();
        
        // Enable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get fakultas IDs
        $fskom = Fakultas::where('kode_fakultas', 'FSKOM')->first();
        $fbpar = Fakultas::where('kode_fakultas', 'FBPAR')->first();
        $fdkka = Fakultas::where('kode_fakultas', 'FDKKA')->first();

        $prodis = [
            // FSKoM - Fakultas Sains dan Komputer
            ['kode_prodi' => 'FM', 'nama_prodi' => 'Fisika Medis', 'fakultas' => 'Fakultas Sains dan Komputer', 'fakultas_id' => $fskom?->id],
            ['kode_prodi' => 'IF', 'nama_prodi' => 'Informatika', 'fakultas' => 'Fakultas Sains dan Komputer', 'fakultas_id' => $fskom?->id],
            ['kode_prodi' => 'DS', 'nama_prodi' => 'Data Science', 'fakultas' => 'Fakultas Sains dan Komputer', 'fakultas_id' => $fskom?->id],
            
            // FBPAR - Fakultas Bisnis, Pariwisata dan Akuntansi
            ['kode_prodi' => 'MB', 'nama_prodi' => 'Manajemen Bisnis', 'fakultas' => 'Fakultas Bisnis, Pariwisata dan Akuntansi', 'fakultas_id' => $fbpar?->id],
            ['kode_prodi' => 'PWS', 'nama_prodi' => 'Pariwisata', 'fakultas' => 'Fakultas Bisnis, Pariwisata dan Akuntansi', 'fakultas_id' => $fbpar?->id],
            ['kode_prodi' => 'AKT', 'nama_prodi' => 'Akuntansi', 'fakultas' => 'Fakultas Bisnis, Pariwisata dan Akuntansi', 'fakultas_id' => $fbpar?->id],
            
            // FDKKA - Fakultas Desain, K3 dan Arsitektur
            ['kode_prodi' => 'K3', 'nama_prodi' => 'Keselamatan & Kesehatan Kerja (K3)', 'fakultas' => 'Fakultas Desain, K3 dan Arsitektur', 'fakultas_id' => $fdkka?->id],
            ['kode_prodi' => 'DKV', 'nama_prodi' => 'Desain Komunikasi Visual', 'fakultas' => 'Fakultas Desain, K3 dan Arsitektur', 'fakultas_id' => $fdkka?->id],
            ['kode_prodi' => 'ARS', 'nama_prodi' => 'Arsitektur', 'fakultas' => 'Fakultas Desain, K3 dan Arsitektur', 'fakultas_id' => $fdkka?->id],
        ];

        foreach ($prodis as $prodi) {
            Prodi::create($prodi);
        }
    }
}
