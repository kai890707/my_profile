'use client';

import { useState, useMemo } from 'react';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ProfileSkeleton } from '@/components/ui/skeleton';
import { StatusBadge } from '@/components/ui/status-badge';
import { Textarea } from '@/components/ui/textarea';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
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
  Search,
  Filter,
  X,
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

// Helper 函數：取得項目名稱
function getItemName(item: any, type: ApprovalType): string {
  switch (type) {
    case 'users':
      return item.username || item.email || '業務員';
    case 'companies':
      return item.name || '公司';
    case 'certifications':
      return item.name || '證照';
    case 'experiences':
      return item.position || '工作經驗';
    default:
      return '項目';
  }
}

// Helper 函數：取得公司名稱（處理類型不一致）
function getCompanyName(company: string | { name: string } | undefined): string {
  if (!company) return '未指定';
  if (typeof company === 'string') return company;
  return company.name || '未指定';
}

type DateFilter = 'all' | 'today' | 'week' | 'month';

// Helper 函數：根據搜尋關鍵字篩選
function filterBySearch(items: any[], query: string, type: ApprovalType): any[] {
  if (!query.trim()) return items;

  const lowerQuery = query.toLowerCase();

  return items.filter((item) => {
    switch (type) {
      case 'users':
        return (
          item.username?.toLowerCase().includes(lowerQuery) ||
          item.email?.toLowerCase().includes(lowerQuery)
        );
      case 'companies':
        return (
          item.name?.toLowerCase().includes(lowerQuery) ||
          item.tax_id?.toLowerCase().includes(lowerQuery)
        );
      case 'certifications':
        return (
          item.name?.toLowerCase().includes(lowerQuery) ||
          item.issuer?.toLowerCase().includes(lowerQuery)
        );
      case 'experiences':
        const companyName = getCompanyName(item.company).toLowerCase();
        return (
          item.position?.toLowerCase().includes(lowerQuery) ||
          companyName.includes(lowerQuery)
        );
      default:
        return true;
    }
  });
}

// Helper 函數：根據日期範圍篩選
function filterByDate(items: any[], dateFilter: DateFilter): any[] {
  if (dateFilter === 'all') return items;

  const now = new Date();
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

  return items.filter((item) => {
    const itemDate = new Date(item.created_at || item.issue_date);
    const itemDateOnly = new Date(itemDate.getFullYear(), itemDate.getMonth(), itemDate.getDate());

    switch (dateFilter) {
      case 'today':
        return itemDateOnly.getTime() === today.getTime();
      case 'week':
        const weekAgo = new Date(today);
        weekAgo.setDate(weekAgo.getDate() - 7);
        return itemDateOnly >= weekAgo;
      case 'month':
        const monthAgo = new Date(today);
        monthAgo.setDate(monthAgo.getDate() - 30);
        return itemDateOnly >= monthAgo;
      default:
        return true;
    }
  });
}

interface ApprovalFiltersProps {
  searchQuery: string;
  onSearchChange: (query: string) => void;
  dateFilter: DateFilter;
  onDateFilterChange: (filter: DateFilter) => void;
  resultCount: number;
  totalCount: number;
}

