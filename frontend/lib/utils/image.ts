/**
 * 圖片上傳工具函數
 * 提供圖片驗證、壓縮和 Base64 轉換功能
 */

// 支援的圖片格式
const SUPPORTED_IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

// 預設最大檔案大小 (2MB)
const DEFAULT_MAX_SIZE_MB = 2;

/**
 * 驗證圖片檔案
 * @param file - 要驗證的檔案
 * @param maxSizeMB - 最大檔案大小 (MB)，預設 2MB
 * @returns 驗證結果物件
 */
export interface ImageValidationResult {
  valid: boolean;
  error?: string;
}

export function validateImage(
  file: File,
  maxSizeMB: number = DEFAULT_MAX_SIZE_MB
): ImageValidationResult {
  // 檢查檔案是否存在
  if (!file) {
    return {
      valid: false,
      error: '請選擇檔案',
    };
  }

  // 檢查檔案類型
  if (!SUPPORTED_IMAGE_TYPES.includes(file.type)) {
    return {
      valid: false,
      error: '不支援的檔案格式，請上傳 JPG、PNG 或 WebP 格式',
    };
  }

  // 檢查檔案大小
  const maxSizeBytes = maxSizeMB * 1024 * 1024;
  if (file.size > maxSizeBytes) {
    return {
      valid: false,
      error: `檔案大小不能超過 ${maxSizeMB}MB`,
    };
  }

  return {
    valid: true,
  };
}

/**
 * 將檔案轉換為 Base64 字串
 * @param file - 要轉換的檔案
 * @returns Promise<string> - Base64 字串
 */
export function fileToBase64(file: File): Promise<string> {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();

    reader.onloadend = () => {
      if (typeof reader.result === 'string') {
        resolve(reader.result);
      } else {
        reject(new Error('無法讀取檔案'));
      }
    };

    reader.onerror = () => {
      reject(new Error('讀取檔案時發生錯誤'));
    };

    reader.readAsDataURL(file);
  });
}

/**
 * 壓縮圖片
 * @param file - 要壓縮的圖片檔案
 * @param maxSizeMB - 目標最大檔案大小 (MB)
 * @param quality - 壓縮品質 (0-1)，預設 0.8
 * @returns Promise<File> - 壓縮後的檔案
 */
export function compressImage(
  file: File,
  maxSizeMB: number = DEFAULT_MAX_SIZE_MB,
  quality: number = 0.8
): Promise<File> {
  return new Promise((resolve, reject) => {
    // 如果檔案已經小於目標大小，直接返回
    if (file.size <= maxSizeMB * 1024 * 1024) {
      resolve(file);
      return;
    }

    const reader = new FileReader();
    reader.readAsDataURL(file);

    reader.onload = (event) => {
      const img = new Image();
      img.src = event.target?.result as string;

      img.onload = () => {
        const canvas = document.createElement('canvas');
        let width = img.width;
        let height = img.height;

        // 計算縮放比例
        const maxDimension = 2048; // 最大寬度或高度
        if (width > height && width > maxDimension) {
          height = (height * maxDimension) / width;
          width = maxDimension;
        } else if (height > maxDimension) {
          width = (width * maxDimension) / height;
          height = maxDimension;
        }

        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d');
        if (!ctx) {
          reject(new Error('無法建立 Canvas Context'));
          return;
        }

        ctx.drawImage(img, 0, 0, width, height);

        // 轉換為 Blob
        canvas.toBlob(
          (blob) => {
            if (!blob) {
              reject(new Error('壓縮圖片失敗'));
              return;
            }

            // 如果壓縮後仍然太大，降低品質重試
            if (blob.size > maxSizeMB * 1024 * 1024 && quality > 0.5) {
              compressImage(file, maxSizeMB, quality - 0.1)
                .then(resolve)
                .catch(reject);
              return;
            }

            // 建立新的 File 物件
            const compressedFile = new File([blob], file.name, {
              type: file.type,
              lastModified: Date.now(),
            });

            resolve(compressedFile);
          },
          file.type,
          quality
        );
      };

      img.onerror = () => {
        reject(new Error('載入圖片失敗'));
      };
    };

    reader.onerror = () => {
      reject(new Error('讀取檔案失敗'));
    };
  });
}

/**
 * 處理圖片上傳（驗證 + 壓縮 + 轉 Base64）
 * @param file - 要處理的圖片檔案
 * @param maxSizeMB - 最大檔案大小 (MB)
 * @returns Promise<string> - Base64 字串
 */
export async function processImageUpload(
  file: File,
  maxSizeMB: number = DEFAULT_MAX_SIZE_MB
): Promise<string> {
  // 1. 驗證圖片
  const validation = validateImage(file, maxSizeMB * 2); // 允許稍大的原始檔案
  if (!validation.valid) {
    throw new Error(validation.error);
  }

  // 2. 壓縮圖片
  const compressedFile = await compressImage(file, maxSizeMB);

  // 3. 轉換為 Base64
  const base64 = await fileToBase64(compressedFile);

  return base64;
}

/**
 * 從 Base64 字串取得檔案大小 (bytes)
 * @param base64 - Base64 字串
 * @returns 檔案大小 (bytes)
 */
export function getBase64Size(base64: string): number {
  // 移除 data:image/xxx;base64, 前綴
  const base64Data = base64.split(',')[1] || base64;

  // Base64 每 4 個字元代表 3 bytes
  // 如果有 padding (=)，需要扣除
  const padding = (base64Data.match(/=/g) || []).length;
  return (base64Data.length * 3) / 4 - padding;
}

/**
 * 格式化檔案大小顯示
 * @param bytes - 檔案大小 (bytes)
 * @returns 格式化後的字串 (例: "1.5 MB")
 */
export function formatFileSize(bytes: number): string {
  if (bytes === 0) return '0 Bytes';

  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));

  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
}
