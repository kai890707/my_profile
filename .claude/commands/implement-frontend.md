# OpenSpec 規範驅動開發 - Frontend 版本

**開發功能**: $ARGUMENTS

**開發方法**: Specification-Driven Development (SDD) for Frontend

---

## 🔴 重要：Frontend 開發使用專門 Agents

**Frontend 開發在不同階段使用不同的專業 agents**

```
Step 1 (Proposal): requirements-analyst - 需求訪談
Step 2 (Specifications): product-designer - UI/UX 設計
Step 5 (Implement): react-specialist - React/Next.js 實作
```

**product-designer 負責**：
- ✅ 使用者研究和角色定義
- ✅ 資訊架構和導航設計
- ✅ 互動設計和狀態設計
- ✅ 視覺設計和設計系統
- ✅ 響應式設計和無障礙設計
- ✅ 元件規格和使用範例

詳見：`.claude/agents/product-designer.md`

**react-specialist 負責**：
- ✅ React/Next.js 程式碼實作
- ✅ TypeScript 型別安全
- ✅ 效能優化（React.memo、useMemo、虛擬化）
- ✅ React Internals 深度理解
- ✅ 確保 Type-Safe 且 High-Performance

詳見：`.claude/agents/react-specialist.md`

---

## 核心理念

**規範先行，介面後行**

1. 先撰寫完整的 UI/UX 規格、組件規格、頁面規格
2. 規格通過驗證後才開始實作
3. 實作嚴格遵循規格
4. 完成後歸檔到規範庫

**目標**:
- ✅ 降低 UI/UX 不一致的風險
- ✅ 確保組件可複用性
- ✅ 減少重複開發
- ✅ 提升代碼可維護性
- ✅ 明確 API 整合方式

---

## 執行流程

```
/implement-frontend [功能描述]
    ↓
┌────────────────────────────────────────────┐
│ 需要用戶確認的階段                          │
└────────────────────────────────────────────┘

Step 1: Create Proposal ✋ 用戶確認
    → 使用 requirements-analyst agent 進行需求訪談
    → 了解 UI/UX 需求和使用者痛點
    → openspec/changes/<feature-name>/proposal.md

    📁 資料夾命名規範 (重要):
       格式: YYYYMMDD-action-description
       範例: 20260113-add-rating-ui
       action: add, fix, update, refactor, optimize, enhance

Step 2: Write Specifications ⚡ 自動執行
    → 使用 product-designer agent 進行 UI/UX 設計
    → 使用者研究、資訊架構、互動設計、視覺設計
    → openspec/changes/<feature-name>/specs/
        ├── ui-ux.md           # UI/UX 設計規格
        ├── components.md      # 組件規格
        ├── pages.md           # 頁面規格
        ├── api-integration.md # API 整合方式
        └── state-routing.md   # 狀態管理與路由

Step 3: Break Down Tasks ⚡ 自動執行
    → openspec/changes/<feature-name>/tasks.md

Step 4: Validate Specs ✋ 最後確認點
    ✓ 使用 spec-validation.md 完整檢查
    ✓ UI/UX 規格檢查（完整性、明確性、可實作性）
    ✓ 組件規格檢查（Props、變體、範例）
    ✓ API 整合檢查（TypeScript 類型、React Query）
    ✓ 量化指標檢查（效能、可訪問性、Bundle Size）
    ✋ 用戶確認是否啟動 AUTO-RUN

┌────────────────────────────────────────────┐
│ 🤖 AUTO-RUN MODE (完全自動)                │
└────────────────────────────────────────────┘

Step 5: Implement 🤖 自動實作
    → 使用 react-specialist agent 進行實作
    → 嚴格按照 tasks.md 執行
    → 使用 TodoWrite 追蹤進度
    → 自動實作組件和頁面（Type-Safe + High-Performance）
    → 自動整合 API（React Query）
    → 自動修復錯誤
    → 不詢問用戶確認

Step 6: Archive 🤖 自動歸檔
    → 合併 specs/ 到 openspec/specs/frontend/
    → 移動到 openspec/changes/archived/
    → 輸出完整報告
```

