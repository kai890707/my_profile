<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Rating;

/**
 * 異常檢測服務介面
 */
interface AnomalyDetectorInterface
{
    /**
     * 檢測評分是否異常
     *
     * @param Rating $rating
     * @return bool
     */
    public function detect(Rating $rating): bool;

    /**
     * 取得異常原因
     *
     * @param Rating $rating
     * @return array<string>
     */
    public function getAnomalyReasons(Rating $rating): array;
}
