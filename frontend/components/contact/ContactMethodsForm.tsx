'use client';

import { useState, useCallback } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Phone, Mail, MessageSquare, User, GripVertical } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useUpdateContactMethods } from '@/hooks/useSalesperson';
import { type UpdateContactMethodsRequest } from '@/lib/api/salesperson';
import type { SalespersonProfile, ContactPreference } from '@/types/api';

// Validation Schema - matching Backend UpdateContactRequest rules
const contactMethodsSchema = z
  .object({
    phone: z
      .string()
      .regex(/^09\d{8}$|^0\d-\d{7,8}$/, '電話格式不正確（手機: 0912345678 或市話: 02-12345678）')
      .optional()
      .or(z.literal('')),
    email_public: z
      .string()
      .email('Email 格式不正確')
      .max(255, 'Email 最多 255 個字元')
      .optional()
      .or(z.literal('')),
    line_id: z
      .string()
      .min(3, 'LINE ID 最少 3 個字元')
      .max(20, 'LINE ID 最多 20 個字元')
      .regex(/^[a-zA-Z0-9_]+$/, 'LINE ID 格式不正確（僅允許英數字和底線）')
      .optional()
      .or(z.literal('')),
    wechat_id: z
      .string()
      .min(6, 'WeChat ID 最少 6 個字元')
      .max(20, 'WeChat ID 最多 20 個字元')
      .regex(/^[a-zA-Z0-9_-]+$/, 'WeChat ID 格式不正確（僅允許英數字、底線和減號）')
      .optional()
      .or(z.literal('')),
  })
  .refine(
    (data) => {
      // At least one contact method must be provided
      return !!(data.phone || data.email_public || data.line_id || data.wechat_id);
    },
    {
      message: '必須至少提供一種聯繫方式（電話、Email、LINE 或 WeChat）',
      path: ['phone'], // Show error on phone field
    }
  );

type ContactMethodsFormData = z.infer<typeof contactMethodsSchema>;

interface ContactMethodsFormProps {
  profile: SalespersonProfile;
}

const CONTACT_METHOD_OPTIONS = [
  { value: 'phone' as ContactPreference, label: '電話', icon: Phone },
  { value: 'email_public' as ContactPreference, label: 'Email', icon: Mail },
  { value: 'line' as ContactPreference, label: 'LINE', icon: MessageSquare },
  { value: 'wechat' as ContactPreference, label: 'WeChat', icon: User },
];

