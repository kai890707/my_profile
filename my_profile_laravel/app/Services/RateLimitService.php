<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContactRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class RateLimitService
{
    /**
     * Check if user can contact a specific salesperson (24h rate limit).
     *
     * BR-RL-002: 24 hours内同業務員只能聯繫 1 次
     */
    public function canContactSalesperson(int $userId, int $salespersonId): bool
    {
        return ! ContactRequest::where('user_id', $userId)
            ->where('salesperson_id', $salespersonId)
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->exists();
    }

    /**
     * Check if user can submit contact request today (daily limit: 5).
     *
     * BR-RL-003: 每天最多提交 5 次聯繫請求（跨業務員）
     */
    public function canSubmitToday(int $userId): bool
    {
        $count = ContactRequest::where('user_id', $userId)
            ->whereDate('created_at', Carbon::today())
            ->count();

        return $count < 5;
    }

    /**
     * Record contact attempt in cache for rate limiting.
     *
     * @param  int  $userId  User ID
     * @param  int  $salespersonId  Salesperson ID
     */
    public function recordContact(int $userId, int $salespersonId): void
    {
        $cacheKey = sprintf('contact_attempt:%d:%d', $userId, $salespersonId);

        // Store in cache for 24 hours
        Cache::put($cacheKey, Carbon::now()->toIso8601String(), 86400); // 24 * 60 * 60 = 86400 seconds
    }

    /**
     * Get the next available time to contact this salesperson.
     *
     * @return Carbon|null Next available time, or null if can contact now
     */
    public function getNextAvailableTime(int $userId, int $salespersonId): ?Carbon
    {
        $lastRequest = ContactRequest::where('user_id', $userId)
            ->where('salesperson_id', $salespersonId)
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $lastRequest || ! $lastRequest->created_at) {
            return null;
        }

        $nextAvailableTime = $lastRequest->created_at->addHours(24);

        return $nextAvailableTime->isFuture() ? $nextAvailableTime : null;
    }

    /**
     * Get remaining daily submission count for today.
     */
    public function getRemainingDailyCount(int $userId): int
    {
        $usedCount = ContactRequest::where('user_id', $userId)
            ->whereDate('created_at', Carbon::today())
            ->count();

        return max(0, 5 - $usedCount);
    }
}
