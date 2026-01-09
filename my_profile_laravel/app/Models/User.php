<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

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
     * Get the companies approved by this user.
     *
     * @return HasMany<Company, $this>
     */
    public function companiesApproved(): HasMany
    {
        return $this->hasMany(Company::class, 'approved_by');
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
}
