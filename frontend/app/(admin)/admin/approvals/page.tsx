'use client';

import { useState } from 'react';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ProfileSkeleton } from '@/components/ui/skeleton';
import { StatusBadge } from '@/components/ui/status-badge';
import {
  Users,
  Building2,
  Award,
  Briefcase,
  CheckCircle2,
  XCircle,
  Eye,
  Calendar,
  Mail,
  Phone,
  FileText,
} from 'lucide-react';
import {
  usePendingApprovals,
  useApproveUser,
  useRejectUser,
  useApproveCompany,
  useRejectCompany,
  useApproveCertification,
  useRejectCertification,
  useApproveExperience,
  useRejectExperience,
} from '@/hooks/useAdmin';
import { formatDate } from '@/lib/utils/format';
import type { User, Company, Certification, Experience } from '@/types/api';

type ApprovalType = 'users' | 'companies' | 'certifications' | 'experiences';

interface DetailModalProps {
  isOpen: boolean;
  onClose: () => void;
  type: ApprovalType;
  item: any;
  onApprove: () => void;
  onReject: () => void;
  isLoading?: boolean;
}

function DetailModal({
  isOpen,
  onClose,
  type,
  item,
  onApprove,
  onReject,
  isLoading,
}: DetailModalProps) {
  if (!isOpen || !item) return null;

  return (
    <>
      {/* 遮罩層 */}
      <div
        className="fixed inset-0 bg-black/50 z-40 transition-opacity"
        onClick={onClose}
      />

      {/* Modal */}
      <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div
          className="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto"
          onClick={(e) => e.stopPropagation()}
        >
          {/* Header */}
          <div className="flex items-center justify-between p-6 border-b border-slate-200">
            <h2 className="text-2xl font-bold text-slate-900">詳細資訊</h2>
            <button
              onClick={onClose}
              className="p-2 hover:bg-slate-100 rounded-lg transition-colors"
            >
              <XCircle className="h-5 w-5 text-slate-500" />
            </button>
          </div>

          {/* Body */}
          <div className="p-6 space-y-6">
            {/* 業務員資訊 */}
            {type === 'users' && (
              <div className="space-y-4">
                <div className="flex items-start gap-4">
                  <div className="p-3 bg-primary-50 rounded-xl">
                    <Users className="h-8 w-8 text-primary-600" />
                  </div>
                  <div className="flex-1">
                    <h3 className="text-xl font-semibold text-slate-900">{item.username}</h3>
                    <div className="mt-3 space-y-2">
                      <div className="flex items-center gap-2 text-slate-600">
                        <Mail className="h-4 w-4" />
                        <span>{item.email}</span>
                      </div>
                      <div className="flex items-center gap-2 text-slate-600">
                        <Calendar className="h-4 w-4" />
                        <span>註冊時間：{formatDate(item.created_at)}</span>
                      </div>
                      <div className="flex items-center gap-2">
                        <span className="text-sm text-slate-600">狀態：</span>
                        <StatusBadge status={item.status} />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            )}

            {/* 公司資訊 */}
            {type === 'companies' && (
              <div className="space-y-4">
                <div className="flex items-start gap-4">
                  <div className="p-3 bg-blue-50 rounded-xl">
                    <Building2 className="h-8 w-8 text-blue-600" />
                  </div>
                  <div className="flex-1">
                    <h3 className="text-xl font-semibold text-slate-900">{item.name}</h3>
                    <div className="mt-3 space-y-3">
                      <div className="grid md:grid-cols-2 gap-4">
                        <div>
                          <label className="text-sm font-semibold text-slate-700">統一編號</label>
                          <p className="text-slate-900 mt-1">{item.tax_id}</p>
                        </div>
                        <div>
                          <label className="text-sm font-semibold text-slate-700">產業類別</label>
                          <p className="text-slate-900 mt-1">
                            {item.industry?.name || '未指定'}
                          </p>
                        </div>
                      </div>

                      {item.address && (
                        <div>
                          <label className="text-sm font-semibold text-slate-700">地址</label>
                          <p className="text-slate-900 mt-1">{item.address}</p>
                        </div>
                      )}

                      {item.phone && (
                        <div className="flex items-center gap-2 text-slate-600">
                          <Phone className="h-4 w-4" />
                          <span>{item.phone}</span>
                        </div>
                      )}

                      <div className="flex items-center gap-2 text-slate-600">
                        <Calendar className="h-4 w-4" />
                        <span>提交時間：{formatDate(item.created_at)}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            )}

            {/* 證照資訊 */}
            {type === 'certifications' && (
              <div className="space-y-4">
                <div className="flex items-start gap-4">
                  <div className="p-3 bg-green-50 rounded-xl">
                    <Award className="h-8 w-8 text-green-600" />
                  </div>
                  <div className="flex-1">
                    <h3 className="text-xl font-semibold text-slate-900">{item.name}</h3>
                    <div className="mt-3 space-y-3">
                      <div className="grid md:grid-cols-2 gap-4">
                        <div>
                          <label className="text-sm font-semibold text-slate-700">發證單位</label>
                          <p className="text-slate-900 mt-1">{item.issuer}</p>
                        </div>
                        <div>
                          <label className="text-sm font-semibold text-slate-700">發證日期</label>
                          <p className="text-slate-900 mt-1">{formatDate(item.issue_date)}</p>
                        </div>
                      </div>

                      {item.expiry_date && (
                        <div>
                          <label className="text-sm font-semibold text-slate-700">到期日期</label>
                          <p className="text-slate-900 mt-1">{formatDate(item.expiry_date)}</p>
                        </div>
                      )}

                      {item.description && (
                        <div>
                          <label className="text-sm font-semibold text-slate-700">說明</label>
                          <p className="text-slate-900 mt-1 whitespace-pre-line">
                            {item.description}
                          </p>
                        </div>
                      )}

                      {/* 證照圖片 */}
                      {item.file_url && (
                        <div>
                          <label className="text-sm font-semibold text-slate-700 mb-2 block">
                            證照圖片
                          </label>
                          <div className="border-2 border-slate-200 rounded-xl overflow-hidden">
                            <img
                              src={item.file_url}
                              alt={item.name}
                              className="w-full h-96 object-contain bg-slate-50"
                            />
                          </div>
                        </div>
                      )}
                    </div>
                  </div>
                </div>
              </div>
            )}

            {/* 工作經驗資訊 */}
            {type === 'experiences' && (
              <div className="space-y-4">
                <div className="flex items-start gap-4">
                  <div className="p-3 bg-purple-50 rounded-xl">
                    <Briefcase className="h-8 w-8 text-purple-600" />
                  </div>
                  <div className="flex-1">
                    <h3 className="text-xl font-semibold text-slate-900">{item.position}</h3>
                    <p className="text-lg text-slate-600 mt-1">{item.company}</p>
                    <div className="mt-3 space-y-3">
                      <div className="flex items-center gap-2 text-slate-600">
                        <Calendar className="h-4 w-4" />
                        <span>
                          {formatDate(item.start_date)} -{' '}
                          {item.end_date ? formatDate(item.end_date) : '至今'}
                        </span>
                      </div>

                      {item.description && (
                        <div>
                          <label className="text-sm font-semibold text-slate-700">工作描述</label>
                          <p className="text-slate-900 mt-1 whitespace-pre-line">
                            {item.description}
                          </p>
                        </div>
                      )}
                    </div>
                  </div>
                </div>
              </div>
            )}
          </div>

          {/* Footer */}
          <div className="flex gap-3 p-6 border-t border-slate-200">
            <Button
              onClick={onApprove}
              isLoading={isLoading}
              className="flex-1"
            >
              <CheckCircle2 className="mr-2 h-4 w-4" />
              審核通過
            </Button>
            <Button
              variant="outline"
              onClick={onReject}
              disabled={isLoading}
              className="flex-1 text-red-600 hover:text-red-700 hover:bg-red-50"
            >
              <XCircle className="mr-2 h-4 w-4" />
              拒絕
            </Button>
            <Button
              variant="outline"
              onClick={onClose}
              disabled={isLoading}
            >
              關閉
            </Button>
          </div>
        </div>
      </div>
    </>
  );
}