function ApprovalFilters({
  searchQuery,
  onSearchChange,
  dateFilter,
  onDateFilterChange,
  resultCount,
  totalCount,
}: ApprovalFiltersProps) {
  return (
    <div className="space-y-4">
      {/* 搜尋和篩選控制 */}
      <div className="flex flex-col md:flex-row gap-4">
        {/* 搜尋框 */}
        <div className="flex-1 relative">
          <Search className="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" />
          <input
            type="text"
            value={searchQuery}
            onChange={(e) => onSearchChange(e.target.value)}
            placeholder="搜尋項目..."
            className="w-full h-11 pl-11 pr-10 rounded-xl border-2 border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 hover:border-slate-300 transition-all"
          />
          {searchQuery && (
            <button
              onClick={() => onSearchChange('')}
              className="absolute right-3 top-1/2 -translate-y-1/2 p-1 hover:bg-slate-100 rounded-lg transition-colors"
            >
              <X className="h-4 w-4 text-slate-400" />
            </button>
          )}
        </div>

        {/* 日期篩選 */}
        <div className="relative">
          <Filter className="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 pointer-events-none" />
          <select
            value={dateFilter}
            onChange={(e) => onDateFilterChange(e.target.value as DateFilter)}
            className="h-11 pl-11 pr-10 rounded-xl border-2 border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 hover:border-slate-300 transition-all cursor-pointer appearance-none"
          >
            <option value="all">所有日期</option>
            <option value="today">今天</option>
            <option value="week">最近 7 天</option>
            <option value="month">最近 30 天</option>
          </select>
          <svg
            className="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
          </svg>
        </div>
      </div>

      {/* 結果計數 */}
      {(searchQuery || dateFilter !== 'all') && (
        <div className="flex items-center justify-between text-sm">
          <p className="text-slate-600">
            找到 <span className="font-semibold text-slate-900">{resultCount}</span> 個項目
            {resultCount !== totalCount && (
              <span className="text-slate-500"> (共 {totalCount} 個)</span>
            )}
          </p>
          {(searchQuery || dateFilter !== 'all') && (
            <button
              onClick={() => {
                onSearchChange('');
                onDateFilterChange('all');
              }}
              className="text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1"
            >
              <X className="h-4 w-4" />
              清除篩選
            </button>
          )}
        </div>
      )}
    </div>
  );
}

interface RejectModalProps {
  isOpen: boolean;
  onClose: () => void;
  onConfirm: (reason: string) => void;
  isLoading: boolean;
  itemType: ApprovalType;
  itemName: string;
}

