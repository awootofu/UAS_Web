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
        // Add role and additional fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'dekan', 'GPM', 'GKM', 'kaprodi', 'BPAP'])->default('kaprodi')->after('email');
            $table->foreignId('prodi_id')->nullable()->after('role')->constrained('prodi')->nullOnDelete();
            $table->foreignId('jabatan_id')->nullable()->after('prodi_id')->constrained('jabatan')->nullOnDelete();
            $table->string('nip')->nullable()->after('jabatan_id');
            $table->string('phone')->nullable()->after('nip');
            $table->boolean('is_active')->default(true)->after('phone');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['prodi_id']);
            $table->dropForeign(['jabatan_id']);
            $table->dropColumn(['role', 'prodi_id', 'jabatan_id', 'nip', 'phone', 'is_active', 'deleted_at']);
        });
    }
};
