<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prodi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'prodi';

    protected $fillable = [
        'nama_prodi',
        'kode_prodi',
        'fakultas',
        'fakultas_id',
        'jenjang',
        'deskripsi',
    ];

    public function fakultasRelation(): BelongsTo
    {
        return $this->belongsTo(Fakultas::class, 'fakultas_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function renstras(): HasMany
    {
        return $this->hasMany(Renstra::class);
    }

    public function evaluasis(): HasMany
    {
        return $this->hasMany(Evaluasi::class);
    }

    public function rtls(): HasMany
    {
        return $this->hasMany(RTL::class);
    }
}
