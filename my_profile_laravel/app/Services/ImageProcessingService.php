<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Image Processing Service
 *
 * 負責圖片壓縮、調整大小、格式優化
 *
 * 壓縮規則：
 * - 觸發條件：圖片尺寸 > 400x400 OR 檔案大小 > 500KB
 * - 目標尺寸：400x400 (保持寬高比)
 * - JPEG 品質：80%
 * - PNG 壓縮：level 8
 * - 保留透明背景 (PNG/GIF)
 * - 不強制轉 JPEG
 *
 * @package App\Services
 */
class ImageProcessingService
{
    private const TARGET_WIDTH = 400;
    private const TARGET_HEIGHT = 400;
    private const COMPRESSION_QUALITY = 80;

    /**
     * 處理圖片：調整大小、壓縮、優化
     *
     * @param string $imageData 二進制圖片資料
     * @param string $mimeType 原始 MIME type
     * @return array{data: string, mime: string, size: int, width: int, height: int} 處理後的圖片資料
     * @throws \Exception 處理失敗時
     */
    public function processImage(string $imageData, string $mimeType): array
    {
        $originalSize = strlen($imageData);

        // 1. 建立 GD 圖片資源
        $image = @imagecreatefromstring($imageData);

        if ($image === false) {
            throw new \Exception('Failed to create image resource');
        }

        try {
            // 2. 取得原始尺寸
            $originalWidth = imagesx($image);
            $originalHeight = imagesy($image);

            // 3. 計算新尺寸（如需要）
            [$newWidth, $newHeight] = $this->calculateNewDimensions(
                $originalWidth,
                $originalHeight
            );

            // 4. 調整大小（如需要）
            if ($newWidth !== $originalWidth || $newHeight !== $originalHeight) {
                $image = $this->resizeImage($image, $newWidth, $newHeight, $mimeType);
            }

            // 5. 壓縮圖片
            $compressed = $this->compressImage($image, $mimeType);

            // 6. 記錄處理結果
            $this->logProcessingResult(
                $originalSize,
                strlen($compressed),
                $originalWidth,
                $originalHeight,
                $newWidth,
                $newHeight
            );

            return [
                'data' => $compressed,
                'mime' => $mimeType, // 保留原始格式
                'size' => strlen($compressed),
                'width' => $newWidth,
                'height' => $newHeight,
            ];

        } finally {
            imagedestroy($image);
        }
    }

    /**
     * 壓縮圖片
     *
     * 根據 MIME type 選擇適當的壓縮策略
     *
     * @param \GdImage $image GD 圖片資源
     * @param string $mimeType MIME type
     * @return string 壓縮後的二進制資料
     * @throws \Exception 壓縮失敗時
     */
    public function compressImage(\GdImage $image, string $mimeType): string
    {
        ob_start();

        switch ($mimeType) {
            case 'image/jpeg':
                $success = imagejpeg($image, null, self::COMPRESSION_QUALITY);
                break;

            case 'image/png':
                // PNG compression level: 0-9 (0=no compression, 9=max)
                // 8 is a good balance between size and speed
                $success = imagepng($image, null, 8);
                break;

            case 'image/webp':
                $success = imagewebp($image, null, self::COMPRESSION_QUALITY);
                break;

            case 'image/gif':
                $success = imagegif($image);
                break;

            default:
                // Fallback to JPEG
                $success = imagejpeg($image, null, self::COMPRESSION_QUALITY);
        }

        $compressed = ob_get_clean();

        if ($success === false || $compressed === false) {
            throw new \Exception('Failed to compress image');
        }

        return $compressed;
    }

