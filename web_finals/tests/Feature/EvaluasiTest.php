<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Prodi;
use App\Models\Renstra;
use App\Models\RenstraKategori;
use App\Models\RenstraKegiatan;
use App\Models\RenstraIndikator;
use App\Models\RenstraTarget;
use App\Models\Evaluasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvaluasiTest extends TestCase
{
    use RefreshDatabase;

    protected User $kaprodiUser;
    protected User $gpmUser;
    protected User $dekanUser;
    protected Prodi $prodi;
    protected Renstra $renstra;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Create Prodi
        $this->prodi = Prodi::create([
            'kode_prodi' => 'TI',
            'nama_prodi' => 'Teknik Informatika',
            'fakultas' => 'Fakultas Teknik',
        ]);

        // Create users
        $this->kaprodiUser = User::factory()->create(['role' => 'kaprodi', 'prodi_id' => $this->prodi->id]);
        $this->gpmUser = User::factory()->create(['role' => 'gpm']);
        $this->dekanUser = User::factory()->create(['role' => 'dekan']);

        // Create supporting data
        $kategori = RenstraKategori::create([
            'kode_kategori' => 'KAT-01',
            'nama_kategori' => 'Pendidikan',
        ]);

        $kegiatan = RenstraKegiatan::create([
            'kategori_id' => $kategori->id,
            'kode_kegiatan' => 'KEG-01',
            'nama_kegiatan' => 'Peningkatan Kualitas',
        ]);

        $indikator = RenstraIndikator::create([
            'kegiatan_id' => $kegiatan->id,
            'kode_indikator' => 'IND-01',
            'nama_indikator' => 'Kelulusan Tepat Waktu',
            'satuan' => 'Persen',
        ]);

        $target = RenstraTarget::create([
            'indikator_id' => $indikator->id,
            'tahun' => 2024,
            'target_value' => '75',
        ]);

        $this->renstra = Renstra::create([
            'prodi_id' => $this->prodi->id,
            'kategori_id' => $kategori->id,
            'kegiatan_id' => $kegiatan->id,
            'indikator_id' => $indikator->id,
            'target_id' => $target->id,
            'kode_renstra' => 'REN-2024-001',
            'tahun' => 2024,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function kaprodi_can_submit_evaluasi()
    {
        $response = $this->actingAs($this->kaprodiUser)->post('/evaluasi', [
            'renstra_id' => $this->renstra->id,
            'periode_evaluasi' => 'Semester 1 2024',
            'nilai_capaian' => '80',
            'keterangan_capaian' => 'Target tercapai dengan baik',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('evaluasi', [
            'renstra_id' => $this->renstra->id,
            'nilai_capaian' => '80',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function evaluasi_below_target_requires_analysis()
    {
        $response = $this->actingAs($this->kaprodiUser)->post('/evaluasi', [
            'renstra_id' => $this->renstra->id,
            'periode_evaluasi' => 'Semester 1 2024',
            'nilai_capaian' => '60', // Below target of 75
            'keterangan_capaian' => 'Target tidak tercapai',
            // Missing analisis_gap and faktor_penghambat
        ]);

        // Should require additional analysis fields when below target
        $response->assertSessionHasErrors(['analisis_gap']);
    }

    /** @test */
    public function gpm_can_approve_evaluasi()
    {
        $evaluasi = Evaluasi::create([
            'renstra_id' => $this->renstra->id,
            'prodi_id' => $this->prodi->id,
            'periode_evaluasi' => 'Semester 1 2024',
            'nilai_capaian' => '80',
            'status' => 'pending',
            'created_by' => $this->kaprodiUser->id,
        ]);

        $response = $this->actingAs($this->gpmUser)->post("/evaluasi/{$evaluasi->id}/approve", [
            'catatan_gpm' => 'Evaluasi sudah sesuai',
        ]);

        $response->assertRedirect();
        $evaluasi->refresh();
        $this->assertEquals('approved', $evaluasi->status);
        $this->assertNotNull($evaluasi->approved_at);
    }

    /** @test */
    public function gpm_can_reject_evaluasi()
    {
        $evaluasi = Evaluasi::create([
            'renstra_id' => $this->renstra->id,
            'prodi_id' => $this->prodi->id,
            'periode_evaluasi' => 'Semester 1 2024',
            'nilai_capaian' => '80',
            'status' => 'pending',
            'created_by' => $this->kaprodiUser->id,
        ]);

        $response = $this->actingAs($this->gpmUser)->post("/evaluasi/{$evaluasi->id}/reject", [
            'catatan_gpm' => 'Data tidak lengkap, mohon dilengkapi',
        ]);

        $response->assertRedirect();
        $evaluasi->refresh();
        $this->assertEquals('rejected', $evaluasi->status);
    }

    /** @test */
    public function all_users_can_view_evaluasi_list()
    {
        $users = [$this->kaprodiUser, $this->gpmUser, $this->dekanUser];

        foreach ($users as $user) {
            $response = $this->actingAs($user)->get('/evaluasi');
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function evaluasi_with_evidence_files()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('bukti.pdf', 1024);

        $response = $this->actingAs($this->kaprodiUser)->post('/evaluasi', [
            'renstra_id' => $this->renstra->id,
            'periode_evaluasi' => 'Semester 1 2024',
            'nilai_capaian' => '80',
            'keterangan_capaian' => 'Target tercapai',
            'bukti_files' => [$file],
        ]);

        $response->assertRedirect();
        
        $evaluasi = Evaluasi::where('renstra_id', $this->renstra->id)->first();
        $this->assertTrue($evaluasi->bukti()->count() > 0);
    }
}
