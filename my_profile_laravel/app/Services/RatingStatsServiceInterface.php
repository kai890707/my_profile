<?php

declare(strict_types=1);

namespace App\Services;

/**
 * 評分統計服務介面
 */
interface RatingStatsServiceInterface
{
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
    public function getStats(int $salespersonId): array;

    /**
     * 清除業務員評分統計快取
     *
     * @param int $salespersonId
     * @return void
     */
    public function clearCache(int $salespersonId): void;

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
    public function recalculate(int $salespersonId): array;
}
