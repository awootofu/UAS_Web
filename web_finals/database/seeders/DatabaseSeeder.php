<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
            UserSeeder::class,
            RenstraSeeder::class,
        ]);
    }
}
