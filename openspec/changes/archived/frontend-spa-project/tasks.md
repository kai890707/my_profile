# Implementation Tasks: YAMU Frontend SPA

**Status**: Ready for Implementation
**Estimated Time**: 9-15 days (72-120 hours)
**Created**: 2026-01-08

---

## Task Overview

| Phase | Tasks | Estimated Time | Dependencies |
|-------|-------|----------------|--------------|
| Phase 1: Project Setup | 8 tasks | 4-6 hours | None |
| Phase 2: Authentication | 6 tasks | 6-8 hours | Phase 1 |
| Phase 3: Shared Components | 10 tasks | 8-12 hours | Phase 1 |
| Phase 4: Public Pages | 8 tasks | 12-16 hours | Phase 2, 3 |
| Phase 5: Dashboard | 12 tasks | 16-20 hours | Phase 2, 3 |
| Phase 6: Admin Panel | 10 tasks | 12-16 hours | Phase 2, 3 |
| Phase 7: Testing & Polish | 6 tasks | 8-12 hours | All phases |

**Total**: 60 tasks, 66-90 hours

---

## Phase 1: Project Setup & Foundation (4-6 hours)

### Task 1.1: Initialize Next.js Project ⏱️ 30min

**Command**:
```bash
npx create-next-app@latest frontend --typescript --tailwind --app --no-src-dir
cd frontend
```

**Configuration**:
- TypeScript: Yes
- Tailwind CSS: Yes
- App Router: Yes
- Import alias: `@/*`

**Verification**:
- [ ] `npm run dev` 啟動成功
- [ ] Localhost:3000 顯示預設頁面

---

### Task 1.2: Install Dependencies ⏱️ 20min

**File**: `frontend/package.json`

**Dependencies**:
```bash
# UI & Styling
npm install @radix-ui/react-dropdown-menu @radix-ui/react-dialog @radix-ui/react-select
npm install @radix-ui/react-tabs @radix-ui/react-avatar
npm install class-variance-authority clsx tailwind-merge lucide-react

# State Management
npm install @tanstack/react-query zustand

# Forms & Validation
npm install react-hook-form @hookform/resolvers zod

# HTTP Client
npm install axios

# Notifications
npm install sonner

# Dev Dependencies
npm install -D @types/node @types/react @types/react-dom
```

**Verification**:
- [ ] `package.json` 包含所有套件
- [ ] `npm install` 執行成功
- [ ] 無版本衝突錯誤

---

### Task 1.3: Setup Tailwind Configuration ⏱️ 30min

**File**: `tailwind.config.ts`

**Implementation**:
```typescript
import type { Config } from 'tailwindcss';

const config: Config = {
  darkMode: ['class'],
  content: [
    './pages/**/*.{ts,tsx}',
    './components/**/*.{ts,tsx}',
    './app/**/*.{ts,tsx}',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#f0f9ff',
          100: '#e0f2fe',
          200: '#bae6fd',
          300: '#7dd3fc',
          400: '#38bdf8',
          500: '#0ea5e9',
          600: '#0284c7',
          700: '#0369a1',
          800: '#075985',
          900: '#0c4a6e',
        },
        secondary: {
          50: '#f0fdfa',
          100: '#ccfbf1',
          200: '#99f6e4',
          300: '#5eead4',
          400: '#2dd4bf',
          500: '#14b8a6',
          600: '#0d9488',
          700: '#0f766e',
          800: '#115e59',
          900: '#134e4a',
        },
        accent: {
          400: '#f472b6',
          500: '#ec4899',
        },
        background: '#f8fafc',
        foreground: '#0f172a',
      },
      fontFamily: {
        sans: ['var(--font-inter)', 'var(--font-noto-sans-tc)', 'sans-serif'],
      },
      borderRadius: {
        lg: '0.5rem',
        xl: '0.75rem',
        '2xl': '1rem',
      },
    },
  },
  plugins: [],
};

export default config;
```

**Verification**:
- [ ] Tailwind classes 正常運作
- [ ] 自訂色彩可用 (`bg-primary-500`)

---

### Task 1.4: Setup Fonts (Google Fonts) ⏱️ 20min

**File**: `app/layout.tsx`

