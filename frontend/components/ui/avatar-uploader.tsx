'use client';

import { useState, useRef, ChangeEvent } from 'react';
import { Avatar } from './avatar';
import { Button } from './button';
import { Camera, ArrowRight, Loader2, CheckCircle2 } from 'lucide-react';
import {
  processImageUpload,
  formatFileSize,
  getBase64Size,
  calculateCompressionRate,
} from '@/lib/utils/image';
import { toast } from 'sonner';
import { cn } from '@/lib/utils';

interface AvatarUploaderProps {
  // 當前頭像
  currentAvatar?: string | null;
  currentFallback?: string;

  // 回調函數
  onUploadStart?: () => void;
  onUploadComplete?: (base64: string) => void;
  onUploadError?: (error: Error) => void;
  onCancel?: () => void;

  // 上傳配置
  maxSizeMB?: number; // 預設 2MB
  allowedTypes?: string[]; // 預設 ['image/jpeg', 'image/png', 'image/webp', 'image/gif']
  targetSize?: number; // 壓縮目標尺寸，預設 400px

  // UI 配置
  size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl'; // Avatar 尺寸，預設 '2xl'
  showComparison?: boolean; // 顯示對比（當前 vs 新），預設 true
  showFileInfo?: boolean; // 顯示檔案資訊，預設 true

  // 樣式
  className?: string;
  disabled?: boolean;
}

