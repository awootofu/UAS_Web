<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Prodi;
use App\Models\Renstra;
use App\Models\Evaluasi;
use App\Models\RTL;
use App\Models\RenstraKategori;
use App\Models\RenstraKegiatan;
use App\Models\RenstraIndikator;
use App\Models\RenstraTarget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for Laravel Policies
 * 
 * Tests the authorization policies for Evaluasi, RTL, and Renstra models.
 * Verifies that role-based permissions are correctly enforced.
 * 
 * @see \App\Policies\EvaluasiPolicy
 * @see \App\Policies\RTLPolicy
 * @see \App\Policies\RenstraPolicy
 */
class PolicyTest extends TestCase
{
    use RefreshDatabase;

    protected Prodi $prodi;
    protected Prodi $otherProdi;
    protected RenstraKategori $kategori;
    protected RenstraKegiatan $kegiatan;
    protected RenstraIndikator $indikator;
    protected RenstraTarget $target;
    protected User $adminUser;
    protected Renstra $renstra;

    protected function setUp(): void
    {
        parent::setUp();

        // Create prodis
        $this->prodi = Prodi::create([
            'kode_prodi' => 'TI',
            'nama_prodi' => 'Teknik Informatika',
            'fakultas' => 'Fakultas Teknik',
        ]);

        $this->otherProdi = Prodi::create([
            'kode_prodi' => 'SI',
            'nama_prodi' => 'Sistem Informasi',
            'fakultas' => 'Fakultas Teknik',
        ]);

        // Create admin user
        $this->adminUser = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        // Create supporting data for Renstra
        $this->kategori = RenstraKategori::create([
            'kode_kategori' => 'KAT-01',
            'nama_kategori' => 'Pendidikan',
        ]);

        $this->kegiatan = RenstraKegiatan::create([
            'kategori_id' => $this->kategori->id,
            'kode_kegiatan' => 'KEG-01',
            'nama_kegiatan' => 'Peningkatan Kualitas',
        ]);

        $this->indikator = RenstraIndikator::create([
            'kegiatan_id' => $this->kegiatan->id,
            'kode_indikator' => 'IND-01',
            'nama_indikator' => 'Kelulusan Tepat Waktu',
            'satuan' => 'Persen',
        ]);

        $this->target = RenstraTarget::create([
            'indikator_id' => $this->indikator->id,
            'tahun' => 2024,
            'target_value' => '75',
        ]);

        // Create a renstra
        $this->renstra = Renstra::create([
            'kode_renstra' => 'REN-TEST-001',
            'indikator' => 'Test Indikator',
            'user_id' => $this->adminUser->id,
            'prodi_id' => $this->prodi->id,
            'kategori_id' => $this->kategori->id,
            'kegiatan_id' => $this->kegiatan->id,
            'indikator_id' => $this->indikator->id,
            'target_id' => $this->target->id,
            'tahun_awal' => 2024,
            'tahun_akhir' => 2028,
            'status' => 'active',
        ]);
    }

    /**
     * Helper to create a user with a specific role.
     */
    protected function createUserWithRole(string $role, ?int $prodiId = null): User
    {
        return User::factory()->create([
            'role' => $role,
            'prodi_id' => $prodiId,
            'is_active' => true,
        ]);
    }

    /**
     * Helper to create an Evaluasi with required fields.
     */
    protected function createEvaluasi(User $creator, ?Prodi $prodi = null, string $status = 'draft'): Evaluasi
    {
        return Evaluasi::create([
            'renstra_id' => $this->renstra->id,
            'prodi_id' => $prodi?->id ?? $this->prodi->id,
            'target_id' => $this->target->id,
            'semester' => 'ganjil',
            'tahun_evaluasi' => 2024,
            'realisasi' => 80,
            'ketercapaian' => 75,
            'status' => $status,
            'created_by' => $creator->id,
        ]);
    }