### AUTO-RUN 特性

✅ **自動化程度**:
- Step 1: 需要用戶確認 UI/UX 需求
- Step 2-3: 自動執行
- Step 4: 最後確認點（規格審查）
- **Step 5-6: 🤖 AUTO-RUN 完全自動**

⚠️ **唯一暫停情況**:
- UI/UX 規格不清需要用戶決策時（極少發生）
- 設計系統選擇需要確認時

⏱️ **預期效果**:
- 20-30 分鐘內完成 15-20 個前端任務
- 無需用戶反覆確認
- 自動錯誤修復
- 自動視覺驗證

---

## Frontend 特有規範

### 1. UI/UX 規格 (ui-ux.md)

**必須包含**:

#### 設計系統
```markdown
## 設計系統

### 色彩方案
- **主色**: #0EA5E9 (Sky-500) - 用於主要按鈕、連結
- **配色**: #14B8A6 (Teal-500) - 用於次要元素
- **成功**: #10B981 (Green-500)
- **警告**: #F59E0B (Yellow-500)
- **錯誤**: #EF4444 (Red-500)
- **背景**: #F8FAFC (Slate-50)
- **文字**: #0F172A (Slate-900)

### 字體系統
- **標題**: 2xl/3xl/4xl (28px/36px/48px)
- **內文**: base/lg (16px/18px)
- **小字**: sm/xs (14px/12px)

### 間距系統
- **元素間距**: 4px, 8px, 12px, 16px, 24px, 32px, 48px
- **容器寬度**: max-w-7xl (1280px)
- **行高**: 1.5 (內文), 1.2 (標題)

### 圓角系統
- **小**: rounded-md (6px) - Input, Button
- **中**: rounded-lg (8px) - Card
- **大**: rounded-xl (12px) - Modal, Hero section

### 陰影系統
- **小**: shadow-sm - Card hover
- **中**: shadow-md - Dropdown
- **大**: shadow-lg - Modal
```

#### 頁面佈局
```markdown
## 頁面佈局

### Layout 結構
\`\`\`
┌─────────────────────────────────┐
│         Header (64px)           │
├─────────────────────────────────┤
│                                 │
│         Main Content            │
│                                 │
├─────────────────────────────────┤
│         Footer (auto)           │
└─────────────────────────────────┘
\`\`\`

### 響應式斷點
- **Mobile**: < 768px
- **Tablet**: 768px - 1024px
- **Desktop**: > 1024px

### Grid System
- **Mobile**: 1 column
- **Tablet**: 2 columns
- **Desktop**: 3-4 columns
```

#### 互動行為
```markdown
## 互動行為

### Loading 狀態
- **按鈕**: 顯示 Spinner + 禁用
- **頁面**: 全頁 Skeleton
- **列表**: Card Skeleton (3-6 個)

### 錯誤處理
- **表單錯誤**: 欄位下方紅色文字
- **API 錯誤**: Toast 通知（右上角）
- **頁面錯誤**: Error Boundary + 重試按鈕

### 動畫效果
- **頁面切換**: Fade (200ms)
- **Modal 開啟**: Scale + Fade (300ms)
- **Dropdown**: Slide down (150ms)
- **Toast**: Slide in from right (200ms)
```

---

### 2. 組件規格 (components.md)

**格式範例**:

