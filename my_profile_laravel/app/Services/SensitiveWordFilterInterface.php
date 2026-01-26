<?php

declare(strict_types=1);

namespace App\Services;

/**
 * 敏感詞過濾服務介面
 */
interface SensitiveWordFilterInterface
{
    /**
     * 檢查文字是否包含敏感詞
     *
     * @param string $text
     * @return bool
     */
    public function check(string $text): bool;

    /**
     * 找出文字中包含的敏感詞
     *
     * @param string $text
     * @return array<string>
     */
    public function find(string $text): array;

    /**
     * 替換敏感詞為星號
     *
     * @param string $text
     * @return string
     */
    public function mask(string $text): string;
}
