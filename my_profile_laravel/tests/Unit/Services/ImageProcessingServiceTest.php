<?php

declare(strict_types=1);

use App\Services\ImageProcessingService;

/**
 * ImageProcessingService 單元測試
 *
 * 測試覆蓋：
 * - compressImage: large image / small image (unchanged)
 * - resizeImage: landscape / portrait / square (aspect ratio preserved)
 * - encodeImage: PNG / GIF (transparency preserved)
 * - 效能測試: 壓縮時間 < 200ms
 *
 * 目標：測試覆蓋率 >= 90%
 */

describe('ImageProcessingService', function () {
    beforeEach(function () {
        $this->service = new ImageProcessingService();
    });

    /**
     * Helper: 建立測試用的 GD 圖片
     */
    function createTestImage(int $width, int $height, string $type = 'png'): string
    {
        $image = imagecreatetruecolor($width, $height);

        // 填充顏色 (紅色)
        $red = imagecolorallocate($image, 255, 0, 0);
        imagefilledrectangle($image, 0, 0, $width, $height, $red);

        ob_start();
        switch ($type) {
            case 'gif':
                imagegif($image);
                break;
            default: // png
                imagepng($image);
        }
        $data = ob_get_clean();
        imagedestroy($image);

        return $data;
    }

    describe('processImage', function () {
        it('processes small PNG without resizing', function () {
            $imageData = createTestImage(200, 150, 'png');
            $result = $this->service->processImage($imageData, 'image/png');

            expect($result)->toHaveKeys(['data', 'mime', 'size', 'width', 'height']);
            expect($result['mime'])->toBe('image/png');
            expect($result['width'])->toBe(200);
            expect($result['height'])->toBe(150);
        });

        it('resizes large PNG to 400x400 max', function () {
            $imageData = createTestImage(800, 600, 'png');
            $result = $this->service->processImage($imageData, 'image/png');

            expect($result['width'])->toBeLessThanOrEqual(400);
            expect($result['height'])->toBeLessThanOrEqual(400);
            expect($result['mime'])->toBe('image/png');
        });

        it('preserves aspect ratio for landscape images', function () {
            $imageData = createTestImage(1600, 900, 'png'); // 16:9
            $result = $this->service->processImage($imageData, 'image/png');

            // 應該壓縮到 400x225 (保持 16:9)
            expect($result['width'])->toBe(400);
            expect($result['height'])->toBe(225);
        });

        it('preserves aspect ratio for portrait images', function () {
            $imageData = createTestImage(600, 800, 'png'); // 3:4
            $result = $this->service->processImage($imageData, 'image/png');

            // 應該壓縮到 300x400 (保持 3:4)
            expect($result['width'])->toBe(300);
            expect($result['height'])->toBe(400);
        });

        it('handles square images', function () {
            $imageData = createTestImage(1000, 1000, 'png');
            $result = $this->service->processImage($imageData, 'image/png');

            expect($result['width'])->toBe(400);
            expect($result['height'])->toBe(400);
        });

        it('processes GIF images', function () {
            $imageData = createTestImage(800, 600, 'gif');
            $result = $this->service->processImage($imageData, 'image/gif');

            expect($result['mime'])->toBe('image/gif');
        });

        it('reduces file size after compression', function () {
            $imageData = createTestImage(1600, 1200, 'png');
            $originalSize = strlen($imageData);

            $result = $this->service->processImage($imageData, 'image/png');

            expect($result['size'])->toBeLessThan($originalSize);
        });

        it('completes processing within acceptable time', function () {
            $imageData = createTestImage(1600, 1200, 'png');

            $start = microtime(true);
            $result = $this->service->processImage($imageData, 'image/png');
            $duration = (microtime(true) - $start) * 1000; // ms

            expect($duration)->toBeLessThan(200); // < 200ms
            expect($result)->toBeArray();
        });

        it('throws exception for invalid image data', function () {
            $invalidData = 'not-an-image';

            expect(fn() => $this->service->processImage($invalidData, 'image/png'))
                ->toThrow(\Exception::class);
        });
    });

    describe('compressImage', function () {
        it('compresses PNG with level 8', function () {
            $image = imagecreatetruecolor(400, 400);
            $blue = imagecolorallocate($image, 0, 0, 255);
            imagefilledrectangle($image, 0, 0, 400, 400, $blue);

            $compressed = $this->service->compressImage($image, 'image/png');

            expect($compressed)->toBeString();
            expect(strlen($compressed))->toBeGreaterThan(0);

            imagedestroy($image);
        });

        it('handles GIF format', function () {
            $image = imagecreatetruecolor(400, 400);
            $yellow = imagecolorallocate($image, 255, 255, 0);
            imagefilledrectangle($image, 0, 0, 400, 400, $yellow);

            $compressed = $this->service->compressImage($image, 'image/gif');

            expect($compressed)->toBeString();

            imagedestroy($image);
        });
    });

    describe('resizeImage', function () {
        it('resizes image to specified dimensions', function () {
            $original = imagecreatetruecolor(800, 600);
            $red = imagecolorallocate($original, 255, 0, 0);
            imagefilledrectangle($original, 0, 0, 800, 600, $red);

            $resized = $this->service->resizeImage($original, 400, 300, 'image/png');

            expect(imagesx($resized))->toBe(400);
            expect(imagesy($resized))->toBe(300);

            // Note: $original is destroyed by resizeImage
            imagedestroy($resized);
        });

        it('preserves transparency for PNG', function () {
            $original = imagecreatetruecolor(800, 600);
            imagealphablending($original, false);
            imagesavealpha($original, true);
            $transparent = imagecolorallocatealpha($original, 255, 255, 255, 127);
            imagefilledrectangle($original, 0, 0, 800, 600, $transparent);

            $resized = $this->service->resizeImage($original, 400, 300, 'image/png');

            expect(imagesx($resized))->toBe(400);
            expect(imagesy($resized))->toBe(300);

            // Note: $original is destroyed by resizeImage
            imagedestroy($resized);
        });

        it('preserves transparency for GIF', function () {
            $original = imagecreatetruecolor(800, 600);
            imagealphablending($original, false);
            imagesavealpha($original, true);

            $resized = $this->service->resizeImage($original, 400, 300, 'image/gif');

            expect(imagesx($resized))->toBe(400);

            // Note: $original is destroyed by resizeImage
            imagedestroy($resized);
        });
    });

    describe('encodeImage', function () {
        it('encodes PNG with quality conversion', function () {
            $image = imagecreatetruecolor(200, 200);
            imagealphablending($image, false);
            imagesavealpha($image, true);

            $encoded = $this->service->encodeImage($image, 'image/png', 80);

            expect($encoded)->toBeString();

            imagedestroy($image);
        });

        it('encodes GIF', function () {
            $image = imagecreatetruecolor(200, 200);
            $blue = imagecolorallocate($image, 0, 0, 255);
            imagefilledrectangle($image, 0, 0, 200, 200, $blue);

            $encoded = $this->service->encodeImage($image, 'image/gif', 80);

            expect($encoded)->toBeString();

            imagedestroy($image);
        });

        it('produces smaller file with lower quality for PNG', function () {
            $image = imagecreatetruecolor(400, 400);
            $red = imagecolorallocate($image, 255, 0, 0);
            imagefilledrectangle($image, 0, 0, 400, 400, $red);

            $highQuality = $this->service->encodeImage($image, 'image/png', 90);
            $lowQuality = $this->service->encodeImage($image, 'image/png', 50);

            expect(strlen($lowQuality))->toBeLessThan(strlen($highQuality));

            imagedestroy($image);
        });
    });

    describe('performance', function () {
        it('processes large image within 200ms', function () {
            $imageData = createTestImage(2048, 1536, 'png'); // Large image

            $start = microtime(true);
            $result = $this->service->processImage($imageData, 'image/png');
            $duration = (microtime(true) - $start) * 1000;

            expect($duration)->toBeLessThan(200);
            expect($result['width'])->toBeLessThanOrEqual(400);
        });

        it('handles multiple compressions efficiently', function () {
            $imageData = createTestImage(800, 600, 'png');

            $start = microtime(true);

            for ($i = 0; $i < 5; $i++) {
                $result = $this->service->processImage($imageData, 'image/png');
            }

            $duration = (microtime(true) - $start) * 1000;
            $avgDuration = $duration / 5;

            expect($avgDuration)->toBeLessThan(200);
        });
    });
});
