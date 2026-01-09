'use client';

import { useState } from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ProfileSkeleton } from '@/components/ui/skeleton';
import {
  Users as UsersIcon,
  Search,
  Filter,
  Trash2,
  CheckCircle2,
  XCircle,
  Mail,
  Calendar,
  Shield,
} from 'lucide-react';
import { useUsers, useUpdateUserStatus, useDeleteUser } from '@/hooks/useAdmin';
import { formatDate } from '@/lib/utils/format';
import type { User, UserRole, UserStatus } from '@/types/api';

export default function UsersManagementPage() {
  const [roleFilter, setRoleFilter] = useState<UserRole | 'all'>('all');
  const [statusFilter, setStatusFilter] = useState<UserStatus | 'all'>('all');
  const [searchQuery, setSearchQuery] = useState('');

  // 獲取使用者列表
  const params = {
    ...(roleFilter !== 'all' && { role: roleFilter }),
    ...(statusFilter !== 'all' && { status: statusFilter }),
  };

  const { data: users, isLoading } = useUsers(params);

  // Mutations
  const updateStatusMutation = useUpdateUserStatus();
  const deleteUserMutation = useDeleteUser();

  // 過濾使用者（客戶端搜尋）
  const filteredUsers = users?.filter((user) => {
    if (!searchQuery) return true;
    const query = searchQuery.toLowerCase();
    return (
      user.username.toLowerCase().includes(query) ||
      user.email.toLowerCase().includes(query)
    );
  });

  const handleToggleStatus = (user: User) => {
    const newStatus = user.status === 'active' ? 'inactive' : 'active';
    const action = newStatus === 'active' ? '啟用' : '停用';

    if (confirm(`確定要${action}使用者「${user.username}」嗎？`)) {
      updateStatusMutation.mutate({ userId: user.id, status: newStatus });
    }
  };

  const handleDeleteUser = (user: User) => {
    if (
      confirm(
        `確定要刪除使用者「${user.username}」嗎？\n\n此操作無法復原，請謹慎操作。`
      )
    ) {
      deleteUserMutation.mutate(user.id);
    }
  };

  // 角色顯示配置
  const getRoleBadge = (role: UserRole) => {
    switch (role) {
      case 'admin':
        return {
          variant: 'error' as const,
          label: '管理員',
          icon: Shield,
        };
      case 'salesperson':
        return {
          variant: 'primary' as const,
          label: '業務員',
          icon: UsersIcon,
        };
      case 'user':
        return {
          variant: 'secondary' as const,
          label: '一般用戶',
          icon: UsersIcon,
        };
    }
  };

  // 狀態顯示配置
  const getStatusBadge = (status: UserStatus) => {
    switch (status) {
      case 'active':
        return {
          variant: 'success' as const,
          label: '啟用中',
        };
      case 'inactive':
        return {
          variant: 'secondary' as const,
          label: '已停用',
        };
      case 'pending':
        return {
          variant: 'warning' as const,
          label: '待審核',
        };
    }
  };

  if (isLoading) {
    return (
      <div className="space-y-6">
        <h1 className="text-2xl font-bold text-slate-900">使用者管理</h1>
        <Card>
          <CardContent className="p-8">
            <ProfileSkeleton />
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* 頁面標題 */}
      <div>
        <h1 className="text-2xl font-bold text-slate-900">使用者管理</h1>
        <p className="text-slate-600 mt-1">管理平台所有使用者帳號</p>
      </div>

      {/* 篩選和搜尋 */}
      <Card>
        <CardContent className="p-6">
          <div className="flex flex-col md:flex-row gap-4">
            {/* 搜尋框 */}
            <div className="flex-1 relative">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" />
              <input
                type="text"
                placeholder="搜尋使用者名稱或 Email..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border-2 border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
              />
            </div>

            {/* 角色篩選 */}
            <div className="flex items-center gap-2">
              <Filter className="h-5 w-5 text-slate-400" />
              <select
                value={roleFilter}
                onChange={(e) => setRoleFilter(e.target.value as UserRole | 'all')}
                className="px-4 py-2 border-2 border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
              >
                <option value="all">所有角色</option>
                <option value="admin">管理員</option>
                <option value="salesperson">業務員</option>
                <option value="user">一般用戶</option>
              </select>
            </div>

            {/* 狀態篩選 */}
            <select
              value={statusFilter}
              onChange={(e) => setStatusFilter(e.target.value as UserStatus | 'all')}
              className="px-4 py-2 border-2 border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
            >
              <option value="all">所有狀態</option>
              <option value="active">啟用中</option>
              <option value="inactive">已停用</option>
              <option value="pending">待審核</option>
            </select>
          </div>
        </CardContent>
      </Card>

      {/* 使用者統計 */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card>
          <CardContent className="p-4">
            <div className="text-center">
              <p className="text-sm text-slate-600">總使用者數</p>
              <p className="text-2xl font-bold text-slate-900 mt-1">
                {users?.length || 0}
              </p>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-4">
            <div className="text-center">
              <p className="text-sm text-slate-600">管理員</p>
              <p className="text-2xl font-bold text-red-600 mt-1">
                {users?.filter((u) => u.role === 'admin').length || 0}
              </p>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-4">
            <div className="text-center">
              <p className="text-sm text-slate-600">業務員</p>
              <p className="text-2xl font-bold text-primary-600 mt-1">
                {users?.filter((u) => u.role === 'salesperson').length || 0}
              </p>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-4">
            <div className="text-center">
              <p className="text-sm text-slate-600">啟用中</p>
              <p className="text-2xl font-bold text-green-600 mt-1">
                {users?.filter((u) => u.status === 'active').length || 0}
              </p>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* 使用者列表 */}
      <Card>
        <CardContent className="p-0">
          {filteredUsers && filteredUsers.length > 0 ? (
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b border-slate-200 bg-slate-50">
                    <th className="px-6 py-4 text-left text-sm font-semibold text-slate-700">
                      使用者
                    </th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-slate-700">
                      角色
                    </th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-slate-700">
                      狀態
                    </th>
                    <th className="px-6 py-4 text-left text-sm font-semibold text-slate-700">
                      註冊時間
                    </th>
                    <th className="px-6 py-4 text-right text-sm font-semibold text-slate-700">
                      操作
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {filteredUsers.map((user) => {
                    const roleBadge = getRoleBadge(user.role);
                    const statusBadge = getStatusBadge(user.status);

                    return (
                      <tr
                        key={user.id}
                        className="border-b border-slate-200 hover:bg-slate-50 transition-colors"
                      >
                        {/* 使用者資訊 */}
                        <td className="px-6 py-4">
                          <div>
                            <div className="font-semibold text-slate-900">
                              {user.username}
                            </div>
                            <div className="flex items-center gap-1 text-sm text-slate-600 mt-1">
                              <Mail className="h-3 w-3" />
                              {user.email}
                            </div>
                          </div>
                        </td>

                        {/* 角色 */}
                        <td className="px-6 py-4">
                          <Badge variant={roleBadge.variant} size="sm">
                            {roleBadge.label}
                          </Badge>
                        </td>

                        {/* 狀態 */}
                        <td className="px-6 py-4">
                          <Badge variant={statusBadge.variant} size="sm">
                            {statusBadge.label}
                          </Badge>
                        </td>

                        {/* 註冊時間 */}
                        <td className="px-6 py-4">
                          <div className="flex items-center gap-1 text-sm text-slate-600">
                            <Calendar className="h-3 w-3" />
                            {formatDate(user.created_at)}
                          </div>
                        </td>

                        {/* 操作 */}
                        <td className="px-6 py-4">
                          <div className="flex items-center justify-end gap-2">
                            {/* 切換狀態按鈕 */}
                            {user.status !== 'pending' && (
                              <Button
                                variant="ghost"
                                size="sm"
                                onClick={() => handleToggleStatus(user)}
                                disabled={
                                  updateStatusMutation.isPending ||
                                  deleteUserMutation.isPending
                                }
                                title={
                                  user.status === 'active' ? '停用使用者' : '啟用使用者'
                                }
                              >
                                {user.status === 'active' ? (
                                  <XCircle className="h-4 w-4 text-orange-600" />
                                ) : (
                                  <CheckCircle2 className="h-4 w-4 text-green-600" />
                                )}
                              </Button>
                            )}

                            {/* 刪除按鈕 */}
                            <Button
                              variant="ghost"
                              size="sm"
                              onClick={() => handleDeleteUser(user)}
                              disabled={
                                updateStatusMutation.isPending ||
                                deleteUserMutation.isPending ||
                                user.role === 'admin' // 禁止刪除管理員
                              }
                              title={
                                user.role === 'admin'
                                  ? '無法刪除管理員帳號'
                                  : '刪除使用者'
                              }
                              className="text-red-600 hover:text-red-700 hover:bg-red-50"
                            >
                              <Trash2 className="h-4 w-4" />
                            </Button>
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          ) : (
            <div className="text-center py-16">
              <UsersIcon className="h-16 w-16 text-slate-300 mx-auto mb-4" />
              <h3 className="text-lg font-semibold text-slate-900 mb-2">
                {searchQuery || roleFilter !== 'all' || statusFilter !== 'all'
                  ? '找不到符合條件的使用者'
                  : '尚未有使用者'}
              </h3>
              <p className="text-slate-600">
                {searchQuery || roleFilter !== 'all' || statusFilter !== 'all'
                  ? '請嘗試調整篩選條件'
                  : '等待使用者註冊'}
              </p>
            </div>
          )}
        </CardContent>
      </Card>

      {/* 說明 */}
      <div className="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <h4 className="font-semibold text-blue-900 mb-2">操作說明</h4>
        <ul className="text-sm text-blue-800 space-y-1">
          <li>• 點擊「啟用/停用」圖標可切換使用者狀態</li>
          <li>• 「待審核」狀態的使用者需要先在審核頁面處理</li>
          <li>• 管理員帳號無法刪除，以確保系統安全</li>
          <li>• 刪除操作無法復原，請謹慎操作</li>
        </ul>
      </div>
    </div>
  );
}
