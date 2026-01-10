'use client';

import { useState, useRef, ChangeEvent, useEffect } from 'react';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Avatar } from '@/components/ui/avatar';
import { ProfileSkeleton } from '@/components/ui/skeleton';
import { Camera, Save, Building2, X } from 'lucide-react';
import { useProfile, useUpdateProfile, useSaveCompany } from '@/hooks/useSalesperson';
import { useRegions } from '@/hooks/useSearch';
import { processImageUpload, formatFileSize } from '@/lib/utils/image';
import { toast } from 'sonner';

// 個人資料表單驗證
const profileSchema = z.object({
  full_name: z.string().min(2, '姓名至少需要 2 個字元').max(100, '姓名不能超過 100 個字元'),
  phone: z.string().regex(/^09\d{8}$/, '請輸入有效的手機號碼（例：0912345678）').optional().or(z.literal('')),
  bio: z.string().max(500, '簡介不能超過 500 個字元').optional().or(z.literal('')),
  specialties: z.string().max(200, '專長不能超過 200 個字元').optional().or(z.literal('')),
  service_regions: z.array(z.string()).optional(),
  avatar: z.string().optional(),
});

type ProfileFormData = z.infer<typeof profileSchema>;

// 公司資訊表單驗證
const companySchema = z.object({
  name: z.string().min(2, '公司名稱至少需要 2 個字元').max(100, '公司名稱不能超過 100 個字元'),
});

type CompanyFormData = z.infer<typeof companySchema>;

