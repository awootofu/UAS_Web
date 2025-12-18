<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use Illuminate\Database\Seeder;

class JabatanSeeder extends Seeder
{
    public function run(): void
    {
        $jabatans = [
            ['kode_jabatan' => 'DKN', 'nama_jabatan' => 'Dekan', 'deskripsi' => 'Pimpinan Fakultas'],
            ['kode_jabatan' => 'WD1', 'nama_jabatan' => 'Wakil Dekan I', 'deskripsi' => 'Wakil Dekan Bidang Akademik'],
            ['kode_jabatan' => 'WD2', 'nama_jabatan' => 'Wakil Dekan II', 'deskripsi' => 'Wakil Dekan Bidang Umum dan Keuangan'],
            ['kode_jabatan' => 'WD3', 'nama_jabatan' => 'Wakil Dekan III', 'deskripsi' => 'Wakil Dekan Bidang Kemahasiswaan'],
            ['kode_jabatan' => 'KPS', 'nama_jabatan' => 'Ketua Program Studi', 'deskripsi' => 'Pimpinan Program Studi'],
            ['kode_jabatan' => 'SPS', 'nama_jabatan' => 'Sekretaris Program Studi', 'deskripsi' => 'Sekretaris Program Studi'],
            ['kode_jabatan' => 'KGPM', 'nama_jabatan' => 'Ketua GPM', 'deskripsi' => 'Ketua Gugus Penjaminan Mutu'],
            ['kode_jabatan' => 'AGPM', 'nama_jabatan' => 'Anggota GPM', 'deskripsi' => 'Anggota Gugus Penjaminan Mutu'],
            ['kode_jabatan' => 'KGKM', 'nama_jabatan' => 'Ketua GKM', 'deskripsi' => 'Ketua Gugus Kendali Mutu'],
            ['kode_jabatan' => 'AGKM', 'nama_jabatan' => 'Anggota GKM', 'deskripsi' => 'Anggota Gugus Kendali Mutu'],
            ['kode_jabatan' => 'BPAP', 'nama_jabatan' => 'Staff BPAP', 'deskripsi' => 'Staff Biro Perencanaan dan Administrasi'],
            ['kode_jabatan' => 'DSN', 'nama_jabatan' => 'Dosen', 'deskripsi' => 'Tenaga Pengajar'],
        ];

        foreach ($jabatans as $jabatan) {
            Jabatan::create($jabatan);
        }
    }
}
