<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class EvaluasiBukti extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'evaluasi_bukti';

    protected $fillable = [
        'nama_file',
        'file_path',
        'file_type',
        'file_size',
        'deskripsi',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    // Max file size in bytes (10MB)
    const MAX_FILE_SIZE = 10 * 1024 * 1024;

    // Allowed file types
    const ALLOWED_TYPES = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'zip'];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function evaluasis(): HasMany
    {
        return $this->hasMany(Evaluasi::class, 'bukti_id');
    }

    // Get download URL
    public function getDownloadUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    // Get human-readable file size
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Check if file type is allowed
    public static function isAllowedType(string $extension): bool
    {
        return in_array(strtolower($extension), self::ALLOWED_TYPES);
    }
}