    /**
     * 調整圖片大小
     *
     * 保持寬高比，並處理透明背景 (PNG/GIF)
     *
     * @param \GdImage $image 原始 GD 圖片資源
     * @param int $newWidth 新寬度
     * @param int $newHeight 新高度
     * @param string $mimeType MIME type
     * @return \GdImage 調整後的 GD 圖片資源
     * @throws \Exception 調整失敗時
     */
    public function resizeImage(
        \GdImage $image,
        int $newWidth,
        int $newHeight,
        string $mimeType
    ): \GdImage {
        if ($newWidth < 1 || $newHeight < 1) {
            throw new \Exception('Invalid dimensions for resize');
        }

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        if ($resized === false) {
            throw new \Exception('Failed to create resized image');
        }

        // 處理透明背景（PNG/GIF）
        if (in_array($mimeType, ['image/png', 'image/gif'], true)) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
            if ($transparent !== false) {
                imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
            }
        }

        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        // 高品質重新取樣
        $success = imagecopyresampled(
            $resized,
            $image,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );

        if ($success === false) {
            imagedestroy($resized);
            throw new \Exception('Failed to resample image');
        }

        // 釋放原始圖片資源
        imagedestroy($image);

        return $resized;
    }

    /**
     * 編碼圖片
     *
     * 根據 MIME type 編碼圖片並設定品質
     *
     * @param \GdImage $image GD 圖片資源
     * @param string $mimeType MIME type
     * @param int $quality 品質 (0-100)
     * @return string 編碼後的二進制資料
     * @throws \Exception 編碼失敗時
     */
    public function encodeImage(\GdImage $image, string $mimeType, int $quality): string
    {
        ob_start();

        switch ($mimeType) {
            case 'image/jpeg':
                $success = imagejpeg($image, null, $quality);
                break;

            case 'image/png':
                // PNG quality: convert 0-100 to 0-9
                $pngQuality = (int) round((100 - $quality) / 11.111);
                $success = imagepng($image, null, $pngQuality);
                break;

            case 'image/webp':
                $success = imagewebp($image, null, $quality);
                break;

            case 'image/gif':
                $success = imagegif($image);
                break;

            default:
                $success = imagejpeg($image, null, $quality);
        }

        $encoded = ob_get_clean();

        if ($success === false || $encoded === false) {
            throw new \Exception('Failed to encode image');
        }

        return $encoded;
    }

    /**
     * 計算新尺寸（保持寬高比）
     *
     * 如果圖片小於目標尺寸，則不放大
     * 如果圖片大於目標尺寸，則縮小並保持寬高比
     *
     * @param int $width 原始寬度
     * @param int $height 原始高度
     * @return array{0: int, 1: int} [newWidth, newHeight]
     */
    private function calculateNewDimensions(int $width, int $height): array
    {
        // 不需要調整
        if ($width <= self::TARGET_WIDTH && $height <= self::TARGET_HEIGHT) {
            return [$width, $height];
        }

        // 計算縮放比例（取最小值以確保不超出限制）
        $scale = min(
            self::TARGET_WIDTH / $width,
            self::TARGET_HEIGHT / $height
        );

        return [
            (int) round($width * $scale),
            (int) round($height * $scale),
        ];
    }

    /**
     * 記錄處理結果（用於監控）
     *
     * @param int $originalSize 原始檔案大小
     * @param int $compressedSize 壓縮後檔案大小
     * @param int $originalWidth 原始寬度
     * @param int $originalHeight 原始高度
     * @param int $newWidth 新寬度
     * @param int $newHeight 新高度
     * @return void
     */
    private function logProcessingResult(
        int $originalSize,
        int $compressedSize,
        int $originalWidth,
        int $originalHeight,
        int $newWidth,
        int $newHeight
    ): void {
        $compressionRatio = $originalSize > 0
            ? round((1 - $compressedSize / $originalSize) * 100, 2)
            : 0;

        Log::info('Avatar processed', [
            'original_size' => $originalSize,
            'compressed_size' => $compressedSize,
            'compression_ratio' => "{$compressionRatio}%",
            'original_dimensions' => "{$originalWidth}x{$originalHeight}",
            'new_dimensions' => "{$newWidth}x{$newHeight}",
        ]);
    }
}