    /**
     * Helper to create an RTL with required fields.
     */
    protected function createRTL(Evaluasi $evaluasi, User $creator): RTL
    {
        return RTL::create([
            'evaluasi_id' => $evaluasi->id,
            'prodi_id' => $evaluasi->prodi_id,
            'users_id' => $creator->id,
            'rtl' => 'Tindak lanjut test',
            'deadline' => now()->addMonth(),
            'pic_rtl' => 'Test PIC',
            'status' => 'pending',
        ]);
    }

    // ========================================
    // Renstra Policy Tests
    // ========================================

    /** @test */
    public function admin_has_full_access_to_renstra(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);

        $this->assertTrue($admin->can('viewAny', Renstra::class));
        $this->assertTrue($admin->can('view', $this->renstra));
        $this->assertTrue($admin->can('create', Renstra::class));
        $this->assertTrue($admin->can('update', $this->renstra));
        $this->assertTrue($admin->can('delete', $this->renstra));
    }

    /** @test */
    public function bpap_can_create_and_manage_renstra(): void
    {
        $bpap = $this->createUserWithRole(User::ROLE_BPAP);

        $this->assertTrue($bpap->can('create', Renstra::class));
        $this->assertTrue($bpap->can('update', $this->renstra));
        $this->assertTrue($bpap->can('delete', $this->renstra));
    }

    /** @test */
    public function kaprodi_cannot_create_renstra(): void
    {
        $kaprodi = $this->createUserWithRole(User::ROLE_KAPRODI, $this->prodi->id);

        $this->assertFalse($kaprodi->can('create', Renstra::class));
        $this->assertFalse($kaprodi->can('update', $this->renstra));
        $this->assertFalse($kaprodi->can('delete', $this->renstra));
    }

    /** @test */
    public function all_users_can_view_renstra(): void
    {
        $roles = [
            User::ROLE_ADMIN,
            User::ROLE_DEKAN,
            User::ROLE_GPM,
            User::ROLE_GKM,
            User::ROLE_KAPRODI,
            User::ROLE_BPAP,
        ];

        foreach ($roles as $role) {
            $user = $this->createUserWithRole($role, $this->prodi->id);
            $this->assertTrue($user->can('view', $this->renstra), "Role {$role} should view renstra");
            $this->assertTrue($user->can('viewAny', Renstra::class), "Role {$role} should viewAny renstra");
        }
    }

    // ========================================
    // Evaluasi Policy Tests
    // ========================================

    /** @test */
    public function admin_has_full_access_to_evaluasi(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);

        $this->assertTrue($admin->can('create', Evaluasi::class));
    }

    /** @test */
    public function kaprodi_can_create_evaluasi(): void
    {
        $kaprodi = $this->createUserWithRole(User::ROLE_KAPRODI, $this->prodi->id);

        $this->assertTrue($kaprodi->can('create', Evaluasi::class));
    }

    /** @test */
    public function gkm_cannot_create_evaluasi(): void
    {
        $gkm = $this->createUserWithRole(User::ROLE_GKM, $this->prodi->id);

        $this->assertFalse($gkm->can('create', Evaluasi::class));
    }

    /** @test */
    public function bpap_cannot_create_evaluasi(): void
    {
        $bpap = $this->createUserWithRole(User::ROLE_BPAP);

        $this->assertFalse($bpap->can('create', Evaluasi::class));
    }

    /** @test */
    public function kaprodi_can_view_their_prodi_evaluasi(): void
    {
        $kaprodi = $this->createUserWithRole(User::ROLE_KAPRODI, $this->prodi->id);
        
        $evaluasi = $this->createEvaluasi($kaprodi, $this->prodi, 'draft');

        $this->assertTrue($kaprodi->can('view', $evaluasi));
    }

    /** @test */
    public function kaprodi_cannot_view_other_prodi_evaluasi(): void
    {
        $kaprodi = $this->createUserWithRole(User::ROLE_KAPRODI, $this->prodi->id);
        $otherKaprodi = $this->createUserWithRole(User::ROLE_KAPRODI, $this->otherProdi->id);
        
        $evaluasi = $this->createEvaluasi($otherKaprodi, $this->otherProdi, 'draft');

        $this->assertFalse($kaprodi->can('view', $evaluasi));
    }

    /** @test */
    public function gpm_can_view_all_evaluasi(): void
    {
        $gpm = $this->createUserWithRole(User::ROLE_GPM);
        $kaprodi = $this->createUserWithRole(User::ROLE_KAPRODI, $this->prodi->id);
        
        $evaluasi = $this->createEvaluasi($kaprodi, $this->prodi, 'draft');

        $this->assertTrue($gpm->can('view', $evaluasi));
    }

    // ========================================
    // RTL Policy Tests
    // ========================================

    /** @test */
    public function admin_has_full_access_to_rtl(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);

        $this->assertTrue($admin->can('create', RTL::class));
    }

    /** @test */
    public function gkm_can_create_rtl(): void
    {
        $gkm = $this->createUserWithRole(User::ROLE_GKM, $this->prodi->id);

        $this->assertTrue($gkm->can('create', RTL::class));
    }

    /** @test */
    public function kaprodi_cannot_create_rtl(): void
    {
        $kaprodi = $this->createUserWithRole(User::ROLE_KAPRODI, $this->prodi->id);

        $this->assertFalse($kaprodi->can('create', RTL::class));
    }

    /** @test */
    public function bpap_cannot_create_rtl(): void
    {
        $bpap = $this->createUserWithRole(User::ROLE_BPAP);

        $this->assertFalse($bpap->can('create', RTL::class));
    }

    /** @test */
    public function gkm_can_view_their_prodi_rtl(): void
    {
        $gkm = $this->createUserWithRole(User::ROLE_GKM, $this->prodi->id);
        $kaprodi = $this->createUserWithRole(User::ROLE_KAPRODI, $this->prodi->id);
        
        $evaluasi = $this->createEvaluasi($kaprodi, $this->prodi, 'approved');
        $rtl = $this->createRTL($evaluasi, $gkm);

        $this->assertTrue($gkm->can('view', $rtl));
    }

    /** @test */
    public function gkm_cannot_view_other_prodi_rtl(): void
    {
        $gkm = $this->createUserWithRole(User::ROLE_GKM, $this->prodi->id);
        $otherGkm = $this->createUserWithRole(User::ROLE_GKM, $this->otherProdi->id);
        $otherKaprodi = $this->createUserWithRole(User::ROLE_KAPRODI, $this->otherProdi->id);
        
        $evaluasi = $this->createEvaluasi($otherKaprodi, $this->otherProdi, 'approved');
        $rtl = $this->createRTL($evaluasi, $otherGkm);

        $this->assertFalse($gkm->can('view', $rtl));
    }

    /** @test */
    public function gpm_can_verify_rtl(): void
    {
        $gpm = $this->createUserWithRole(User::ROLE_GPM);
        $gkm = $this->createUserWithRole(User::ROLE_GKM, $this->prodi->id);
        $kaprodi = $this->createUserWithRole(User::ROLE_KAPRODI, $this->prodi->id);
        
        $evaluasi = $this->createEvaluasi($kaprodi, $this->prodi, 'approved');
        
        $rtl = RTL::create([
            'evaluasi_id' => $evaluasi->id,
            'prodi_id' => $this->prodi->id,
            'users_id' => $gkm->id,
            'rtl' => 'Tindak lanjut test',
            'deadline' => now()->addMonth(),
            'pic_rtl' => 'Test PIC',
            'status' => 'completed',
        ]);

        $this->assertTrue($gpm->can('verify', $rtl));
    }

    /** @test */
    public function dekan_can_view_all_rtl(): void
    {
        $dekan = $this->createUserWithRole(User::ROLE_DEKAN);
        $gkm = $this->createUserWithRole(User::ROLE_GKM, $this->prodi->id);
        $kaprodi = $this->createUserWithRole(User::ROLE_KAPRODI, $this->prodi->id);
        
        $evaluasi = $this->createEvaluasi($kaprodi, $this->prodi, 'approved');
        $rtl = $this->createRTL($evaluasi, $gkm);

        $this->assertTrue($dekan->can('view', $rtl));
    }
}
