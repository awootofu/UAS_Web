<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RenstraIndikator extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'renstra_indikator';

    protected $fillable = [
        'kode_indikator',
        'nama_indikator',
        'deskripsi',
        'satuan',
        'kegiatan_id',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(RenstraKegiatan::class, 'kegiatan_id');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(RenstraTarget::class, 'indikator_id');
    }

    public function renstras(): HasMany
    {
        return $this->hasMany(Renstra::class, 'indikator_id');
    }

    // Get target for a specific year
    public function getTargetForYear(int $year): ?RenstraTarget
    {
        return $this->targets()->where('tahun', $year)->first();
    }
}
