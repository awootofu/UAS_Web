<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jabatan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jabatan';

    protected $fillable = [
        'nama_jabatan',
        'kode_jabatan',
        'deskripsi',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
