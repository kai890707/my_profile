# 頁面規格 - 業務員詳情頁面整合

**頁面**: `/salesperson/[id]`
**檔案**: `frontend/app/salesperson/[id]/page.tsx`
**日期**: 2026-01-20

---

## 📋 目錄

- [頁面概述](#頁面概述)
- [現有實作分析](#現有實作分析)
- [整合方案](#整合方案)
- [佈局調整](#佈局調整)
- [錯誤處理](#錯誤處理)
- [效能優化](#效能優化)

---

## 🎯 頁面概述

### 頁面職責

業務員公開詳情頁面，展示業務員的完整資料，包括:
- 基本資料 (頭像、姓名、公司、專長、簡介)
- 工作經驗 (時間軸) ⭐ **新增/改善**
- 專業證照 (卡片列表) ⭐ **新增/改善**
- 聯絡資訊 (右側欄)

### 頁面路由

```
URL: /salesperson/[id]
範例: /salesperson/123

參數:
- id: 業務員 ID (number)
```

---

## 🔍 現有實作分析

### 當前頁面結構 (page.tsx)

```tsx
export default function SalespersonDetailPage() {
  // 1. 取得路由參數
  const params = useParams();
  const id = parseInt(params.id as string);

  // 2. 使用 Hook 取得資料
  const { data: salesperson, isLoading, error } = useSalespersonDetail(id);

  // 3. Loading 狀態
  if (isLoading) {
    return <Layout><ProfileSkeleton /></Layout>;
  }

  // 4. 錯誤狀態
  if (error || !salesperson) {
    return <Layout><ErrorMessage /></Layout>;
  }

  // 5. 主要內容
  return (
    <Layout>
      <Container>
        <Grid>
          {/* 左側 (2/3) */}
          <div className="lg:col-span-2">
            {/* 個人資料卡片 */}
            <Card>基本資料</Card>

            {/* 工作經驗 - 需要改善 ⚠️ */}
            {salesperson.experiences?.length > 0 && (
              <Card>
                <CardHeader>工作經驗</CardHeader>
                <CardContent>
                  {/* 當前: 簡單的左邊框列表 */}
                  <div className="space-y-6">
                    {salesperson.experiences.map((exp) => (
                      <div key={exp.id} className="border-l-2 border-primary-200 pl-4">
                        {/* 職位、公司、日期、描述 */}
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            )}

            {/* 專業證照 - 需要改善 ⚠️ */}
            {salesperson.certifications?.length > 0 && (
              <Card>
                <CardHeader>專業證照</CardHeader>
                <CardContent>
                  {/* 當前: 2欄網格 */}
                  <div className="grid md:grid-cols-2 gap-4">
                    {salesperson.certifications.map((cert) => (
                      <div key={cert.id} className="p-4 rounded-lg border border-slate-200">
                        {/* 名稱、機構、日期、描述 */}
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            )}
          </div>

          {/* 右側 (1/3) */}
          <div className="lg:col-span-1">
            <Card>聯絡資訊</Card>
          </div>
        </Grid>
      </Container>
    </Layout>
  );
}
```

### 資料結構 (已確認)

```typescript
salesperson = {
  id: number;
  full_name: string;
  email: string;
  phone: string;
  avatar: string | null;
  company: {
    id: number;
    name: string;
  } | null;
  specialties: string | null;  // 逗號分隔
  service_regions: string[] | string | null;
  bio: string | null;
  approval_status: 'pending' | 'approved' | 'rejected';
  created_at: string;
  updated_at: string;

  // 工作經驗 ⭐
  experiences: Experience[];

  // 專業證照 ⭐
  certifications: Certification[];
}
```

### 現有問題

1. **工作經驗區塊**:
   - 使用簡單的 `border-l-2` 左邊框
   - 缺少時間軸視覺元素
   - 無展開/收合功能
   - 缺少 Loading 骨架屏

2. **專業證照區塊**:
   - 簡單的 2 欄網格
   - 卡片設計過於樸素
   - 無展開/收合功能
   - 無證書查看按鈕
   - 缺少 Loading 骨架屏

3. **狀態處理**:
   - 使用通用的 `ProfileSkeleton` (不適合新設計)
   - 缺少區塊級別的 Loading 狀態

---

## 🔧 整合方案

### 修改策略

**原則**: 最小化修改，無縫整合新組件。

#### 1. 保留現有邏輯
```tsx
✅ 保留:
- 路由參數取得
- useSalespersonDetail Hook
- 整體佈局結構
- 基本資料卡片
- 聯絡資訊卡片
- 錯誤處理邏輯

❌ 修改:
- 工作經驗區塊 → 使用 ExperienceTimeline
- 專業證照區塊 → 使用 CertificationCards
```

#### 2. 引入新組件

```tsx
// 新增 import
import { ExperienceTimeline } from '@/components/features/salesperson/experience-timeline';
import { CertificationCards } from '@/components/features/salesperson/certification-cards';
```

#### 3. 替換舊實作

```tsx
{/* 工作經驗 - 新實作 ✅ */}
{salesperson.experiences && salesperson.experiences.length > 0 && (
  <Card>
    <CardHeader>
      <CardTitle className="flex items-center gap-2">
        <Briefcase className="h-5 w-5" />
        工作經驗
      </CardTitle>
    </CardHeader>
    <CardContent>
      <ExperienceTimeline
        experiences={salesperson.experiences}
        isLoading={false}
      />
    </CardContent>
  </Card>
)}

{/* 專業證照 - 新實作 ✅ */}
{salesperson.certifications && salesperson.certifications.length > 0 && (
  <Card>
    <CardHeader>
      <CardTitle className="flex items-center gap-2">
        <Award className="h-5 w-5" />
        專業證照
      </CardTitle>
    </CardHeader>
    <CardContent>
      <CertificationCards
        certifications={salesperson.certifications}
        isLoading={false}
        showFilter={salesperson.certifications.length > 4}
      />
    </CardContent>
  </Card>
)}
```

### 完整頁面實作

**檔案**: `frontend/app/salesperson/[id]/page.tsx`

```tsx
'use client';

import { useParams } from 'next/navigation';
import Link from 'next/link';
import { Header } from '@/components/layout/header';
import { Footer } from '@/components/layout/footer';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { ProfileSkeleton } from '@/components/ui/skeleton';
import { getAvatarFallback } from '@/lib/utils/avatar';
import {
  Mail,
  Phone,
  MapPin,
  Briefcase,
  Award,
  Calendar,
  Building2,
  ArrowLeft,
  CheckCircle2,
  Clock,
} from 'lucide-react';
import { useSalespersonDetail } from '@/hooks/useSearch';
import { formatDate } from '@/lib/utils/format';

// 新增的組件
import { ExperienceTimeline } from '@/components/features/salesperson/experience-timeline';
import { CertificationCards } from '@/components/features/salesperson/certification-cards';

export default function SalespersonDetailPage() {
  const params = useParams();
  const id = parseInt(params.id as string);

  const { data: salesperson, isLoading, error } = useSalespersonDetail(id);

  // Loading 狀態
  if (isLoading) {
    return (
      <div className="min-h-screen flex flex-col">
        <Header />
        <main className="flex-1 bg-slate-50">
          <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <Card>
              <CardContent className="p-8">
                <ProfileSkeleton />
              </CardContent>
            </Card>
          </div>
        </main>
        <Footer />
      </div>
    );
  }

  // 錯誤狀態
  if (error || !salesperson) {
    return (
      <div className="min-h-screen flex flex-col">
        <Header />
        <main className="flex-1 bg-slate-50">
          <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <Card>
              <CardContent className="py-16 text-center">
                <h3 className="text-xl font-semibold text-slate-900 mb-2">
                  找不到業務員資料
                </h3>
                <p className="text-slate-600 mb-6">
                  此業務員可能不存在或已被移除
                </p>
                <Link href="/search">
                  <Button>
                    <ArrowLeft className="mr-2 h-4 w-4" />
                    返回搜尋頁面
                  </Button>
                </Link>
              </CardContent>
            </Card>
          </div>
        </main>
        <Footer />
      </div>
    );
  }

  const specialtiesList = salesperson.specialties
    ? salesperson.specialties.split(',')
    : [];

  // Normalize service_regions to array
  const serviceRegions = (() => {
    const regions = salesperson.service_regions as string[] | string | null | undefined;
    if (!regions) return [];
    if (Array.isArray(regions)) return regions;
    if (typeof regions === 'string') {
      try {
        const parsed = JSON.parse(regions);
        return Array.isArray(parsed) ? parsed : [regions];
      } catch {
        return regions.split(',').map((r: string) => r.trim());
      }
    }
    return [];
  })();

  const statusBadgeMap = {
    pending: { variant: 'warning' as const, label: '審核中', icon: Clock },
    approved: { variant: 'success' as const, label: '已審核', icon: CheckCircle2 },
    rejected: { variant: 'error' as const, label: '已拒絕', icon: Clock },
  };

  const statusBadge = salesperson.approval_status && statusBadgeMap[salesperson.approval_status as keyof typeof statusBadgeMap]
    ? statusBadgeMap[salesperson.approval_status as keyof typeof statusBadgeMap]
    : { variant: 'secondary' as const, label: '未知', icon: Clock };

  return (
    <div className="min-h-screen flex flex-col">
      <Header />

      <main className="flex-1 bg-slate-50">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
          {/* 返回按鈕 */}
          <div className="mb-6">
            <Link href="/search">
              <Button variant="ghost">
                <ArrowLeft className="mr-2 h-4 w-4" />
                返回搜尋結果
              </Button>
            </Link>
          </div>

          <div className="grid lg:grid-cols-3 gap-6">
            {/* 左側主要資訊 */}
            <div className="lg:col-span-2 space-y-6">
              {/* 個人資料卡片 - 保持不變 */}
              <Card>
                <CardContent className="p-8">
                  <div className="flex flex-col md:flex-row items-start md:items-center gap-6 mb-6">
                    <Avatar
                      src={salesperson.avatar}
                      fallback={getAvatarFallback(salesperson)}
                      size="2xl"
                    />
                    <div className="flex-1">
                      <div className="flex items-center gap-3 mb-2">
                        <h1 className="text-3xl font-bold text-slate-900">
                          {salesperson.full_name}
                        </h1>
                        <Badge variant={statusBadge.variant} size="sm">
                          <statusBadge.icon className="mr-1 h-3 w-3" />
                          {statusBadge.label}
                        </Badge>
                      </div>

                      {salesperson.company && (
                        <div className="flex items-center gap-2 text-slate-600 mb-3">
                          <Building2 className="h-5 w-5" />
                          <span className="text-lg">{salesperson.company.name}</span>
                        </div>
                      )}

                      {/* 專長標籤 */}
                      {specialtiesList.length > 0 && (
                        <div className="flex flex-wrap gap-2">
                          {specialtiesList.map((specialty, index) => (
                            <Badge key={index} variant="primary" size="sm">
                              {specialty.trim()}
                            </Badge>
                          ))}
                        </div>
                      )}
                    </div>
                  </div>

                  {/* 簡介 */}
                  {salesperson.bio && (
                    <div className="pt-6 border-t border-slate-200">
                      <h3 className="text-lg font-semibold text-slate-900 mb-3">
                        個人簡介
                      </h3>
                      <p className="text-slate-600 whitespace-pre-line">
                        {salesperson.bio}
                      </p>
                    </div>
                  )}
                </CardContent>
              </Card>

              {/* 工作經驗 - 新實作 ✅ */}
              {salesperson.experiences && salesperson.experiences.length > 0 && (
                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                      <Briefcase className="h-5 w-5" />
                      工作經驗
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    <ExperienceTimeline
                      experiences={salesperson.experiences}
                      isLoading={false}
                    />
                  </CardContent>
                </Card>
              )}

              {/* 專業證照 - 新實作 ✅ */}
              {salesperson.certifications && salesperson.certifications.length > 0 && (
                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                      <Award className="h-5 w-5" />
                      專業證照
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    <CertificationCards
                      certifications={salesperson.certifications}
                      isLoading={false}
                      showFilter={salesperson.certifications.length > 4}
                    />
                  </CardContent>
                </Card>
              )}
            </div>

            {/* 右側聯絡資訊 - 保持不變 */}
            <div className="lg:col-span-1">
              <Card className="sticky top-20">
                <CardHeader>
                  <CardTitle>聯絡資訊</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  {/* 電話 */}
                  <div className="flex items-start gap-3">
                    <Phone className="h-5 w-5 text-slate-400 mt-0.5 flex-shrink-0" />
                    <div>
                      <p className="text-sm text-slate-500 mb-1">電話</p>
                      <a
                        href={`tel:${salesperson.phone}`}
                        className="text-slate-900 hover:text-primary-600 transition-colors"
                      >
                        {salesperson.phone}
                      </a>
                    </div>
                  </div>

                  {/* 服務地區 */}
                  {serviceRegions.length > 0 && (
                    <div className="flex items-start gap-3">
                      <MapPin className="h-5 w-5 text-slate-400 mt-0.5 flex-shrink-0" />
                      <div>
                        <p className="text-sm text-slate-500 mb-1">服務地區</p>
                        <div className="flex flex-wrap gap-2">
                          {serviceRegions.map((region, index) => (
                            <Badge key={index} variant="secondary" size="sm">
                              {region}
                            </Badge>
                          ))}
                        </div>
                      </div>
                    </div>
                  )}

                  {/* 註冊時間 */}
                  <div className="pt-4 border-t border-slate-200">
                    <div className="flex items-center gap-2 text-sm text-slate-500">
                      <Calendar className="h-4 w-4" />
                      <span>
                        註冊時間：{formatDate(salesperson.created_at)}
                      </span>
                    </div>
                  </div>

                  {/* CTA */}
                  <div className="pt-4 space-y-2">
                    <Button className="w-full" size="lg">
                      <Phone className="mr-2 h-4 w-4" />
                      立即聯絡
                    </Button>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
      </main>

      <Footer />
    </div>
  );
}
```

---

## 📐 佈局調整

### 響應式佈局

```tsx
Container:
className="
  container mx-auto
  px-4 sm:px-6 lg:px-8           /* 響應式水平內邊距 */
  py-8                            /* 垂直內邊距 */
"

Grid:
className="
  grid
  grid-cols-1 lg:grid-cols-3      /* Mobile: 1欄, Desktop: 3欄 */
  gap-6                           /* 間距 24px */
"

左側主要區塊:
className="
  lg:col-span-2                   /* Desktop 佔 2/3 寬度 */
  space-y-6                       /* 卡片間距 24px */
"

右側聯絡區塊:
className="
  lg:col-span-1                   /* Desktop 佔 1/3 寬度 */
"
```

### 卡片間距

```tsx
卡片容器:
<div className="space-y-6">
  <Card>個人資料</Card>
  <Card>工作經驗</Card>
  <Card>專業證照</Card>
</div>

間距: 24px (space-y-6)
```

---

## ⚠️ 錯誤處理

### 錯誤類型

#### 1. 載入錯誤

```tsx
if (error) {
  return (
    <Layout>
      <Card>
        <CardContent className="py-16 text-center">
          <h3 className="text-xl font-semibold text-slate-900 mb-2">
            無法載入業務員資料
          </h3>
          <p className="text-slate-600 mb-6">
            請稍後再試或聯絡客服
          </p>
          <Button onClick={() => window.location.reload()}>
            重新載入
          </Button>
        </CardContent>
      </Card>
    </Layout>
  );
}
```

#### 2. 業務員不存在

```tsx
if (!salesperson) {
  return (
    <Layout>
      <Card>
        <CardContent className="py-16 text-center">
          <h3 className="text-xl font-semibold text-slate-900 mb-2">
            找不到業務員資料
          </h3>
          <p className="text-slate-600 mb-6">
            此業務員可能不存在或已被移除
          </p>
          <Link href="/search">
            <Button>
              <ArrowLeft className="mr-2 h-4 w-4" />
              返回搜尋頁面
            </Button>
          </Link>
        </CardContent>
      </Card>
    </Layout>
  );
}
```

#### 3. 無效的 ID

```tsx
if (isNaN(id) || id <= 0) {
  redirect('/search');
}
```

---

## ⚡ 效能優化

### 優化策略

#### 1. 圖片延遲載入

```tsx
<Avatar
  src={salesperson.avatar}
  loading="lazy"
  ...
/>
```

#### 2. 條件渲染

```tsx
{/* 只在有資料時渲染組件 */}
{salesperson.experiences?.length > 0 && (
  <Card>
    <ExperienceTimeline experiences={salesperson.experiences} />
  </Card>
)}

{/* 避免渲染空狀態的卡片 */}
{salesperson.certifications?.length > 0 && (
  <Card>
    <CertificationCards certifications={salesperson.certifications} />
  </Card>
)}
```

#### 3. useMemo 優化排序

```tsx
// 組件內部已使用 useMemo 優化
const sortedExperiences = useMemo(() => {
  return sortExperiences(experiences);
}, [experiences]);
```

#### 4. 避免不必要的重渲染

```tsx
// ExperienceItem 和 CertificationCard 使用 React.memo
export const ExperienceItem = React.memo(ExperienceItemComponent);
export const CertificationCard = React.memo(CertificationCardComponent);
```

### 效能指標目標

| 指標 | 目標 | 說明 |
|------|------|------|
| **LCP** | < 2.5s | 主要內容載入時間 |
| **FCP** | < 1.8s | 首次內容繪製 |
| **TTI** | < 3.8s | 可互動時間 |
| **CLS** | < 0.1 | 累積版面配置位移 |

---

## ✅ 整合檢查清單

### 開發前檢查
- [ ] 確認現有頁面結構
- [ ] 確認 API 資料結構
- [ ] 確認設計系統規範
- [ ] 確認組件規格

### 開發中檢查
- [ ] 新組件正確引入
- [ ] 舊實作已移除
- [ ] Props 正確傳遞
- [ ] 條件渲染邏輯正確
- [ ] Loading 狀態正常
- [ ] 錯誤處理完整

### 開發後檢查
- [ ] 所有功能正常運作
- [ ] 響應式佈局正常
- [ ] Loading 狀態流暢
- [ ] 錯誤處理友善
- [ ] 效能指標達標
- [ ] 無 console 錯誤
- [ ] 無 TypeScript 錯誤

---

**版本**: 1.0
**日期**: 2026-01-20
**狀態**: Ready for Implementation
