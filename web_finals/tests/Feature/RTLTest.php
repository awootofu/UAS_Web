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
use App\Models\RTL;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RTLTest extends TestCase
{
    use RefreshDatabase;

    protected User $gkmUser;
    protected User $gpmUser;
    protected Prodi $prodi;
    protected Renstra $renstra;
    protected Evaluasi $evaluasi;

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
        $this->gkmUser = User::factory()->create(['role' => 'gkm', 'prodi_id' => $this->prodi->id]);
        $this->gpmUser = User::factory()->create(['role' => 'gpm']);

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
            'status' => 'not_achieved',
        ]);

        $this->evaluasi = Evaluasi::create([
            'renstra_id' => $this->renstra->id,
            'prodi_id' => $this->prodi->id,
            'periode_evaluasi' => 'Semester 1 2024',
            'nilai_capaian' => '70',
            'status' => 'approved',
            'created_by' => $this->gkmUser->id,
        ]);
    }

    /** @test */
    public function gkm_can_create_rtl()
    {
        $response = $this->actingAs($this->gkmUser)->post('/rtl', [
            'evaluasi_id' => $this->evaluasi->id,
            'rtl' => 'Meningkatkan bimbingan akademik mahasiswa',
            'deadline' => now()->addMonth()->format('Y-m-d'),
            'pic_rtl' => 'Koordinator Akademik',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('rtl', [
            'evaluasi_id' => $this->evaluasi->id,
            'rtl' => 'Meningkatkan bimbingan akademik mahasiswa',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function rtl_requires_evaluasi_id()
    {
        $response = $this->actingAs($this->gkmUser)->post('/rtl', [
            'rtl' => 'Test RTL',
            'deadline' => now()->addMonth()->format('Y-m-d'),
            'pic_rtl' => 'Test PIC',
        ]);

        $response->assertSessionHasErrors('evaluasi_id');
    }

    /** @test */
    public function gkm_can_update_rtl()
    {
        $rtl = RTL::create([
            'evaluasi_id' => $this->evaluasi->id,
            'prodi_id' => $this->prodi->id,
            'rtl' => 'Original RTL',
            'deadline' => now()->addMonth(),
            'pic_rtl' => 'Original PIC',
            'status' => 'pending',
            'created_by' => $this->gkmUser->id,
        ]);

        $response = $this->actingAs($this->gkmUser)->put("/rtl/{$rtl->id}", [
            'evaluasi_id' => $this->evaluasi->id,
            'rtl' => 'Updated RTL',
            'deadline' => now()->addMonths(2)->format('Y-m-d'),
            'pic_rtl' => 'Updated PIC',
            'status' => 'in_progress',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('rtl', [
            'id' => $rtl->id,
            'rtl' => 'Updated RTL',
            'pic_rtl' => 'Updated PIC',
            'status' => 'in_progress',
        ]);
    }

    /** @test */
    public function gpm_can_verify_completed_rtl()
    {
        $rtl = RTL::create([
            'evaluasi_id' => $this->evaluasi->id,
            'prodi_id' => $this->prodi->id,
            'rtl' => 'Test RTL',
            'deadline' => now()->addMonth(),
            'pic_rtl' => 'Test PIC',
            'status' => 'completed',
            'created_by' => $this->gkmUser->id,
        ]);

        $response = $this->actingAs($this->gpmUser)->post("/rtl/{$rtl->id}/verify");

        $response->assertRedirect();
        $rtl->refresh();
        $this->assertNotNull($rtl->verified_at);
        $this->assertEquals($this->gpmUser->id, $rtl->verified_by);
    }

    /** @test */
    public function rtl_with_file_upload()
    {
        $file = UploadedFile::fake()->create('bukti.pdf', 1024);

        $response = $this->actingAs($this->gkmUser)->post('/rtl', [
            'evaluasi_id' => $this->evaluasi->id,
            'rtl' => 'RTL dengan bukti',
            'deadline' => now()->addMonth()->format('Y-m-d'),
            'pic_rtl' => 'Test PIC',
            'bukti_file' => $file,
        ]);

        $response->assertRedirect();
        
        $rtl = RTL::where('rtl', 'RTL dengan bukti')->first();
        $this->assertNotNull($rtl->bukti_rtl);
    }
}
