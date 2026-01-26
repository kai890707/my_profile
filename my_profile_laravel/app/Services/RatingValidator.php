<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContactRequest;
use App\Models\Rating;
use App\Models\User;

/**
 * 評分資格驗證服務
 *
 * 負責驗證用戶是否有資格評分
 */
class RatingValidator
{
    /**
     * IP 限制時間（小時）
     */
    private const IP_LIMIT_HOURS = 24;

    /**
     * 新用戶限制天數
     */
    private const NEW_USER_RESTRICTION_DAYS = 7;

    /**
     * 檢查用戶是否可以評分
     *
     * @param User $user 評分用戶
     * @param int $salespersonId 業務員 ID
     * @return bool
     */
    public function canUserRate(User $user, int $salespersonId): bool
    {
        // 檢查 IP 限制
        if (!$this->checkIpLimit($user->last_login_ip ?? '')) {
            return false;
        }

        // 檢查新用戶限制
        if ($this->isNewUser($user)) {
            return false;
        }

        // 檢查是否已完成聯繫
        if (!$this->hasCompletedContact($user->id, $salespersonId)) {
            return false;
        }

        // 檢查是否已評分
        if ($this->hasRated($user->id, $salespersonId)) {
            return false;
        }

        return true;
    }

    /**
     * 取得無法評分的原因
     *
     * @param User $user
     * @param int $salespersonId
     * @return string|null
     */
    public function getIneligibleReason(User $user, int $salespersonId): ?string
    {
        if ($this->isNewUser($user)) {
            return 'new_user_restriction';
        }

        if (!$this->hasCompletedContact($user->id, $salespersonId)) {
            return 'no_completed_contact';
        }

        if ($this->hasRated($user->id, $salespersonId)) {
            return 'already_rated';
        }

        return null;
    }

    /**
     * 檢查 IP 限制
     *
     * @param string $ipAddress
     * @return bool
     */
    public function checkIpLimit(string $ipAddress): bool
    {
        if (empty($ipAddress)) {
            return true; // 無 IP 資訊時不限制
        }

        $recentRating = Rating::query()
            ->where('ip_address', $ipAddress)
            ->where('created_at', '>=', now()->subHours(self::IP_LIMIT_HOURS))
            ->exists();

        return !$recentRating;
    }

    /**
     * 檢查是否為新用戶
     *
     * @param User $user
     * @return bool
     */
    public function isNewUser(User $user): bool
    {
        if ($user->created_at === null) {
            return true; // 無創建時間視為新用戶
        }

        return $user->created_at->diffInDays(now()) < self::NEW_USER_RESTRICTION_DAYS;
    }

    /**
     * 檢查是否已完成聯繫
     *
     * @param int $userId
     * @param int $salespersonId
     * @return bool
     */
    private function hasCompletedContact(int $userId, int $salespersonId): bool
    {
        return ContactRequest::query()
            ->where('user_id', $userId)
            ->where('salesperson_id', $salespersonId)
            ->whereIn('status', ['contacted', 'closed'])
            ->exists();
    }

    /**
     * 檢查是否已評分
     *
     * @param int $userId
     * @param int $salespersonId
     * @return bool
     */
    private function hasRated(int $userId, int $salespersonId): bool
    {
        return Rating::query()
            ->where('user_id', $userId)
            ->where('salesperson_id', $salespersonId)
            ->exists();
    }
}
