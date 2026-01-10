<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    // Roles
    public const ROLE_USER = 'user';
    public const ROLE_SALESPERSON = 'salesperson';
    public const ROLE_ADMIN = 'admin';

    // Salesperson Status
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    // Default reapply days
    public const DEFAULT_REAPPLY_DAYS = 7;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password_hash',
        'role',
        'status',
        'salesperson_status',
        'salesperson_applied_at',
        'salesperson_approved_at',
        'rejection_reason',
        'can_reapply_at',
        'is_paid_member',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
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
            'password_hash' => 'hashed',
            'salesperson_applied_at' => 'datetime',
            'salesperson_approved_at' => 'datetime',
            'can_reapply_at' => 'datetime',
            'is_paid_member' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the salesperson profile for the user.
     *
     * @return HasOne<SalespersonProfile, $this>
     */
    public function salespersonProfile(): HasOne
    {
        return $this->hasOne(SalespersonProfile::class);
    }

    /**
     * Get the companies created by this user.
     *
     * @return HasMany<Company, $this>
     */
    public function companiesCreated(): HasMany
    {
        return $this->hasMany(Company::class, 'created_by');
    }

    /**
     * Get the certifications for this user.
     *
     * @return HasMany<Certification, $this>
     */
    public function certifications(): HasMany
    {
        return $this->hasMany(Certification::class);
    }

    /**
     * Get the experiences for this user.
     *
     * @return HasMany<Experience, $this>
     */
    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    /**
     * Get the approval logs created by this admin user.
     *
     * @return HasMany<ApprovalLog, $this>
     */
    public function approvalLogs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class, 'admin_id');
    }

    /**
     * Get the identifier that will be stored in the JWT subject claim.
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role,
            'status' => $this->status,
        ];
    }

    /**
     * Get the password attribute name for authentication.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    /**
     * Check if user is a general user.
     */
    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    /**
     * Check if user is a salesperson (any status).
     */
    public function isSalesperson(): bool
    {
        return $this->role === self::ROLE_SALESPERSON;
    }

    /**
     * Check if user is an approved salesperson.
     */
    public function isApprovedSalesperson(): bool
    {
        return $this->role === self::ROLE_SALESPERSON
            && $this->salesperson_status === self::STATUS_APPROVED;
    }

    /**
     * Check if user is a pending salesperson.
     */
    public function isPendingSalesperson(): bool
    {
        return $this->role === self::ROLE_SALESPERSON
            && $this->salesperson_status === self::STATUS_PENDING;
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user can reapply for salesperson.
     */
    public function canReapply(): bool
    {
        if ($this->salesperson_status !== self::STATUS_REJECTED) {
            return false;
        }

        if (!$this->can_reapply_at) {
            return true;
        }

        return $this->can_reapply_at->isPast();
    }

    /**
     * Upgrade user to salesperson.
     *
     * @param array<string, mixed> $profileData
     */
    public function upgradeToSalesperson(array $profileData): void
    {
        $this->update([
            'role' => self::ROLE_SALESPERSON,
            'salesperson_status' => self::STATUS_PENDING,
            'salesperson_applied_at' => now(),
            'rejection_reason' => null,
            'can_reapply_at' => null,
        ]);

        $this->salespersonProfile()->updateOrCreate(
            ['user_id' => $this->id],
            $profileData
        );
    }

    /**
     * Approve salesperson application.
     */
    public function approveSalesperson(): void
    {
        $this->update([
            'salesperson_status' => self::STATUS_APPROVED,
            'salesperson_approved_at' => now(),
            'rejection_reason' => null,
            'can_reapply_at' => null,
        ]);
    }

    /**
     * Reject salesperson application.
     */
    public function rejectSalesperson(string $reason, int $reapplyDays = self::DEFAULT_REAPPLY_DAYS): void
    {
        $this->update([
            'role' => self::ROLE_USER,
            'salesperson_status' => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'can_reapply_at' => $reapplyDays > 0 ? now()->addDays($reapplyDays) : null,
        ]);
    }
}
