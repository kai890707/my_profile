'use client';

import { useState, useRef, ChangeEvent } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { X, Upload, Image as ImageIcon } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { processImageUpload, formatFileSize } from '@/lib/utils/image';
import { toast } from 'sonner';

// 表單驗證 Schema
const certificationSchema = z.object({
  name: z.string().min(2, '證照名稱至少需要 2 個字元').max(100, '證照名稱不能超過 100 個字元'),
  issuer: z.string().min(2, '發證單位至少需要 2 個字元').max(100, '發證單位不能超過 100 個字元'),
  issue_date: z.string().min(1, '請選擇發證日期'),
  expiry_date: z.string().optional().or(z.literal('')),
  description: z.string().max(500, '說明不能超過 500 個字元').optional().or(z.literal('')),
  certificate_image: z.string().min(1, '請上傳證照圖片'),
}).refine((data) => {
  // 如果有到期日，檢查是否晚於發證日期
  if (data.expiry_date && data.issue_date) {
    return new Date(data.expiry_date) >= new Date(data.issue_date);
  }
  return true;
}, {
  message: '到期日期必須晚於或等於發證日期',
  path: ['expiry_date'],
});

type CertificationFormData = z.infer<typeof certificationSchema>;

interface CertificationUploadModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (data: CertificationFormData) => void;
  isLoading?: boolean;
}