```markdown
## Button 組件

### Props 定義
\`\`\`typescript
interface ButtonProps {
  variant?: 'primary' | 'secondary' | 'outline' | 'ghost';
  size?: 'sm' | 'md' | 'lg';
  isLoading?: boolean;
  disabled?: boolean;
  onClick?: () => void;
  children: React.ReactNode;
}
\`\`\`

### 變體說明

#### primary (預設)
- **背景**: bg-primary-600
- **文字**: text-white
- **Hover**: bg-primary-700
- **用途**: 主要操作（提交、確認）

#### secondary
- **背景**: bg-secondary-600
- **文字**: text-white
- **Hover**: bg-secondary-700
- **用途**: 次要操作（取消、返回）

#### outline
- **邊框**: border-primary-600
- **文字**: text-primary-600
- **Hover**: bg-primary-50
- **用途**: 輔助操作

#### ghost
- **背景**: transparent
- **文字**: text-slate-700
- **Hover**: bg-slate-100
- **用途**: 低優先級操作

### 尺寸規格
- **sm**: px-3 py-1.5 text-sm (height: 32px)
- **md**: px-4 py-2 text-base (height: 40px)
- **lg**: px-6 py-3 text-lg (height: 48px)

### Loading 狀態
- 顯示 Spinner 圖標
- 禁用點擊
- 不透明度 50%

### 使用範例
\`\`\`tsx
<Button variant="primary" size="lg" onClick={handleSubmit}>
  提交
</Button>

<Button variant="outline" isLoading>
  載入中...
</Button>
\`\`\`

### 檔案位置
\`components/ui/button.tsx\`
```

---

### 3. 頁面規格 (pages.md)

**格式範例**:

```markdown
## 業務員搜尋頁面

### 路由
\`/search\`

### 頁面結構
\`\`\`
┌─────────────────────────────────┐
│         Header                  │
├─────────────────────────────────┤
│  ┌──────────┐  ┌─────────────┐ │
│  │          │  │             │ │
│  │ Filters  │  │  Results    │ │
│  │ (Sticky) │  │  (Grid)     │ │
│  │          │  │             │ │
│  └──────────┘  └─────────────┘ │
├─────────────────────────────────┤
│         Pagination              │
└─────────────────────────────────┘
\`\`\`

### 功能需求

#### 篩選器 (Filters)
- **關鍵字搜尋**: Input with search icon
- **產業類別**: Multi-select dropdown
- **服務地區**: Multi-select dropdown
- **公司**: Select dropdown
- **重置按鈕**: 清除所有篩選

#### 搜尋結果 (Results)
- **顯示格式**: Grid (3 columns desktop, 2 tablet, 1 mobile)
- **每頁數量**: 12 筆
- **排序選項**: 相關度、最新註冊
- **無結果**: 顯示空狀態插圖 + 建議文字

#### 業務員卡片 (SalespersonCard)
- **頭像**: 圓形 64x64px
- **姓名**: font-bold text-lg
- **公司**: text-sm text-slate-600
- **專長**: Badge 列表（最多 3 個）
- **服務地區**: 地區圖標 + 文字
- **Hover**: shadow-lg + scale(1.02)

### 狀態管理
\`\`\`typescript
interface SearchState {
  keyword: string;
  industries: number[];
  regions: number[];
  company: number | null;
  sort: 'relevance' | 'latest';
  page: number;
}
\`\`\`

### API 整合
- **GET /api/search/salespersons**
  - Query params: keyword, industries[], regions[], company_id, sort, page
  - Response: { data: Salesperson[], pagination: {...} }

### Loading 狀態
- 初次載入: 顯示 6 個 Skeleton Card
- 篩選變更: 結果區域顯示 Spinner overlay
- 分頁切換: 平滑滾動到頂部

### 錯誤處理
- API 失敗: Toast 通知 + 重試按鈕
- 網路錯誤: 顯示離線提示

### 檔案位置
- \`app/(public)/search/page.tsx\`
- \`components/features/search/search-filters.tsx\`
- \`components/features/search/salesperson-card.tsx\`
```

---

### 4. API 整合規格 (api-integration.md)

**格式範例**:

