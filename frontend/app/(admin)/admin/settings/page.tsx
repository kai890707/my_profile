'use client';

import { useState } from 'react';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { ProfileSkeleton } from '@/components/ui/skeleton';
import {
  Building2,
  MapPin,
  Plus,
  Trash2,
  Tag,
  X,
} from 'lucide-react';
import {
  useIndustries,
  useCreateIndustry,
  useDeleteIndustry,
  useRegions,
  useCreateRegion,
  useDeleteRegion,
} from '@/hooks/useAdmin';
import { formatDate } from '@/lib/utils/format';
import type { Industry, Region } from '@/types/api';

type Tab = 'industries' | 'regions';

interface AddModalProps {
  isOpen: boolean;
  onClose: () => void;
  type: 'industry' | 'region';
  onSubmit: (data: any) => void;
  isLoading?: boolean;
}

function AddModal({ isOpen, onClose, type, onSubmit, isLoading }: AddModalProps) {
  const [name, setName] = useState('');
  const [slug, setSlug] = useState('');
  const [description, setDescription] = useState('');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    if (!name || !slug) {
      alert('請填寫必填欄位');
      return;
    }

    onSubmit({
      name,
      slug,
      ...(type === 'industry' && description && { description }),
    });

    // 清空表單
    setName('');
    setSlug('');
    setDescription('');
  };

  const handleClose = () => {
    setName('');
    setSlug('');
    setDescription('');
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
          className="bg-white rounded-2xl shadow-2xl max-w-md w-full"
          onClick={(e) => e.stopPropagation()}
        >
          {/* Header */}
          <div className="flex items-center justify-between p-6 border-b border-slate-200">
            <h2 className="text-xl font-bold text-slate-900">
              新增{type === 'industry' ? '產業類別' : '地區'}
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
          <form onSubmit={handleSubmit} className="p-6 space-y-4">
            <Input
              label="名稱"
              type="text"
              value={name}
              onChange={(e) => setName(e.target.value)}
              placeholder={type === 'industry' ? '例如：資訊科技' : '例如：台北市'}
              required
              disabled={isLoading}
            />

            <Input
              label="代碼 (Slug)"
              type="text"
              value={slug}
              onChange={(e) => setSlug(e.target.value)}
              placeholder={type === 'industry' ? '例如：it' : '例如：taipei'}
              helperText="用於 URL 的唯一識別碼，建議使用英文小寫"
              required
              disabled={isLoading}
            />

            {type === 'industry' && (
              <div className="space-y-2">
                <label className="text-sm font-semibold text-slate-700">
                  描述（選填）
                </label>
                <textarea
                  className="flex w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-2 text-base placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 hover:border-slate-300 transition-all duration-200 resize-none disabled:opacity-50 disabled:bg-slate-50"
                  rows={3}
                  value={description}
                  onChange={(e) => setDescription(e.target.value)}
                  placeholder="簡述此產業類別..."
                  disabled={isLoading}
                />
              </div>
            )}

            {/* Footer */}
            <div className="flex gap-3 pt-4">
              <Button
                type="submit"
                isLoading={isLoading}
                className="flex-1"
              >
                新增
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

export default function SettingsPage() {
  const [activeTab, setActiveTab] = useState<Tab>('industries');
  const [isModalOpen, setIsModalOpen] = useState(false);

  // 獲取資料
  const { data: industries, isLoading: industriesLoading } = useIndustries();
  const { data: regions, isLoading: regionsLoading } = useRegions();

  // Mutations
  const createIndustryMutation = useCreateIndustry();
  const deleteIndustryMutation = useDeleteIndustry();
  const createRegionMutation = useCreateRegion();
  const deleteRegionMutation = useDeleteRegion();

  const handleAdd = () => {
    setIsModalOpen(true);
  };

  const handleSubmitIndustry = (data: any) => {
    createIndustryMutation.mutate(data, {
      onSuccess: () => {
        setIsModalOpen(false);
      },
    });
  };

  const handleSubmitRegion = (data: any) => {
    createRegionMutation.mutate(data, {
      onSuccess: () => {
        setIsModalOpen(false);
      },
    });
  };

  const handleDeleteIndustry = (industry: Industry) => {
    if (confirm(`確定要刪除產業類別「${industry.name}」嗎？\n\n此操作可能影響相關的公司資料。`)) {
      deleteIndustryMutation.mutate(industry.id);
    }
  };

  const handleDeleteRegion = (region: Region) => {
    if (confirm(`確定要刪除地區「${region.name}」嗎？\n\n此操作可能影響相關的業務員資料。`)) {
      deleteRegionMutation.mutate(region.id);
    }
  };

  const isLoading = industriesLoading || regionsLoading;

  if (isLoading) {
    return (
      <div className="space-y-6">
        <h1 className="text-2xl font-bold text-slate-900">系統設定</h1>
        <Card>
          <CardContent className="p-8">
            <ProfileSkeleton />
          </CardContent>
        </Card>
      </div>
    );
  }

  const tabs = [
    { id: 'industries' as Tab, label: '產業類別', icon: Building2, count: industries?.length || 0 },
    { id: 'regions' as Tab, label: '地區管理', icon: MapPin, count: regions?.length || 0 },
  ];

  return (
    <div className="space-y-6">
      {/* 頁面標題 */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-slate-900">系統設定</h1>
          <p className="text-slate-600 mt-1">管理產業類別與地區資料</p>
        </div>

        <Button onClick={handleAdd}>
          <Plus className="mr-2 h-4 w-4" />
          新增{activeTab === 'industries' ? '產業類別' : '地區'}
        </Button>
      </div>

      {/* Tabs */}
      <div className="flex gap-4">
        {tabs.map((tab) => {
          const Icon = tab.icon;
          return (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id)}
              className={`
                flex items-center gap-3 px-6 py-3 rounded-xl font-medium transition-all
                ${
                  activeTab === tab.id
                    ? 'bg-primary-600 text-white shadow-lg shadow-primary-600/30'
                    : 'bg-white text-slate-700 hover:bg-slate-50 border-2 border-slate-200'
                }
              `}
            >
              <Icon className="h-5 w-5" />
              <span>{tab.label}</span>
              <span
                className={`
                  px-2 py-0.5 text-xs font-semibold rounded-full
                  ${
                    activeTab === tab.id
                      ? 'bg-white text-primary-600'
                      : 'bg-slate-200 text-slate-700'
                  }
                `}
              >
                {tab.count}
              </span>
            </button>
          );
        })}
      </div>

      {/* 產業類別 */}
      {activeTab === 'industries' && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Building2 className="h-5 w-5 text-primary-600" />
              產業類別
            </CardTitle>
          </CardHeader>
          <CardContent>
            {industries && industries.length > 0 ? (
              <div className="space-y-3">
                {industries.map((industry) => (
                  <div
                    key={industry.id}
                    className="flex items-center justify-between p-4 border-2 border-slate-200 rounded-xl hover:border-primary-300 transition-colors"
                  >
                    <div className="flex items-start gap-4 flex-1">
                      <div className="p-2 bg-primary-50 rounded-lg">
                        <Tag className="h-5 w-5 text-primary-600" />
                      </div>

                      <div className="flex-1">
                        <h4 className="font-semibold text-slate-900">{industry.name}</h4>
                        <p className="text-sm text-slate-600 mt-1">代碼：{industry.slug}</p>
                        {industry.description && (
                          <p className="text-sm text-slate-500 mt-2">{industry.description}</p>
                        )}
                        <p className="text-xs text-slate-400 mt-2">
                          建立時間：{formatDate(industry.created_at)}
                        </p>
                      </div>
                    </div>

                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => handleDeleteIndustry(industry)}
                      disabled={deleteIndustryMutation.isPending}
                      className="text-red-600 hover:text-red-700 hover:bg-red-50"
                    >
                      <Trash2 className="h-4 w-4" />
                    </Button>
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-center py-12">
                <Building2 className="h-12 w-12 text-slate-300 mx-auto mb-3" />
                <p className="text-slate-500">尚未新增產業類別</p>
                <Button onClick={handleAdd} variant="outline" className="mt-4">
                  <Plus className="mr-2 h-4 w-4" />
                  新增第一個產業類別
                </Button>
              </div>
            )}
          </CardContent>
        </Card>
      )}

      {/* 地區管理 */}
      {activeTab === 'regions' && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <MapPin className="h-5 w-5 text-primary-600" />
              地區管理
            </CardTitle>
          </CardHeader>
          <CardContent>
            {regions && regions.length > 0 ? (
              <div className="space-y-3">
                {regions.map((region) => (
                  <div
                    key={region.id}
                    className="flex items-center justify-between p-4 border-2 border-slate-200 rounded-xl hover:border-primary-300 transition-colors"
                  >
                    <div className="flex items-start gap-4 flex-1">
                      <div className="p-2 bg-blue-50 rounded-lg">
                        <MapPin className="h-5 w-5 text-blue-600" />
                      </div>

                      <div className="flex-1">
                        <h4 className="font-semibold text-slate-900">{region.name}</h4>
                        <p className="text-sm text-slate-600 mt-1">代碼：{region.slug}</p>
                        {region.parent_id && (
                          <p className="text-sm text-slate-500 mt-1">
                            上層地區 ID：{region.parent_id}
                          </p>
                        )}
                        <p className="text-xs text-slate-400 mt-2">
                          建立時間：{formatDate(region.created_at)}
                        </p>
                      </div>
                    </div>

                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => handleDeleteRegion(region)}
                      disabled={deleteRegionMutation.isPending}
                      className="text-red-600 hover:text-red-700 hover:bg-red-50"
                    >
                      <Trash2 className="h-4 w-4" />
                    </Button>
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-center py-12">
                <MapPin className="h-12 w-12 text-slate-300 mx-auto mb-3" />
                <p className="text-slate-500">尚未新增地區</p>
                <Button onClick={handleAdd} variant="outline" className="mt-4">
                  <Plus className="mr-2 h-4 w-4" />
                  新增第一個地區
                </Button>
              </div>
            )}
          </CardContent>
        </Card>
      )}

      {/* 新增 Modal */}
      <AddModal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        type={activeTab === 'industries' ? 'industry' : 'region'}
        onSubmit={activeTab === 'industries' ? handleSubmitIndustry : handleSubmitRegion}
        isLoading={createIndustryMutation.isPending || createRegionMutation.isPending}
      />
    </div>
  );
}
