# 變更提案：修復頭像選單缺少 Dashboard 連結及頁面重整跳轉問題

**提案日期**: 2026-01-15
**提案者**: Development Team
**狀態**: ✅ 已完成

---

## 1. 變更概述

### 1.1 問題描述

用戶報告兩個問題：
1. **頭像選單缺少 Dashboard 連結** - 點擊頭像後的下拉選單只顯示「我的帳號」和「登出」，沒有導向 Dashboard 的選項
2. **業務員回到首頁後角色顯示錯誤** - 業務員在 Dashboard 時選單正確，但回到首頁後角色變成「使用者」

### 1.2 影響範圍

- **頁面**: Header 組件（全站）、Dashboard 頁面、首頁
- **功能**: 導航選單、使用者角色識別
- **使用者**: 所有已登入使用者

### 1.3 優先級

**高** - 影響核心導航體驗

---

## 2. 根本原因分析

### 2.1 問題 1：選單對一般使用者不顯示連結

**檔案**: `frontend/components/layout/header.tsx`

**現有邏輯**:
```typescript
const getDashboardLinks = () => {
  if (user?.role === 'admin') return adminLinks;
  if (user?.role === 'salesperson') return salespersonLinks;
  return [];  // ← 一般使用者返回空陣列
};
```

**問題**: 當使用者角色為 `user`（一般使用者）時，函數返回空陣列，導致選單沒有任何功能連結。

### 2.2 問題 2：useAuth API 回應結構不匹配

**檔案**: `frontend/hooks/useAuth.ts`

**Backend API `/auth/me` 回傳格式**:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "role": "salesperson",
      ...
    }
  }
}
```

**Frontend useAuth hook 處理**:
```typescript
// ❌ 錯誤：直接返回 response.data
return response.data;  // 返回 { user: {...} }
```

**結果**: `user` 變成 `{ user: {...} }` 而非直接的 user 物件，導致 `user.role` 為 `undefined`，header 無法正確判斷角色。

### 2.3 問題 3：salespersonLinks 指向不存在的頁面

**檔案**: `frontend/components/layout/header.tsx`

```typescript
const salespersonLinks = [
  { href: '/dashboard', label: '個人中心', icon: LayoutDashboard },
  { href: '/dashboard/profile', label: '個人資料', icon: User },  // ← 404
];
```

**問題**: `/dashboard/profile` 頁面不存在，個人資料功能在 `/dashboard` 主頁。

---

## 3. 解決方案

### 3.1 修復頭像選單 - 為一般使用者新增連結

**檔案**: `frontend/components/layout/header.tsx`

```typescript
// 新增 import
import { Home, Search } from 'lucide-react';

// 新增一般使用者連結
const userLinks = [
  { href: '/', label: '首頁', icon: Home },
  { href: '/search', label: '搜尋業務員', icon: Search },
];

const getDashboardLinks = () => {
  if (user?.role === 'admin') return adminLinks;
  if (user?.role === 'salesperson') return salespersonLinks;
  return userLinks;  // ← 一般使用者顯示基本連結
};
```

### 3.2 修復 useAuth hook

**檔案**: `frontend/hooks/useAuth.ts`

```typescript
// ✅ 修復後：解包 user 物件
export function useAuth() {
  return useQuery({
    queryKey: queryKeys.auth.me,
    queryFn: async () => {
      const response = await getCurrentUser();
      // Backend 返回 { success: true, data: { user: {...} } }
      // 需要解包取出 user
      const data = response.data as { user?: any } | any;
      return data?.user ?? data;
    },
    retry: false,
    staleTime: 5 * 60 * 1000,
  });
}
```

### 3.3 修復 salespersonLinks 指向正確頁面

**檔案**: `frontend/components/layout/header.tsx`

```typescript
const salespersonLinks = [
  { href: '/dashboard', label: '個人中心', icon: LayoutDashboard },
  { href: '/dashboard/experiences', label: '工作經驗', icon: User },  // ← 修正
];
```

---

## 4. 驗收標準

- [x] AC-1: 業務員點擊頭像後，選單顯示「個人中心」連結導向 `/dashboard`
- [x] AC-2: 業務員回到首頁後，頭像選單仍正確顯示業務員選項
- [x] AC-3: 業務員在 Dashboard 頁面重新整理後，仍停留在 Dashboard
- [x] AC-4: 一般使用者點擊頭像後，選單顯示「首頁」和「搜尋業務員」連結
- [x] AC-5: TypeScript 編譯無錯誤
- [x] AC-6: 選單連結都指向實際存在的頁面

---

## 5. 變更記錄

| 檔案 | 變更類型 | 說明 |
|------|----------|------|
| `frontend/components/layout/header.tsx` | 修改 | 新增 import、為一般使用者新增 userLinks、修正 salespersonLinks |
| `frontend/hooks/useAuth.ts` | 修改 | 解包 API 回應中的 user 物件 |

---

## 6. 測試結果

- [x] 業務員登入後，首頁頭像選單顯示正確角色和連結
- [x] 業務員點擊「個人中心」可進入 Dashboard
- [x] 業務員點擊「工作經驗」可進入 /dashboard/experiences
- [x] 業務員 Dashboard 頁面重整後維持在該頁面
- [x] 一般使用者登入後頭像選單顯示正確連結
- [x] TypeScript 編譯通過

---

## 7. Git 記錄

**Branch**: `feature/20260115-fix-header-dropdown-and-dashboard-access`
**Status**: ✅ 已完成
