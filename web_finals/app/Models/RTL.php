<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RTL extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rtl';

    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'evaluasi_id',
        'users_id',
        'prodi_id',
        'rtl',
        'deadline',
        'pic_rtl',
        'bukti_rtl',
        'status',
        'keterangan',
        'verified_by',
        'verified_at',
        'verification_notes',
        'completed_at',
    ];

    protected $casts = [
        'deadline' => 'date',
        'verified_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function evaluasi(): BelongsTo
    {
        return $this->belongsTo(Evaluasi::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_OVERDUE)
                     ->orWhere(function ($q) {
                         $q->where('deadline', '<', now())
                           ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
                     });
    }

    public function scopeByProdi($query, $prodiId)
    {
        return $query->where('prodi_id', $prodiId);
    }

    // Helper methods
    public function isOverdue(): bool
    {
        return $this->deadline < now() && !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function canEdit(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    public function canComplete(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    public function getDaysUntilDeadlineAttribute(): int
    {
        return now()->diffInDays($this->deadline, false);
    }
}