```markdown
## API 整合規範

### API Client 設置

#### Base Configuration
\`\`\`typescript
// lib/api/client.ts
import axios from 'axios';

export const apiClient = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
  },
});
\`\`\`

#### Request Interceptor
\`\`\`typescript
apiClient.interceptors.request.use((config) => {
  const token = getAccessToken();
  if (token) {
    config.headers.Authorization = \`Bearer \${token}\`;
  }
  return config;
});
\`\`\`

#### Response Interceptor
\`\`\`typescript
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      // Token 過期，嘗試續期
      const refreshed = await refreshAccessToken();
      if (refreshed) {
        return apiClient(error.config);
      } else {
        redirectToLogin();
      }
    }
    return Promise.reject(error);
  }
);
\`\`\`

### TypeScript 類型定義

#### API Response 格式
\`\`\`typescript
interface ApiResponse<T> {
  status: 'success' | 'error';
  message: string;
  data?: T;
}

interface PaginatedResponse<T> {
  data: T[];
  pagination: {
    total: number;
    current_page: number;
    per_page: number;
    total_pages: number;
  };
}
\`\`\`

#### 實體類型
\`\`\`typescript
interface User {
  id: number;
  username: string;
  email: string;
  role: 'admin' | 'salesperson' | 'user';
  status: 'active' | 'inactive' | 'pending';
}

interface SalespersonProfile {
  id: number;
  user_id: number;
  full_name: string;
  phone: string;
  bio: string;
  specialties: string;
  avatar_url: string | null;
  company: Company | null;
  industries: Industry[];
  regions: Region[];
}
\`\`\`

### API 函數封裝

#### 範例: 搜尋業務員
\`\`\`typescript
// lib/api/search.ts
import { apiClient } from './client';
import type { ApiResponse, PaginatedResponse, SalespersonSearchResult } from '@/types/api';

export interface SearchParams {
  keyword?: string;
  industries?: number[];
  regions?: number[];
  company_id?: number;
  sort?: 'relevance' | 'latest';
  page?: number;
  per_page?: number;
}

export async function searchSalespersons(
  params: SearchParams
): Promise<PaginatedResponse<SalespersonSearchResult>> {
  const response = await apiClient.get<ApiResponse<PaginatedResponse<SalespersonSearchResult>>>(
    '/search/salespersons',
    { params }
  );
  return response.data.data!;
}
\`\`\`

### React Query 整合

#### Query Keys 管理
\`\`\`typescript
// lib/query/keys.ts
export const queryKeys = {
  search: {
    all: ['search'] as const,
    salespersons: (params: SearchParams) => ['search', 'salespersons', params] as const,
  },
  auth: {
    me: ['auth', 'me'] as const,
  },
  salesperson: {
    profile: ['salesperson', 'profile'] as const,
    experiences: ['salesperson', 'experiences'] as const,
  },
};
\`\`\`

#### Custom Hooks
\`\`\`typescript
// hooks/useSearch.ts
import { useQuery } from '@tanstack/react-query';
import { searchSalespersons, SearchParams } from '@/lib/api/search';
import { queryKeys } from '@/lib/query/keys';

export function useSearchSalespersons(params: SearchParams) {
  return useQuery({
    queryKey: queryKeys.search.salespersons(params),
    queryFn: () => searchSalespersons(params),
    staleTime: 5 * 60 * 1000, // 5 分鐘
    gcTime: 10 * 60 * 1000, // 10 分鐘
  });
}
\`\`\`

### 錯誤處理

#### 統一錯誤處理函數
\`\`\`typescript
// lib/api/errors.ts
import { toast } from 'sonner';
import { AxiosError } from 'axios';

export function handleApiError(error: unknown): string {
  if (error instanceof AxiosError) {
    const message = error.response?.data?.message || '發生錯誤';
    toast.error(message);
    return message;
  }
  toast.error('發生未知錯誤');
  return '發生未知錯誤';
}
\`\`\`

#### 在組件中使用
\`\`\`typescript
const { data, error, isLoading } = useSearchSalespersons(params);

if (error) {
  handleApiError(error);
}
\`\`\`
```

---

### 5. 狀態管理與路由 (state-routing.md)

**格式範例**:

