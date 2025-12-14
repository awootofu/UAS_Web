<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    const ROLE_ADMIN = 'admin';
    const ROLE_DEKAN = 'dekan';
    const ROLE_GPM = 'GPM';
    const ROLE_GKM = 'GKM';
    const ROLE_KAPRODI = 'kaprodi';
    const ROLE_BPAP = 'BPAP';

    const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_DEKAN,
        self::ROLE_GPM,
        self::ROLE_GKM,
        self::ROLE_KAPRODI,
        self::ROLE_BPAP,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'prodi_id',
        'fakultas_id',
        'jabatan_id',
        'nip',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function fakultas(): BelongsTo
    {
        return $this->belongsTo(Fakultas::class);
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function renstras(): HasMany
    {
        return $this->hasMany(Renstra::class);
    }

    public function evaluasis(): HasMany
    {
        return $this->hasMany(Evaluasi::class, 'created_by');
    }

    public function rtls(): HasMany
    {
        return $this->hasMany(RTL::class, 'users_id');
    }

    // Role checking methods
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isDekan(): bool
    {
        return $this->role === self::ROLE_DEKAN;
    }

    public function isGPM(): bool
    {
        return $this->role === self::ROLE_GPM;
    }

    public function isGKM(): bool
    {
        return $this->role === self::ROLE_GKM;
    }

    public function isKaprodi(): bool
    {
        return $this->role === self::ROLE_KAPRODI;
    }

    public function isBPAP(): bool
    {
        return $this->role === self::ROLE_BPAP;
    }

    public function hasRole(string|array $roles): bool
    {
        // Case-insensitive role comparison
        if (is_string($roles)) {
            return strtolower($this->role) === strtolower($roles);
        }
        $normalizedRoles = array_map('strtolower', $roles);
        return in_array(strtolower($this->role), $normalizedRoles);
    }

    public function canVerify(): bool
    {
        return $this->hasRole([self::ROLE_GPM, self::ROLE_DEKAN, self::ROLE_ADMIN]);
    }

    public function canApprove(): bool
    {
        return $this->hasRole([self::ROLE_DEKAN, self::ROLE_ADMIN]);
    }

    /**
     * Get the prodi IDs that this user can access
     * - Admin: All prodis
     * - BPAP: All prodis
     * - GPM: All prodis (no fakultas restriction)
     * - Dekan: Prodis in their fakultas
     * - GKM: Only their own prodi (like Kaprodi)
     * - Kaprodi: Only their own prodi
     */
    public function getAccessibleProdiIds(): array
    {
        if ($this->isAdmin() || $this->isBPAP() || $this->isGPM()) {
            return Prodi::pluck('id')->toArray();
        }

        if ($this->isDekan() && $this->fakultas_id) {
            return Prodi::where('fakultas_id', $this->fakultas_id)->pluck('id')->toArray();
        }

        // GKM and Kaprodi can only see their own prodi
        if (($this->isGKM() || $this->isKaprodi()) && $this->prodi_id) {
            return [$this->prodi_id];
        }

        return [];
    }

    /**
     * Check if user can access a specific prodi
     */
    public function canAccessProdi($prodiId): bool
    {
        if ($this->isAdmin() || $this->isBPAP()) {
            return true;
        }

        return in_array($prodiId, $this->getAccessibleProdiIds());
    }

    /**
     * Get the fakultas IDs that this user can access
     */
    public function getAccessibleFakultasIds(): array
    {
        if ($this->isAdmin() || $this->isBPAP()) {
            return Fakultas::pluck('id')->toArray();
        }

        if ($this->fakultas_id) {
            return [$this->fakultas_id];
        }

        if ($this->prodi_id) {
            $prodi = Prodi::find($this->prodi_id);
            return $prodi && $prodi->fakultas_id ? [$prodi->fakultas_id] : [];
        }

        return [];
    }
}