**Implementation**:
```typescript
import { Inter, Noto_Sans_TC } from 'next/font/google';
import './globals.css';

const inter = Inter({
  subsets: ['latin'],
  variable: '--font-inter',
  display: 'swap',
});

const notoSansTC = Noto_Sans_TC({
  subsets: ['chinese-traditional'],
  weight: ['400', '500', '700'],
  variable: '--font-noto-sans-tc',
  display: 'swap',
});

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="zh-TW" className={`${inter.variable} ${notoSansTC.variable}`}>
      <body className="font-sans antialiased">
        {children}
      </body>
    </html>
  );
}
```

---

### Task 1.5: Setup Environment Variables ⏱️ 15min

**Files**:
- `.env.local` (開發環境)
- `.env.production` (生產環境)
- `.env.example` (範本)

**Content** (`.env.local`):
```env
# API Configuration
NEXT_PUBLIC_API_BASE_URL=http://localhost:8080/api

# App Configuration
NEXT_PUBLIC_APP_NAME=YAMU
NEXT_PUBLIC_APP_URL=http://localhost:3000
```

**Content** (`.env.example`):
```env
# Copy this file to .env.local and fill in your values

NEXT_PUBLIC_API_BASE_URL=
NEXT_PUBLIC_APP_NAME=YAMU
NEXT_PUBLIC_APP_URL=
```

---

### Task 1.6: Setup Directory Structure ⏱️ 20min

**Command**:
```bash
mkdir -p \
  app/\(public\) \
  app/\(auth\) \
  app/\(dashboard\)/dashboard \
  app/\(admin\)/admin \
  components/ui \
  components/layout \
  components/features \
  lib/api \
  lib/auth \
  lib/query \
  lib/utils \
  hooks \
  store \
  types \
  public/images
```

**Verification**:
- [ ] 所有目錄已建立
- [ ] 目錄結構符合規格

---

### Task 1.7: Setup Utility Functions ⏱️ 30min

**File**: `lib/utils/cn.ts`

```typescript
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

/**
 * Merge Tailwind CSS classes
 */
export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}
```

**File**: `lib/utils/format.ts`

```typescript
/**
 * 格式化日期
 */
export function formatDate(date: string | Date): string {
  return new Intl.DateTimeFormat('zh-TW', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).format(new Date(date));
}

/**
 * 格式化相對時間
 */
export function formatRelativeTime(date: string | Date): string {
  const now = new Date();
  const target = new Date(date);
  const diffInSeconds = Math.floor((now.getTime() - target.getTime()) / 1000);

  if (diffInSeconds < 60) return '剛剛';
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} 分鐘前`;
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} 小時前`;
  if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} 天前`;

  return formatDate(date);
}
```

---

### Task 1.8: Setup React Query Provider ⏱️ 30min

**File**: `lib/query/client.ts`

```typescript
import { QueryClient } from '@tanstack/react-query';

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 60 * 1000,
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
});
```

**File**: `app/providers.tsx`

```typescript
'use client';

import { QueryClientProvider } from '@tanstack/react-query';
import { ReactQueryDevtools } from '@tanstack/react-query-devtools';
import { queryClient } from '@/lib/query/client';
import { Toaster } from 'sonner';

export function Providers({ children }: { children: React.ReactNode }) {
  return (
    <QueryClientProvider client={queryClient}>
      {children}
      <Toaster position="top-right" richColors />
      <ReactQueryDevtools initialIsOpen={false} />
    </QueryClientProvider>
  );
}
```

**Update**: `app/layout.tsx`

```typescript
import { Providers } from './providers';

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="zh-TW">
      <body>
        <Providers>{children}</Providers>
      </body>
    </html>
  );
}
```

---

## Phase 2: Authentication System (6-8 hours)

### Task 2.1: Implement API Client ⏱️ 45min

**File**: `lib/api/client.ts`

**Content**: 參照 `specs/api-integration.md` 的 API Client Configuration

**Key Features**:
- Axios instance setup
- Request interceptor (自動添加 Token)
- Response interceptor (處理 Token 過期)

**Verification**:
- [ ] API 請求自動帶 Authorization header
- [ ] 401 錯誤自動嘗試 refresh token

---

### Task 2.2: Implement Token Management ⏱️ 45min

**File**: `lib/auth/token.ts`

```typescript
const ACCESS_TOKEN_KEY = 'access_token';
const REFRESH_TOKEN_KEY = 'refresh_token';

export function getAccessToken(): string | null {
  if (typeof window === 'undefined') return null;
  return localStorage.getItem(ACCESS_TOKEN_KEY);
}

