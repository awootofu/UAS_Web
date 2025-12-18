<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder - Main seeder that orchestrates all other seeders.
 * 
 * Run with: php artisan db:seed
 * Fresh migration with seed: php artisan migrate:fresh --seed
 * 
 * Seeder execution order (dependencies first):
 * 1. JabatanSeeder - Creates position/jabatan reference data
 * 2. ProdiSeeder - Creates program studi reference data  
 * 3. RoleSeeder - Creates sample users for each of the 6 roles
 * 4. RenstraSeeder - Creates sample renstra data with categories, activities, indicators
 * 
 * @see \Database\Seeders\RoleSeeder for role-specific user creation
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run seeders in order (dependencies first)
        $this->call([
            JabatanSeeder::class,
            FakultasSeeder::class,  // Must run before ProdiSeeder
            ProdiSeeder::class,
            RoleSeeder::class,      // Creates users for each role
            RenstraSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('🎉 Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Login credentials (all passwords: "password"):');
        $this->command->info('  admin@renstra.test        - Full system access');
        $this->command->info('');
        $this->command->info('  Dekan per Fakultas:');
        $this->command->info('  dekan.fskom@renstra.test  - Dekan FSKoM (FM, IF, DS)');
        $this->command->info('  dekan.fbpar@renstra.test  - Dekan FBPAR (MB, PWS, AKT)');
        $this->command->info('  dekan.fdkka@renstra.test  - Dekan FDKKA (K3, DKV, ARS)');
        $this->command->info('');
        $this->command->info('  GPM & GKM (per fakultas):');
        $this->command->info('  gpm.fskom@renstra.test    - GPM FSKoM');
        $this->command->info('  gkm.fskom@renstra.test    - GKM FSKoM');
        $this->command->info('');
        $this->command->info('  Kaprodi (per prodi):');
        $this->command->info('  kaprodi.if@renstra.test   - Kaprodi Informatika');
        $this->command->info('  kaprodi.fm@renstra.test   - Kaprodi Fisika Medis');
        $this->command->info('  (dan lainnya...)');
        $this->command->info('');
        $this->command->info('  bpap@renstra.test         - BPAP (manage renstra)');
    }
}
