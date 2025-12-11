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
            ProdiSeeder::class,
            RoleSeeder::class,      // Creates users for each role
            RenstraSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('🎉 Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Login credentials (all passwords: "password"):');
        $this->command->info('  admin@renstra.test    - Full system access');
        $this->command->info('  dekan@renstra.test    - View reports, approve evaluations');
        $this->command->info('  gpm@renstra.test      - Review evaluations, verify RTL');
        $this->command->info('  gkm@renstra.test      - Create RTL for prodi');
        $this->command->info('  kaprodi@renstra.test  - Create evaluations for prodi');
        $this->command->info('  bpap@renstra.test     - Manage renstra data');
    }
}
