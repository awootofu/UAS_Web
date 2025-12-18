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
            $table->foreignId('renstra_id')->constrained('renstra')->cascadeOnDelete();
            $table->foreignId('prodi_id')->constrained('prodi')->cascadeOnDelete();
            $table->foreignId('target_id')->constrained('renstra_target')->cascadeOnDelete();
            $table->foreignId('bukti_id')->nullable()->constrained('evaluasi_bukti')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            
            $table->enum('semester', ['ganjil', 'genap']);
            $table->year('tahun_evaluasi');
            $table->decimal('realisasi', 10, 2)->nullable();
            $table->decimal('ketercapaian', 5, 2)->nullable(); // Percentage
            $table->text('akar_masalah')->nullable();
            $table->text('faktor_pendukung')->nullable();
            $table->text('faktor_penghambat')->nullable();
            
            $table->enum('status', ['draft', 'submitted', 'verified', 'rejected', 'approved'])->default('draft');
            
            // Audit trail for verification
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['renstra_id', 'prodi_id', 'target_id', 'semester', 'tahun_evaluasi'], 'evaluasi_unique');
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
