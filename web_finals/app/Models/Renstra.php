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
        'indikator_value',
        'kategori',
        'kegiatan',
        'user_id',
        'kategori_id',
        'kegiatan_id',
        'indikator_id',
        'target_id',
        'prodi_id',
        'periode',
        'status',
        'keterangan',
    ];

    // Boot the model
    protected static function boot()
    {
        parent::boot();

        // Auto-generate kode_renstra if not provided
        static::creating(function ($model) {
            if (!$model->kode_renstra) {
                $tahun = self::extractYearFromPeriode($model->periode ?? self::getCurrentPeriode());
                $model->kode_renstra = self::generateKodeRenstra($tahun);
            }
        });
    }

    /**
     * Generate unique kode_renstra
     */
    public static function generateKodeRenstra($tahun = null)
    {
        if (!$tahun) {
            $tahun = date('Y');
        }

        // Count renstra for this year
        $count = self::whereYear('created_at', $tahun)->count() + 1;

        // Format: RENS-2025-001, RENS-2025-002, etc.
        return sprintf('RENS-%d-%03d', $tahun, $count);
    }

    /**
     * Generate periode options for dropdown
     * Format: 2024/2025 ganjil, 2024/2025 genap, 2025/2026 ganjil, etc.
     */
    public static function getPeriodeOptions($count = 6)
    {
        $options = [];
        $currentYear = (int) date('Y');
        
        for ($i = 0; $i < $count; $i++) {
            $startYear = $currentYear - 1 + floor($i / 2);
            $endYear = $startYear + 1;
            $semester = ($i % 2 == 0) ? 'ganjil' : 'genap';
            
            $periode = sprintf('%d/%d %s', $startYear, $endYear, $semester);
            $options[$periode] = $periode;
        }
        
        return $options;
    }

    /**
     * Get current periode based on current month
     * Ganjil: Januari - Juni (months 1-6)
     * Genap: Juli - Desember (months 7-12)
     */
    public static function getCurrentPeriode()
    {
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');
        
        if ($currentMonth <= 6) {
            $semester = 'ganjil';
            $startYear = $currentYear - 1;
            $endYear = $currentYear;
        } else {
            $semester = 'genap';
            $startYear = $currentYear;
            $endYear = $currentYear + 1;
        }
        
        return sprintf('%d/%d %s', $startYear, $endYear, $semester);
    }

    /**
     * Extract year from periode string
     */
    public static function extractYearFromPeriode($periode)
    {
        // Extract first year from format like "2024/2025 ganjil"
        preg_match('/^(\d{4})/', $periode, $matches);
        return $matches[1] ?? date('Y');
    }

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

    public function scopeByPeriode($query, $periode)
    {
        return $query->where('periode', $periode);
    }
}