export function setAccessToken(token: string): void {
  localStorage.setItem(ACCESS_TOKEN_KEY, token);
}

export function getRefreshToken(): string | null {
  if (typeof window === 'undefined') return null;
  return localStorage.getItem(REFRESH_TOKEN_KEY);
}

export function setRefreshToken(token: string): void {
  localStorage.setItem(REFRESH_TOKEN_KEY, token);
}

export function clearTokens(): void {
  localStorage.removeItem(ACCESS_TOKEN_KEY);
  localStorage.removeItem(REFRESH_TOKEN_KEY);
}

export async function refreshAccessToken(): Promise<string | null> {
  // Implementation from specs/api-integration.md
}
```

---

### Task 2.3: Implement Auth API Functions ⏱️ 60min

**File**: `lib/api/auth.ts`

**Functions**:
- `register(data: RegisterRequest)`
- `login(credentials: LoginRequest)`
- `logout()`
- `getCurrentUser()`

**Specification**: 參照 `specs/api-integration.md` Authentication APIs

---

### Task 2.4: Create TypeScript Types ⏱️ 30min

**File**: `types/api.ts`

**Content**: 參照 `specs/api-integration.md` TypeScript Type Definitions

**Includes**:
- Auth types (User, RegisterRequest, LoginRequest, etc.)
- Search types
- Salesperson types
- Admin types
- Common types (ApiResponse, Pagination)

---

### Task 2.5: Implement Auth Hooks ⏱️ 90min

**File**: `hooks/useAuth.ts`

**Hooks**:
- `useAuth()` - 取得當前使用者
- `useLogin()` - 登入 mutation
- `useLogout()` - 登出 mutation
- `useRegister()` - 註冊 mutation

**Specification**: 參照 `specs/state-routing.md` Authentication Hooks

---

### Task 2.6: Create Auth Pages ⏱️ 120min

**Files**:
- `app/(auth)/layout.tsx` - Auth layout (置中卡片)
- `app/(auth)/login/page.tsx` - 登入頁面
- `app/(auth)/register/page.tsx` - 註冊頁面

**Features**:
- React Hook Form + Zod 驗證
- Loading 狀態
- 錯誤處理
- 登入後導向

**Specification**: 參照 `specs/ui-components.md` Login Page

**Verification**:
- [ ] 註冊成功後導向登入頁
- [ ] 登入成功後根據角色導向對應頁面
- [ ] 表單驗證正確顯示錯誤

---

## Phase 3: Shared Components (8-12 hours)

### Task 3.1: Create Button Component ⏱️ 30min

**File**: `components/ui/button.tsx`

**Specification**: 參照 `specs/ui-components.md` Button Component

**Variants**:
- default, secondary, outline, ghost, link

**Sizes**:
- sm, default, lg, icon

---

### Task 3.2: Create Input Component ⏱️ 30min

**File**: `components/ui/input.tsx`

**Features**:
- Label 支援
- Error message 顯示
- Helper text
- Required indicator

---

### Task 3.3: Create Card Component ⏱️ 20min

**Files**:
- `components/ui/card.tsx`
- `CardHeader`, `CardContent`, `CardFooter`

---

### Task 3.4: Create Avatar Component ⏱️ 30min

**File**: `components/ui/avatar.tsx`

**Features**:
- 圖片顯示
- Fallback (姓名首字)
- 多種尺寸 (sm, md, lg, xl)
- Gradient 背景

---

### Task 3.5: Create Badge Component ⏱️ 20min

**File**: `components/ui/badge.tsx`

**Variants**:
- default, success, warning, danger

---

### Task 3.6: Create Skeleton Component ⏱️ 30min

**File**: `components/ui/skeleton.tsx`

**Usage Examples**:
- `SalespersonCardSkeleton`
- `ProfileSkeleton`

---

### Task 3.7: Create Dropdown Menu ⏱️ 45min

**File**: `components/ui/dropdown-menu.tsx`

**Based on**: Radix UI Dropdown Menu

---

### Task 3.8: Create Select Component ⏱️ 45min

**File**: `components/ui/select.tsx`

**Based on**: Radix UI Select

---

### Task 3.9: Create Header Component ⏱️ 90min

**File**: `components/layout/header.tsx`

**Features**:
- Logo
- Navigation links
- User menu (已登入)
- Login/Register buttons (未登入)
- 響應式設計

**Specification**: 參照 `specs/ui-components.md` Header

---

### Task 3.10: Create Footer Component ⏱️ 60min

**File**: `components/layout/footer.tsx`

**Sections**:
- Logo & description
- Feature links
- About links
- Legal links
- Copyright

**Specification**: 參照 `specs/ui-components.md` Footer

---

## Phase 4: Public Pages (12-16 hours)

### Task 4.1: Implement Search API Functions ⏱️ 30min

**File**: `lib/api/search.ts`

**Functions**:
- `searchSalespersons(params: SearchParams)`
- `getSalespersonDetail(id: number)`

---

### Task 4.2: Create Search Hooks ⏱️ 30min

**File**: `hooks/useSearch.ts`

**Hooks**:
- `useSearchSalespersons(params)`
- `useSalespersonDetail(id)`

---

### Task 4.3: Create Homepage ⏱️ 180min

**File**: `app/(public)/page.tsx`

**Sections**:
1. Hero Section (搜尋列 + CTA)
2. Features Section (3 卡片)
3. Popular Salespersons
4. Final CTA

**Specification**: 參照 `specs/ui-components.md` Homepage

---

### Task 4.4: Create SalespersonCard Component ⏱️ 60min

**File**: `components/features/search/salesperson-card.tsx`

**Features**:
- Avatar
- 姓名、公司
- 產業 Badge
- 簡介 (line-clamp-2)
- 專長 Tags

**Specification**: 參照 `specs/ui-components.md` SalespersonCard

---

### Task 4.5: Create Search Filters Component ⏱️ 90min

**File**: `components/features/search/search-filters.tsx`

**Filters**:
- 產業類別 (Select)
- 服務地區 (Select)
- 公司名稱 (Input)
- 重置按鈕

---

### Task 4.6: Create Search Page ⏱️ 180min

**File**: `app/(public)/search/page.tsx`

**Layout**:
- Filters sidebar (左側)
- Search results grid (右側)
- Pagination

**Features**:
- 關鍵字搜尋
- 進階篩選
- 排序
- 分頁

**Specification**: 參照 `specs/ui-components.md` Search Page

---

### Task 4.7: Create Salesperson Detail Page ⏱️ 180min

**File**: `app/(public)/salesperson/[id]/page.tsx`

**Sections**:
1. Hero Card (頭像、基本資訊)
2. About Section (簡介)
3. Experience Section (工作經驗)
4. Certifications Section (證照)

**Specification**: 參照 `specs/ui-components.md` Salesperson Detail Page

---

### Task 4.8: Implement SSR for Detail Page ⏱️ 60min

**Features**:
- Server-side data fetching
- SEO meta tags
- Open Graph tags
- generateStaticParams (可選)

---

## Phase 5: Dashboard (Salesperson) (16-20 hours)

### Task 5.1: Implement Salesperson API Functions ⏱️ 60min

**File**: `lib/api/salesperson.ts`

**Functions**:
- Profile: `getProfile()`, `updateProfile()`
- Experiences: `getExperiences()`, `createExperience()`, `deleteExperience()`
- Certifications: `getCertifications()`, `createCertification()`
- Company: `saveCompany()`
- Approval: `getApprovalStatus()`

---

### Task 5.2: Create Salesperson Hooks ⏱️ 90min

**File**: `hooks/useSalesperson.ts`

**Hooks**:
- `useProfile()`, `useUpdateProfile()`
- `useExperiences()`, `useCreateExperience()`, `useDeleteExperience()`
- `useCertifications()`, `useCreateCertification()`

---

### Task 5.3: Create Dashboard Layout ⏱️ 90min

**File**: `app/(dashboard)/layout.tsx`

**Features**:
- Permission check (salesperson role)
- Sidebar navigation
- Responsive design

---

### Task 5.4: Create Dashboard Sidebar ⏱️ 60min

**File**: `components/layout/dashboard-sidebar.tsx`

**Navigation Items**:
- 個人資料
- 工作經驗
- 專業證照
- 審核狀態

---

### Task 5.5: Create Profile Page ⏱️ 180min

**File**: `app/(dashboard)/dashboard/page.tsx`

**Features**:
- 查看個人資料
- 編輯表單
- 頭像上傳 (Base64)
- 公司資訊
- 服務地區 (多選)

---

### Task 5.6: Implement Image Upload ⏱️ 60min

**File**: `lib/utils/image.ts`

**Functions**:
- `fileToBase64(file: File)`
- `compressImage(file: File, maxSizeMB: number)`
- `validateImage(file: File)`

---

### Task 5.7: Create Experiences Page ⏱️ 180min

**File**: `app/(dashboard)/dashboard/experiences/page.tsx`

**Features**:
- 工作經驗列表
- 新增經驗 Modal
- 編輯經驗
- 刪除經驗

---

### Task 5.8: Create Experience Form Modal ⏱️ 120min

**Component**: `components/features/dashboard/experience-form-modal.tsx`

**Fields**:
- 公司名稱
- 職稱
- 開始日期
- 結束日期 (可選)
- 工作描述

---

### Task 5.9: Create Certifications Page ⏱️ 180min

**File**: `app/(dashboard)/dashboard/certifications/page.tsx`

**Features**:
- 證照列表 (含審核狀態)
- 上傳證照 Modal
- 圖片預覽

---

### Task 5.10: Create Certification Upload Modal ⏱️ 120min

**Component**: `components/features/dashboard/certification-upload-modal.tsx`

**Fields**:
- 證照名稱
- 發證單位
- 發證日期
- 說明
- 證照圖片上傳

---

### Task 5.11: Create Approval Status Page ⏱️ 120min

**File**: `app/(dashboard)/dashboard/approval-status/page.tsx`

**Sections**:
- 個人資料審核狀態
- 公司資訊審核狀態
- 證照審核狀態列表

---

### Task 5.12: Create Status Badge Component ⏱️ 30min

**Component**: `components/features/dashboard/status-badge.tsx`

**Statuses**:
- pending (黃色)
- approved (綠色)
- rejected (紅色)

---

## Phase 6: Admin Panel (12-16 hours)

### Task 6.1: Implement Admin API Functions ⏱️ 60min

**File**: `lib/api/admin.ts`

**Functions**:
- Approvals: `getPendingApprovals()`, `approveUser()`, `rejectUser()`, etc.
- Users: `getUsers()`, `updateUserStatus()`, `deleteUser()`
- Settings: `getIndustries()`, `createIndustry()`, `getRegions()`, `createRegion()`
- Statistics: `getStatistics()`

---

### Task 6.2: Create Admin Hooks ⏱️ 90min

**File**: `hooks/useAdmin.ts`

**Hooks**:
- `usePendingApprovals()`
- `useApproveUser()`, `useRejectUser()`
- `useStatistics()`
- `useUsers()`

---

### Task 6.3: Create Admin Layout ⏱️ 60min

**File**: `app/(admin)/layout.tsx`

**Features**:
- Permission check (admin role)
- Admin header with navigation

---

### Task 6.4: Create Admin Dashboard ⏱️ 180min

**File**: `app/(admin)/admin/page.tsx`

**Sections**:
1. Statistics Cards (4 卡片)
2. Pending Approvals (Tabs)
   - 業務員註冊
   - 公司資訊
   - 證照

**Specification**: 參照 `specs/ui-components.md` Admin Dashboard

---

### Task 6.5: Create Stats Card Component ⏱️ 45min

**Component**: `components/features/admin/stats-card.tsx`

**Props**:
- title
- value
- icon
- color

---

### Task 6.6: Create Pending Users List ⏱️ 90min

**Component**: `components/features/admin/pending-users-list.tsx`

**Features**:
- 使用者列表
- Approve/Reject 按鈕
- 確認 Modal

---

### Task 6.7: Create Approvals Page ⏱️ 180min

**File**: `app/(admin)/admin/approvals/page.tsx`

**Features**:
- 完整的待審核列表
- 詳細資訊查看
- 批次審核 (進階功能)

---

### Task 6.8: Create Users Management Page ⏱️ 180min

**File**: `app/(admin)/admin/users/page.tsx`

**Features**:
- 使用者列表
- 篩選 (角色、狀態)
- 編輯狀態
- 刪除使用者

---

### Task 6.9: Create Settings Page ⏱️ 120min

**File**: `app/(admin)/admin/settings/page.tsx`

**Sections**:
- 產業類別管理
- 地區管理
- 新增/刪除功能

---

### Task 6.10: Create Statistics Page with Charts ⏱️ 180min

**File**: `app/(admin)/admin/statistics/page.tsx`

**Install**:
```bash
npm install recharts
```

**Charts**:
- 業務員統計 (長條圖)
- 審核狀態 (圓餅圖)
- 趨勢圖 (折線圖)

---

## Phase 7: Testing & Polish (8-12 hours)

### Task 7.1: Implement Route Guards ⏱️ 90min

**File**: `middleware.ts`

**Features**:
- 認證檢查
- 重新導向未登入使用者
- 保存原始 URL

**Specification**: 參照 `specs/state-routing.md` Route Guards

---

### Task 7.2: Create Loading & Error Pages ⏱️ 90min

**Files**:
- `app/loading.tsx`
- `app/error.tsx`
- `app/not-found.tsx`
- `app/(dashboard)/dashboard/loading.tsx`

---

### Task 7.3: Implement Error Handling ⏱️ 120min

**File**: `lib/api/errors.ts`

**Features**:
- 統一錯誤處理
- Toast 通知
- 錯誤訊息映射

---

### Task 7.4: Responsive Design Testing ⏱️ 180min

**Breakpoints Testing**:
- [ ] Mobile (375px)
- [ ] Tablet (768px)
- [ ] Desktop (1280px)

**Pages to Test**:
- [ ] Homepage
- [ ] Search page
- [ ] Detail page
- [ ] Dashboard
- [ ] Admin panel

---

### Task 7.5: Browser Compatibility Testing ⏱️ 120min

**Browsers**:
- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge

**Test Items**:
- [ ] 所有頁面可正常顯示
- [ ] 表單功能正常
- [ ] 圖片上傳正常

---

### Task 7.6: Performance Optimization ⏱️ 120min

**Optimizations**:
- [ ] 圖片使用 Next/Image
- [ ] 動態 import 減少初始載入
- [ ] React Query staleTime 設定
- [ ] Lazy load 非關鍵組件

**Target Metrics**:
- [ ] Lighthouse Performance > 80
- [ ] First Contentful Paint < 2s
- [ ] Time to Interactive < 3s

---

## Task Dependencies Graph

```
Phase 1 (Project Setup)
    ↓
