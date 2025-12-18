<?php

namespace Database\Seeders;

use App\Models\Prodi;
use App\Models\User;
use App\Models\RenstraKategori;
use App\Models\RenstraKegiatan;
use App\Models\RenstraIndikator;
use App\Models\RenstraTarget;
use App\Models\Renstra;
use Illuminate\Database\Seeder;

class RenstraSeeder extends Seeder
{
    public function run(): void
    {
        $prodi = Prodi::first();
        $adminUser = User::where('role', 'admin')->first();

        // Create Renstra Kategori
        $kategori1 = RenstraKategori::create([
            'kode_kategori' => 'KAT-01',
            'nama_kategori' => 'Pendidikan',
            'deskripsi' => 'Kategori terkait kegiatan pendidikan dan pengajaran',
        ]);

        $kategori2 = RenstraKategori::create([
            'kode_kategori' => 'KAT-02',
            'nama_kategori' => 'Penelitian',
            'deskripsi' => 'Kategori terkait kegiatan penelitian dan publikasi',
        ]);

        $kategori3 = RenstraKategori::create([
            'kode_kategori' => 'KAT-03',
            'nama_kategori' => 'Pengabdian Masyarakat',
            'deskripsi' => 'Kategori terkait kegiatan pengabdian kepada masyarakat',
        ]);

        // Create Renstra Kegiatan
        $kegiatan1 = RenstraKegiatan::create([
            'kategori_id' => $kategori1->id,
            'kode_kegiatan' => 'KEG-01',
            'nama_kegiatan' => 'Peningkatan Kualitas Pembelajaran',
            'deskripsi' => 'Kegiatan untuk meningkatkan kualitas proses belajar mengajar',
        ]);

        $kegiatan2 = RenstraKegiatan::create([
            'kategori_id' => $kategori2->id,
            'kode_kegiatan' => 'KEG-02',
            'nama_kegiatan' => 'Peningkatan Publikasi Ilmiah',
            'deskripsi' => 'Kegiatan untuk meningkatkan jumlah dan kualitas publikasi ilmiah',
        ]);

        $kegiatan3 = RenstraKegiatan::create([
            'kategori_id' => $kategori3->id,
            'kode_kegiatan' => 'KEG-03',
            'nama_kegiatan' => 'Program Pemberdayaan Masyarakat',
            'deskripsi' => 'Kegiatan pemberdayaan masyarakat berbasis teknologi',
        ]);

        // Create Renstra Indikator
        $indikator1 = RenstraIndikator::create([
            'kegiatan_id' => $kegiatan1->id,
            'kode_indikator' => 'IND-01',
            'nama_indikator' => 'Persentase Kelulusan Tepat Waktu',
            'satuan' => 'Persen',
            'deskripsi' => 'Persentase mahasiswa yang lulus dalam waktu maksimal 4 tahun',
        ]);

        $indikator2 = RenstraIndikator::create([
            'kegiatan_id' => $kegiatan1->id,
            'kode_indikator' => 'IND-02',
            'nama_indikator' => 'IPK Rata-rata Lulusan',
            'satuan' => 'Nilai',
            'deskripsi' => 'Nilai IPK rata-rata mahasiswa yang lulus',
        ]);

        $indikator3 = RenstraIndikator::create([
            'kegiatan_id' => $kegiatan2->id,
            'kode_indikator' => 'IND-03',
            'nama_indikator' => 'Jumlah Publikasi Terindeks',
            'satuan' => 'Jumlah',
            'deskripsi' => 'Jumlah publikasi yang terindeks Scopus/WoS',
        ]);

        $indikator4 = RenstraIndikator::create([
            'kegiatan_id' => $kegiatan3->id,
            'kode_indikator' => 'IND-04',
            'nama_indikator' => 'Jumlah Kegiatan PKM',
            'satuan' => 'Jumlah',
            'deskripsi' => 'Jumlah kegiatan pengabdian kepada masyarakat per tahun',
        ]);

        // Create Renstra Target
        $target1 = RenstraTarget::create([
            'indikator_id' => $indikator1->id,
            'tahun' => 2024,
            'target_value' => '75',
            'keterangan' => 'Target kelulusan tepat waktu 75%',
        ]);

        $target2 = RenstraTarget::create([
            'indikator_id' => $indikator2->id,
            'tahun' => 2024,
            'target_value' => '3.25',
            'keterangan' => 'Target IPK rata-rata lulusan minimal 3.25',
        ]);

        $target3 = RenstraTarget::create([
            'indikator_id' => $indikator3->id,
            'tahun' => 2024,
            'target_value' => '10',
            'keterangan' => 'Target 10 publikasi terindeks per tahun',
        ]);

        $target4 = RenstraTarget::create([
            'indikator_id' => $indikator4->id,
            'tahun' => 2024,
            'target_value' => '5',
            'keterangan' => 'Target 5 kegiatan PKM per tahun',
        ]);

        // Create Renstra records (matching migration schema)
        Renstra::create([
            'kode_renstra' => 'REN-2024-001',
            'indikator' => 'Persentase Kelulusan Tepat Waktu minimal 75%',
            'user_id' => $adminUser->id,
            'prodi_id' => $prodi?->id,
            'kategori_id' => $kategori1->id,
            'kegiatan_id' => $kegiatan1->id,
            'indikator_id' => $indikator1->id,
            'target_id' => $target1->id,
            'tahun_awal' => 2024,
            'tahun_akhir' => 2028,
            'status' => 'active',
            'keterangan' => 'Renstra bidang pendidikan untuk kelulusan tepat waktu',
        ]);

        Renstra::create([
            'kode_renstra' => 'REN-2024-002',
            'indikator' => 'IPK rata-rata lulusan minimal 3.25',
            'user_id' => $adminUser->id,
            'prodi_id' => $prodi?->id,
            'kategori_id' => $kategori1->id,
            'kegiatan_id' => $kegiatan1->id,
            'indikator_id' => $indikator2->id,
            'target_id' => $target2->id,
            'tahun_awal' => 2024,
            'tahun_akhir' => 2028,
            'status' => 'active',
            'keterangan' => 'Renstra bidang pendidikan untuk IPK lulusan',
        ]);

        Renstra::create([
            'kode_renstra' => 'REN-2024-003',
            'indikator' => 'Minimal 10 publikasi terindeks Scopus/WoS per tahun',
            'user_id' => $adminUser->id,
            'prodi_id' => $prodi?->id,
            'kategori_id' => $kategori2->id,
            'kegiatan_id' => $kegiatan2->id,
            'indikator_id' => $indikator3->id,
            'target_id' => $target3->id,
            'tahun_awal' => 2024,
            'tahun_akhir' => 2028,
            'status' => 'active',
            'keterangan' => 'Renstra bidang penelitian untuk publikasi ilmiah',
        ]);

        Renstra::create([
            'kode_renstra' => 'REN-2024-004',
            'indikator' => 'Minimal 5 kegiatan PKM per tahun',
            'user_id' => $adminUser->id,
            'prodi_id' => $prodi?->id,
            'kategori_id' => $kategori3->id,
            'kegiatan_id' => $kegiatan3->id,
            'indikator_id' => $indikator4->id,
            'target_id' => $target4->id,
            'tahun_awal' => 2024,
            'tahun_akhir' => 2028,
            'status' => 'active',
            'keterangan' => 'Renstra bidang pengabdian masyarakat',
        ]);
    }
}