```markdown
## 狀態管理

### React Query (伺服器狀態)

**用途**: 管理所有 API 資料

**配置**:
\`\`\`typescript
// app/providers.tsx
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000, // 5 分鐘
      gcTime: 10 * 60 * 1000, // 10 分鐘
      retry: 3,
      refetchOnWindowFocus: false,
    },
  },
});
\`\`\`

### Zustand (客戶端狀態)

**用途**: 管理 UI 狀態（篩選條件、Modal 開關等）

**範例: 搜尋篩選 Store**
\`\`\`typescript
// store/search-filters.ts
import { create } from 'zustand';

interface SearchFiltersStore {
  keyword: string;
  industries: number[];
  regions: number[];
  company: number | null;
  sort: 'relevance' | 'latest';
  page: number;

  setKeyword: (keyword: string) => void;
  setIndustries: (industries: number[]) => void;
  setRegions: (regions: number[]) => void;
  setCompany: (company: number | null) => void;
  setSort: (sort: 'relevance' | 'latest') => void;
  setPage: (page: number) => void;
  reset: () => void;
}

export const useSearchFilters = create<SearchFiltersStore>((set) => ({
  keyword: '',
  industries: [],
  regions: [],
  company: null,
  sort: 'relevance',
  page: 1,

  setKeyword: (keyword) => set({ keyword, page: 1 }),
  setIndustries: (industries) => set({ industries, page: 1 }),
  setRegions: (regions) => set({ regions, page: 1 }),
  setCompany: (company) => set({ company, page: 1 }),
  setSort: (sort) => set({ sort, page: 1 }),
  setPage: (page) => set({ page }),
  reset: () => set({
    keyword: '',
    industries: [],
    regions: [],
    company: null,
    sort: 'relevance',
    page: 1,
  }),
}));
\`\`\`

## 路由管理

### 路由結構
\`\`\`
/                         # 首頁
/search                   # 搜尋頁面
/salesperson/:id          # 業務員詳細頁
/login                    # 登入
/register                 # 註冊

/dashboard                # 業務員 Dashboard (需 salesperson 角色)
/dashboard/experiences    # 工作經驗
/dashboard/certifications # 證照
/dashboard/approval-status # 審核狀態

/admin                    # 管理員 Dashboard (需 admin 角色)
/admin/approvals          # 審核管理
/admin/users              # 使用者管理
/admin/settings           # 系統設定
/admin/statistics         # 統計報表
\`\`\`

### Route Guards (middleware.ts)

\`\`\`typescript
import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

const protectedRoutes = ['/dashboard', '/admin'];
const adminOnlyRoutes = ['/admin'];
const salespersonOnlyRoutes = ['/dashboard'];

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;
  const accessToken = request.cookies.get('access_token')?.value;
  const userRole = request.cookies.get('user_role')?.value;

  // 檢查需要認證的路由
  const isProtectedRoute = protectedRoutes.some((route) =>
    pathname.startsWith(route)
  );

  if (isProtectedRoute && !accessToken) {
    // 未登入，重定向到登入頁
    const loginUrl = new URL('/login', request.url);
    loginUrl.searchParams.set('callbackUrl', pathname);
    return NextResponse.redirect(loginUrl);
  }

  // 檢查角色權限
  if (userRole) {
    if (adminOnlyRoutes.some((route) => pathname.startsWith(route)) &&
        userRole !== 'admin') {
      return NextResponse.redirect(new URL('/403', request.url));
    }

    if (salespersonOnlyRoutes.some((route) => pathname.startsWith(route)) &&
        userRole !== 'salesperson') {
      return NextResponse.redirect(new URL('/403', request.url));
    }
  }

  return NextResponse.next();
}

export const config = {
  matcher: ['/((?!api|_next/static|_next/image|favicon.ico).*)'],
};
\`\`\`

### 導航處理

#### useRouter Hook
\`\`\`typescript
import { useRouter } from 'next/navigation';

function MyComponent() {
  const router = useRouter();

  const handleNavigate = () => {
    router.push('/search?keyword=insurance');
  };

  const handleBack = () => {
    router.back();
  };

  return (
    // ...
  );
}
\`\`\`

#### Link Component
\`\`\`typescript
import Link from 'next/link';

<Link href="/salesperson/123" className="...">
  查看詳情
</Link>
\`\`\`
```