export function AvatarUploader({
  currentAvatar,
  currentFallback,
  onUploadStart,
  onUploadComplete,
  onUploadError,
  onCancel,
  maxSizeMB = 2,
  allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
  size = '2xl',
  showComparison = true,
  showFileInfo = true,
  className,
  disabled = false,
}: AvatarUploaderProps) {
  // 狀態管理
  const [preview, setPreview] = useState<string | null>(null);
  const [isProcessing, setIsProcessing] = useState(false);
  const [fileInfo, setFileInfo] = useState<{
    name: string;
    originalSize: number;
    compressedSize: number;
  } | null>(null);

  const fileInputRef = useRef<HTMLInputElement>(null);

  // 處理檔案選擇
  const handleFileChange = async (e: ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    try {
      setIsProcessing(true);
      onUploadStart?.();

      const originalSize = file.size;

      // 顯示載入提示
      const loadingToast = toast.loading('處理圖片中...');

      // 處理圖片（驗證 + 壓縮 + 轉 Base64）
      const base64String = await processImageUpload(file, maxSizeMB);

      // 計算壓縮後大小（從 Base64 估算）
      const compressedSize = getBase64Size(base64String);

      // 更新狀態
      setPreview(base64String);
      setFileInfo({
        name: file.name,
        originalSize,
        compressedSize,
      });

      // 關閉載入提示
      toast.dismiss(loadingToast);

      // 顯示成功訊息
      const savedPercent = calculateCompressionRate(originalSize, compressedSize);

      toast.success('圖片已處理', {
        description: `已壓縮 ${savedPercent}% (節省 ${formatFileSize(originalSize - compressedSize)})`,
      });

      // 通知上層
      onUploadComplete?.(base64String);
    } catch (error) {
      // 錯誤處理
      const errorMessage =
        error instanceof Error ? error.message : '處理圖片失敗';
      toast.error(errorMessage);
      onUploadError?.(error as Error);

      // 清除檔案選擇
      if (fileInputRef.current) {
        fileInputRef.current.value = '';
      }
    } finally {
      setIsProcessing(false);
    }
  };

  // 重置狀態
  const handleCancel = () => {
    setPreview(null);
    setFileInfo(null);
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
    onCancel?.();
  };

  return (
    <div className={cn('space-y-4', className)}>
      {/* Avatar 與上傳按鈕 */}
      {!preview ? (
        // 初始狀態
        <div className="space-y-2">
          <div className="relative inline-block">
            <Avatar src={currentAvatar} fallback={currentFallback} size={size} />
            <button
              type="button"
              onClick={() => fileInputRef.current?.click()}
              disabled={disabled || isProcessing}
              className={cn(
                'absolute bottom-0 right-0',
                'p-2 rounded-full',
                'bg-primary-600 text-white',
                'shadow-md',
                'hover:bg-primary-700 hover:scale-110',
                'transition-all duration-200',
                'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2',
                'disabled:opacity-50 disabled:cursor-not-allowed'
              )}
              aria-label="更換頭像"
            >
              <Camera className="h-4 w-4" />
            </button>
          </div>

          <div>
            <p className="text-sm text-slate-600">點擊相機圖示上傳頭像</p>
            <p className="text-xs text-slate-500">
              支援 JPG、PNG、WebP、GIF，最大 {maxSizeMB}MB
            </p>
          </div>

          <input
            ref={fileInputRef}
            type="file"
            accept={allowedTypes.join(',')}
            className="hidden"
            onChange={handleFileChange}
            disabled={disabled || isProcessing}
            aria-label="選擇頭像檔案"
          />
        </div>
      ) : (
        // 預覽狀態
        <div className="space-y-4">
          {/* 對比顯示 */}
          {showComparison && (
            <div className="flex items-center gap-6">
              {/* 當前頭像 */}
              <div className="text-center">
                <p className="text-xs text-slate-500 mb-2">目前頭像</p>
                <Avatar
                  src={currentAvatar}
                  fallback={currentFallback}
                  size={size}
                />
              </div>

              {/* 箭頭 */}
              <div className="text-slate-400">
                <ArrowRight className="h-6 w-6" />
              </div>

              {/* 新頭像 */}
              <div className="text-center">
                <p className="text-xs text-slate-500 mb-2">新頭像</p>
                <Avatar src={preview} size={size} />
              </div>
            </div>
          )}

          {/* 檔案資訊 */}
          {showFileInfo && fileInfo && (
            <div className="bg-slate-50 rounded-lg p-4 space-y-2">
              <div className="flex items-center justify-between text-sm">
                <span className="text-slate-600">檔案名稱</span>
                <span className="text-slate-900 font-medium truncate max-w-[200px]">
                  {fileInfo.name}
                </span>
              </div>
              <div className="flex items-center justify-between text-sm">
                <span className="text-slate-600">原始大小</span>
                <span className="text-slate-900">
                  {formatFileSize(fileInfo.originalSize)}
                </span>
              </div>
              <div className="flex items-center justify-between text-sm">
                <span className="text-slate-600">壓縮後</span>
                <span className="text-slate-900 font-medium">
                  {formatFileSize(fileInfo.compressedSize)}
                </span>
              </div>
            </div>
          )}

          {/* 壓縮提示 */}
          {fileInfo && fileInfo.compressedSize < fileInfo.originalSize && (
            <div className="bg-primary-50 border border-primary-200 rounded-lg p-4">
              <div className="flex items-start gap-3">
                <CheckCircle2 className="h-5 w-5 text-primary-600 flex-shrink-0 mt-0.5" />
                <div className="flex-1">
                  <p className="text-sm font-medium text-primary-900">
                    圖片已壓縮
                  </p>
                  <p className="text-xs text-primary-700 mt-1">
                    節省{' '}
                    {formatFileSize(
                      fileInfo.originalSize - fileInfo.compressedSize
                    )}{' '}
                    (
                    {calculateCompressionRate(
                      fileInfo.originalSize,
                      fileInfo.compressedSize
                    )}
                    %)
                  </p>
                </div>
              </div>
            </div>
          )}

          {/* 操作按鈕 */}
          <div className="flex gap-3">
            <Button
              type="button"
              variant="outline"
              onClick={handleCancel}
              disabled={disabled || isProcessing}
            >
              取消
            </Button>
            <Button
              type="button"
              onClick={() => fileInputRef.current?.click()}
              disabled={disabled || isProcessing}
            >
              重新選擇
            </Button>
          </div>
        </div>
      )}

      {/* 處理中提示 */}
      {isProcessing && (
        <div className="flex items-center gap-3 text-sm text-slate-600">
          <Loader2 className="h-4 w-4 animate-spin" />
          <span>處理圖片中...</span>
        </div>
      )}
    </div>
  );
}
