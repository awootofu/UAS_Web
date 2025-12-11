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
        Schema::create('renstra', function (Blueprint $table) {
            $table->id();
            $table->string('kode_renstra')->unique();
            $table->text('indikator'); // Textual description
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('renstra_kategori')->cascadeOnDelete();
            $table->foreignId('kegiatan_id')->constrained('renstra_kegiatan')->cascadeOnDelete();
            $table->foreignId('indikator_id')->constrained('renstra_indikator')->cascadeOnDelete();
            $table->foreignId('target_id')->nullable()->constrained('renstra_target')->nullOnDelete();
            $table->foreignId('prodi_id')->nullable()->constrained('prodi')->nullOnDelete();
            $table->year('tahun_awal');
            $table->year('tahun_akhir');
            $table->enum('status', ['draft', 'active', 'completed', 'archived'])->default('draft');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('renstra');
    }
};
