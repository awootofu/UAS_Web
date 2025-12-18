<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RenstraTarget extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'renstra_target';

    protected $fillable = [
        'indikator_id',
        'tahun',
        'target_value',
        'satuan',
        'keterangan',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'target_value' => 'decimal:2',
    ];

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(RenstraIndikator::class, 'indikator_id');
    }

    public function renstras(): HasMany
    {
        return $this->hasMany(Renstra::class, 'target_id');
    }

    public function evaluasis(): HasMany
    {
        return $this->hasMany(Evaluasi::class, 'target_id');
    }
}
