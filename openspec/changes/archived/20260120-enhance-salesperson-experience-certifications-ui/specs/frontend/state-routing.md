# 狀態管理與路由規格

**功能**: 工作經驗與專業證照狀態管理
**日期**: 2026-01-20

---

## 📋 目錄

- [狀態管理總覽](#狀態管理總覽)
- [Local State 管理](#local-state-管理)
- [Server State 管理](#server-state-管理)
- [路由設計](#路由設計)
- [URL 參數](#url-參數)

---

## 🎯 狀態管理總覽

### 狀態類型

本功能涉及的狀態類型:

| 狀態類型 | 管理方式 | 說明 |
|----------|----------|------|
| **Server State** | React Query | 業務員詳情、工作經驗、證照資料 |
| **Local State** | useState | 展開/收合、篩選狀態 |
| **Global State** | ❌ 不需要 | 無需跨組件共享狀態 |
| **URL State** | ❌ 不需要 | 無需保存狀態到 URL |

### 狀態來源

```
業務員詳情頁面
├── Server State (React Query)
│   └── useSalespersonDetail(id)
│       ├── salesperson.experiences[]
│       └── salesperson.certifications[]
│
└── Local State (useState)
    ├── ExperienceItem
    │   └── isExpanded (展開/收合)
    │
    ├── CertificationCard
    │   └── isExpanded (展開/收合)
    │
    └── CertificationCards
        └── filter ('all' | 'approved')
```

---

## 🔄 Local State 管理

### ExperienceItem 組件狀態

**用途**: 控制單個工作經驗的展開/收合狀態。

#### 狀態定義

```typescript
// 在 ExperienceItem 組件內部
const [isExpanded, setIsExpanded] = useState(false);
```

#### 狀態操作

```typescript
/**
 * 切換展開/收合
 */
const toggleExpand = () => {
  setIsExpanded((prev) => !prev);
};

/**
 * 展開
 */
const expand = () => {
  setIsExpanded(true);
};

/**
 * 收合
 */
const collapse = () => {
  setIsExpanded(false);
};
```

#### 使用範例

```tsx
export function ExperienceItem({ experience }: ExperienceItemProps) {
  const [isExpanded, setIsExpanded] = useState(false);

  return (
    <article>
      {/* 描述區域 */}
      <div
        className={cn(
          "transition-all duration-300 ease-out overflow-hidden",
          isExpanded ? "max-h-96" : "max-h-[72px]"
        )}
      >
        <p className="whitespace-pre-line">
          {experience.description}
        </p>
      </div>

      {/* 展開按鈕 */}
      <button
        onClick={() => setIsExpanded(!isExpanded)}
        aria-expanded={isExpanded}
      >
        {isExpanded ? '收合' : '展開更多'}
      </button>
    </article>
  );
}
```

#### 狀態重置

```typescript
// 當資料變更時重置狀態
useEffect(() => {
  setIsExpanded(false);
}, [experience.id]);
```

### CertificationCard 組件狀態

**用途**: 控制單張證照卡片的展開/收合狀態。

#### 狀態定義

```typescript
// 在 CertificationCard 組件內部
const [isExpanded, setIsExpanded] = useState(false);
```

#### 使用方式

與 ExperienceItem 相同，控制描述區域的展開/收合。

### CertificationCards 組件狀態

**用途**: 控制證照篩選狀態。

#### 狀態定義

```typescript
// 在 CertificationCards 組件內部
const [filter, setFilter] = useState<'all' | 'approved'>('all');
```

#### 狀態操作

```typescript
/**
 * 切換篩選條件
 */
const handleFilterChange = (newFilter: 'all' | 'approved') => {
  setFilter(newFilter);
};
```

#### 衍生狀態 (Derived State)

```typescript
/**
 * 根據篩選條件過濾證照
 */
const filteredCertifications = useMemo(() => {
  let filtered = certifications;

  // 篩選已驗證
  if (filter === 'approved') {
    filtered = filtered.filter((cert) => cert.approval_status === 'approved');
  }

  // 排序: 已驗證優先，再依發證日期倒序
  return [...filtered].sort((a, b) => {
    // 已驗證優先
    if (a.approval_status === 'approved' && b.approval_status !== 'approved') {
      return -1;
    }
    if (a.approval_status !== 'approved' && b.approval_status === 'approved') {
      return 1;
    }

    // 依發證日期倒序
    return new Date(b.issue_date).getTime() - new Date(a.issue_date).getTime();
  });
}, [certifications, filter]);
```

#### 使用範例

```tsx
export function CertificationCards({
  certifications,
  showFilter = false,
}: CertificationCardsProps) {
  const [filter, setFilter] = useState<'all' | 'approved'>('all');

  const filteredCertifications = useMemo(() => {
    // 篩選和排序邏輯
  }, [certifications, filter]);

  return (
    <div>
      {/* 篩選下拉選單 */}
      {showFilter && (
        <select
          value={filter}
          onChange={(e) => setFilter(e.target.value as 'all' | 'approved')}
        >
          <option value="all">全部</option>
          <option value="approved">已驗證</option>
        </select>
      )}

      {/* 證照網格 */}
      <div className="grid md:grid-cols-2 gap-4">
        {filteredCertifications.map((cert) => (
          <CertificationCard key={cert.id} certification={cert} />
        ))}
      </div>

      {/* 篩選後無結果 */}
      {filteredCertifications.length === 0 && filter === 'approved' && (
        <p>目前沒有已驗證的證照</p>
      )}
    </div>
  );
}
```

---

## 🌐 Server State 管理

### React Query 狀態

**管理工具**: React Query (TanStack Query v5)

#### Hook: useSalespersonDetail

```typescript
export function useSalespersonDetail(id: number) {
  return useQuery({
    queryKey: ['salesperson', id],
    queryFn: () => searchAPI.getSalespersonDetail(id),
    enabled: id > 0,
    staleTime: 5 * 60 * 1000,   // 5 分鐘
    gcTime: 10 * 60 * 1000,     // 10 分鐘
    refetchOnWindowFocus: false,
    refetchOnMount: false,
    refetchOnReconnect: true,
  });
}
```

#### 狀態屬性

```typescript
const {
  data,           // 業務員詳情資料
  isLoading,      // 初始載入中
  isFetching,     // 背景重新取得中
  error,          // 錯誤物件
  refetch,        // 手動重新取得
  isSuccess,      // 是否成功取得資料
  isError,        // 是否有錯誤
} = useSalespersonDetail(id);
```

#### 使用範例

```tsx
export default function SalespersonDetailPage() {
  const params = useParams();
  const id = parseInt(params.id as string);

  const {
    data: salesperson,
    isLoading,
    error,
    refetch,
  } = useSalespersonDetail(id);

  // Loading 狀態
  if (isLoading) {
    return <LoadingPage />;
  }

  // 錯誤狀態
  if (error) {
    return <ErrorPage onRetry={refetch} />;
  }

  // 資料不存在
  if (!salesperson) {
    return <NotFoundPage />;
  }

  // 主要內容
  return (
    <Layout>
      {/* 傳遞資料給組件 */}
      <ExperienceTimeline experiences={salesperson.experiences} />
      <CertificationCards certifications={salesperson.certifications} />
    </Layout>
  );
}
```

### 快取管理

#### 自動快取

React Query 自動快取資料:
- **staleTime**: 5 分鐘內視為新鮮，不重新取得
- **gcTime**: 10 分鐘後清除未使用的快取
- **queryKey**: `['salesperson', id]` 作為快取鍵值

#### 手動失效快取

```typescript
import { useQueryClient } from '@tanstack/react-query';

const queryClient = useQueryClient();

// 失效特定業務員快取
queryClient.invalidateQueries({
  queryKey: ['salesperson', id],
});

// 重新取得資料
refetch();
```

#### 預載入 (可選)

```typescript
// 在搜尋頁面預載入業務員詳情
const queryClient = useQueryClient();

const handleMouseEnter = () => {
  queryClient.prefetchQuery({
    queryKey: ['salesperson', salesperson.id],
    queryFn: () => searchAPI.getSalespersonDetail(salesperson.id),
  });
};
```

---

## 🛣️ 路由設計

### 路由結構

本功能僅涉及一個頁面，不需要新增或修改路由。

```
現有路由:
/salesperson/[id]  →  業務員詳情頁面
```

### 路由參數

#### 動態參數

```typescript
// URL: /salesperson/123

// 取得參數
const params = useParams();
const id = parseInt(params.id as string);

// 驗證參數
if (isNaN(id) || id <= 0) {
  // 無效 ID，重定向到搜尋頁面
  redirect('/search');
}
```

#### 參數驗證

```typescript
/**
 * 驗證業務員 ID
 */
function validateSalespersonId(id: string | string[]): number | null {
  if (Array.isArray(id)) {
    return null;  // 無效
  }

  const numericId = parseInt(id, 10);

  if (isNaN(numericId) || numericId <= 0) {
    return null;  // 無效
  }

  return numericId;
}

// 使用
const params = useParams();
const id = validateSalespersonId(params.id);

if (!id) {
  redirect('/search');
}
```

### 導航

#### 返回搜尋頁面

```tsx
<Link href="/search">
  <Button variant="ghost">
    <ArrowLeft className="mr-2 h-4 w-4" />
    返回搜尋結果
  </Button>
</Link>
```

#### 分享連結

```tsx
const shareUrl = `${window.location.origin}/salesperson/${salesperson.id}`;

// 複製到剪貼簿
navigator.clipboard.writeText(shareUrl);
```

---

## 🔍 URL 參數

### 不使用 URL 參數

本功能**不使用** Query Parameters 或 Hash，原因:

1. **展開/收合狀態**: 臨時 UI 狀態，不需要保存
2. **篩選狀態**: 預設值即可，無需分享
3. **簡化 URL**: 保持 URL 簡潔

### 如果未來需要 URL 參數 (參考)

```typescript
// 範例: 如果需要保存篩選狀態到 URL

// URL: /salesperson/123?filter=approved&expand=1,2,3

// 讀取參數
const searchParams = useSearchParams();
const filter = searchParams.get('filter') || 'all';
const expandedIds = searchParams.get('expand')?.split(',').map(Number) || [];

// 更新參數
const pathname = usePathname();
const router = useRouter();

const updateFilter = (newFilter: string) => {
  const params = new URLSearchParams(searchParams);
  params.set('filter', newFilter);
  router.push(`${pathname}?${params.toString()}`);
};
```

---

## 🔄 狀態流程圖

### 資料載入流程

```
使用者訪問頁面
    ↓
取得路由參數 (id)
    ↓
驗證 ID 是否有效
    ├─ 無效 → 重定向到 /search
    └─ 有效 ↓
調用 useSalespersonDetail(id)
    ↓
React Query 檢查快取
    ├─ 有快取且新鮮 → 使用快取資料
    └─ 無快取或過期 ↓
發送 API 請求
    ├─ Loading → 顯示骨架屏
    ├─ Error → 顯示錯誤訊息
    └─ Success ↓
更新 Server State
    ↓
傳遞資料給組件
    ↓
渲染工作經驗和證照
```

### 展開/收合流程

```
使用者點擊「展開更多」按鈕
    ↓
觸發 onClick 事件
    ↓
setIsExpanded(true)
    ↓
React 重新渲染組件
    ↓
動畫: max-height 從 72px → 384px (300ms)
    ↓
圖示旋轉: 0deg → 180deg (300ms)
    ↓
按鈕文字改為「收合」
    ↓
使用者點擊「收合」按鈕
    ↓
setIsExpanded(false)
    ↓
動畫: max-height 從 384px → 72px (300ms)
    ↓
圖示旋轉: 180deg → 0deg (300ms)
    ↓
按鈕文字改為「展開更多」
```

### 篩選流程

```
使用者選擇「已驗證」篩選
    ↓
觸發 onChange 事件
    ↓
setFilter('approved')
    ↓
React 重新渲染組件
    ↓
useMemo 重新計算 filteredCertifications
    ↓
過濾出 approval_status === 'approved' 的證照
    ↓
排序證照
    ↓
渲染篩選後的證照列表
    ├─ 有結果 → 顯示證照卡片
    └─ 無結果 → 顯示「目前沒有已驗證的證照」
```

---

## 🧪 測試策略

### Local State 測試

```typescript
import { render, screen, fireEvent } from '@testing-library/react';
import { ExperienceItem } from './experience-item';

describe('ExperienceItem 展開/收合', () => {
  it('預設應該收合', () => {
    render(<ExperienceItem experience={mockExperience} />);

    const button = screen.getByRole('button', { name: /展開更多/i });
    expect(button).toBeInTheDocument();
  });

  it('點擊應該展開', () => {
    render(<ExperienceItem experience={mockExperience} />);

    const button = screen.getByRole('button', { name: /展開更多/i });
    fireEvent.click(button);

    expect(screen.getByRole('button', { name: /收合/i })).toBeInTheDocument();
  });

  it('再次點擊應該收合', () => {
    render(<ExperienceItem experience={mockExperience} />);

    const expandButton = screen.getByRole('button', { name: /展開更多/i });
    fireEvent.click(expandButton);

    const collapseButton = screen.getByRole('button', { name: /收合/i });
    fireEvent.click(collapseButton);

    expect(screen.getByRole('button', { name: /展開更多/i })).toBeInTheDocument();
  });
});
```

### Server State 測試

```typescript
import { renderHook, waitFor } from '@testing-library/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useSalespersonDetail } from './useSearch';

describe('useSalespersonDetail', () => {
  const queryClient = new QueryClient({
    defaultOptions: {
      queries: { retry: false },
    },
  });

  const wrapper = ({ children }) => (
    <QueryClientProvider client={queryClient}>
      {children}
    </QueryClientProvider>
  );

  it('應該成功取得資料', async () => {
    const { result } = renderHook(() => useSalespersonDetail(1), { wrapper });

    await waitFor(() => {
      expect(result.current.isLoading).toBe(false);
    });

    expect(result.current.data).toBeDefined();
    expect(result.current.data?.experiences).toBeInstanceOf(Array);
    expect(result.current.data?.certifications).toBeInstanceOf(Array);
  });

  it('應該快取資料', async () => {
    // 第一次調用
    const { result: result1 } = renderHook(() => useSalespersonDetail(1), { wrapper });
    await waitFor(() => expect(result1.current.isSuccess).toBe(true));

    // 第二次調用 (應該使用快取)
    const { result: result2 } = renderHook(() => useSalespersonDetail(1), { wrapper });
    expect(result2.current.isLoading).toBe(false);  // 立即從快取取得
    expect(result2.current.data).toEqual(result1.current.data);
  });
});
```

---

## ✅ 狀態管理檢查清單

### 開發前
- [ ] 確認狀態類型和管理方式
- [ ] 確認 React Query 配置
- [ ] 確認路由結構

### 開發中
- [ ] Local State 使用 useState
- [ ] Server State 使用 React Query
- [ ] 不使用不必要的 Global State
- [ ] 正確處理衍生狀態 (useMemo)

### 開發後
- [ ] 展開/收合功能正常
- [ ] 篩選功能正常
- [ ] 快取策略正常
- [ ] 無不必要的重渲染
- [ ] 無 console 錯誤或警告

---

**版本**: 1.0
**日期**: 2026-01-20
**狀態**: Ready for Implementation
