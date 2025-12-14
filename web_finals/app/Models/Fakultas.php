<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fakultas extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fakultas';

    // Fakultas constants
    const FSKOM = 'FSKOM';      // Fisika Medis, Informatika, Data Science
    const FBPAR = 'FBPAR';      // Manajemen Bisnis, Pariwisata, Akutansi
    const FDKKA = 'FDKKA';      // K3, DKV, Arsitektur

    protected $fillable = [
        'kode_fakultas',
        'nama_fakultas',
        'deskripsi',
    ];

    public function prodis(): HasMany
    {
        return $this->hasMany(Prodi::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all prodi IDs for this fakultas
     */
    public function getProdiIds(): array
    {
        return $this->prodis()->pluck('id')->toArray();
    }
}
