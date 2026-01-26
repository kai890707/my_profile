<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * 敏感詞過濾服務
 *
 * 負責檢測和過濾評論中的敏感詞
 */
class SensitiveWordFilter implements SensitiveWordFilterInterface
{
    /**
     * 敏感詞快取鍵
     */
    private const CACHE_KEY = 'sensitive_words';

    /**
     * 快取時間（小時）
     */
    private const CACHE_TTL = 24;

    /**
     * 預設敏感詞庫
     */
    private const DEFAULT_WORDS = [
        // 髒話、辱罵
        '髒話', '辱罵', '垃圾', '爛', '白痴', '智障',

        // 政治敏感
        '政治', '敏感詞',

        // 廣告詞彙
        '加line', '加賴', '私訊我', '聯絡電話', '優惠', '促銷',

        // 聯絡方式
        '0900', '0800', '@gmail.com', '@yahoo.com', 'line id', 'wechat',

        // 其他
        '刷分', '假評論', '買評論',
    ];

    /**
     * 取得敏感詞庫
     *
     * @return array<string>
     */
    private function getWords(): array
    {
        /** @var array<string>|null $cached */
        $cached = Cache::get(self::CACHE_KEY);

        if (is_array($cached)) {
            // Validate that all elements are strings
            $allStrings = true;
            foreach ($cached as $word) {
                if (!is_string($word)) {
                    $allStrings = false;
                    break;
                }
            }

            if ($allStrings) {
                return $cached;
            }
        }

        // Cache miss or invalid cache, store default words
        Cache::put(self::CACHE_KEY, self::DEFAULT_WORDS, now()->addHours(self::CACHE_TTL));

        return self::DEFAULT_WORDS;
    }

    /**
     * 檢查文字是否包含敏感詞
     *
     * @param string $text
     * @return bool
     */
    public function check(string $text): bool
    {
        $words = $this->getWords();
        $lowerText = strtolower($text);

        foreach ($words as $word) {
            if (str_contains($lowerText, strtolower($word))) {
                return true;
            }
        }

        return false;
    }

    /**
     * 找出文字中包含的敏感詞
     *
     * @param string $text
     * @return array<string>
     */
    public function find(string $text): array
    {
        $words = $this->getWords();
        $lowerText = strtolower($text);
        $found = [];

        foreach ($words as $word) {
            if (str_contains($lowerText, strtolower($word))) {
                $found[] = $word;
            }
        }

        return $found;
    }

    /**
     * 替換敏感詞為星號
     *
     * @param string $text
     * @return string
     */
    public function mask(string $text): string
    {
        $words = $this->getWords();

        foreach ($words as $word) {
            $replacement = str_repeat('*', mb_strlen($word));
            $text = str_ireplace($word, $replacement, $text);
        }

        return $text;
    }

    /**
     * 清除快取
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