---

## Step 4: 規格驗證清單

**重要**: 必須使用 `.claude/knowledge/workflow/spec-validation.md` 進行完整驗證

**驗證流程**:

### 4.1 UI/UX 規格驗證（參考 spec-validation.md）

**完整性**:
- [ ] 色彩方案完整定義（Primary, Secondary, Success, Error, etc.）
- [ ] 字體系統清晰（Font Family, Size Scale, Weight, Line Height）
- [ ] 間距系統一致（4px/8px/16px/24px/32px...）
- [ ] 圓角系統定義（sm/md/lg/xl）
- [ ] 陰影系統定義（sm/md/lg）
- [ ] 響應式斷點明確（mobile/tablet/desktop）
- [ ] 互動行為詳細說明（Loading/Error/Success 狀態）

**明確性**:
- [ ] 設計 Token 可直接轉為 CSS Variables
- [ ] 組件變體清楚定義（variant, size, color）
- [ ] 狀態轉換邏輯明確
- [ ] 動畫效果有具體參數（duration, easing）

**可實作性**:
- [ ] 設計可用現有組件庫實作（shadcn/ui, Radix UI）
- [ ] 無技術不可行的設計
- [ ] 響應式設計可用 Tailwind 實作

### 4.2 組件規格驗證（參考 spec-validation.md）

**完整性**:
- [ ] Props 類型完整（名稱、型別、必填/可選、預設值）
- [ ] 所有變體都有說明和範例
- [ ] 事件處理函式定義（onClick, onChange, etc.）
- [ ] Children 類型定義（ReactNode, ReactElement, etc.）
- [ ] Ref forwarding 說明（如需要）
- [ ] 檔案位置明確（components/ui/, components/features/）

**可測試性**:
- [ ] 可直接撰寫 Component Test
- [ ] 互動行為可測試
- [ ] 狀態變化可驗證

### 4.3 頁面規格驗證（參考 spec-validation.md）

**完整性**:
- [ ] 路由定義清楚（路徑、參數、Query）
- [ ] 頁面結構完整（Layout + 所有 Section）
- [ ] 功能需求明確（CRUD、搜尋、篩選、排序）
- [ ] API 整合方式清晰（使用哪些 hooks）
- [ ] 狀態管理合理（React Query + Zustand）
- [ ] 載入狀態處理（Skeleton, Spinner）
- [ ] 錯誤狀態處理（Error Boundary, Fallback UI）
- [ ] 空狀態處理（Empty State + CTA）

**導航與權限**:
- [ ] Route Guards 定義（認證、授權）
- [ ] 重定向邏輯明確
- [ ] Breadcrumb 結構定義

### 4.4 API 整合驗證（參考 spec-validation.md）

**完整性**:
- [ ] API Client 配置完整（Base URL, Interceptors）
- [ ] TypeScript 類型定義完整（Request + Response）
- [ ] React Query hooks 定義（query key, queryFn, options）
- [ ] Mutation hooks 定義（mutationFn, onSuccess, onError）
- [ ] 錯誤處理統一（Error Interceptor + Error Boundary）
- [ ] Loading 狀態處理（isLoading, isFetching）
- [ ] Cache 策略定義（staleTime, cacheTime）

**類型安全**:
- [ ] 無 `any` 類型
- [ ] API 回應類型與後端一致
- [ ] 欄位映射正確（camelCase vs snake_case）

### 4.5 量化指標驗證（參考 metrics-standards.md）

**前端效能標準**:
- [ ] Core Web Vitals 目標定義
  - LCP (Largest Contentful Paint) < 2.5s
  - FID (First Input Delay) < 100ms
  - CLS (Cumulative Layout Shift) < 0.1
- [ ] Bundle Size 限制定義
  - Initial JS Bundle < 200KB (gzip)
  - Page Chunk < 100KB (gzip)
