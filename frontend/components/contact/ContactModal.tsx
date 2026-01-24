'use client';

import { useCallback, useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Send, User, Mail, Phone as PhoneIcon } from 'lucide-react';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { useCreateContactRequest } from '@/hooks/useContact';
import { useAuth } from '@/hooks/useAuth';
import { useTrackEventManually } from '@/hooks/useTracking';

// Validation Schema
const contactRequestSchema = z.object({
  phone: z.string().optional(),
  message: z
    .string()
    .min(10, '訊息內容至少需要 10 個字元')
    .max(500, '訊息內容不能超過 500 個字元'),
});

type ContactRequestFormData = z.infer<typeof contactRequestSchema>;

interface ContactModalProps {
  isOpen: boolean;
  onClose: () => void;
  salespersonId: number;
  salespersonName: string;
}

export function ContactModal({
  isOpen,
  onClose,
  salespersonId,
  salespersonName,
}: ContactModalProps) {
  const { data: user } = useAuth();
  const { mutate: createContactRequest, isPending } = useCreateContactRequest();
  const trackEvent = useTrackEventManually();

  const {
    register,
    handleSubmit,
    formState: { errors },
    reset,
    watch,
  } = useForm<ContactRequestFormData>({
    resolver: zodResolver(contactRequestSchema),
    defaultValues: {
      phone: '',
      message: '',
    },
  });

  const messageValue = watch('message');
  const messageLength = messageValue?.length || 0;

  // Reset form when modal closes
  useEffect(() => {
    if (!isOpen) {
      reset();
    }
  }, [isOpen, reset]);

  const onSubmit = useCallback(
    (data: ContactRequestFormData) => {
      createContactRequest(
        {
          salesperson_id: salespersonId,
          phone: data.phone || undefined,
          message: data.message,
        },
        {
          onSuccess: () => {
            // Track contact_form_submission event
            trackEvent('contact_form_submission', salespersonId);

            onClose();
            reset();
          },
        }
      );
    },
    [salespersonId, createContactRequest, onClose, reset, trackEvent]
  );

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <Send className="h-5 w-5 text-primary-600" />
            聯繫 {salespersonName}
          </DialogTitle>
          <DialogDescription>
            填寫以下表單，業務員將透過 Email 或您提供的聯繫方式回覆。
          </DialogDescription>
        </DialogHeader>

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
          {/* Auto-filled user info */}
          <div className="space-y-3 p-4 bg-slate-50 rounded-xl border border-slate-200">
            <p className="text-sm font-semibold text-slate-700">您的資訊（自動填入）</p>
            <div className="space-y-2">
              <div className="flex items-center gap-2 text-sm">
                <User className="h-4 w-4 text-slate-500" />
                <span className="text-slate-600">姓名：</span>
                <span className="font-medium text-slate-900">{user?.username || '未提供'}</span>
              </div>
              <div className="flex items-center gap-2 text-sm">
                <Mail className="h-4 w-4 text-slate-500" />
                <span className="text-slate-600">Email：</span>
                <span className="font-medium text-slate-900">{user?.email || '未提供'}</span>
              </div>
            </div>
          </div>

          {/* Phone input (optional) */}
          <Input
            {...register('phone')}
            label="聯繫電話（選填）"
            placeholder="0912-345-678"
            icon={<PhoneIcon className="h-5 w-5" />}
            error={errors.phone?.message}
            helperText="提供電話可加快業務員回覆速度"
          />

          {/* Message textarea (required) */}
          <div className="space-y-2">
            <Textarea
              {...register('message')}
              label="訊息內容"
              placeholder="請描述您的需求、想了解的產品或服務，或任何問題..."
              rows={6}
              required
              error={errors.message?.message}
            />
            <div className="flex justify-between text-xs text-slate-500">
              <span>至少 10 個字元</span>
              <span
                className={`${
                  messageLength > 500
                    ? 'text-red-600 font-semibold'
                    : messageLength >= 450
                    ? 'text-yellow-600'
                    : ''
                }`}
              >
                {messageLength} / 500
              </span>
            </div>
          </div>

          {/* Rate limit notice */}
          <div className="p-3 bg-blue-50 rounded-lg border border-blue-200">
            <p className="text-xs text-blue-800">
              <strong>提示：</strong>為避免濫用，每位使用者每天最多可發送 5 次聯繫請求，
              且 24 小時內不可重複聯繫同一位業務員。
            </p>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" onClick={onClose} disabled={isPending}>
              取消
            </Button>
            <Button type="submit" isLoading={isPending}>
              <Send className="mr-2 h-4 w-4" />
              送出聯繫請求
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}
