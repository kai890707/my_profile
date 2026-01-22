<?php

declare(strict_types=1);

use App\Services\AvatarService;
use App\Services\ImageProcessingService;

/**
 * AvatarService 單元測試
 *
 * 測試覆蓋：
 * - validateDataUrlFormat: valid/invalid 格式
 * - validateMimeType: 支援的格式 + SVG 被拒絕
 * - validateFileSize: within limit / exceeds limit
 * - validateImageContent: valid / corrupted / forged images
 * - processAvatar: 完整處理流程
 *
 * 目標：測試覆蓋率 >= 90%
 */

describe('AvatarService', function () {
    beforeEach(function () {
        $this->imageProcessor = Mockery::mock(ImageProcessingService::class);
        $this->service = new AvatarService($this->imageProcessor);
    });

    afterEach(function () {
        Mockery::close();
    });

    describe('validateDataUrlFormat', function () {
        it('accepts valid JPEG data URL', function () {
            $dataUrl = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2w==';

            expect($this->service->validateDataUrlFormat($dataUrl))->toBeTrue();
        });

        it('accepts valid PNG data URL', function () {
            $dataUrl = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

            expect($this->service->validateDataUrlFormat($dataUrl))->toBeTrue();
        });

        it('accepts valid WebP data URL', function () {
            $dataUrl = 'data:image/webp;base64,UklGRiQAAABXRUJQVlA4IBgAAAAwAQCdASoBAAEAAwA0JaQAA3AA/vuUAAA=';

            expect($this->service->validateDataUrlFormat($dataUrl))->toBeTrue();
        });

        it('accepts valid GIF data URL', function () {
            $dataUrl = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

            expect($this->service->validateDataUrlFormat($dataUrl))->toBeTrue();
        });

        it('rejects SVG data URL', function () {
            $dataUrl = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjwvc3ZnPg==';

            expect(fn() => $this->service->validateDataUrlFormat($dataUrl))
                ->toThrow(\InvalidArgumentException::class, '不支援的圖片格式');
        });

        it('rejects plain Base64 without data URL prefix', function () {
            $dataUrl = '/9j/4AAQSkZJRgABAQAAAQABAAD/2w==';

            expect(fn() => $this->service->validateDataUrlFormat($dataUrl))
                ->toThrow(\InvalidArgumentException::class);
        });

        it('rejects HTTP URL', function () {
            $dataUrl = 'http://example.com/avatar.jpg';

            expect(fn() => $this->service->validateDataUrlFormat($dataUrl))
                ->toThrow(\InvalidArgumentException::class);
        });

        it('rejects invalid format without base64 marker', function () {
            $dataUrl = 'data:image/jpeg,notbase64data';

            expect(fn() => $this->service->validateDataUrlFormat($dataUrl))
                ->toThrow(\InvalidArgumentException::class);
        });
    });

    describe('validateMimeType', function () {
        it('accepts image/jpeg', function () {
            expect($this->service->validateMimeType('image/jpeg'))->toBeTrue();
        });

        it('accepts image/png', function () {
            expect($this->service->validateMimeType('image/png'))->toBeTrue();
        });

        it('accepts image/webp', function () {
            expect($this->service->validateMimeType('image/webp'))->toBeTrue();
        });

        it('accepts image/gif', function () {
            expect($this->service->validateMimeType('image/gif'))->toBeTrue();
        });

        it('rejects image/svg+xml for security reasons', function () {
            expect(fn() => $this->service->validateMimeType('image/svg+xml'))
                ->toThrow(\InvalidArgumentException::class, '不支援的圖片格式 (image/svg+xml)');
        });

        it('rejects image/bmp', function () {
            expect(fn() => $this->service->validateMimeType('image/bmp'))
                ->toThrow(\InvalidArgumentException::class);
        });

        it('rejects application/pdf', function () {
            expect(fn() => $this->service->validateMimeType('application/pdf'))
                ->toThrow(\InvalidArgumentException::class);
        });
    });

    describe('validateFileSize', function () {
        it('accepts file within 2MB limit', function () {
            $data = str_repeat('a', 1024 * 1024); // 1MB

            expect($this->service->validateFileSize($data))->toBeTrue();
        });

        it('accepts file exactly at 2MB limit', function () {
            $data = str_repeat('a', 2097152); // Exactly 2MB

            expect($this->service->validateFileSize($data))->toBeTrue();
        });

        it('rejects file exceeding 2MB limit', function () {
            $data = str_repeat('a', 2097153); // 2MB + 1 byte

            expect(fn() => $this->service->validateFileSize($data))
                ->toThrow(\InvalidArgumentException::class, '圖片檔案過大');
        });

        it('rejects file much larger than limit', function () {
            $data = str_repeat('a', 5 * 1024 * 1024); // 5MB

            expect(fn() => $this->service->validateFileSize($data))
                ->toThrow(\InvalidArgumentException::class);
        });

        it('error message includes actual file size', function () {
            $data = str_repeat('a', 3 * 1024 * 1024); // 3MB

            try {
                $this->service->validateFileSize($data);
                expect(false)->toBeTrue(); // Should not reach here
            } catch (\InvalidArgumentException $e) {
                expect($e->getMessage())->toContain('3');
                expect($e->getMessage())->toContain('MB');
            }
        });
    });

    describe('validateImageContent', function () {
        it('accepts valid PNG image from GD', function () {
            // Create a minimal valid PNG using GD
            $image = imagecreatetruecolor(1, 1);
            ob_start();
            imagepng($image);
            $validPng = ob_get_clean();
            imagedestroy($image);

            expect($this->service->validateImageContent($validPng))->toBeTrue();
        });

        it('accepts valid PNG image', function () {
            // Minimal valid PNG (1x1 transparent pixel)
            $validPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==', true);

            expect($this->service->validateImageContent($validPng))->toBeTrue();
        });

        it('rejects text file pretending to be image', function () {
            $fakeImage = 'This is just plain text, not an image file';

            expect(fn() => $this->service->validateImageContent($fakeImage))
                ->toThrow(\InvalidArgumentException::class, '圖片檔案損壞或無效');
        });

        it('rejects corrupted JPEG', function () {
            $corruptedJpeg = "\xFF\xD8\xFF\xE0" . random_bytes(100); // Invalid JPEG header

            expect(fn() => $this->service->validateImageContent($corruptedJpeg))
                ->toThrow(\InvalidArgumentException::class);
        });

        it('rejects empty data', function () {
            expect(fn() => $this->service->validateImageContent(''))
                ->toThrow(\InvalidArgumentException::class);
        });

        it('rejects partial image data', function () {
            $partialJpeg = "\xFF\xD8\xFF"; // Only JPEG magic bytes

            expect(fn() => $this->service->validateImageContent($partialJpeg))
                ->toThrow(\InvalidArgumentException::class);
        });
    });

    describe('processAvatar', function () {
        it('processes valid avatar successfully', function () {
            // Create a valid 1x1 PNG
            $image = imagecreatetruecolor(1, 1);
            ob_start();
            imagepng($image);
            $validPng = ob_get_clean();
            imagedestroy($image);

            $dataUrl = 'data:image/png;base64,' . base64_encode($validPng);

            $this->imageProcessor->shouldReceive('processImage')
                ->once()
                ->with($validPng, 'image/png')
                ->andReturn([
                    'data' => $validPng,
                    'mime' => 'image/png',
                    'size' => strlen($validPng),
                    'width' => 1,
                    'height' => 1,
                ]);

            $result = $this->service->processAvatar($dataUrl);

            expect($result)->toHaveKeys(['avatar_data', 'avatar_mime', 'avatar_size']);
            expect($result['avatar_mime'])->toBe('image/png');
        });

        it('throws exception for invalid format', function () {
            $invalidDataUrl = 'not-a-data-url';

            expect(fn() => $this->service->processAvatar($invalidDataUrl))
                ->toThrow(\InvalidArgumentException::class);
        });

        it('throws exception for unsupported MIME type', function () {
            $svgDataUrl = 'data:image/svg+xml;base64,PHN2Zz48L3N2Zz4=';

            expect(fn() => $this->service->processAvatar($svgDataUrl))
                ->toThrow(\InvalidArgumentException::class);
        });

        it('throws exception for oversized file', function () {
            $largeData = str_repeat('a', 3 * 1024 * 1024); // 3MB
            $dataUrl = 'data:image/jpeg;base64,' . base64_encode($largeData);

            expect(fn() => $this->service->processAvatar($dataUrl))
                ->toThrow(\InvalidArgumentException::class, '圖片檔案過大');
        });

        it('throws exception for corrupted image', function () {
            $corruptedData = 'corrupted-image-data';
            $dataUrl = 'data:image/jpeg;base64,' . base64_encode($corruptedData);

            expect(fn() => $this->service->processAvatar($dataUrl))
                ->toThrow(\InvalidArgumentException::class);
        });
    });

    describe('clearAvatar', function () {
        it('returns array with null values', function () {
            $result = $this->service->clearAvatar();

            expect($result)->toBe([
                'avatar_data' => null,
                'avatar_mime' => null,
                'avatar_size' => null,
            ]);
        });
    });
});
