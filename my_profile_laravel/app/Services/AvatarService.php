<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Avatar Service
 *
 * 負責 Avatar 驗證、處理和儲存邏輯
 *
 * 驗證規則：
 * - VR-001: Data URL 格式驗證
 * - VR-002: MIME Type 白名單驗證 (禁止 SVG)
 * - VR-003: Base64 strict mode 解碼驗證
 * - VR-004: 檔案大小 <= 2MB (2,097,152 bytes)
 * - VR-005: GD imagecreatefromstring() 內容驗證
 *
 * @package App\Services
 */
class AvatarService
{
    // 常數定義
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    private const MAX_FILE_SIZE = 2097152; // 2MB in bytes
    private const DATA_URL_PATTERN = '/^data:image\/(jpeg|png|webp|gif);base64,[A-Za-z0-9+\/]+=*$/';

    /**
     * AvatarService constructor
     *
     * @param ImageProcessingService $imageProcessor 圖片處理服務
     */
    public function __construct(
        private readonly ImageProcessingService $imageProcessor
    ) {}

    /**
     * 處理 Avatar 上傳
     *
     * 完整流程：
     * 1. 驗證 data URL 格式
     * 2. 提取並驗證 MIME type
     * 3. 解碼 Base64 資料
     * 4. 驗證檔案大小
     * 5. 驗證圖片內容
     * 6. 處理圖片（壓縮、調整大小）
     *
     * @param string $dataUrl Data URL 格式的圖片 (e.g., data:image/jpeg;base64,...)
     * @return array{avatar_data: string, avatar_mime: string, avatar_size: int} 處理後的 Avatar 資料
     * @throws \InvalidArgumentException 驗證失敗時
     * @throws \Exception 處理失敗時
     */
    public function processAvatar(string $dataUrl): array
    {
        // 1. 驗證 data URL 格式
        $this->validateDataUrlFormat($dataUrl);

        // 2. 提取 MIME type 和 Base64 資料
        [$mimeType, $base64Data] = $this->parseDataUrl($dataUrl);

        // 3. 驗證 MIME type
        $this->validateMimeType($mimeType);

        // 4. 解碼 Base64
        $decoded = $this->decodeBase64($base64Data);

        // 5. 驗證檔案大小
        $this->validateFileSize($decoded);

        // 6. 驗證圖片內容
        $this->validateImageContent($decoded);

        // 7. 處理圖片（壓縮、調整大小）
        $processed = $this->imageProcessor->processImage($decoded, $mimeType);

        // 8. 回傳處理後的資料
        return [
            'avatar_data' => $processed['data'],
            'avatar_mime' => $processed['mime'],
            'avatar_size' => $processed['size'],
        ];
    }

    /**
     * VR-001: 驗證 Data URL 格式
     *
     * 必須符合格式：data:image/(jpeg|png|webp|gif);base64,{base64_data}
     *
     * @param string $dataUrl Data URL 字串
     * @return bool 驗證是否通過
     * @throws \InvalidArgumentException 格式錯誤時
     */
    public function validateDataUrlFormat(string $dataUrl): bool
    {
        if (!preg_match(self::DATA_URL_PATTERN, $dataUrl)) {
            throw new \InvalidArgumentException(
                '不支援的圖片格式，請選擇 JPG、PNG、WebP 或 GIF 圖片'
            );
        }

        return true;
    }

    /**
     * VR-002: 驗證 MIME Type 白名單
     *
     * 只接受：image/jpeg, image/png, image/webp, image/gif
     * 禁止 SVG (防止 XSS 攻擊)
     *
     * @param string $mimeType MIME type 字串
     * @return bool 驗證是否通過
     * @throws \InvalidArgumentException MIME type 不在白名單時
     */
    public function validateMimeType(string $mimeType): bool
    {
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new \InvalidArgumentException(
                "不支援的圖片格式 ({$mimeType})，請選擇 JPG、PNG、WebP 或 GIF"
            );
        }

        return true;
    }

    /**
     * VR-004: 驗證檔案大小
     *
     * 解碼後的檔案大小必須 <= 2MB (2,097,152 bytes)
     *
     * @param string $data 二進制圖片資料
     * @return bool 驗證是否通過
     * @throws \InvalidArgumentException 檔案過大時
     */
    public function validateFileSize(string $data): bool
    {
        $size = strlen($data);

        if ($size > self::MAX_FILE_SIZE) {
            $sizeMB = round($size / 1024 / 1024, 1);
            throw new \InvalidArgumentException(
                "圖片檔案過大（{$sizeMB} MB），請選擇小於 2MB 的圖片"
            );
        }

        return true;
    }

    /**
     * VR-005: 驗證圖片內容
     *
     * 使用 GD imagecreatefromstring() 驗證圖片是否有效
     * 防止檔案偽造攻擊 (例如將 .txt 改名為 .jpg)
     *
     * @param string $data 二進制圖片資料
     * @return bool 驗證是否通過
     * @throws \InvalidArgumentException 圖片無效或損壞時
     */
    public function validateImageContent(string $data): bool
    {
        try {
            // 使用 GD 解析圖片
            $image = @imagecreatefromstring($data);

            if ($image === false) {
                throw new \InvalidArgumentException(
                    '圖片檔案損壞或無效，請選擇其他圖片'
                );
            }

            // 清理資源
            imagedestroy($image);

            return true;

        } catch (\InvalidArgumentException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException('圖片驗證失敗：' . $e->getMessage());
        }
    }

    /**
     * 清除 Avatar 資料
     *
     * 用於移除使用者的 Avatar
     *
     * @return array{avatar_data: null, avatar_mime: null, avatar_size: null}
     */
    public function clearAvatar(): array
    {
        return [
            'avatar_data' => null,
            'avatar_mime' => null,
            'avatar_size' => null,
        ];
    }

    /**
     * 解析 Data URL，提取 MIME type 和 Base64 資料
     *
     * @param string $dataUrl Data URL 字串
     * @return array{0: string, 1: string} [mimeType, base64Data]
     * @throws \InvalidArgumentException 解析失敗時
     */
    private function parseDataUrl(string $dataUrl): array
    {
        if (!preg_match('/^data:(image\/[a-z]+);base64,(.+)$/s', $dataUrl, $matches)) {
            throw new \InvalidArgumentException('圖片資料格式錯誤');
        }

        return [$matches[1], $matches[2]];
    }

    /**
     * VR-003: 解碼 Base64 資料 (strict mode)
     *
     * @param string $base64Data Base64 編碼的字串
     * @return string 解碼後的二進制資料
     * @throws \InvalidArgumentException 解碼失敗時
     */
    private function decodeBase64(string $base64Data): string
    {
        // 使用 strict mode 確保嚴格驗證
        $decoded = base64_decode($base64Data, true);

        if ($decoded === false) {
            throw new \InvalidArgumentException('圖片資料格式錯誤，無法解碼');
        }

        return $decoded;
    }
}
