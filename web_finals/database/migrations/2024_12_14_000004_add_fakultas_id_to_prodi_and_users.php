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
        Schema::table('prodi', function (Blueprint $table) {
            // Add fakultas_id foreign key
            $table->foreignId('fakultas_id')->nullable()->after('id')->constrained('fakultas')->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            // Add fakultas_id for Dekan users
            $table->foreignId('fakultas_id')->nullable()->after('prodi_id')->constrained('fakultas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prodi', function (Blueprint $table) {
            $table->dropForeign(['fakultas_id']);
            $table->dropColumn('fakultas_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['fakultas_id']);
            $table->dropColumn('fakultas_id');
        });
    }
};
