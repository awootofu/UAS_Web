<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for RoleMiddleware
 * 
 * Tests the role-based access control middleware that restricts
 * route access based on user roles.
 * 
 * @see \App\Http\Middleware\RoleMiddleware
 */
class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected Prodi $prodi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prodi = Prodi::create([
            'kode_prodi' => 'TI',
            'nama_prodi' => 'Teknik Informatika',
            'fakultas' => 'Fakultas Teknik',
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

    /** @test */
    public function guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function admin_can_access_admin_only_routes(): void
    {
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);

        $response = $this->actingAs($admin)->get('/users');

        $response->assertStatus(200);
    }

    /** @test */
    public function non_admin_cannot_access_admin_only_routes(): void
    {
        $kaprodi = $this->createUserWithRole(User::ROLE_KAPRODI, $this->prodi->id);

        $response = $this->actingAs($kaprodi)->get('/users');

        $response->assertStatus(403);
    }

    /** @test */
    public function gkm_cannot_access_bpap_only_routes(): void
    {
        $gkm = $this->createUserWithRole(User::ROLE_GKM, $this->prodi->id);

        $response = $this->actingAs($gkm)->get('/renstra/create');

        $response->assertStatus(403);
    }

    /** @test */
    public function bpap_can_access_renstra_create(): void
    {
        $bpap = $this->createUserWithRole(User::ROLE_BPAP);

        $response = $this->actingAs($bpap)->get('/renstra/create');

        $response->assertStatus(200);
    }

    /** @test */
    public function middleware_accepts_multiple_roles(): void
    {
        // Admin should access routes that allow admin,bpap
        $admin = $this->createUserWithRole(User::ROLE_ADMIN);
        $response = $this->actingAs($admin)->get('/renstra/create');
        $response->assertStatus(200);

        // BPAP should also access the same route
        $bpap = $this->createUserWithRole(User::ROLE_BPAP);
        $response = $this->actingAs($bpap)->get('/renstra/create');
        $response->assertStatus(200);
    }

    /** @test */
    public function deactivated_user_is_logged_out(): void
    {
        $user = $this->createUserWithRole(User::ROLE_ADMIN);
        $user->update(['is_active' => false]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function gkm_can_access_rtl_create(): void
    {
        $gkm = $this->createUserWithRole(User::ROLE_GKM, $this->prodi->id);

        $response = $this->actingAs($gkm)->get('/rtl/create');

        $response->assertStatus(200);
    }

    /** @test */
    public function kaprodi_cannot_access_rtl_create(): void
    {
        $kaprodi = $this->createUserWithRole(User::ROLE_KAPRODI, $this->prodi->id);

        $response = $this->actingAs($kaprodi)->get('/rtl/create');

        $response->assertStatus(403);
    }

    /** @test */
    public function kaprodi_can_access_evaluasi_create(): void
    {
        $kaprodi = $this->createUserWithRole(User::ROLE_KAPRODI, $this->prodi->id);

        $response = $this->actingAs($kaprodi)->get('/evaluasi/create');

        $response->assertStatus(200);
    }

    /** @test */
    public function gpm_can_access_evaluasi_create(): void
    {
        $gpm = $this->createUserWithRole(User::ROLE_GPM);

        $response = $this->actingAs($gpm)->get('/evaluasi/create');

        $response->assertStatus(200);
    }

    /** @test */
    public function dekan_can_access_reports(): void
    {
        $dekan = $this->createUserWithRole(User::ROLE_DEKAN);

        $response = $this->actingAs($dekan)->get('/reports/renstra');

        $response->assertStatus(200);
    }

    /** @test */
    public function bpap_cannot_access_reports(): void
    {
        $bpap = $this->createUserWithRole(User::ROLE_BPAP);

        $response = $this->actingAs($bpap)->get('/reports/renstra');

        $response->assertStatus(403);
    }

    /** @test */
    public function all_authenticated_users_can_view_renstra_index(): void
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
            $response = $this->actingAs($user)->get('/renstra');
            $response->assertStatus(200, "Role {$role} should be able to view renstra index");
        }
    }

    /** @test */
    public function all_authenticated_users_can_access_dashboard(): void
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
            $response = $this->actingAs($user)->get('/dashboard');
            $response->assertStatus(200, "Role {$role} should be able to access dashboard");
        }
    }
}
