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
            $table->string('kategori')->nullable()->after('kode_renstra');
            $table->string('kegiatan')->nullable()->after('kategori');
            $table->decimal('indikator_value', 10, 2)->nullable()->after('indikator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('renstra', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'kegiatan', 'indikator_value']);
        });
    }
};
