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
        Schema::create('rtl', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluasi_id')->constrained('evaluasi')->cascadeOnDelete();
            $table->foreignId('users_id')->constrained('users')->cascadeOnDelete(); // Created by
            $table->foreignId('prodi_id')->constrained('prodi')->cascadeOnDelete();
            
            $table->text('rtl'); // Rencana Tindak Lanjut description
            $table->date('deadline');
            $table->string('pic_rtl'); // Person in charge
            $table->string('bukti_rtl')->nullable(); // Evidence file path
            
            $table->enum('status', ['pending', 'in_progress', 'completed', 'verified', 'rejected', 'overdue', 'cancelled'])->default('pending');
            $table->text('keterangan')->nullable();
            
            // Audit trail
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rtl');
    }
};
