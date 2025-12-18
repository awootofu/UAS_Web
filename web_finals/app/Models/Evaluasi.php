<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'evaluasi';

    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_VERIFIED = 'verified';
    const STATUS_REJECTED = 'rejected';
    const STATUS_APPROVED = 'approved';

    const SEMESTER_GANJIL = 'ganjil';
    const SEMESTER_GENAP = 'genap';

    protected $fillable = [
        'renstra_id',
        'prodi_id',
        'target_id',
        'bukti_id',
        'created_by',
        'semester',
        'tahun_evaluasi',
        'realisasi',
        'ketercapaian',
        'akar_masalah',
        'faktor_pendukung',
        'faktor_penghambat',
        'status',
        'verified_by',
        'verified_at',
        'verification_notes',
        'approved_by',
        'approved_at',
        'approval_notes',
    ];

    protected $casts = [
        'tahun_evaluasi' => 'integer',
        'realisasi' => 'decimal:2',
        'ketercapaian' => 'decimal:2',
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function renstra(): BelongsTo
    {
        return $this->belongsTo(Renstra::class);
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(RenstraTarget::class, 'target_id');
    }

    public function bukti(): BelongsTo
    {
        return $this->belongsTo(EvaluasiBukti::class, 'bukti_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rtls(): HasMany
    {
        return $this->hasMany(RTL::class, 'evaluasi_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_DRAFT, self::STATUS_SUBMITTED]);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeVerified($query)
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeByProdi($query, $prodiId)
    {
        return $query->where('prodi_id', $prodiId);
    }

    public function scopeBySemester($query, $semester, $year)
    {
        return $query->where('semester', $semester)->where('tahun_evaluasi', $year);
    }

    // Helper methods
    public function isAchieved(): bool
    {
        return $this->ketercapaian !== null && $this->ketercapaian >= 100;
    }

    public function canEdit(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED]);
    }

    public function canVerify(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function canApprove(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }
}
