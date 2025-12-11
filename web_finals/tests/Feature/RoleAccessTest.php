<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Prodi;
use App\Models\Jabatan;
use App\Models\Renstra;
use App\Models\RenstraKategori;
use App\Models\RenstraKegiatan;
use App\Models\RenstraIndikator;
use App\Models\RenstraTarget;
use App\Models\Evaluasi;
use App\Models\RTL;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $dekanUser;
    protected User $gpmUser;
    protected User $gkmUser;
    protected User $kaprodiUser;
    protected User $bpapUser;
    protected Prodi $prodi;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Prodi
        $this->prodi = Prodi::create([
            'kode_prodi' => 'TI',
            'nama_prodi' => 'Teknik Informatika',
            'fakultas' => 'Fakultas Teknik',
        ]);

        // Create users with different roles
        $this->adminUser = User::factory()->create(['role' => 'admin']);
        $this->dekanUser = User::factory()->create(['role' => 'dekan']);
        $this->gpmUser = User::factory()->create(['role' => 'gpm']);
        $this->gkmUser = User::factory()->create(['role' => 'gkm', 'prodi_id' => $this->prodi->id]);
        $this->kaprodiUser = User::factory()->create(['role' => 'kaprodi', 'prodi_id' => $this->prodi->id]);
        $this->bpapUser = User::factory()->create(['role' => 'bpap']);
    }

    /** @test */
    public function guest_cannot_access_dashboard()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_access_dashboard()
    {
        $response = $this->actingAs($this->adminUser)->get('/dashboard');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_user_management()
    {
        $response = $this->actingAs($this->adminUser)->get('/users');
        $response->assertStatus(200);
    }

    /** @test */
    public function non_admin_cannot_access_user_management()
    {
        $response = $this->actingAs($this->kaprodiUser)->get('/users');
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_create_renstra()
    {
        $response = $this->actingAs($this->adminUser)->get('/renstra/create');
        $response->assertStatus(200);
    }

    /** @test */
    public function bpap_can_create_renstra()
    {
        $response = $this->actingAs($this->bpapUser)->get('/renstra/create');
        $response->assertStatus(200);
    }

    /** @test */
    public function kaprodi_cannot_create_renstra()
    {
        $response = $this->actingAs($this->kaprodiUser)->get('/renstra/create');
        $response->assertStatus(403);
    }

    /** @test */
    public function all_authenticated_users_can_view_renstra_list()
    {
        $users = [$this->adminUser, $this->dekanUser, $this->gpmUser, $this->gkmUser, $this->kaprodiUser, $this->bpapUser];

        foreach ($users as $user) {
            $response = $this->actingAs($user)->get('/renstra');
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function kaprodi_can_create_evaluasi()
    {
        $response = $this->actingAs($this->kaprodiUser)->get('/evaluasi/create');
        $response->assertStatus(200);
    }

    /** @test */
    public function gkm_cannot_create_evaluasi()
    {
        $response = $this->actingAs($this->gkmUser)->get('/evaluasi/create');
        $response->assertStatus(403);
    }

    /** @test */
    public function gkm_can_create_rtl()
    {
        $response = $this->actingAs($this->gkmUser)->get('/rtl/create');
        $response->assertStatus(200);
    }

    /** @test */
    public function kaprodi_cannot_create_rtl()
    {
        $response = $this->actingAs($this->kaprodiUser)->get('/rtl/create');
        $response->assertStatus(403);
    }

    /** @test */
    public function dekan_can_access_reports()
    {
        $response = $this->actingAs($this->dekanUser)->get('/reports/renstra');
        $response->assertStatus(200);
    }

    /** @test */
    public function gkm_cannot_access_reports()
    {
        $response = $this->actingAs($this->gkmUser)->get('/reports/renstra');
        $response->assertStatus(403);
    }
}
