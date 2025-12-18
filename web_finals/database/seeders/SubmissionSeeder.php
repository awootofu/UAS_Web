<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Submission;
use Illuminate\Support\Facades\DB;

class SubmissionSeeder extends Seeder
{
    public function run()
    {
        // Bersihkan data lama dulu (opsional)
        // DB::table('submissions')->truncate();

        // Buat 5 data dummy status 'pending' (Untuk dites GPM/Dekan)
        Submission::create([
            'title' => 'Laporan Evaluasi Kurikulum TI 2024',
            'submitted_by' => 'Kaprodi TI',
            'status' => 'pending', // Penting: Status pending agar muncul tombol verify
            'verifier_id' => null,
            'verified_at' => null,
        ]);

        Submission::create([
            'title' => 'Evaluasi Kinerja Dosen Semester Ganjil',
            'submitted_by' => 'Kaprodi SI',
            'status' => 'pending',
            'verifier_id' => null,
            'verified_at' => null,
        ]);

        // Buat 1 data yang sudah Approved (Untuk tes tampilan history)
        Submission::create([
            'title' => 'Rencana Anggaran Lab 2023',
            'submitted_by' => 'Kepala Lab',
            'status' => 'approved',
            'verifier_id' => 1, // Anggap ID 1 adalah GPM
            'verified_at' => now(),
        ]);
    }
}