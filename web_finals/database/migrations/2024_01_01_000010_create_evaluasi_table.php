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
        Schema::create('evaluasi', function (Blueprint $table) {
            $table->id();

            // Relasi Utama
            $table->foreignId('renstra_id')->constrained('renstra')->cascadeOnDelete();
            
            // Fix 1: Menggunakan 'created_by' (bukan user_id)
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            // Relasi Tambahan
            $table->foreignId('prodi_id')->nullable()->constrained('prodi')->nullOnDelete();
            $table->foreignId('target_id')->nullable()->constrained('renstra_target')->nullOnDelete();

            // Fix 2: Menggunakan 'bukti_id' (bukan bukti_path)
            $table->unsignedBigInteger('bukti_id')->nullable(); 

            // Inti Data Evaluasi
            $table->string('realisasi'); 
            
            // Fix 3: Nullable agar tidak error jika kosong dari form
            $table->enum('status_ketercapaian', ['tercapai', 'gagal'])->nullable(); 

            // Kolom Conditional
            $table->text('ketercapaian')->nullable();       
            $table->text('faktor_pendukung')->nullable();   
            $table->text('akar_masalah')->nullable();       
            $table->text('faktor_penghambat')->nullable();  

            // Status Workflow
            $table->enum('status', ['draft', 'submitted', 'verified', 'rejected', 'approved'])->default('submitted');

            // Audit Trail
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();

            // === PERBAIKAN BARU DISINI ===
            // Menambahkan kolom semester yang sebelumnya hilang
            $table->enum('semester', ['ganjil', 'genap']);
            
            $table->year('tahun_evaluasi')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluasi');
    }
};