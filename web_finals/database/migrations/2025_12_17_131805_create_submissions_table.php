<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Judul Dokumen
            $table->string('submitted_by'); // Nama/ID Pengaju
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            
            // Buat: Simpan verifier_id + timestamp
            $table->unsignedBigInteger('verifier_id')->nullable(); // ID GPM/Dekan
            $table->timestamp('verified_at')->nullable(); // Waktu verifikasi
            
            $table->timestamps(); // created_at & updated_at
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