export default function ProfilePage() {
  const [editMode, setEditMode] = useState(false);
  const [avatarPreview, setAvatarPreview] = useState<string | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  // 獲取資料
  const { data: profile, isLoading: profileLoading } = useProfile();
  const { data: regions, isLoading: regionsLoading } = useRegions();

  // Mutations
  const updateProfileMutation = useUpdateProfile();
  const saveCompanyMutation = useSaveCompany();

  // 個人資料表單
  const {
    register: registerProfile,
    handleSubmit: handleProfileSubmit,
    control: profileControl,
    setValue: setProfileValue,
    watch: watchProfile,
    formState: { errors: profileErrors },
    reset: resetProfile,
  } = useForm<ProfileFormData>({
    resolver: zodResolver(profileSchema),
    defaultValues: {
      full_name: profile?.full_name || '',
      phone: profile?.phone || '',
      bio: profile?.bio || '',
      specialties: profile?.specialties || '',
      service_regions: profile?.service_regions || [],
    },
  });

  // 公司資訊表單
  const {
    register: registerCompany,
    handleSubmit: handleCompanySubmit,
    formState: { errors: companyErrors },
    reset: resetCompany,
  } = useForm<CompanyFormData>({
    resolver: zodResolver(companySchema),
    defaultValues: {
      name: profile?.company?.name || '',
    },
  });

  // 當資料載入完成時，重置表單
  useEffect(() => {
    if (profile) {
      // 確保 service_regions 是陣列
      let serviceRegions: string[] = [];
      if (Array.isArray(profile.service_regions)) {
        serviceRegions = profile.service_regions;
      } else if (typeof profile.service_regions === 'string') {
        try {
          const parsed = JSON.parse(profile.service_regions);
          serviceRegions = Array.isArray(parsed) ? parsed : [];
        } catch {
          serviceRegions = [];
        }
      }

      resetProfile({
        full_name: profile.full_name,
        phone: profile.phone || '',
        bio: profile.bio || '',
        specialties: profile.specialties || '',
        service_regions: serviceRegions,
      });
      resetCompany({
        name: profile.company?.name || '',
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [profile?.id]);

  // 處理頭像上傳
  const handleAvatarChange = async (e: ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    try {
      // 顯示載入提示
      const loadingToast = toast.loading('處理圖片中...');

      // 使用圖片工具處理（驗證 + 壓縮 + 轉 Base64）
      const base64String = await processImageUpload(file, 2); // 最大 2MB

      // 更新預覽和表單
      setAvatarPreview(base64String);
      setProfileValue('avatar', base64String);

      // 關閉載入提示並顯示成功訊息
      toast.dismiss(loadingToast);
      toast.success(`圖片已處理（${formatFileSize(file.size)}）`);
    } catch (error) {
      // 顯示錯誤訊息
      toast.error(error instanceof Error ? error.message : '處理圖片失敗');

      // 清除檔案選擇
      if (fileInputRef.current) {
        fileInputRef.current.value = '';
      }
    }
  };

  // 處理服務地區選擇
  const selectedRegions = watchProfile('service_regions') || [];
  const toggleRegion = (regionName: string) => {
    const current = selectedRegions;
    if (current.includes(regionName)) {
      setProfileValue('service_regions', current.filter(name => name !== regionName));
    } else {
      setProfileValue('service_regions', [...current, regionName]);
    }
  };

  // 提交個人資料
  const onSubmitProfile = (data: ProfileFormData) => {
    updateProfileMutation.mutate(data, {
      onSuccess: () => {
        setEditMode(false);
        setAvatarPreview(null);
      },
    });
  };

  // 提交公司資訊
  const onSubmitCompany = (data: CompanyFormData) => {
    saveCompanyMutation.mutate(data);
  };

  if (profileLoading || regionsLoading) {
    return (
      <div className="space-y-6">
        <div className="mb-6">
          <h1 className="text-2xl font-bold text-slate-900">個人資料</h1>
          <p className="text-slate-600 mt-1">管理您的個人資訊</p>
        </div>
        <Card>
          <CardContent className="p-8">
            <ProfileSkeleton />
          </CardContent>
        </Card>
      </div>
    );
  }

  const profileData = profile;

  return (
    <div className="space-y-6">
      {/* 頁面標題 */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-slate-900">個人資料</h1>
          <p className="text-slate-600 mt-1">管理您的個人資訊</p>
        </div>
        {!editMode && (
          <Button onClick={() => setEditMode(true)}>編輯資料</Button>
        )}
      </div>

      {/* 個人資料卡片 */}
      <Card>
        <CardHeader>
          <CardTitle>基本資料</CardTitle>
        </CardHeader>
        <CardContent>
          {!editMode ? (
            // 檢視模式
            <div className="space-y-6">
              {/* 頭像 */}
              <div className="flex items-center gap-6">
                <Avatar
                  src={profileData?.avatar}
                  fallback={profileData?.full_name.substring(0, 2)}
                  size="2xl"
                />
                <div>
                  <h3 className="text-xl font-semibold text-slate-900">
                    {profileData?.full_name}
                  </h3>
                  {profileData?.phone && (
                    <p className="text-slate-600">{profileData.phone}</p>
                  )}
                </div>
              </div>

              {/* 簡介 */}
              {profileData?.bio && (
                <div>
                  <h4 className="text-sm font-semibold text-slate-700 mb-2">個人簡介</h4>
                  <p className="text-slate-600 whitespace-pre-line">{profileData.bio}</p>
                </div>
              )}

              {/* 專長 */}
              {profileData?.specialties && (
                <div>
                  <h4 className="text-sm font-semibold text-slate-700 mb-2">專長領域</h4>
                  <div className="flex flex-wrap gap-2">
                    {profileData.specialties.split(',').map((specialty, index) => (
                      <Badge key={index} variant="primary" size="sm">
                        {specialty.trim()}
                      </Badge>
                    ))}
                  </div>
                </div>
              )}

              {/* 服務地區 */}
              {profileData?.service_regions && Array.isArray(profileData.service_regions) && profileData.service_regions.length > 0 && (
                <div>
                  <h4 className="text-sm font-semibold text-slate-700 mb-2">服務地區</h4>
                  <div className="flex flex-wrap gap-2">
                    {profileData.service_regions.map((regionName, index) => (
                      <Badge key={index} variant="secondary" size="sm">
                        {regionName}
                      </Badge>
                    ))}
                  </div>
                </div>
              )}
            </div>
          ) : (
            // 編輯模式
            <form onSubmit={handleProfileSubmit(onSubmitProfile)} className="space-y-6">
              {/* 頭像上傳 */}
              <div className="flex items-start gap-6">
                <div className="relative">
                  <Avatar
                    src={avatarPreview || profileData?.avatar}
                    fallback={profileData?.full_name.substring(0, 2)}
                    size="2xl"
                  />
                  <button
                    type="button"
                    onClick={() => fileInputRef.current?.click()}
                    className="absolute bottom-0 right-0 p-2 bg-primary-600 text-white rounded-full hover:bg-primary-700 transition-colors"
                  >
                    <Camera className="h-4 w-4" />
                  </button>
                  <input
                    ref={fileInputRef}
                    type="file"
                    accept="image/*"
                    className="hidden"
                    onChange={handleAvatarChange}
                  />
                </div>
                <div className="flex-1">
                  <p className="text-sm text-slate-600 mb-1">點擊相機圖示上傳頭像</p>
                  <p className="text-xs text-slate-500">支援 JPG、PNG 格式，檔案大小不超過 2MB</p>
                </div>
              </div>

              <Input
                label="姓名"
                type="text"
                error={profileErrors.full_name?.message}
                required
                {...registerProfile('full_name')}
              />

              <Input
                label="手機號碼"
                type="tel"
                placeholder="0912345678"
                error={profileErrors.phone?.message}
                {...registerProfile('phone')}
              />

              {/* 簡介 */}
              <div className="space-y-2">
                <label className="text-sm font-semibold text-slate-700">
                  個人簡介
                </label>
                <textarea
                  className="flex w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-2 text-base placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 hover:border-slate-300 transition-all duration-200 resize-none"
                  rows={4}
                  placeholder="介紹您的工作經驗和專業背景..."
                  {...registerProfile('bio')}
                />
                {profileErrors.bio && (
                  <p className="text-sm text-red-600">{profileErrors.bio.message}</p>
                )}
              </div>

              <Input
                label="專長領域"
                type="text"
                placeholder="例如：電子產品, 工業設備, 醫療器材（用逗號分隔）"
                helperText="請用逗號分隔多個專長"
                error={profileErrors.specialties?.message}
                {...registerProfile('specialties')}
              />

              {/* 服務地區 */}
              <div className="space-y-2">
                <label className="text-sm font-semibold text-slate-700">
                  服務地區
                </label>
                <div className="flex flex-wrap gap-2 p-4 border-2 border-slate-200 rounded-xl">
                  {regions?.map((region) => {
                    const isSelected = selectedRegions.includes(region.name);
                    return (
                      <button
                        key={region.id}
                        type="button"
                        onClick={() => toggleRegion(region.name)}
                        className={`px-3 py-1.5 rounded-lg text-sm font-medium transition-colors ${
                          isSelected
                            ? 'bg-primary-600 text-white'
                            : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
                        }`}
                      >
                        {region.name}
                      </button>
                    );
                  })}
                </div>
              </div>

              {/* 表單按鈕 */}
              <div className="flex gap-3 pt-4">
                <Button
                  type="submit"
                  isLoading={updateProfileMutation.isPending}
                >
                  <Save className="mr-2 h-4 w-4" />
                  儲存變更
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => {
                    setEditMode(false);
                    setAvatarPreview(null);
                    resetProfile();
                  }}
                >
                  取消
                </Button>
              </div>
            </form>
          )}
        </CardContent>
      </Card>

      {/* 公司資訊卡片 */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Building2 className="h-5 w-5" />
            公司資訊
          </CardTitle>
        </CardHeader>
        <CardContent>
          {profileData?.company && !editMode ? (
            // 檢視模式
            <div className="space-y-4">
              <div>
                <h4 className="text-sm font-semibold text-slate-700 mb-1">公司名稱</h4>
                <p className="text-slate-900">{profileData.company.name}</p>
              </div>
            </div>
          ) : (
            // 編輯模式或無公司資訊
            <form onSubmit={handleCompanySubmit(onSubmitCompany)} className="space-y-4">
              <Input
                label="公司名稱"
                type="text"
                placeholder="請輸入公司名稱"
                error={companyErrors.name?.message}
                required
                {...registerCompany('name')}
              />


              <Button
                type="submit"
                isLoading={saveCompanyMutation.isPending}
              >
                <Save className="mr-2 h-4 w-4" />
                儲存公司資訊
              </Button>
            </form>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
