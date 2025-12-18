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
        Schema::create('renstra_target', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_id')->constrained('renstra_indikator')->cascadeOnDelete();
            $table->year('tahun');
            $table->decimal('target_value', 10, 2);
            $table->string('satuan')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['indikator_id', 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('renstra_target');
    }
};
