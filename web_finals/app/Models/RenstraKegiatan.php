<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RenstraKegiatan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'renstra_kegiatan';

    protected $fillable = [
        'kode_kegiatan',
        'nama_kegiatan',
        'deskripsi',
        'kategori_id',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(RenstraKategori::class, 'kategori_id');
    }

    public function indikators(): HasMany
    {
        return $this->hasMany(RenstraIndikator::class, 'kegiatan_id');
    }

    public function renstras(): HasMany
    {
        return $this->hasMany(Renstra::class, 'kegiatan_id');
    }
}