- [ ] Runtime 效能要求
  - 元件渲染時間 < 16ms (60 FPS)
  - 列表渲染時間 < 100ms (100 項)

**可訪問性標準**（參考 metrics-standards.md）:
- [ ] 符合 WCAG AA 標準
- [ ] 色彩對比 >= 4.5:1
- [ ] 鍵盤導航支援
- [ ] Screen Reader 支援（ARIA 標籤）
- [ ] Focus 可見性明確

**測試覆蓋率**:
- [ ] Component Tests >= 80%
- [ ] E2E Tests 100% (關鍵流程)
- [ ] TypeScript strict mode 啟用

### 4.6 通過標準

**必須全部通過**:
- ✅ 所有上述檢查項目全部勾選
- ✅ 無任何模糊或未定義項目
- ✅ 開發人員可直接實作（無需再次詢問）
- ✅ QA 可直接撰寫測試案例

**若未通過**:
- ❌ 返回 Step 2，補充規格
- ❌ 不允許進入 Step 5 (Implement)

---

## Frontend 特有原則

### 1. 組件優先原則

❌ **禁止**:
- 直接在頁面中寫大量 JSX
- 複製貼上相似代碼
- 不可複用的組件

✅ **必須**:
- 提取可複用組件
- 組件單一職責
- Props 定義清晰

### 2. 類型安全原則

❌ **禁止**:
- 使用 `any` 類型
- 忽略 TypeScript 錯誤
- 不定義 API 回應類型

✅ **必須**:
- 所有 Props 都有類型定義
- API 回應都有對應 interface
- 使用 Zod 驗證運行時資料

### 3. 性能優先原則

❌ **禁止**:
- 不必要的重渲染
- 過大的 Bundle Size
- 未優化的圖片

✅ **必須**:
- 使用 React.memo 優化重渲染
- 使用 Dynamic Import 分割代碼
- 圖片使用 Next/Image 優化

### 4. 無障礙原則

❌ **禁止**:
- 缺少 aria labels
- 鍵盤無法操作
- 色彩對比度不足

✅ **必須**:
- 所有互動元素可鍵盤訪問
- ARIA 屬性正確使用
- 符合 WCAG AA 標準

---

## 使用範例

### 範例 1: 新增使用者評分功能

```bash
/implement-frontend 新增業務員評分與評論 UI
```

**產出規格**:
1. `ui-ux.md` - 評分星星樣式、評論卡片設計
2. `components.md` - Rating 組件、ReviewCard 組件
3. `pages.md` - 評分 Modal、評論列表區塊
4. `api-integration.md` - POST /api/ratings, GET /api/ratings/:id
5. `state-routing.md` - 評分 Modal 狀態管理

### 範例 2: Dashboard 響應式優化

```bash
/implement-frontend Dashboard 響應式佈局優化
```

**產出規格**:
1. `ui-ux.md` - Mobile Sidebar 收合行為、觸控手勢
2. `components.md` - MobileSidebar 組件、Hamburger Menu
3. `pages.md` - Dashboard Layout 響應式調整
4. `state-routing.md` - Sidebar 開關狀態管理

---

## 總結

**Frontend SDD 核心價值**:
- 🎨 **設計一致** - 設計系統統一，避免視覺不一致
- 🧩 **組件可複用** - 減少重複代碼，提升開發效率
- 🔒 **類型安全** - TypeScript 保證，減少運行時錯誤
- ⚡ **性能優化** - 規格階段考慮性能，避免事後優化
- ♿ **無障礙** - 從設計階段確保可訪問性

**適用場景**:
- ✅ 新頁面開發
- ✅ 新組件開發
- ✅ UI 重構
- ✅ 響應式優化
- ✅ API 整合

**不適用場景**:
- ❌ 純樣式調整（直接修改即可）
- ❌ 文案更新
- ❌ Bug 修復（使用原 /implement）

---

**開始執行**: 使用 `/implement-frontend [功能描述]` 啟動前端規範驅動開發流程
