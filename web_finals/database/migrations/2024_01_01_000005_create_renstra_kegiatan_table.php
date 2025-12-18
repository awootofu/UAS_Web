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
        Schema::create('renstra_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kegiatan')->unique();
            $table->string('nama_kegiatan');
            $table->text('deskripsi')->nullable();
            $table->foreignId('kategori_id')->constrained('renstra_kategori')->cascadeOnDelete();
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
        Schema::dropIfExists('renstra_kegiatan');
    }
};
