<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RenstraKategori extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'renstra_kategori';

    protected $fillable = [
        'kode_kategori',
        'nama_kategori',
        'deskripsi',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    public function kegiatan(): HasMany
    {
        return $this->hasMany(RenstraKegiatan::class, 'kategori_id');
    }

    public function renstras(): HasMany
    {
        return $this->hasMany(Renstra::class, 'kategori_id');
    }
}
