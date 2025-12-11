<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Prodi;
use App\Models\Jabatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * RoleSeeder - Seeds sample users for each role in the system.
 * 
 * This seeder creates one user for each of the 6 defined roles:
 * - admin: Full system access, can manage users and all data
 * - dekan: Faculty dean, can view reports and approve evaluations
 * - GPM (Gugus Penjaminan Mutu): Quality assurance, reviews evaluations, verifies RTL
 * - GKM (Gugus Kendali Mutu): Quality control per prodi, creates RTL
 * - kaprodi: Program head, creates evaluations for their prodi
 * - BPAP: Planning bureau, manages Renstra data
 * 
 * Usage:
 *   php artisan db:seed --class=RoleSeeder
 * 
 * Or include in DatabaseSeeder:
 *   $this->call(RoleSeeder::class);
 * 
 * Default credentials for all users: password = 'password'
 * 
 * @see \App\Models\User for role constants
 * @see \App\Http\Middleware\RoleMiddleware for route protection
 * @see \App\Policies for authorization rules
 */
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates sample users with appropriate role assignments and
     * prodi associations where applicable.
     */
    public function run(): void
    {
        // Ensure Jabatan and Prodi exist first
        $this->call([JabatanSeeder::class, ProdiSeeder::class]);

        // Get reference data
        $prodi = Prodi::first();
        $jabatans = Jabatan::all()->keyBy('kode_jabatan');

        // Define sample users for each role
        $users = [
            [
                'name' => 'Administrator',
                'email' => 'admin@renstra.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'prodi_id' => null, // Admin has global access
                'jabatan_id' => null,
                'nip' => '199001010001',
                'phone' => '081234567890',
                'is_active' => true,
            ],
            [
                'name' => 'Dr. Ahmad Dekan, M.Sc.',
                'email' => 'dekan@renstra.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_DEKAN,
                'prodi_id' => null, // Dekan oversees all prodis
                'jabatan_id' => $jabatans->get('DKN')?->id,
                'nip' => '198501010001',
                'phone' => '081234567891',
                'is_active' => true,
            ],
            [
                'name' => 'Ir. Siti GPM, M.T.',
                'email' => 'gpm@renstra.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_GPM,
                'prodi_id' => null, // GPM reviews all prodis
                'jabatan_id' => $jabatans->get('KGPM')?->id,
                'nip' => '198701010001',
                'phone' => '081234567892',
                'is_active' => true,
            ],
            [
                'name' => 'Budi GKM, S.Kom., M.Kom.',
                'email' => 'gkm@renstra.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_GKM,
                'prodi_id' => $prodi?->id, // GKM assigned to specific prodi
                'jabatan_id' => $jabatans->get('KGKM')?->id,
                'nip' => '199001010002',
                'phone' => '081234567893',
                'is_active' => true,
            ],
            [
                'name' => 'Dr. Dewi Kaprodi, M.Cs.',
                'email' => 'kaprodi@renstra.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_KAPRODI,
                'prodi_id' => $prodi?->id, // Kaprodi leads specific prodi
                'jabatan_id' => $jabatans->get('KPS')?->id,
                'nip' => '198801010001',
                'phone' => '081234567894',
                'is_active' => true,
            ],
            [
                'name' => 'Eko BPAP, S.E., M.M.',
                'email' => 'bpap@renstra.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_BPAP,
                'prodi_id' => null, // BPAP works across all prodis
                'jabatan_id' => $jabatans->get('BPAP')?->id,
                'nip' => '199201010001',
                'phone' => '081234567895',
                'is_active' => true,
            ],
        ];

        // Create users
        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('✓ Created sample users for all 6 roles');
        $this->command->table(
            ['Email', 'Role', 'Password'],
            collect($users)->map(fn($u) => [$u['email'], $u['role'], 'password'])->toArray()
        );
    }
}