export default function ApprovalsPage() {
  const [activeTab, setActiveTab] = useState<ApprovalType>('users');
  const [selectedItem, setSelectedItem] = useState<any>(null);
  const [detailType, setDetailType] = useState<ApprovalType>('users');

  // 獲取待審核項目
  const { data: pendingData, isLoading } = usePendingApprovals();

  // Mutations
  const approveUserMutation = useApproveUser();
  const rejectUserMutation = useRejectUser();
  const approveCompanyMutation = useApproveCompany();
  const rejectCompanyMutation = useRejectCompany();
  const approveCertMutation = useApproveCertification();
  const rejectCertMutation = useRejectCertification();
  const approveExpMutation = useApproveExperience();
  const rejectExpMutation = useRejectExperience();

  const handleViewDetail = (item: any, type: ApprovalType) => {
    setSelectedItem(item);
    setDetailType(type);
  };

  const handleApprove = () => {
    if (!selectedItem) return;

    switch (detailType) {
      case 'users':
        approveUserMutation.mutate(selectedItem.id, {
          onSuccess: () => setSelectedItem(null),
        });
        break;
      case 'companies':
        approveCompanyMutation.mutate(selectedItem.id, {
          onSuccess: () => setSelectedItem(null),
        });
        break;
      case 'certifications':
        approveCertMutation.mutate(selectedItem.id, {
          onSuccess: () => setSelectedItem(null),
        });
        break;
      case 'experiences':
        approveExpMutation.mutate(selectedItem.id, {
          onSuccess: () => setSelectedItem(null),
        });
        break;
    }
  };

  const handleReject = () => {
    if (!selectedItem) return;

    const reason = prompt('請輸入拒絕原因（選填）：');
    if (reason === null) return;

    switch (detailType) {
      case 'users':
        rejectUserMutation.mutate(
          { userId: selectedItem.id, reason: reason || undefined },
          { onSuccess: () => setSelectedItem(null) }
        );
        break;
      case 'companies':
        rejectCompanyMutation.mutate(
          { companyId: selectedItem.id, reason: reason || undefined },
          { onSuccess: () => setSelectedItem(null) }
        );
        break;
      case 'certifications':
        rejectCertMutation.mutate(
          { certId: selectedItem.id, reason: reason || undefined },
          { onSuccess: () => setSelectedItem(null) }
        );
        break;
      case 'experiences':
        rejectExpMutation.mutate(
          { expId: selectedItem.id, reason: reason || undefined },
          { onSuccess: () => setSelectedItem(null) }
        );
        break;
    }
  };

  if (isLoading) {
    return (
      <div className="space-y-6">
        <h1 className="text-2xl font-bold text-slate-900">待審核項目</h1>
        <Card>
          <CardContent className="p-8">
            <ProfileSkeleton />
          </CardContent>
        </Card>
      </div>
    );
  }

  const tabs = [
    {
      id: 'users' as ApprovalType,
      label: '業務員註冊',
      icon: Users,
      count: pendingData?.users?.length || 0,
      color: 'primary',
    },
    {
      id: 'companies' as ApprovalType,
      label: '公司資訊',
      icon: Building2,
      count: pendingData?.companies?.length || 0,
      color: 'blue',
    },
    {
      id: 'certifications' as ApprovalType,
      label: '專業證照',
      icon: Award,
      count: pendingData?.certifications?.length || 0,
      color: 'green',
    },
    {
      id: 'experiences' as ApprovalType,
      label: '工作經驗',
      icon: Briefcase,
      count: pendingData?.profiles?.filter((p: any) => p.approval_status === 'pending').length || 0,
      color: 'purple',
    },
  ];

  const getItemsByTab = () => {
    switch (activeTab) {
      case 'users':
        return pendingData?.users || [];
      case 'companies':
        return pendingData?.companies || [];
      case 'certifications':
        return pendingData?.certifications || [];
      case 'experiences':
        return pendingData?.profiles?.filter((p: any) => p.approval_status === 'pending') || [];
      default:
        return [];
    }
  };

  const items = getItemsByTab();
  const activeTabData = tabs.find((t) => t.id === activeTab);

  const isAnyMutationLoading =
    approveUserMutation.isPending ||
    rejectUserMutation.isPending ||
    approveCompanyMutation.isPending ||
    rejectCompanyMutation.isPending ||
    approveCertMutation.isPending ||
    rejectCertMutation.isPending ||
    approveExpMutation.isPending ||
    rejectExpMutation.isPending;

  return (
    <div className="space-y-6">
      {/* 頁面標題 */}
      <div>
        <h1 className="text-2xl font-bold text-slate-900">待審核項目</h1>
        <p className="text-slate-600 mt-1">審核業務員註冊、公司資訊、證照與工作經驗</p>
      </div>

      {/* Tabs */}
      <div className="flex gap-4 flex-wrap">
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
              {tab.count > 0 && (
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
              )}
            </button>
          );
        })}
      </div>

      {/* Content */}
      <Card>
        <CardContent className="p-6">
          {items.length > 0 ? (
            <div className="space-y-4">
              {items.map((item: any) => (
                <div
                  key={item.id}
                  className="flex items-center justify-between p-4 border-2 border-slate-200 rounded-xl hover:border-primary-300 transition-colors"
                >
                  <div className="flex items-start gap-4 flex-1">
                    <div className={`p-3 bg-${activeTabData?.color}-50 rounded-xl`}>
                      {activeTabData && <activeTabData.icon className={`h-6 w-6 text-${activeTabData.color}-600`} />}
                    </div>

                    <div className="flex-1">
                      <h4 className="font-semibold text-slate-900">
                        {activeTab === 'users' && item.username}
                        {activeTab === 'companies' && item.name}
                        {activeTab === 'certifications' && item.name}
                        {activeTab === 'experiences' && item.position}
                      </h4>

                      <p className="text-sm text-slate-600 mt-1">
                        {activeTab === 'users' && item.email}
                        {activeTab === 'companies' && `統編：${item.tax_id}`}
                        {activeTab === 'certifications' && `發證單位：${item.issuer}`}
                        {activeTab === 'experiences' && item.company}
                      </p>

                      <p className="text-xs text-slate-500 mt-1">
                        {formatDate(item.created_at || item.issue_date)}
                      </p>
                    </div>
                  </div>

                  <div className="flex gap-2">
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => handleViewDetail(item, activeTab)}
                    >
                      <Eye className="h-4 w-4" />
                    </Button>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="text-center py-16">
              {activeTabData && <activeTabData.icon className="h-16 w-16 text-slate-300 mx-auto mb-4" />}
              <h3 className="text-lg font-semibold text-slate-900 mb-2">
                目前沒有待審核的{activeTabData?.label}
              </h3>
              <p className="text-slate-600">所有項目都已處理完畢</p>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Detail Modal */}
      <DetailModal
        isOpen={!!selectedItem}
        onClose={() => setSelectedItem(null)}
        type={detailType}
        item={selectedItem}
        onApprove={handleApprove}
        onReject={handleReject}
        isLoading={isAnyMutationLoading}
      />
    </div>
  );
}
