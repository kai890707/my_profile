<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Rating;
use Illuminate\Support\Facades\Cache;

/**
 * 評分統計服務
 *
 * 負責計算和快取評分統計資料
 */
class RatingStatsService implements RatingStatsServiceInterface
{
    /**
     * 快取鍵前綴
     */
    private const CACHE_PREFIX = 'rating_stats:salesperson:';

    /**
     * 快取時間（秒）
     */
    private const CACHE_TTL = 300; // 5 分鐘

    /**
     * 取得業務員評分統計
     *
     * @param int $salespersonId
     * @return array{
     *     salesperson_id: int,
     *     average_rating: float,
     *     total_ratings: int,
     *     rating_distribution: array<string|int, int>,
     *     rating_percentage: array<string, float>,
     *     recent_ratings: array<int, array{rating: float, created_at: string}>
     * }
     */
    public function getStats(int $salespersonId): array
    {
        /** @var array{salesperson_id: int, average_rating: float, total_ratings: int, rating_distribution: array<string|int, int>, rating_percentage: array<string, float>, recent_ratings: array<int, array{rating: float, created_at: string}>}|null $cached */
        $cached = Cache::get(self::CACHE_PREFIX . $salespersonId);

        if (is_array($cached) && isset($cached['salesperson_id'])) {
            return $cached;
        }

        $stats = $this->calculateStats($salespersonId);
        Cache::put(self::CACHE_PREFIX . $salespersonId, $stats, self::CACHE_TTL);

        return $stats;
    }

    /**
     * 清除業務員評分統計快取
     *
     * @param int $salespersonId
     * @return void
     */
    public function clearCache(int $salespersonId): void
    {
        Cache::forget(self::CACHE_PREFIX . $salespersonId);
    }

    /**
     * 重新計算統計資料
     *
     * @param int $salespersonId
     * @return array{
     *     salesperson_id: int,
     *     average_rating: float,
     *     total_ratings: int,
     *     rating_distribution: array<string|int, int>,
     *     rating_percentage: array<string, float>,
     *     recent_ratings: array<int, array{rating: float, created_at: string}>
     * }
     */
    public function recalculate(int $salespersonId): array
    {
        $this->clearCache($salespersonId);
        return $this->getStats($salespersonId);
    }

    /**
     * 計算評分統計
     *
     * @param int $salespersonId
     * @return array{
     *     salesperson_id: int,
     *     average_rating: float,
     *     total_ratings: int,
     *     rating_distribution: array<string|int, int>,
     *     rating_percentage: array<string, float>,
     *     recent_ratings: array<int, array{rating: float, created_at: string}>
     * }
     */
    private function calculateStats(int $salespersonId): array
    {
        $ratings = Rating::query()
            ->forSalesperson($salespersonId)
            ->public()
            ->get();

        $totalRatings = $ratings->count();
        $averageRating = $totalRatings > 0
            ? (float)$ratings->avg('rating')
            : 0.0;

        // 評分分布（精確到 0.5）
        /** @var array<string|int, int> $distribution */
        $distribution = $ratings->groupBy(fn($r) => (string)$r->rating)
            ->map(fn($group) => $group->count())
            ->sortKeysDesc()
            ->toArray();

        // 評分百分比（整數星級）
        $percentage = [];
        foreach ([5, 4, 3, 2, 1] as $star) {
            $count = $ratings->filter(
                fn($r) => (int)floor($r->rating) === $star
            )->count();

            $percentage["{$star}_star"] = $totalRatings > 0
                ? round(($count / $totalRatings) * 100, 1)
                : 0;
        }

        // 最新評分
        $recentRatings = $ratings->sortByDesc('created_at')
            ->take(5)
            ->map(fn($r) => [
                'rating' => $r->rating,
                'created_at' => $r->created_at->toIso8601String(),
            ])
            ->values()
            ->all();

        return [
            'salesperson_id' => $salespersonId,
            'average_rating' => round($averageRating, 1),
            'total_ratings' => $totalRatings,
            'rating_distribution' => $distribution,
            'rating_percentage' => $percentage,
            'recent_ratings' => $recentRatings,
        ];
    }
}
