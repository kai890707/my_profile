'use client';

import { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { X } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import type { Experience } from '@/types/api';

// 表單驗證 Schema
const experienceSchema = z.object({
  company: z.string().min(2, '公司名稱至少需要 2 個字元').max(100, '公司名稱不能超過 100 個字元'),
  position: z.string().min(2, '職稱至少需要 2 個字元').max(100, '職稱不能超過 100 個字元'),
  start_date: z.string().min(1, '請選擇開始日期'),
  end_date: z.string().optional().or(z.literal('')),
  description: z.string().max(500, '描述不能超過 500 個字元').optional().or(z.literal('')),
}).refine((data) => {
  // 如果有結束日期，檢查是否晚於開始日期
  if (data.end_date && data.start_date) {
    return new Date(data.end_date) >= new Date(data.start_date);
  }
  return true;
}, {
  message: '結束日期必須晚於或等於開始日期',
  path: ['end_date'],
});

type ExperienceFormData = z.infer<typeof experienceSchema>;

interface ExperienceFormModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (data: ExperienceFormData) => void;
  experience?: Experience | null;
  isLoading?: boolean;
}

export function ExperienceFormModal({
  isOpen,
  onClose,
  onSubmit,
  experience,
  isLoading = false,
}: ExperienceFormModalProps) {
  const {
    register,
    handleSubmit,
    formState: { errors },
    reset,
  } = useForm<ExperienceFormData>({
    resolver: zodResolver(experienceSchema),
    defaultValues: {
      company: experience?.company || '',
      position: experience?.position || '',
      start_date: experience?.start_date || '',
      end_date: experience?.end_date || '',
      description: experience?.description || '',
    },
  });

  // 當 experience 改變時重置表單
  useEffect(() => {
    if (experience) {
      reset({
        company: experience.company,
        position: experience.position,
        start_date: experience.start_date,
        end_date: experience.end_date || '',
        description: experience.description || '',
      });
    } else {
      reset({
        company: '',
        position: '',
        start_date: '',
        end_date: '',
        description: '',
      });
    }
  }, [experience, reset]);

  const handleFormSubmit = (data: ExperienceFormData) => {
    onSubmit(data);
  };

  const handleClose = () => {
    reset();
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
            <h2 className="text-2xl font-bold text-slate-900">
              {experience ? '編輯工作經驗' : '新增工作經驗'}
            </h2>
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
              label="公司名稱"
              type="text"
              placeholder="請輸入公司名稱"
              error={errors.company?.message}
              required
              disabled={isLoading}
              {...register('company')}
            />

            <Input
              label="職稱"
              type="text"
              placeholder="請輸入職稱"
              error={errors.position?.message}
              required
              disabled={isLoading}
              {...register('position')}
            />

            <div className="grid md:grid-cols-2 gap-4">
              <Input
                label="開始日期"
                type="date"
                error={errors.start_date?.message}
                required
                disabled={isLoading}
                {...register('start_date')}
              />

              <Input
                label="結束日期"
                type="date"
                helperText="選填，留空表示至今"
                error={errors.end_date?.message}
                disabled={isLoading}
                {...register('end_date')}
              />
            </div>

            {/* 工作描述 */}
            <div className="space-y-2">
              <label className="text-sm font-semibold text-slate-700">
                工作描述
              </label>
              <textarea
                className="flex w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-2 text-base placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 hover:border-slate-300 transition-all duration-200 resize-none disabled:opacity-50 disabled:bg-slate-50"
                rows={4}
                placeholder="請描述您的工作內容和成就..."
                disabled={isLoading}
                {...register('description')}
              />
              {errors.description && (
                <p className="text-sm text-red-600">{errors.description.message}</p>
              )}
            </div>

            {/* Footer */}
            <div className="flex gap-3 pt-4 border-t border-slate-200">
              <Button
                type="submit"
                isLoading={isLoading}
                className="flex-1"
              >
                {experience ? '儲存變更' : '新增經驗'}
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
