<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('renstra', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['tahun_awal', 'tahun_akhir']);
            
            // Add new periode column
            // Format: 2024/2025 ganjil or 2024/2025 genap
            $table->string('periode')->after('kode_renstra')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('renstra', function (Blueprint $table) {
            $table->dropColumn(['periode']);
            
            // Restore old columns
            $table->year('tahun_awal')->after('prodi_id')->nullable();
            $table->year('tahun_akhir')->after('tahun_awal')->nullable();
        });
    }
};
