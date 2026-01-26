<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Rating;

/**
 * 異常檢測服務
 *
 * 負責檢測評分中的異常行為
 */
class AnomalyDetector implements AnomalyDetectorInterface
{
    /**
     * 大量高分檢測閾值
     */
    private const MASS_HIGH_RATING_THRESHOLD = 5;

    /**
     * 大量高分檢測時間範圍（小時）
     */
    private const MASS_HIGH_RATING_HOURS = 1;

    /**
     * 相同 IP 檢測時間範圍（小時）
     */
    private const SAME_IP_HOURS = 24;

    /**
     * 評論相似度閾值（百分比）
     */
    private const SIMILARITY_THRESHOLD = 80;

    /**
     * 檢測評分是否異常
     *
     * @param Rating $rating
     * @return bool
     */
    public function detect(Rating $rating): bool
    {
        return $this->detectMassHighRatings($rating)
            || $this->detectSameIpMultipleRatings($rating)
            || $this->detectSimilarComments($rating);
    }

    /**
     * 取得異常原因
     *
     * @param Rating $rating
     * @return array<string>
     */
    public function getAnomalyReasons(Rating $rating): array
    {
        $reasons = [];

        if ($this->detectMassHighRatings($rating)) {
            $reasons[] = 'mass_high_ratings';
        }

        if ($this->detectSameIpMultipleRatings($rating)) {
            $reasons[] = 'same_ip_multiple_ratings';
        }

        if ($this->detectSimilarComments($rating)) {
            $reasons[] = 'similar_comments';
        }

        return $reasons;
    }

    /**
     * 檢測短時間大量高分
     *
     * @param Rating $rating
     * @return bool
     */
    private function detectMassHighRatings(Rating $rating): bool
    {
        // 僅檢測 5 星評分
        if ($rating->rating < 5.0) {
            return false;
        }

        $recentFiveStars = Rating::query()
            ->where('salesperson_id', $rating->salesperson_id)
            ->where('rating', 5.0)
            ->where('created_at', '>=', now()->subHours(self::MASS_HIGH_RATING_HOURS))
            ->count();

        return $recentFiveStars >= self::MASS_HIGH_RATING_THRESHOLD;
    }

    /**
     * 檢測相同 IP 多次評分
     *
     * @param Rating $rating
     * @return bool
     */
    private function detectSameIpMultipleRatings(Rating $rating): bool
    {
        $sameIpRatings = Rating::query()
            ->where('ip_address', $rating->ip_address)
            ->where('created_at', '>=', now()->subHours(self::SAME_IP_HOURS))
            ->where('id', '!=', $rating->id) // 排除自己
            ->count();

        return $sameIpRatings > 0;
    }

    /**
     * 檢測評論高度相似
     *
     * @param Rating $rating
     * @return bool
     */
    private function detectSimilarComments(Rating $rating): bool
    {
        // 沒有評論則不檢測
        if (empty($rating->comment)) {
            return false;
        }

        $recentComments = Rating::query()
            ->where('salesperson_id', $rating->salesperson_id)
            ->where('created_at', '>=', now()->subDays(7))
            ->where('id', '!=', $rating->id) // 排除自己
            ->whereNotNull('comment')
            ->pluck('comment');

        foreach ($recentComments as $existingComment) {
            if (!is_string($existingComment)) {
                continue;
            }

            $similarity = 0;
            similar_text(
                $rating->comment,
                $existingComment,
                $similarity
            );

            if ($similarity >= self::SIMILARITY_THRESHOLD) {
                return true;
            }
        }

        return false;
    }
}
