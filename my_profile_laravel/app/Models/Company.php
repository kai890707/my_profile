<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Company extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'tax_id',
        'is_personal',
        'created_by',
        'approval_status',
        'rejected_reason',
        'approved_by',
        'approved_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_personal' => 'boolean',
            'approved_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user who created this company.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the salesperson profiles for this company.
     *
     * @return HasMany<SalespersonProfile, $this>
     */
    public function salespersonProfiles(): HasMany
    {
        return $this->hasMany(SalespersonProfile::class);
    }

    /**
     * Scope to get only registered companies (with tax_id).
     *
     * @param  Builder<Company>  $query
     * @return Builder<Company>
     */
    public function scopeRegistered(Builder $query): Builder
    {
        return $query->where('is_personal', false)
            ->whereNotNull('tax_id');
    }

    /**
     * Scope to get only personal companies (without tax_id).
     *
     * @param  Builder<Company>  $query
     * @return Builder<Company>
     */
    public function scopePersonal(Builder $query): Builder
    {
        return $query->where('is_personal', true);
    }

    /**
     * Get the admin who approved this company.
     *
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get all approval logs for this company.
     *
     * @return MorphMany<ApprovalLog, $this>
     */
    public function approvalLogs(): MorphMany
    {
        return $this->morphMany(ApprovalLog::class, 'approvable');
    }
}
