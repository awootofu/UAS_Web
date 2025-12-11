<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Renstra extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'renstra';

    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'kode_renstra',
        'indikator',
        'user_id',
        'kategori_id',
        'kegiatan_id',
        'indikator_id',
        'target_id',
        'prodi_id',
        'tahun_awal',
        'tahun_akhir',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tahun_awal' => 'integer',
        'tahun_akhir' => 'integer',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(RenstraKategori::class, 'kategori_id');
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(RenstraKegiatan::class, 'kegiatan_id');
    }

    public function indikatorRelation(): BelongsTo
    {
        return $this->belongsTo(RenstraIndikator::class, 'indikator_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(RenstraTarget::class, 'target_id');
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function evaluasis(): HasMany
    {
        return $this->hasMany(Evaluasi::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByProdi($query, $prodiId)
    {
        return $query->where('prodi_id', $prodiId);
    }

    public function scopeByYear($query, $year)
    {
        return $query->where('tahun_awal', '<=', $year)
                     ->where('tahun_akhir', '>=', $year);
    }
}