function RejectModal({
  isOpen,
  onClose,
  onConfirm,
  isLoading,
  itemType,
  itemName,
}: RejectModalProps) {
  const [reason, setReason] = useState('');
  const [error, setError] = useState('');

  const typeLabels = {
    users: '業務員註冊',
    companies: '公司資訊',
    certifications: '專業證照',
    experiences: '工作經驗',
  };

  const handleSubmit = () => {
    // 驗證：不能空白
    if (!reason.trim()) {
      setError('請輸入拒絕原因');
      return;
    }

    // 驗證：最少 10 字元
    if (reason.trim().length < 10) {
      setError('拒絕原因至少需要 10 個字元');
      return;
    }

    // 清除錯誤並提交
    setError('');
    onConfirm(reason.trim());
  };

  const handleClose = () => {
    setReason('');
    setError('');
    onClose();
  };

  return (
    <Dialog open={isOpen} onOpenChange={handleClose}>
      <DialogContent className="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2 text-red-600">
            <XCircle className="h-5 w-5" />
            拒絕{typeLabels[itemType]}
          </DialogTitle>
          <DialogDescription>
            確定要拒絕「{itemName}」嗎？請提供詳細的拒絕原因。
          </DialogDescription>
        </DialogHeader>

        <div className="py-4">
          <Textarea
            label="拒絕原因"
            required
            value={reason}
            onChange={(e) => {
              setReason(e.target.value);
              if (error) setError('');
            }}
            rows={5}
            placeholder="請詳細說明拒絕的原因（至少 10 個字元）"
            error={error}
            disabled={isLoading}
          />
        </div>

        <DialogFooter>
          <Button
            variant="outline"
            onClick={handleClose}
            disabled={isLoading}
          >
            取消
          </Button>
          <Button
            onClick={handleSubmit}
            isLoading={isLoading}
            className="bg-red-600 hover:bg-red-700 text-white"
          >
            <XCircle className="mr-2 h-4 w-4" />
            確認拒絕
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}

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
                    <p className="text-lg text-slate-600 mt-1">
                      {getCompanyName(item.company)}
                    </p>
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
  const [isRejectModalOpen, setIsRejectModalOpen] = useState(false);
  const [rejectItemName, setRejectItemName] = useState('');
  const [searchQuery, setSearchQuery] = useState('');
  const [dateFilter, setDateFilter] = useState<DateFilter>('all');

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
    const itemName = getItemName(selectedItem, detailType);
    setRejectItemName(itemName);
    setIsRejectModalOpen(true);
  };

  const handleRejectConfirm = (reason: string) => {
    if (!selectedItem) return;

    switch (detailType) {
      case 'users':
        rejectUserMutation.mutate(
          { userId: selectedItem.id, reason },
          {
            onSuccess: () => {
              setSelectedItem(null);
              setIsRejectModalOpen(false);
            },
          }
        );
        break;
      case 'companies':
        rejectCompanyMutation.mutate(
          { companyId: selectedItem.id, reason },
          {
            onSuccess: () => {
              setSelectedItem(null);
              setIsRejectModalOpen(false);
            },
          }
        );
        break;
      case 'certifications':
        rejectCertMutation.mutate(
          { certId: selectedItem.id, reason },
          {
            onSuccess: () => {
              setSelectedItem(null);
              setIsRejectModalOpen(false);
            },
          }
        );
        break;
      case 'experiences':
        rejectExpMutation.mutate(
          { expId: selectedItem.id, reason },
          {
            onSuccess: () => {
              setSelectedItem(null);
              setIsRejectModalOpen(false);
            },
          }
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
      count: pendingData?.experiences?.length || 0,
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
        return pendingData?.experiences || [];
      default:
        return [];
    }
  };

  const rawItems = getItemsByTab();
  const activeTabData = tabs.find((t) => t.id === activeTab);

  // 篩選邏輯
  const filteredItems = useMemo(() => {
    let result = rawItems;
    result = filterBySearch(result, searchQuery, activeTab);
    result = filterByDate(result, dateFilter);
    return result;
  }, [rawItems, searchQuery, dateFilter, activeTab]);

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

      {/* 搜尋與篩選 */}
      <ApprovalFilters
        searchQuery={searchQuery}
        onSearchChange={setSearchQuery}
        dateFilter={dateFilter}
        onDateFilterChange={setDateFilter}
        resultCount={filteredItems.length}
        totalCount={rawItems.length}
      />

      {/* Content */}
      <Card>
        <CardContent className="p-6">
          {filteredItems.length > 0 ? (
            <div className="space-y-4">
              {filteredItems.map((item: any) => (
                <div
                  key={item.id}
                  className="flex items-center justify-between p-4 border-2 border-slate-200 rounded-xl hover:border-primary-300 transition-colors"
                >
                  <div className="flex items-start gap-4 flex-1">
                    <div className={`p-3 bg-${activeTabData?.color}-50 rounded-xl`}>
                      {activeTabData && (() => {
                        const Icon = activeTabData.icon;
                        return <Icon className={`h-6 w-6 text-${activeTabData.color}-600`} />;
                      })()}
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
                        {activeTab === 'companies' && `統編：${item.tax_id || '無'}`}
                        {activeTab === 'certifications' && `發證單位：${item.issuer}`}
                        {activeTab === 'experiences' && getCompanyName(item.company)}
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
              {searchQuery || dateFilter !== 'all' ? (
                // 搜尋無結果
                <>
                  <Search className="h-16 w-16 text-slate-300 mx-auto mb-4" />
                  <h3 className="text-lg font-semibold text-slate-900 mb-2">
                    找不到符合條件的項目
                  </h3>
                  <p className="text-slate-600 mb-4">
                    請嘗試調整搜尋關鍵字或篩選條件
                  </p>
                  <Button
                    variant="outline"
                    onClick={() => {
                      setSearchQuery('');
                      setDateFilter('all');
                    }}
                  >
                    <X className="mr-2 h-4 w-4" />
                    清除所有篩選
                  </Button>
                </>
              ) : (
                // 真的沒有待審核項目
                <>
                  {activeTabData && (() => {
                    const Icon = activeTabData.icon;
                    return <Icon className="h-16 w-16 text-slate-300 mx-auto mb-4" />;
                  })()}
                  <h3 className="text-lg font-semibold text-slate-900 mb-2">
                    目前沒有待審核的{activeTabData?.label}
                  </h3>
                  <p className="text-slate-600">所有項目都已處理完畢</p>
                </>
              )}
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

      {/* Reject Modal */}
      <RejectModal
        isOpen={isRejectModalOpen}
        onClose={() => setIsRejectModalOpen(false)}
        onConfirm={handleRejectConfirm}
        isLoading={isAnyMutationLoading}
        itemType={detailType}
        itemName={rejectItemName}
      />
    </div>
  );
}
