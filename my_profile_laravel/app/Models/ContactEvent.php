<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Request;

class ContactEvent extends Model
{
    use HasFactory;

    /**
     * No updated_at column for this table (events are immutable).
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'salesperson_id',
        'event_type',
        'ip_address_hash',
        'user_agent',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the user that triggered this event.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the salesperson related to this event.
     *
     * @return BelongsTo<User, $this>
     */
    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    /**
     * Scope to filter events for a specific salesperson.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     * @return \Illuminate\Database\Eloquent\Builder<self>
     */
    public function scopeForSalesperson($query, int $salespersonId)
    {
        return $query->where('salesperson_id', $salespersonId);
    }

    /**
     * Scope to filter events by type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     * @return \Illuminate\Database\Eloquent\Builder<self>
     */
    public function scopeOfType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope to filter events within a date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     * @param  string|\DateTimeInterface  $startDate
     * @param  string|\DateTimeInterface  $endDate
     * @return \Illuminate\Database\Eloquent\Builder<self>
     */
    public function scopeBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Track an event (static factory method).
     *
     * @param  string  $eventType  Event type (profile_view, contact_form_submission)
     * @param  int  $salespersonId  Salesperson ID
     * @param  int|null  $userId  User ID (nullable for anonymous users)
     * @param  string|null  $ipAddress  IP address (will be hashed with SHA256)
     * @param  string|null  $userAgent  User agent string
     */
    public static function track(
        string $eventType,
        int $salespersonId,
        ?int $userId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): self {
        // Get IP and User Agent from request if not provided
        $ipAddress = $ipAddress ?? Request::ip();
        $userAgent = $userAgent ?? Request::userAgent();

        return self::create([
            'user_id' => $userId,
            'salesperson_id' => $salespersonId,
            'event_type' => $eventType,
            'ip_address_hash' => $ipAddress ? hash('sha256', $ipAddress) : '',
            'user_agent' => $userAgent,
        ]);
    }
}
