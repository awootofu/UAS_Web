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
        Schema::create('renstra_kategori', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kategori')->unique();
            $table->string('nama_kategori');
            $table->text('deskripsi')->nullable();
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
        Schema::dropIfExists('renstra_kategori');
    }
};
