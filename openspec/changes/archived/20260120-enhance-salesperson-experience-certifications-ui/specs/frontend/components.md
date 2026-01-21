# 組件規格 - 工作經驗與專業證照組件

**功能**: 業務員工作經驗與專業證照呈現組件
**日期**: 2026-01-20
**狀態**: Draft → Ready for Implementation

---

## 📋 目錄

- [組件總覽](#組件總覽)
- [ExperienceTimeline 組件](#experiencetimeline-組件)
- [ExperienceItem 組件](#experienceitem-組件)
- [CertificationCards 組件](#certificationcards-組件)
- [CertificationCard 組件](#certificationcard-組件)
- [共用子組件](#共用子組件)
- [類型定義](#類型定義)

---

## 🧩 組件總覽

### 組件架構

```
SalespersonDetailPage
├── ExperienceTimeline (工作經驗時間軸)
│   └── ExperienceItem (單個工作經驗)
│       ├── 時間節點 (圓點)
│       ├── 工作資訊卡片
│       └── 展開/收合按鈕
│
└── CertificationCards (專業證照容器)
    ├── 篩選下拉選單 (可選)
    └── CertificationCard (單張證照卡片)
        ├── 徽章圖示
        ├── 證照資訊
        ├── 展開/收合按鈕
        └── 查看證書按鈕 (如有)
```

### 檔案結構

```
frontend/
└── components/
    └── features/
        └── salesperson/
            ├── experience-timeline.tsx        # 時間軸容器組件
            ├── experience-item.tsx            # 單個經驗項目
            ├── certification-cards.tsx        # 證照容器組件
            ├── certification-card.tsx         # 單張證照卡片
            └── __tests__/
                ├── experience-timeline.test.tsx
                ├── experience-item.test.tsx
                ├── certification-cards.test.tsx
                └── certification-card.test.tsx
```

---

## 📅 ExperienceTimeline 組件

### 組件職責

**用途**: 顯示業務員的完整工作經驗歷程，以時間軸形式呈現。

**功能**:
- 按時間倒序排列工作經驗
- 顯示時間軸視覺元素
- 處理空狀態
- 處理 Loading 狀態

### Props 定義

```typescript
interface ExperienceTimelineProps {
  /**
   * 工作經驗列表
   */
  experiences: Experience[];

  /**
   * Loading 狀態
   */
  isLoading?: boolean;

  /**
   * 額外的 CSS class
   */
  className?: string;
}

interface Experience {
  id: number;
  user_id: number;
  company: string;
  position: string;
  start_date: string;          // YYYY-MM-DD
  end_date: string | null;      // YYYY-MM-DD or null (至今)
  description: string | null;
  approval_status: 'pending' | 'approved' | 'rejected';
  approved_by: number | null;
  approved_at: string | null;
  rejected_reason: string | null;
  created_at: string;
  updated_at: string;
}
```

### 狀態管理

```typescript
// 本組件無內部狀態 (Stateless)
// 所有狀態由子組件 ExperienceItem 管理
```

### 組件邏輯

#### 排序邏輯

```typescript
/**
 * 排序工作經驗 (最新在上)
 * 規則:
 * 1. 在職中 (end_date === null) 的排在最前
 * 2. 依開始日期倒序排列
 */
function sortExperiences(experiences: Experience[]): Experience[] {
  return [...experiences].sort((a, b) => {
    // 在職中的優先
    if (a.end_date === null && b.end_date !== null) return -1;
    if (a.end_date !== null && b.end_date === null) return 1;

    // 依開始日期倒序
    return new Date(b.start_date).getTime() - new Date(a.start_date).getTime();
  });
}
```

#### 空狀態判斷

```typescript
if (!isLoading && experiences.length === 0) {
  return <EmptyState type="experience" />;
}
```

### 組件結構

```tsx
export function ExperienceTimeline({
  experiences,
  isLoading = false,
  className,
}: ExperienceTimelineProps) {
  // Loading 狀態
  if (isLoading) {
    return <ExperienceTimelineSkeleton count={3} />;
  }

  // 空狀態
  if (experiences.length === 0) {
    return (
      <div className="text-center py-12">
        <Briefcase className="mx-auto h-12 w-12 text-slate-300 mb-4" />
        <p className="text-base font-medium text-slate-600 mb-2">
          尚無工作經驗記錄
        </p>
        <p className="text-sm text-slate-500">
          此業務員尚未新增工作經驗
        </p>
      </div>
    );
  }

  // 排序經驗
  const sortedExperiences = sortExperiences(experiences);

  return (
    <div className={cn("relative", className)}>
      {/* 時間軸線 */}
      <div
        className="absolute left-6 top-0 bottom-0 w-0.5 bg-primary-200"
        aria-hidden="true"
      />

      {/* 經驗列表 */}
      <ol role="list" aria-label="工作經驗時間軸" className="space-y-6">
        {sortedExperiences.map((experience, index) => (
          <ExperienceItem
            key={experience.id}
            experience={experience}
            isLast={index === sortedExperiences.length - 1}
          />
        ))}
      </ol>
    </div>
  );
}
```

### 骨架屏組件

```tsx
interface ExperienceTimelineSkeletonProps {
  count?: number;
}

export function ExperienceTimelineSkeleton({
  count = 3,
}: ExperienceTimelineSkeletonProps) {
  return (
    <div className="relative space-y-6">
      {/* 時間軸線 */}
      <div className="absolute left-6 top-0 bottom-0 w-0.5 bg-slate-200" />

      {/* 骨架項目 */}
      {Array.from({ length: count }).map((_, index) => (
        <div key={index} className="relative pl-16">
          {/* 時間節點 */}
          <div className="absolute left-3 top-2 w-6 h-6 rounded-full bg-slate-200 ring-4 ring-white" />

          {/* 卡片骨架 */}
          <div className="animate-pulse bg-white rounded-xl p-6 border border-slate-100 shadow-sm">
            <div className="h-5 bg-slate-200 rounded w-3/4 mb-3" />
            <div className="h-4 bg-slate-200 rounded w-1/2 mb-4" />
            <div className="h-4 bg-slate-200 rounded w-1/3 mb-4" />
            <div className="space-y-2">
              <div className="h-3 bg-slate-200 rounded w-full" />
              <div className="h-3 bg-slate-200 rounded w-5/6" />
            </div>
          </div>
        </div>
      ))}
    </div>
  );
}
```

### 響應式行為

```tsx
className="
  relative
  space-y-4 md:space-y-6          /* Mobile: 16px, Desktop: 24px 間距 */
"

時間軸線:
className="
  absolute left-3 md:left-6       /* Mobile: 12px, Desktop: 24px 左側位置 */
  top-0 bottom-0
  w-px md:w-0.5                   /* Mobile: 1px, Desktop: 2px 線寬 */
  bg-primary-200
"

經驗項目:
className="
  relative
  pl-12 md:pl-16                  /* Mobile: 48px, Desktop: 64px 左側內邊距 */
"
```

### 無障礙屬性

```tsx
<ol
  role="list"
  aria-label="工作經驗時間軸"
  className="space-y-6"
>
  {experiences.map((exp) => (
    <li key={exp.id} role="listitem">
      <ExperienceItem experience={exp} />
    </li>
  ))}
</ol>
```

---

## 📄 ExperienceItem 組件

### 組件職責

**用途**: 顯示單個工作經驗項目，包含時間節點、工作資訊卡片、展開/收合功能。

**功能**:
- 顯示職位、公司、日期、描述
- 顯示驗證標記
- 展開/收合描述
- 計算年資

### Props 定義

```typescript
interface ExperienceItemProps {
  /**
   * 工作經驗資料
   */
  experience: Experience;

  /**
   * 是否為最後一項 (用於判斷時間節點樣式)
   */
  isLast?: boolean;

  /**
   * 額外的 CSS class
   */
  className?: string;
}
```

### 狀態管理

```typescript
const [isExpanded, setIsExpanded] = useState(false);

// 切換展開/收合
const toggleExpand = () => {
  setIsExpanded((prev) => !prev);
};
```

### 組件邏輯

#### 日期格式化

```typescript
/**
 * 格式化日期為 YYYY/MM
 */
function formatDate(dateString: string): string {
  const date = new Date(dateString);
  const year = date.getFullYear();
  const month = (date.getMonth() + 1).toString();
  return `${year}/${month}`;
}

/**
 * 計算年資
 * @returns "X年X個月" 或 "X個月"
 */
function calculateDuration(startDate: string, endDate: string | null): string {
  const start = new Date(startDate);
  const end = endDate ? new Date(endDate) : new Date();

  const months = (end.getFullYear() - start.getFullYear()) * 12 +
                 (end.getMonth() - start.getMonth());

  const years = Math.floor(months / 12);
  const remainingMonths = months % 12;

  if (years === 0) {
    return `${remainingMonths}個月`;
  } else if (remainingMonths === 0) {
    return `${years}年`;
  } else {
    return `${years}年${remainingMonths}個月`;
  }
}
```

#### 驗證標記判斷

```typescript
const isApproved = experience.approval_status === 'approved';
const isPending = experience.approval_status === 'pending';
const isOngoing = experience.end_date === null;
```

#### 描述截斷判斷

```typescript
/**
 * 判斷是否需要顯示展開按鈕
 * 規則: 描述超過 3 行或超過 200 字元
 */
const shouldShowExpandButton = () => {
  if (!experience.description) return false;
  return experience.description.length > 200;
};
```

### 組件結構

```tsx
export function ExperienceItem({
  experience,
  isLast = false,
  className,
}: ExperienceItemProps) {
  const [isExpanded, setIsExpanded] = useState(false);

  const isOngoing = experience.end_date === null;
  const isApproved = experience.approval_status === 'approved';
  const duration = calculateDuration(
    experience.start_date,
    experience.end_date
  );

  return (
    <li
      role="listitem"
      className={cn("relative pl-12 md:pl-16", className)}
    >
      {/* 時間節點 */}
      <div
        className={cn(
          "absolute left-3 md:left-3 top-2",
          "w-3 h-3 md:w-3 md:h-3 rounded-full",
          "ring-4 ring-white",
          isOngoing
            ? "bg-primary-500"           // 在職中: 實心藍色
            : isLast
            ? "bg-white border-2 border-slate-300"  // 最後一個: 空心灰色
            : "bg-primary-300"           // 已離職: 實心淡藍色
        )}
        aria-hidden="true"
      />

      {/* 工作資訊卡片 */}
      <article
        className={cn(
          "bg-white rounded-xl p-4 md:p-6",
          "border border-slate-100 shadow-sm",
          "hover:shadow-md hover:-translate-y-1",
          "transition-all duration-200 ease-out"
        )}
      >
        {/* 標題行 */}
        <div className="flex items-start justify-between mb-2">
          <div className="flex items-start gap-2 flex-1">
            <Briefcase
              className="h-5 w-5 text-primary-500 flex-shrink-0 mt-0.5"
              aria-hidden="true"
            />
            <div className="flex-1 min-w-0">
              <h3 className="text-base md:text-lg font-semibold text-slate-900 mb-1">
                {experience.position}
              </h3>
              <p className="text-sm md:text-base text-slate-600 truncate">
                {experience.company}
              </p>
            </div>
          </div>

          {/* 驗證標記 */}
          {isApproved && (
            <Badge variant="success" size="sm" className="flex-shrink-0">
              <CheckCircle2 className="mr-1 h-3 w-3" aria-hidden="true" />
              已驗證
            </Badge>
          )}
        </div>

        {/* 日期行 */}
        <div className="flex items-center gap-2 text-xs md:text-sm text-slate-600 mb-3">
          <Calendar className="h-4 w-4" aria-hidden="true" />
          <time dateTime={experience.start_date}>
            {formatDate(experience.start_date)}
          </time>
          <span>-</span>
          {isOngoing ? (
            <>
              <time dateTime={new Date().toISOString()}>至今</time>
              <span className="inline-flex items-center gap-1">
                <span className="w-2 h-2 rounded-full bg-success-500 animate-pulse" />
                <span className="text-success-600 font-medium">在職中</span>
              </span>
            </>
          ) : (
            <time dateTime={experience.end_date!}>
              {formatDate(experience.end_date!)}
            </time>
          )}
          <span className="text-slate-500">({duration})</span>
        </div>

        {/* 描述 */}
        {experience.description && (
          <div className="mt-3">
            <p
              className={cn(
                "text-sm md:text-base text-slate-700 leading-relaxed whitespace-pre-line",
                !isExpanded && "line-clamp-3"
              )}
            >
              {experience.description}
            </p>

            {/* 展開按鈕 */}
            {shouldShowExpandButton() && (
              <button
                onClick={toggleExpand}
                aria-expanded={isExpanded}
                aria-controls={`experience-detail-${experience.id}`}
                className={cn(
                  "mt-2 text-sm font-medium text-primary-600",
                  "hover:text-primary-700 hover:underline",
                  "transition-colors duration-150",
                  "flex items-center gap-1"
                )}
              >
                {isExpanded ? (
                  <>
                    收合
                    <ChevronUp className="h-4 w-4" aria-hidden="true" />
                  </>
                ) : (
                  <>
                    展開更多
                    <ChevronDown className="h-4 w-4" aria-hidden="true" />
                  </>
                )}
              </button>
            )}
          </div>
        )}
      </article>
    </li>
  );
}
```

### 展開動畫

```tsx
描述容器:
<div
  id={`experience-detail-${experience.id}`}
  className={cn(
    "overflow-hidden transition-all duration-300 ease-out",
    isExpanded ? "max-h-96" : "max-h-[72px]"  // 3 行約 72px
  )}
>
  <p className="text-slate-700 leading-relaxed whitespace-pre-line">
    {experience.description}
  </p>
</div>

按鈕圖示旋轉:
<ChevronDown
  className={cn(
    "h-4 w-4 transition-transform duration-300",
    isExpanded && "rotate-180"
  )}
/>
```

### 響應式行為

```tsx
時間節點:
className="
  w-2.5 h-2.5 md:w-3 md:h-3      /* Mobile: 10px, Desktop: 12px */
  rounded-full
  ring-3 md:ring-4 ring-white    /* Mobile: 3px, Desktop: 4px ring */
"

卡片:
className="
  p-4 md:p-6                     /* Mobile: 16px, Desktop: 24px 內邊距 */
  rounded-lg md:rounded-xl       /* Mobile: 8px, Desktop: 12px 圓角 */
"

職位名稱:
className="
  text-base md:text-lg           /* Mobile: 16px, Desktop: 18px */
  font-semibold
"

日期:
className="
  text-xs md:text-sm             /* Mobile: 12px, Desktop: 14px */
  text-slate-600
"
```

---

## 🏆 CertificationCards 組件

### 組件職責

**用途**: 顯示業務員的所有專業證照，以網格卡片形式呈現。

**功能**:
- 按日期倒序排列證照
- 提供篩選功能 (已驗證/全部)
- 處理空狀態
- 處理 Loading 狀態

### Props 定義

```typescript
interface CertificationCardsProps {
  /**
   * 證照列表
   */
  certifications: Certification[];

  /**
   * Loading 狀態
   */
  isLoading?: boolean;

  /**
   * 是否顯示篩選下拉選單
   * @default false
   */
  showFilter?: boolean;

  /**
   * 額外的 CSS class
   */
  className?: string;
}

interface Certification {
  id: number;
  user_id: number;
  name: string;
  issuer: string;
  issue_date: string;          // YYYY-MM-DD
  expiry_date: string | null;  // YYYY-MM-DD or null (永久有效)
  description: string | null;
  file_url: string | null;
  approval_status: 'pending' | 'approved' | 'rejected';
  approved_by: number | null;
  approved_at: string | null;
  rejected_reason: string | null;
  created_at: string;
  updated_at: string;
}
```

### 狀態管理

```typescript
const [filter, setFilter] = useState<'all' | 'approved'>('all');

// 篩選證照
const filteredCertifications = useMemo(() => {
  let filtered = certifications;

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

### 組件結構

```tsx
export function CertificationCards({
  certifications,
  isLoading = false,
  showFilter = false,
  className,
}: CertificationCardsProps) {
  const [filter, setFilter] = useState<'all' | 'approved'>('all');

  // Loading 狀態
  if (isLoading) {
    return <CertificationCardsSkeleton count={4} />;
  }

  // 空狀態
  if (certifications.length === 0) {
    return (
      <div className="text-center py-12">
        <Award className="mx-auto h-12 w-12 text-slate-300 mb-4" />
        <p className="text-base font-medium text-slate-600 mb-2">
          尚無專業證照記錄
        </p>
        <p className="text-sm text-slate-500">
          此業務員尚未新增專業證照
        </p>
      </div>
    );
  }

  // 篩選證照
  const filteredCertifications = useMemo(() => {
    // ... 篩選和排序邏輯
  }, [certifications, filter]);

  return (
    <div className={cn(className)}>
      {/* 篩選下拉選單 */}
      {showFilter && (
        <div className="flex justify-end mb-4">
          <select
            value={filter}
            onChange={(e) => setFilter(e.target.value as 'all' | 'approved')}
            className="px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            aria-label="篩選證照"
          >
            <option value="all">全部 ({certifications.length})</option>
            <option value="approved">
              已驗證 ({certifications.filter(c => c.approval_status === 'approved').length})
            </option>
          </select>
        </div>
      )}

      {/* 證照網格 */}
      <div
        role="list"
        aria-label="專業證照列表"
        className="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6"
      >
        {filteredCertifications.map((certification) => (
          <div key={certification.id} role="listitem">
            <CertificationCard certification={certification} />
          </div>
        ))}
      </div>

      {/* 篩選後無結果 */}
      {filteredCertifications.length === 0 && filter === 'approved' && (
        <div className="text-center py-12">
          <p className="text-slate-600">目前沒有已驗證的證照</p>
        </div>
      )}
    </div>
  );
}
```

### 骨架屏組件

```tsx
interface CertificationCardsSkeletonProps {
  count?: number;
}

export function CertificationCardsSkeleton({
  count = 4,
}: CertificationCardsSkeletonProps) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
      {Array.from({ length: count }).map((_, index) => (
        <div
          key={index}
          className="animate-pulse bg-white rounded-2xl p-6 border-2 border-slate-100 shadow-sm"
        >
          <div className="flex items-start gap-3 mb-4">
            <div className="w-6 h-6 bg-slate-200 rounded" />
            <div className="flex-1">
              <div className="h-5 bg-slate-200 rounded w-3/4 mb-2" />
              <div className="h-4 bg-slate-200 rounded w-1/2" />
            </div>
          </div>
          <div className="h-4 bg-slate-200 rounded w-1/3 mb-4" />
          <div className="space-y-2">
            <div className="h-3 bg-slate-200 rounded w-full" />
            <div className="h-3 bg-slate-200 rounded w-5/6" />
          </div>
        </div>
      ))}
    </div>
  );
}
```

### 響應式行為

```tsx
網格:
className="
  grid
  grid-cols-1 md:grid-cols-2     /* Mobile: 1欄, Tablet+: 2欄 */
  gap-4 md:gap-6                 /* Mobile: 16px, Desktop: 24px 間距 */
"
```

---

## 🎓 CertificationCard 組件

### 組件職責

**用途**: 顯示單張專業證照卡片。

**功能**:
- 顯示證照名稱、發證機構、日期、描述
- 顯示驗證標記
- 展開/收合描述
- 查看證書 (如有 file_url)

### Props 定義

```typescript
interface CertificationCardProps {
  /**
   * 證照資料
   */
  certification: Certification;

  /**
   * 額外的 CSS class
   */
  className?: string;
}
```

### 狀態管理

```typescript
const [isExpanded, setIsExpanded] = useState(false);

// 切換展開/收合
const toggleExpand = () => {
  setIsExpanded((prev) => !prev);
};
```

### 組件邏輯

#### 日期格式化

```typescript
/**
 * 格式化日期
 */
function formatDate(dateString: string): string {
  const date = new Date(dateString);
  return date.toLocaleDateString('zh-TW', {
    year: 'numeric',
    month: '2-digit',
  });
}

/**
 * 檢查證照是否即將過期
 * @returns 'valid' | 'expiring' | 'expired'
 */
function checkExpiryStatus(expiryDate: string | null): {
  status: 'valid' | 'expiring' | 'expired';
  message?: string;
} {
  if (!expiryDate) {
    return { status: 'valid' };
  }

  const now = new Date();
  const expiry = new Date(expiryDate);
  const monthsUntilExpiry = (expiry.getTime() - now.getTime()) / (1000 * 60 * 60 * 24 * 30);

  if (monthsUntilExpiry < 0) {
    return { status: 'expired', message: '已過期' };
  } else if (monthsUntilExpiry < 3) {
    return { status: 'expiring', message: '即將過期' };
  } else {
    return { status: 'valid' };
  }
}
```

### 組件結構

```tsx
export function CertificationCard({
  certification,
  className,
}: CertificationCardProps) {
  const [isExpanded, setIsExpanded] = useState(false);

  const isApproved = certification.approval_status === 'approved';
  const isPending = certification.approval_status === 'pending';
  const expiryStatus = checkExpiryStatus(certification.expiry_date);

  return (
    <article
      className={cn(
        "bg-white rounded-2xl p-6",
        "border-2 border-slate-100",
        "shadow-sm hover:shadow-lg",
        "hover:-translate-y-1 hover:border-primary-200",
        "transition-all duration-200 ease-out",
        className
      )}
    >
      {/* 標題行 */}
      <div className="flex items-start gap-3 mb-3">
        {/* 徽章圖示 */}
        <Award
          className="h-6 w-6 text-amber-500 flex-shrink-0 mt-0.5"
          aria-hidden="true"
        />

        {/* 證照名稱 */}
        <div className="flex-1 min-w-0">
          <h3 className="text-lg font-bold text-slate-900 line-clamp-2 mb-1">
            {certification.name}
          </h3>
        </div>

        {/* 驗證標記 */}
        {isApproved && (
          <CheckCircle2
            className="h-5 w-5 text-success-600 flex-shrink-0"
            aria-label="已驗證"
          />
        )}
        {isPending && (
          <Clock
            className="h-5 w-5 text-warning-600 flex-shrink-0"
            aria-label="審核中"
          />
        )}
      </div>

      {/* 發證機構 */}
      <p className="text-sm text-slate-600 mb-3 truncate">
        {certification.issuer}
      </p>

      {/* 日期行 */}
      <div className="flex items-center gap-2 text-xs text-slate-500 mb-3">
        <Calendar className="h-4 w-4" aria-hidden="true" />
        <time dateTime={certification.issue_date}>
          {formatDate(certification.issue_date)}
        </time>
        <span>-</span>
        {certification.expiry_date ? (
          <>
            <time dateTime={certification.expiry_date}>
              {formatDate(certification.expiry_date)}
            </time>
            {expiryStatus.status === 'expiring' && (
              <span className="text-warning-600 font-medium">
                ({expiryStatus.message})
              </span>
            )}
            {expiryStatus.status === 'expired' && (
              <span className="text-error-600 font-medium">
                ({expiryStatus.message})
              </span>
            )}
          </>
        ) : (
          <span className="text-slate-500">永久有效</span>
        )}
      </div>

      {/* 描述 */}
      {certification.description && (
        <div className="mt-3">
          <p
            className={cn(
              "text-sm text-slate-600 leading-relaxed",
              !isExpanded && "line-clamp-2"
            )}
          >
            {certification.description}
          </p>

          {/* 展開按鈕 */}
          {certification.description.length > 100 && (
            <button
              onClick={toggleExpand}
              aria-expanded={isExpanded}
              aria-controls={`cert-detail-${certification.id}`}
              className={cn(
                "mt-2 text-sm font-medium text-primary-600",
                "hover:text-primary-700 hover:underline",
                "transition-colors duration-150",
                "flex items-center gap-1"
              )}
            >
              {isExpanded ? (
                <>
                  收合
                  <ChevronUp className="h-4 w-4" aria-hidden="true" />
                </>
              ) : (
                <>
                  展開更多
                  <ChevronDown className="h-4 w-4" aria-hidden="true" />
                </>
              )}
            </button>
          )}
        </div>
      )}

      {/* 查看證書按鈕 */}
      {certification.file_url && (
        <div className="mt-4 pt-4 border-t border-slate-100">
          <a
            href={certification.file_url}
            target="_blank"
            rel="noopener noreferrer"
            className={cn(
              "inline-flex items-center gap-2",
              "px-4 py-2 text-sm font-medium",
              "text-primary-600 border-2 border-primary-500 rounded-lg",
              "hover:bg-primary-50 hover:border-primary-600",
              "transition-all duration-200"
            )}
          >
            <FileText className="h-4 w-4" aria-hidden="true" />
            查看證書
            <ExternalLink className="h-3 w-3" aria-hidden="true" />
          </a>
        </div>
      )}
    </article>
  );
}
```

### 響應式行為

```tsx
卡片:
className="
  p-4 md:p-6                     /* Mobile: 16px, Desktop: 24px */
  rounded-xl md:rounded-2xl      /* Mobile: 12px, Desktop: 16px */
"

證照名稱:
className="
  text-base md:text-lg           /* Mobile: 16px, Desktop: 18px */
  font-bold
  line-clamp-2
"

發證機構:
className="
  text-xs md:text-sm             /* Mobile: 12px, Desktop: 14px */
  text-slate-600
"
```

---

## 🧩 共用子組件

### EmptyState 組件

```typescript
interface EmptyStateProps {
  type: 'experience' | 'certification';
}

export function EmptyState({ type }: EmptyStateProps) {
  const config = {
    experience: {
      icon: Briefcase,
      title: '尚無工作經驗記錄',
      description: '此業務員尚未新增工作經驗',
    },
    certification: {
      icon: Award,
      title: '尚無專業證照記錄',
      description: '此業務員尚未新增專業證照',
    },
  };

  const { icon: Icon, title, description } = config[type];

  return (
    <div className="text-center py-12">
      <Icon className="mx-auto h-12 w-12 text-slate-300 mb-4" aria-hidden="true" />
      <p className="text-base font-medium text-slate-600 mb-2">{title}</p>
      <p className="text-sm text-slate-500">{description}</p>
    </div>
  );
}
```

---

## 📦 類型定義

### 統一的類型檔案

**位置**: `types/salesperson.ts`

```typescript
/**
 * 工作經驗
 */
export interface Experience {
  id: number;
  user_id: number;
  company: string;
  position: string;
  start_date: string;          // YYYY-MM-DD
  end_date: string | null;      // YYYY-MM-DD or null (至今)
  description: string | null;
  approval_status: 'pending' | 'approved' | 'rejected';
  approved_by: number | null;
  approved_at: string | null;
  rejected_reason: string | null;
  created_at: string;
  updated_at: string;
}

/**
 * 專業證照
 */
export interface Certification {
  id: number;
  user_id: number;
  name: string;
  issuer: string;
  issue_date: string;          // YYYY-MM-DD
  expiry_date: string | null;  // YYYY-MM-DD or null
  description: string | null;
  file_url: string | null;
  approval_status: 'pending' | 'approved' | 'rejected';
  approved_by: number | null;
  approved_at: string | null;
  rejected_reason: string | null;
  created_at: string;
  updated_at: string;
}

/**
 * 審核狀態
 */
export type ApprovalStatus = 'pending' | 'approved' | 'rejected';

/**
 * 證照過期狀態
 */
export type ExpiryStatus = 'valid' | 'expiring' | 'expired';
```

---

## ✅ 組件檢查清單

### 開發完成後檢查

#### ExperienceTimeline
- [ ] 正確排序經驗 (最新在上)
- [ ] 時間軸視覺正確
- [ ] 空狀態顯示
- [ ] Loading 骨架屏
- [ ] 響應式佈局正常

#### ExperienceItem
- [ ] 展開/收合功能正常
- [ ] 驗證標記正確顯示
- [ ] 日期格式化正確
- [ ] 年資計算正確
- [ ] Hover 效果流暢
- [ ] 鍵盤可操作

#### CertificationCards
- [ ] 正確排序證照
- [ ] 篩選功能正常 (如啟用)
- [ ] 空狀態顯示
- [ ] Loading 骨架屏
- [ ] 網格佈局響應式

#### CertificationCard
- [ ] 展開/收合功能正常
- [ ] 驗證標記正確顯示
- [ ] 證書查看按鈕 (如有 file_url)
- [ ] 過期狀態提示
- [ ] Hover 效果流暢
- [ ] 鍵盤可操作

---

**版本**: 1.0
**日期**: 2026-01-20
**狀態**: Ready for Implementation
