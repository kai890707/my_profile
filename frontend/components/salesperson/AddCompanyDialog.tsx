'use client';

import * as React from 'react';
import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { createCompany, type CreateCompanyRequest } from '@/lib/api/companies';
import type { Company } from '@/types/api';
import { toast } from 'sonner';

// Validation schema
const companySchema = z.object({
  name: z
    .string()
    .min(1, '公司名稱為必填')
    .max(255, '公司名稱不能超過 255 個字元')
    .refine((val) => val.trim().length > 0, '公司名稱不能為空白'),
  tax_id: z
    .string()
    .optional()
    .refine(
      (val) => !val || val === '' || /^\d{8}$/.test(val),
      '統一編號必須為 8 位數字'
    ),
});

type CompanyFormData = z.infer<typeof companySchema>;

export interface AddCompanyDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  onSuccess: (company: Company) => void;
}

export function AddCompanyDialog({
  open,
  onOpenChange,
  onSuccess,
}: AddCompanyDialogProps) {
  const [isSubmitting, setIsSubmitting] = useState(false);

  const {
    register,
    handleSubmit,
    formState: { errors },
    reset,
  } = useForm<CompanyFormData>({
    resolver: zodResolver(companySchema),
    defaultValues: {
      name: '',
      tax_id: '',
    },
  });

  // Handle form submission
  const onSubmit = async (data: CompanyFormData) => {
    setIsSubmitting(true);

    try {
      const requestData: CreateCompanyRequest = {
        name: data.name.trim(),
        tax_id: data.tax_id && data.tax_id.trim() !== '' ? data.tax_id.trim() : null,
        is_personal: false, // This is a registered company, not personal
      };

      const response = await createCompany(requestData);

      if (response.success && response.data) {
        toast.success('公司新增成功');
        onSuccess(response.data);
        reset();
        onOpenChange(false);
      } else {
        toast.error('新增失敗，請稍後再試');
      }
    } catch (error: any) {
      console.error('新增公司失敗:', error);

      // Handle specific error messages
      const errorMessage =
        error?.response?.data?.message ||
        error?.response?.data?.error ||
        '新增失敗，請稍後再試';

      // Check for duplicate company error
      if (
        errorMessage.includes('已存在') ||
        errorMessage.includes('duplicate') ||
        errorMessage.includes('unique')
      ) {
        toast.error('此公司已存在，請使用搜尋功能');
      } else {
        toast.error(errorMessage);
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  // Handle dialog close
  const handleOpenChange = (newOpen: boolean) => {
    if (!isSubmitting) {
      if (!newOpen) {
        reset();
      }
      onOpenChange(newOpen);
    }
  };

  return (
    <Dialog open={open} onOpenChange={handleOpenChange}>
      <DialogContent className="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle>新增公司</DialogTitle>
          <DialogDescription>
            填寫公司資訊以新增到系統中。統一編號為選填。
          </DialogDescription>
        </DialogHeader>

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
          {/* Company Name */}
          <Input
            label="公司名稱"
            type="text"
            placeholder="例：台灣科技股份有限公司"
            error={errors.name?.message}
            required
            disabled={isSubmitting}
            {...register('name')}
          />

          {/* Tax ID */}
          <Input
            label="統一編號"
            type="text"
            placeholder="8 位數字（選填）"
            helperText="統一編號為 8 位數字，若無可留空"
            error={errors.tax_id?.message}
            disabled={isSubmitting}
            maxLength={8}
            {...register('tax_id')}
          />

          {/* Form Actions */}
          <DialogFooter>
            <Button
              type="button"
              variant="outline"
              onClick={() => handleOpenChange(false)}
              disabled={isSubmitting}
            >
              取消
            </Button>
            <Button type="submit" isLoading={isSubmitting}>
              確認新增
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}
