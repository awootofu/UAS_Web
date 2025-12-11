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
        Schema::create('renstra_indikator', function (Blueprint $table) {
            $table->id();
            $table->string('kode_indikator')->unique();
            $table->text('nama_indikator');
            $table->text('deskripsi')->nullable();
            $table->string('satuan')->nullable(); // %, jumlah, rasio, etc.
            $table->foreignId('kegiatan_id')->constrained('renstra_kegiatan')->cascadeOnDelete();
            $table->integer('urutan')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('renstra_indikator');
    }
};