Phase 2 (Authentication)
    ↓
Phase 3 (Shared Components)
    ↓
    ├──→ Phase 4 (Public Pages)
    ├──→ Phase 5 (Dashboard)
    └──→ Phase 6 (Admin Panel)
    ↓
Phase 7 (Testing & Polish)
```

**Critical Path**:
1. Phase 1 → Phase 2 → Phase 3 → Phase 4/5/6 → Phase 7

**Parallel Work**:
- Phase 4, 5, 6 可以部分並行 (共用 Phase 3 的組件)

---

## Completion Checklist

### 功能完整性
- [ ] 所有 31 個 API 端點都有對應前端功能
- [ ] 認證流程完整 (註冊、登入、登出、Token 續期)
- [ ] 角色權限控制正確 (訪客、業務員、管理員)
- [ ] 搜尋與篩選功能正常
- [ ] 業務員 CRUD 操作完整
- [ ] 管理員審核流程完整
- [ ] 圖片上傳與顯示正常

### UI/UX
- [ ] 所有頁面符合設計規範
- [ ] 響應式設計在所有裝置正常
- [ ] Loading 狀態明確
- [ ] 錯誤處理友善
- [ ] 表單驗證即時顯示

### 效能
- [ ] 首頁載入 < 3 秒
- [ ] API 請求有快取
- [ ] 圖片已優化
- [ ] 無不必要的重新渲染

### 安全
- [ ] Token 安全儲存
- [ ] API 請求有認證
- [ ] XSS 防護
- [ ] 表單輸入驗證

### 程式碼品質
- [ ] TypeScript 無型別錯誤
- [ ] ESLint 無錯誤
- [ ] 程式碼遵循規範
- [ ] 組件可複用

---

## Implementation Guidelines

### 執行順序
1. **嚴格按照 Phase 順序**執行
2. **Phase 內的 Tasks 按編號順序**執行
3. **完成一個 Task 立即測試**驗證
4. **遇到錯誤立即修復**，不累積技術債

### 品質標準
- 每個 Task 完成必須通過 **Verification** 檢查
- 每個 Component 必須符合 **specs/ui-components.md** 規範
- 每個 API 整合必須符合 **specs/api-integration.md** 規範
- 每個狀態管理必須符合 **specs/state-routing.md** 規範

### 時間管理
- 單個 Task 不超過 **3 小時**
- 超時需重新評估或拆分
- 預留 **20% Buffer Time** 處理意外問題

---

**Status**: ✅ Tasks Breakdown Complete
**Ready for**: Implementation Phase (`/develop frontend-spa-project`)
