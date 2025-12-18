<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Prodi;
use App\Models\Fakultas;
use App\Models\Jabatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * RoleSeeder - Seeds sample users for each role in the system.
 * 
 * This seeder creates users for each of the 6 defined roles:
 * - admin: Full system access, can manage users and all data
 * - dekan: Faculty dean (per fakultas), can view reports and approve evaluations
 * - GPM (Gugus Penjaminan Mutu): Quality assurance (no fakultas scope), reviews evaluations
 * - GKM (Gugus Kendali Mutu): Quality control (per prodi), verifies kaprodi
 * - kaprodi: Program head (per prodi), creates evaluations
 * - BPAP: Planning bureau, manages Renstra data
 * 
 * Usage:
 *   php artisan db:seed --class=RoleSeeder
 */
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get reference data
        $jabatans = Jabatan::all()->keyBy('kode_jabatan');
        $fakultas = Fakultas::all()->keyBy('kode_fakultas');
        $prodis = Prodi::all()->keyBy('kode_prodi');

        $users = [];

        // 1. Admin
        $users[] = [
            'name' => 'Administrator',
            'email' => 'admin@renstra.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'prodi_id' => null,
            'fakultas_id' => null,
            'jabatan_id' => null,
            'nip' => '199001010001',
            'phone' => '081234567890',
            'is_active' => true,
        ];

        // 2. BPAP
        $users[] = [
            'name' => 'Eko BPAP, S.E., M.M.',
            'email' => 'bpap@renstra.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_BPAP,
            'prodi_id' => null,
            'fakultas_id' => null,
            'jabatan_id' => $jabatans->get('BPAP')?->id,
            'nip' => '199201010001',
            'phone' => '081234567895',
            'is_active' => true,
        ];

        // 3. Dekan per Fakultas
        $dekanData = [
            'FSKOM' => ['name' => 'Dr. Agus Dekan FSKoM, M.Sc.', 'email' => 'dekan.fskom@renstra.test', 'nip' => '198501010001'],
            'FBPAR' => ['name' => 'Dr. Budi Dekan FBPAR, M.M.', 'email' => 'dekan.fbpar@renstra.test', 'nip' => '198501010002'],
            'FDKKA' => ['name' => 'Dr. Citra Dekan FDKKA, M.Ars.', 'email' => 'dekan.fdkka@renstra.test', 'nip' => '198501010003'],
        ];

        foreach ($dekanData as $kodeFakultas => $data) {
            $users[] = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => User::ROLE_DEKAN,
                'prodi_id' => null,
                'fakultas_id' => $fakultas->get($kodeFakultas)?->id,
                'jabatan_id' => $jabatans->get('DKN')?->id,
                'nip' => $data['nip'],
                'phone' => '0812345678' . substr($data['nip'], -2),
                'is_active' => true,
            ];
        }

        // 4. GPM (only 1 GPM for all fakultas)
        $users[] = [
            'name' => 'Ir. Diana GPM, M.T.',
            'email' => 'gpm@renstra.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_GPM,
            'prodi_id' => null,
            'fakultas_id' => null,
            'jabatan_id' => $jabatans->get('KGPM')?->id,
            'nip' => '198701010001',
            'phone' => '081234567901',
            'is_active' => true,
        ];

        // 5. GKM per Prodi (each prodi has its own GKM)
        $gkmData = [
            // FSKoM
            'FM' => ['name' => 'Gita GKM FM, S.Si., M.Si.', 'email' => 'gkm.fm@renstra.test', 'nip' => '199001010001'],
            'IF' => ['name' => 'Hadi GKM IF, S.Kom., M.Kom.', 'email' => 'gkm.if@renstra.test', 'nip' => '199001010002'],
            'DS' => ['name' => 'Indra GKM DS, S.Kom., M.Sc.', 'email' => 'gkm.ds@renstra.test', 'nip' => '199001010003'],
            // FBPAR
            'MB' => ['name' => 'Jaka GKM MB, S.E., M.M.', 'email' => 'gkm.mb@renstra.test', 'nip' => '199001010004'],
            'PWS' => ['name' => 'Kiki GKM PWS, S.Par., M.Par.', 'email' => 'gkm.pws@renstra.test', 'nip' => '199001010005'],
            'AKT' => ['name' => 'Lala GKM AKT, S.Ak., M.Ak.', 'email' => 'gkm.akt@renstra.test', 'nip' => '199001010006'],
            // FDKKA
            'K3' => ['name' => 'Maman GKM K3, S.T., M.K3.', 'email' => 'gkm.k3@renstra.test', 'nip' => '199001010007'],
            'DKV' => ['name' => 'Nana GKM DKV, S.Ds., M.Ds.', 'email' => 'gkm.dkv@renstra.test', 'nip' => '199001010008'],
            'ARS' => ['name' => 'Oki GKM ARS, S.T., M.Ars.', 'email' => 'gkm.ars@renstra.test', 'nip' => '199001010009'],
        ];

        foreach ($gkmData as $kodeProdi => $data) {
            $prodi = $prodis->get($kodeProdi);
            $users[] = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => User::ROLE_GKM,
                'prodi_id' => $prodi?->id,
                'fakultas_id' => $prodi?->fakultas_id,
                'jabatan_id' => $jabatans->get('KGKM')?->id,
                'nip' => $data['nip'],
                'phone' => '0812345680' . substr($data['nip'], -2),
                'is_active' => true,
            ];
        }

        // 6. Kaprodi per Prodi
        $kaprodiData = [
            // FSKoM
            'FM' => ['name' => 'Dr. Joko Kaprodi FM, M.Si.', 'email' => 'kaprodi.fm@renstra.test', 'nip' => '198801010001'],
            'IF' => ['name' => 'Dr. Kartika Kaprodi IF, M.Cs.', 'email' => 'kaprodi.if@renstra.test', 'nip' => '198801010002'],
            'DS' => ['name' => 'Dr. Lina Kaprodi DS, M.Sc.', 'email' => 'kaprodi.ds@renstra.test', 'nip' => '198801010003'],
            // FBPAR
            'MB' => ['name' => 'Dr. Mira Kaprodi MB, M.M.', 'email' => 'kaprodi.mb@renstra.test', 'nip' => '198801010004'],
            'PWS' => ['name' => 'Dr. Nanda Kaprodi PWS, M.Par.', 'email' => 'kaprodi.pws@renstra.test', 'nip' => '198801010005'],
            'AKT' => ['name' => 'Dr. Oka Kaprodi AKT, M.Ak.', 'email' => 'kaprodi.akt@renstra.test', 'nip' => '198801010006'],
            // FDKKA
            'K3' => ['name' => 'Dr. Putu Kaprodi K3, M.K3.', 'email' => 'kaprodi.k3@renstra.test', 'nip' => '198801010007'],
            'DKV' => ['name' => 'Dr. Qori Kaprodi DKV, M.Ds.', 'email' => 'kaprodi.dkv@renstra.test', 'nip' => '198801010008'],
            'ARS' => ['name' => 'Dr. Rudi Kaprodi ARS, M.Ars.', 'email' => 'kaprodi.ars@renstra.test', 'nip' => '198801010009'],
        ];

        foreach ($kaprodiData as $kodeProdi => $data) {
            $prodi = $prodis->get($kodeProdi);
            $users[] = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => User::ROLE_KAPRODI,
                'prodi_id' => $prodi?->id,
                'fakultas_id' => $prodi?->fakultas_id,
                'jabatan_id' => $jabatans->get('KPS')?->id,
                'nip' => $data['nip'],
                'phone' => '0812345681' . substr($data['nip'], -2),
                'is_active' => true,
            ];
        }

        // Create users
        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('✓ Created sample users for all roles');
        $this->command->table(
            ['Email', 'Role', 'Fakultas/Prodi'],
            collect($users)->map(function($u) use ($fakultas, $prodis) {
                $scope = '-';
                if ($u['fakultas_id']) {
                    $fak = $fakultas->first(fn($f) => $f->id == $u['fakultas_id']);
                    $scope = $fak ? $fak->kode_fakultas : '-';
                }
                if ($u['prodi_id']) {
                    $prodi = $prodis->first(fn($p) => $p->id == $u['prodi_id']);
                    $scope = $prodi ? $prodi->kode_prodi : '-';
                }
                return [$u['email'], $u['role'], $scope];
            })->toArray()
        );
    }
}