export function CertificationUploadModal({
  isOpen,
  onClose,
  onSubmit,
  isLoading = false,
}: CertificationUploadModalProps) {
  const [imagePreview, setImagePreview] = useState<string | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const {
    register,
    handleSubmit,
    formState: { errors },
    reset,
    setValue,
  } = useForm<CertificationFormData>({
    resolver: zodResolver(certificationSchema),
    defaultValues: {
      name: '',
      issuer: '',
      issue_date: '',
      expiry_date: '',
      description: '',
      certificate_image: '',
    },
  });

  // 處理圖片上傳
  const handleImageChange = async (e: ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    try {
      // 顯示載入提示
      const loadingToast = toast.loading('處理證照圖片中...');

      // 使用圖片工具處理（驗證 + 壓縮 + 轉 Base64）
      const base64String = await processImageUpload(file, 3); // 證照圖片最大 3MB

      // 更新預覽和表單
      setImagePreview(base64String);
      setValue('certificate_image', base64String);

      // 關閉載入提示並顯示成功訊息
      toast.dismiss(loadingToast);
      toast.success(`證照圖片已處理（${formatFileSize(file.size)}）`);
    } catch (error) {
      // 顯示錯誤訊息
      toast.error(error instanceof Error ? error.message : '處理圖片失敗');

      // 清除檔案選擇
      if (fileInputRef.current) {
        fileInputRef.current.value = '';
      }
    }
  };

  const handleFormSubmit = (data: CertificationFormData) => {
    onSubmit(data);
  };

  const handleClose = () => {
    reset();
    setImagePreview(null);
    onClose();
  };

  if (!isOpen) return null;

  return (
    <>
      {/* 遮罩層 */}
      <div
        className="fixed inset-0 bg-black/50 z-40 transition-opacity"
        onClick={handleClose}
      />

      {/* Modal */}
      <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div
          className="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
          onClick={(e) => e.stopPropagation()}
        >
          {/* Header */}
          <div className="flex items-center justify-between p-6 border-b border-slate-200">
            <h2 className="text-2xl font-bold text-slate-900">上傳專業證照</h2>
            <button
              onClick={handleClose}
              className="p-2 hover:bg-slate-100 rounded-lg transition-colors"
              disabled={isLoading}
            >
              <X className="h-5 w-5 text-slate-500" />
            </button>
          </div>

          {/* Body */}
          <form onSubmit={handleSubmit(handleFormSubmit)} className="p-6 space-y-4">
            <Input
              label="證照名稱"
              type="text"
              placeholder="例如：PMP 專案管理師"
              error={errors.name?.message}
              required
              disabled={isLoading}
              {...register('name')}
            />

            <Input
              label="發證單位"
              type="text"
              placeholder="例如：PMI"
              error={errors.issuer?.message}
              required
              disabled={isLoading}
              {...register('issuer')}
            />

            <div className="grid md:grid-cols-2 gap-4">
              <Input
                label="發證日期"
                type="date"
                error={errors.issue_date?.message}
                required
                disabled={isLoading}
                {...register('issue_date')}
              />

              <Input
                label="到期日期"
                type="date"
                helperText="選填，若證照無期限可留空"
                error={errors.expiry_date?.message}
                disabled={isLoading}
                {...register('expiry_date')}
              />
            </div>

            {/* 說明 */}
            <div className="space-y-2">
              <label className="text-sm font-semibold text-slate-700">
                說明
              </label>
              <textarea
                className="flex w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-2 text-base placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 hover:border-slate-300 transition-all duration-200 resize-none disabled:opacity-50 disabled:bg-slate-50"
                rows={3}
                placeholder="簡述此證照的相關資訊..."
                disabled={isLoading}
                {...register('description')}
              />
              {errors.description && (
                <p className="text-sm text-red-600">{errors.description.message}</p>
              )}
            </div>

            {/* 證照圖片上傳 */}
            <div className="space-y-2">
              <label className="text-sm font-semibold text-slate-700">
                證照圖片
                <span className="text-red-500 ml-1">*</span>
              </label>

              {imagePreview ? (
                // 圖片預覽
                <div className="relative border-2 border-slate-200 rounded-xl overflow-hidden">
                  <img
                    src={imagePreview}
                    alt="證照預覽"
                    className="w-full h-64 object-contain bg-slate-50"
                  />
                  <button
                    type="button"
                    onClick={() => {
                      setImagePreview(null);
                      setValue('certificate_image', '');
                      if (fileInputRef.current) {
                        fileInputRef.current.value = '';
                      }
                    }}
                    disabled={isLoading}
                    className="absolute top-2 right-2 p-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
                  >
                    <X className="h-4 w-4" />
                  </button>
                </div>
              ) : (
                // 上傳按鈕
                <button
                  type="button"
                  onClick={() => fileInputRef.current?.click()}
                  disabled={isLoading}
                  className="w-full border-2 border-dashed border-slate-300 rounded-xl p-8 hover:border-primary-500 hover:bg-primary-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <div className="flex flex-col items-center gap-2">
                    <div className="p-3 bg-primary-100 rounded-full">
                      <Upload className="h-6 w-6 text-primary-600" />
                    </div>
                    <p className="text-sm font-medium text-slate-700">
                      點擊上傳證照圖片
                    </p>
                    <p className="text-xs text-slate-500">
                      支援 JPG、PNG 格式，檔案大小不超過 3MB
                    </p>
                  </div>
                </button>
              )}

              <input
                ref={fileInputRef}
                type="file"
                accept="image/*"
                className="hidden"
                onChange={handleImageChange}
                disabled={isLoading}
              />

              {errors.certificate_image && (
                <p className="text-sm text-red-600">{errors.certificate_image.message}</p>
              )}
            </div>

            {/* 提示訊息 */}
            <div className="bg-blue-50 border border-blue-200 rounded-xl p-4">
              <div className="flex items-start">
                <svg
                  className="h-5 w-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fillRule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                    clipRule="evenodd"
                  />
                </svg>
                <p className="text-sm text-blue-800 leading-relaxed">
                  上傳的證照需要經過管理員審核才會顯示在您的公開個人檔案上
                </p>
              </div>
            </div>

            {/* Footer */}
            <div className="flex gap-3 pt-4 border-t border-slate-200">
              <Button
                type="submit"
                isLoading={isLoading}
                className="flex-1"
              >
                上傳證照
              </Button>
              <Button
                type="button"
                variant="outline"
                onClick={handleClose}
                disabled={isLoading}
              >
                取消
              </Button>
            </div>
          </form>
        </div>
      </div>
    </>
  );
}
