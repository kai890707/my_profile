<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DailyAnalytics Model
 *
 * @property int $id
 * @property int $salesperson_id
 * @property string $date
 * @property int $profile_views_count
 * @property int $contact_requests_count
 * @property int $unique_visitors_count
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read User $salesperson
 */
class DailyAnalytics extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'daily_analytics';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'salesperson_id',
        'date',
        'profile_views_count',
        'contact_requests_count',
        'unique_visitors_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'salesperson_id' => 'integer',
        'date' => 'date',
        'profile_views_count' => 'integer',
        'contact_requests_count' => 'integer',
        'unique_visitors_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the salesperson that owns this analytics record.
     *
     * @return BelongsTo<User, $this>
     */
    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    /**
     * Scope a query to filter by salesperson.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<DailyAnalytics>  $query
     * @return \Illuminate\Database\Eloquent\Builder<DailyAnalytics>
     */
    public function scopeForSalesperson($query, int $salespersonId)
    {
        return $query->where('salesperson_id', $salespersonId);
    }

    /**
     * Scope a query to filter by date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<DailyAnalytics>  $query
     * @return \Illuminate\Database\Eloquent\Builder<DailyAnalytics>
     */
    public function scopeDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Get aggregated stats for a salesperson in a date range.
     *
     * @return object{profile_views: int, contact_requests: int, unique_visitors: int}|null
     */
    public static function getAggregatedStats(
        int $salespersonId,
        string $startDate,
        string $endDate
    ): ?object {
        return self::forSalesperson($salespersonId)
            ->dateRange($startDate, $endDate)
            ->selectRaw('
                SUM(profile_views_count) as profile_views,
                SUM(contact_requests_count) as contact_requests,
                SUM(unique_visitors_count) as unique_visitors
            ')
            ->first();
    }

    /**
     * Get daily trends for a salesperson in a date range.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, DailyAnalytics>
     */
    public static function getDailyTrends(
        int $salespersonId,
        string $startDate,
        string $endDate
    ): \Illuminate\Database\Eloquent\Collection {
        return self::forSalesperson($salespersonId)
            ->dateRange($startDate, $endDate)
            ->select([
                'date',
                'profile_views_count as profile_views',
                'contact_requests_count as contact_requests',
                'unique_visitors_count as unique_visitors',
            ])
            ->orderBy('date', 'asc')
            ->get();
    }
}