export function ContactMethodsForm({ profile }: ContactMethodsFormProps) {
  const { mutate: updateContactMethods, isPending } = useUpdateContactMethods();

  // Initialize contact preferences order
  const [preferences, setPreferences] = useState<ContactPreference[]>(() => {
    if (profile.contact_preferences && profile.contact_preferences.length > 0) {
      return profile.contact_preferences as ContactPreference[];
    }
    return ['phone', 'email_public', 'line', 'wechat'];
  });

  const [draggedItem, setDraggedItem] = useState<ContactPreference | null>(null);

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<ContactMethodsFormData>({
    resolver: zodResolver(contactMethodsSchema),
    defaultValues: {
      phone: profile.phone || '',
      email_public: profile.email_public || '',
      line_id: profile.line_id || '',
      wechat_id: profile.wechat_id || '',
    },
  });

  const onSubmit = useCallback(
    (data: ContactMethodsFormData) => {
      // Clean empty fields (convert empty strings to undefined)
      const cleanedData: UpdateContactMethodsRequest = {
        phone: data.phone?.trim() || undefined,
        email_public: data.email_public?.trim() || undefined,
        line_id: data.line_id?.trim() || undefined,
        wechat_id: data.wechat_id?.trim() || undefined,
        contact_preferences: preferences,
      };

      updateContactMethods(cleanedData);
    },
    [preferences, updateContactMethods]
  );

  const handleDragStart = useCallback((preference: ContactPreference) => {
    setDraggedItem(preference);
  }, []);

  const handleDragOver = useCallback((e: React.DragEvent) => {
    e.preventDefault();
  }, []);

  const handleDrop = useCallback(
    (targetPreference: ContactPreference) => {
      if (!draggedItem) return;

      const newPreferences = [...preferences];
      const draggedIndex = newPreferences.indexOf(draggedItem);
      const targetIndex = newPreferences.indexOf(targetPreference);

      newPreferences.splice(draggedIndex, 1);
      newPreferences.splice(targetIndex, 0, draggedItem);

      setPreferences(newPreferences);
      setDraggedItem(null);
    },
    [draggedItem, preferences]
  );

  const getLabelForPreference = (pref: ContactPreference): string => {
    const option = CONTACT_METHOD_OPTIONS.find((opt) => opt.value === pref);
    return option?.label || pref;
  };

  const getIconForPreference = (pref: ContactPreference) => {
    const option = CONTACT_METHOD_OPTIONS.find((opt) => opt.value === pref);
    const IconComponent = option?.icon || Phone;
    return <IconComponent className="h-4 w-4" />;
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle>聯繫方式設定</CardTitle>
        <CardDescription>
          設定您的公開聯繫方式，讓潛在客戶可以聯繫您。至少需要填寫一種聯繫方式。
        </CardDescription>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
          {/* Contact Methods Inputs */}
          <div className="space-y-4">
            <Input
              {...register('phone')}
              label="聯繫電話"
              placeholder="0912-345-678"
              icon={<Phone className="h-5 w-5" />}
              error={errors.phone?.message}
              helperText="客戶可直接撥打此號碼聯繫您"
            />

            <Input
              {...register('email_public')}
              label="公開 Email"
              type="email"
              placeholder="contact@example.com"
              icon={<Mail className="h-5 w-5" />}
              error={errors.email_public?.message}
              helperText="此 Email 將公開顯示，可能與登入 Email 不同"
            />

            <Input
              {...register('line_id')}
              label="LINE ID"
              placeholder="my_line_id"
              icon={<MessageSquare className="h-5 w-5" />}
              error={errors.line_id?.message}
              helperText="您的 LINE 帳號 ID（3-20 字元，僅英數字和底線）"
            />

            <Input
              {...register('wechat_id')}
              label="WeChat ID"
              placeholder="my_wechat"
              icon={<User className="h-5 w-5" />}
              error={errors.wechat_id?.message}
              helperText="您的 WeChat 帳號 ID（6-20 字元，可含英數字、底線、減號）"
            />
          </div>

          {/* Contact Preferences Order */}
          <div className="space-y-3">
            <div>
              <h4 className="text-sm font-semibold text-slate-700 mb-2">
                聯繫偏好順序
              </h4>
              <p className="text-sm text-slate-500 mb-3">
                拖曳以調整客戶優先聯繫方式的順序
              </p>
            </div>

            <div className="space-y-2">
              {preferences.map((pref, index) => (
                <div
                  key={pref}
                  draggable
                  onDragStart={() => handleDragStart(pref)}
                  onDragOver={handleDragOver}
                  onDrop={() => handleDrop(pref)}
                  className={`
                    flex items-center gap-3 p-3 bg-slate-50 rounded-lg border-2 border-slate-200
                    cursor-move hover:bg-slate-100 hover:border-slate-300
                    transition-all duration-200
                    ${draggedItem === pref ? 'opacity-50' : 'opacity-100'}
                  `}
                >
                  <GripVertical className="h-5 w-5 text-slate-400" />
                  <span className="flex items-center justify-center w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-sm font-bold">
                    {index + 1}
                  </span>
                  <span className="text-slate-600">{getIconForPreference(pref)}</span>
                  <span className="font-medium text-slate-700">
                    {getLabelForPreference(pref)}
                  </span>
                </div>
              ))}
            </div>
          </div>

          {/* Submit Button */}
          <div className="flex justify-end">
            <Button type="submit" isLoading={isPending} size="lg">
              儲存聯繫方式
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  );
}
