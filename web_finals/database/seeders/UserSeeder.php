<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Prodi;
use App\Models\Jabatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Get first prodi for role assignments
        $prodi = Prodi::first();
        
        // Get jabatan IDs
        $jabatanDekan = Jabatan::where('nama_jabatan', 'Dekan')->first();
        $jabatanKaprodi = Jabatan::where('nama_jabatan', 'Ketua Program Studi')->first();
        $jabatanGPM = Jabatan::where('nama_jabatan', 'Ketua GPM')->first();
        $jabatanGKM = Jabatan::where('nama_jabatan', 'Ketua GKM')->first();
        $jabatanBPAP = Jabatan::where('nama_jabatan', 'Staff BPAP')->first();

        // Create users for each role
        $users = [
            [
                'name' => 'Administrator',
                'email' => 'admin@renstra.test',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'prodi_id' => null,
                'jabatan_id' => null,
            ],
            [
                'name' => 'Dekan Fakultas',
                'email' => 'dekan@renstra.test',
                'password' => Hash::make('password'),
                'role' => 'dekan',
                'prodi_id' => null,
                'jabatan_id' => $jabatanDekan?->id,
            ],
            [
                'name' => 'Ketua GPM',
                'email' => 'gpm@renstra.test',
                'password' => Hash::make('password'),
                'role' => 'gpm',
                'prodi_id' => null,
                'jabatan_id' => $jabatanGPM?->id,
            ],
            [
                'name' => 'Ketua GKM Teknik Informatika',
                'email' => 'gkm@renstra.test',
                'password' => Hash::make('password'),
                'role' => 'gkm',
                'prodi_id' => $prodi?->id,
                'jabatan_id' => $jabatanGKM?->id,
            ],
            [
                'name' => 'Kaprodi Teknik Informatika',
                'email' => 'kaprodi@renstra.test',
                'password' => Hash::make('password'),
                'role' => 'kaprodi',
                'prodi_id' => $prodi?->id,
                'jabatan_id' => $jabatanKaprodi?->id,
            ],
            [
                'name' => 'Staff BPAP',
                'email' => 'bpap@renstra.test',
                'password' => Hash::make('password'),
                'role' => 'bpap',
                'prodi_id' => null,
                'jabatan_id' => $jabatanBPAP?->id,
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}
