# UI Components Design Specification

**Project**: YAMU Frontend SPA
**Version**: 1.0.0
**Design System**: Tailwind CSS + Shadcn/ui
**Last Updated**: 2026-01-08

---

## Design System

### Color Palette

基於「清新藍綠 - 現代活潑」風格：

```typescript
// tailwind.config.ts
export default {
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#f0f9ff',
          100: '#e0f2fe',
          200: '#bae6fd',
          300: '#7dd3fc',
          400: '#38bdf8',
          500: '#0ea5e9', // 主色 Sky-500
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
          500: '#14b8a6', // 配色 Teal-500
          600: '#0d9488',
          700: '#0f766e',
          800: '#115e59',
          900: '#134e4a',
        },
        accent: {
          400: '#f472b6', // 強調色 Pink-400
          500: '#ec4899',
        },
        background: '#f8fafc', // Slate-50
        foreground: '#0f172a', // Slate-900
      },
    },
  },
};
```

### Typography

```typescript
// fonts
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

// app/layout.tsx
<html className={`${inter.variable} ${notoSansTC.variable}`}>
  <body className="font-sans">
    {children}
  </body>
</html>
```

**Font Scales**:
```css
.text-xs { font-size: 0.75rem; }     /* 12px */
.text-sm { font-size: 0.875rem; }    /* 14px */
.text-base { font-size: 1rem; }      /* 16px */
.text-lg { font-size: 1.125rem; }    /* 18px */
.text-xl { font-size: 1.25rem; }     /* 20px */
.text-2xl { font-size: 1.5rem; }     /* 24px */
.text-3xl { font-size: 1.875rem; }   /* 30px */
.text-4xl { font-size: 2.25rem; }    /* 36px */
```

### Spacing & Layout

```css
/* Container */
.container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 1rem;
}

/* Common Spacing */
space-y-4  /* 1rem / 16px */
space-y-6  /* 1.5rem / 24px */
space-y-8  /* 2rem / 32px */
gap-4      /* 1rem / 16px */
gap-6      /* 1.5rem / 24px */
```

### Border Radius

```css
/* 活潑年輕風格 - 使用較大圓角 */
rounded-lg   /* 0.5rem / 8px */
rounded-xl   /* 0.75rem / 12px */
rounded-2xl  /* 1rem / 16px */
rounded-full /* 完全圓形 */
```

### Shadows

```css
shadow-sm    /* 細微陰影 */
shadow-md    /* 中等陰影 */
shadow-lg    /* 較大陰影 */
shadow-xl    /* 明顯陰影 */
```

---

## Shared Components (Shadcn/ui)

### Button Component

```tsx
// components/ui/button.tsx
import { cn } from '@/lib/utils';

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'default' | 'secondary' | 'outline' | 'ghost' | 'link';
  size?: 'default' | 'sm' | 'lg' | 'icon';
  isLoading?: boolean;
}

export function Button({
  className,
  variant = 'default',
  size = 'default',
  isLoading = false,
  disabled,
  children,
  ...props
}: ButtonProps) {
  const variants = {
    default: 'bg-primary-500 text-white hover:bg-primary-600 active:bg-primary-700',
    secondary: 'bg-secondary-500 text-white hover:bg-secondary-600',
    outline: 'border-2 border-primary-500 text-primary-500 hover:bg-primary-50',
    ghost: 'hover:bg-primary-50 text-primary-500',
    link: 'text-primary-500 underline-offset-4 hover:underline',
  };

  const sizes = {
    default: 'h-10 px-4 py-2 text-base',
    sm: 'h-8 px-3 py-1 text-sm rounded-lg',
    lg: 'h-12 px-6 py-3 text-lg rounded-xl',
    icon: 'h-10 w-10 p-2',
  };

  return (
    <button
      className={cn(
        'inline-flex items-center justify-center rounded-xl font-medium transition-colors',
        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500',
        'disabled:opacity-50 disabled:cursor-not-allowed',
        variants[variant],
        sizes[size],
        className
      )}
      disabled={disabled || isLoading}
      {...props}
    >
      {isLoading && <LoadingSpinner className="mr-2" />}
      {children}
    </button>
  );
}
```

**Usage**:
```tsx
<Button variant="default" size="lg">
  立即搜尋
</Button>

<Button variant="outline" onClick={handleCancel}>
  取消
</Button>

<Button variant="ghost" size="icon">
  <XIcon className="h-4 w-4" />
</Button>
```

---

### Input Component

```tsx
// components/ui/input.tsx
interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
  helperText?: string;
}

export function Input({
  className,
  label,
  error,
  helperText,
  ...props
}: InputProps) {
  return (
    <div className="space-y-2">
      {label && (
        <label className="text-sm font-medium text-foreground">
          {label}
          {props.required && <span className="text-red-500 ml-1">*</span>}
        </label>
      )}
      <input
        className={cn(
          'flex h-10 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm',
          'placeholder:text-slate-400',
          'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent',
          'disabled:cursor-not-allowed disabled:opacity-50',
          error && 'border-red-500 focus:ring-red-500',
          className
        )}
        {...props}
      />
      {error && <p className="text-sm text-red-500">{error}</p>}
      {helperText && !error && <p className="text-sm text-slate-500">{helperText}</p>}
    </div>
  );
}
```

---

### Card Component

```tsx
// components/ui/card.tsx
export function Card({
  className,
  children,
  ...props
}: React.HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={cn(
        'rounded-2xl border border-slate-200 bg-white shadow-sm',
        'hover:shadow-md transition-shadow',
        className
      )}
      {...props}
    >
      {children}
    </div>
  );
}

export function CardHeader({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('flex flex-col space-y-1.5 p-6', className)} {...props} />;
}

export function CardContent({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
  return <div className={cn('p-6 pt-0', className)} {...props} />;
}
```

---

### Avatar Component

```tsx
// components/ui/avatar.tsx
interface AvatarProps {
  src?: string | null;
  alt: string;
  fallback: string; // 姓名首字
  size?: 'sm' | 'md' | 'lg' | 'xl';
}

export function Avatar({ src, alt, fallback, size = 'md' }: AvatarProps) {
  const sizes = {
    sm: 'h-8 w-8 text-sm',
    md: 'h-12 w-12 text-base',
    lg: 'h-16 w-16 text-lg',
    xl: 'h-24 w-24 text-2xl',
  };

  return (
    <div
      className={cn(
        'relative inline-flex items-center justify-center rounded-full',
        'bg-gradient-to-br from-primary-400 to-secondary-400',
        'text-white font-medium',
        sizes[size]
      )}
    >
      {src ? (
        <img
          src={src}
          alt={alt}
          className="h-full w-full rounded-full object-cover"
        />
      ) : (
        <span>{fallback}</span>
      )}
    </div>
  );
}
```

---

### Badge Component

```tsx
// components/ui/badge.tsx
interface BadgeProps {
  variant?: 'default' | 'success' | 'warning' | 'danger';
  children: React.ReactNode;
}

export function Badge({ variant = 'default', children }: BadgeProps) {
  const variants = {
    default: 'bg-slate-100 text-slate-700',
    success: 'bg-green-100 text-green-700',
    warning: 'bg-amber-100 text-amber-700',
    danger: 'bg-red-100 text-red-700',
  };

  return (
    <span
      className={cn(
        'inline-flex items-center rounded-full px-3 py-1 text-xs font-medium',
        variants[variant]
      )}
    >
      {children}
    </span>
  );
}
```

---

## Layout Components

### Header (導航列)

```tsx
// components/layout/header.tsx
import Link from 'next/link';
import { useAuth } from '@/hooks/useAuth';
import { Avatar } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';

export function Header() {
  const { data: user, isLoading } = useAuth();

  return (
    <header className="sticky top-0 z-50 w-full border-b border-slate-200 bg-white/80 backdrop-blur-sm">
      <div className="container flex h-16 items-center justify-between">
        {/* Logo */}
        <Link href="/" className="flex items-center space-x-2">
          <div className="h-8 w-8 rounded-lg bg-gradient-to-br from-primary-500 to-secondary-500" />
          <span className="text-2xl font-bold bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent">
            YAMU
          </span>
        </Link>

        {/* Navigation */}
        <nav className="hidden md:flex items-center space-x-6">
          <Link href="/search" className="text-sm font-medium hover:text-primary-500 transition-colors">
            搜尋業務員
          </Link>
          {user?.role === 'salesperson' && (
            <>
              <Link href="/dashboard" className="text-sm font-medium hover:text-primary-500 transition-colors">
                我的檔案
              </Link>
              <Link href="/dashboard/experiences" className="text-sm font-medium hover:text-primary-500 transition-colors">
                工作經驗
              </Link>
            </>
          )}
          {user?.role === 'admin' && (
            <Link href="/admin" className="text-sm font-medium hover:text-primary-500 transition-colors">
              管理後台
            </Link>
          )}
        </nav>

        {/* User Menu */}
        <div className="flex items-center space-x-4">
          {isLoading ? (
            <div className="h-8 w-20 animate-pulse bg-slate-200 rounded-lg" />
          ) : user ? (
            <UserMenu user={user} />
          ) : (
            <>
              <Link href="/login">
                <Button variant="ghost" size="sm">登入</Button>
              </Link>
              <Link href="/register">
                <Button size="sm">免費註冊</Button>
              </Link>
            </>
          )}
        </div>
      </div>
    </header>
  );
}

function UserMenu({ user }: { user: User }) {
  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <button className="flex items-center space-x-2 hover:opacity-80 transition-opacity">
          <Avatar
            src={null}
            alt={user.username}
            fallback={user.username[0].toUpperCase()}
            size="sm"
          />
          <span className="hidden md:inline text-sm font-medium">{user.username}</span>
        </button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-56">
        <DropdownMenuItem asChild>
          <Link href="/settings">設定</Link>
        </DropdownMenuItem>
        <DropdownMenuSeparator />
        <DropdownMenuItem onClick={handleLogout}>
          登出
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  );
}
```

---

### Footer

```tsx
// components/layout/footer.tsx
export function Footer() {
  return (
    <footer className="border-t border-slate-200 bg-slate-50 mt-auto">
      <div className="container py-12">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          {/* Logo & Description */}
          <div className="space-y-4">
            <div className="flex items-center space-x-2">
              <div className="h-8 w-8 rounded-lg bg-gradient-to-br from-primary-500 to-secondary-500" />
              <span className="text-xl font-bold">YAMU</span>
            </div>
            <p className="text-sm text-slate-600">
              專業的業務員推廣平台，協助您找到最適合的業務夥伴。
            </p>
          </div>

          {/* Links */}
          <div>
            <h3 className="font-semibold mb-4">功能</h3>
            <ul className="space-y-2 text-sm text-slate-600">
              <li><Link href="/search">搜尋業務員</Link></li>
              <li><Link href="/register">免費註冊</Link></li>
            </ul>
          </div>

          <div>
            <h3 className="font-semibold mb-4">關於</h3>
            <ul className="space-y-2 text-sm text-slate-600">
              <li><Link href="/about">關於我們</Link></li>
              <li><Link href="/contact">聯絡我們</Link></li>
            </ul>
          </div>

          <div>
            <h3 className="font-semibold mb-4">法律</h3>
            <ul className="space-y-2 text-sm text-slate-600">
              <li><Link href="/terms">服務條款</Link></li>
              <li><Link href="/privacy">隱私政策</Link></li>
            </ul>
          </div>
        </div>

        <div className="mt-8 pt-8 border-t border-slate-200">
          <p className="text-center text-sm text-slate-600">
            © 2026 YAMU. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  );
}
```

---

## Page Components

### 1. Homepage (首頁)

**Route**: `/`

**Layout**:
```
┌─────────────────────────────────┐
│  Header (導航列)                 │
├─────────────────────────────────┤
│  Hero Section                   │
│  - 大標題                        │
│  - 搜尋列                        │
│  - CTA 按鈕                      │
├─────────────────────────────────┤
│  Features Section               │
│  - 3 個特色卡片                  │
├─────────────────────────────────┤
│  Popular Salespersons           │
│  - 熱門業務員卡片 (橫向滾動)      │
├─────────────────────────────────┤
│  CTA Section                    │
│  - 立即加入 CTA                  │
├─────────────────────────────────┤
│  Footer                         │
└─────────────────────────────────┘
```

**Implementation**:
```tsx
// app/(public)/page.tsx
export default function Homepage() {
  return (
    <div className="min-h-screen flex flex-col">
      <Header />

      {/* Hero Section */}
      <section className="bg-gradient-to-br from-primary-50 via-secondary-50 to-pink-50 py-20">
        <div className="container">
          <div className="max-w-3xl mx-auto text-center space-y-8">
            <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold text-foreground">
              找到最適合您的
              <span className="bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent">
                專業業務員
              </span>
            </h1>
            <p className="text-lg text-slate-600">
              瀏覽數百位專業業務員的資料，快速找到最符合您需求的業務夥伴
            </p>

            {/* Search Bar */}
            <div className="flex gap-2 max-w-2xl mx-auto">
              <Input
                placeholder="搜尋業務員姓名、公司或專長..."
                className="h-14 text-lg"
              />
              <Button size="lg" className="px-8">
                搜尋
              </Button>
            </div>

            {/* Quick Filters */}
            <div className="flex flex-wrap gap-2 justify-center">
              <Badge>科技業</Badge>
              <Badge>金融業</Badge>
              <Badge>製造業</Badge>
              <Badge>服務業</Badge>
            </div>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-20">
        <div className="container">
          <h2 className="text-3xl font-bold text-center mb-12">為什麼選擇 YAMU?</h2>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            <FeatureCard
              icon={<SearchIcon className="h-8 w-8" />}
              title="快速搜尋"
              description="透過關鍵字、產業、地區等多重條件，快速找到適合的業務員"
            />
            <FeatureCard
              icon={<ShieldCheckIcon className="h-8 w-8" />}
              title="資料審核"
              description="所有業務員資料經過嚴格審核，確保資訊真實可靠"
            />
            <FeatureCard
              icon={<UsersIcon className="h-8 w-8" />}
              title="專業認證"
              description="查看業務員的工作經驗與專業證照，做出明智決策"
            />
          </div>
        </div>
      </section>

      {/* Popular Salespersons */}
      <section className="py-20 bg-slate-50">
        <div className="container">
          <div className="flex items-center justify-between mb-8">
            <h2 className="text-3xl font-bold">熱門業務員</h2>
            <Link href="/search">
              <Button variant="outline">查看全部</Button>
            </Link>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
            {/* 使用 SalespersonCard 組件 */}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-20 bg-gradient-to-r from-primary-500 to-secondary-500 text-white">
        <div className="container text-center space-y-6">
          <h2 className="text-4xl font-bold">準備好開始了嗎？</h2>
          <p className="text-xl opacity-90">立即註冊，開始建立您的專業形象</p>
          <Link href="/register">
            <Button size="lg" variant="secondary" className="bg-white text-primary-500 hover:bg-slate-100">
              免費註冊
            </Button>
          </Link>
        </div>
      </section>

      <Footer />
    </div>
  );
}
```

---

### 2. Search Page (搜尋頁面)

**Route**: `/search`

**Layout**:
```
┌─────────────────────────────────┐
│  Header                         │
├──────────┬──────────────────────┤
│          │  Search Results      │
│  Filters │  - Keyword Input     │
│  Sidebar │  - Sort Options      │
│          │  - Cards Grid        │
│  - 產業   │  - Pagination        │
│  - 地區   │                      │
│  - 公司   │                      │
│          │                      │
└──────────┴──────────────────────┘
```

**Implementation**:
```tsx
// app/(public)/search/page.tsx
'use client';

import { useState } from 'react';
import { useSearchSalespersons } from '@/hooks/useSearch';
import { SearchFilters } from '@/components/features/search/search-filters';
import { SalespersonCard } from '@/components/features/search/salesperson-card';
import { Pagination } from '@/components/ui/pagination';

export default function SearchPage() {
  const [filters, setFilters] = useState<SearchParams>({
    keyword: '',
    page: 1,
    per_page: 12,
  });

  const { data, isLoading } = useSearchSalespersons(filters);

  return (
    <div className="min-h-screen flex flex-col">
      <Header />

      <div className="flex-1 bg-slate-50 py-8">
        <div className="container">
          <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
            {/* Filters Sidebar */}
            <aside className="lg:col-span-1">
              <SearchFilters
                filters={filters}
                onChange={setFilters}
              />
            </aside>

            {/* Results */}
            <main className="lg:col-span-3">
              {/* Search Bar */}
              <Card className="p-4 mb-6">
                <div className="flex gap-2">
                  <Input
                    placeholder="搜尋業務員..."
                    value={filters.keyword}
                    onChange={(e) => setFilters({ ...filters, keyword: e.target.value })}
                  />
                  <Button>搜尋</Button>
                </div>
              </Card>

              {/* Results Header */}
              <div className="flex items-center justify-between mb-6">
                <p className="text-sm text-slate-600">
                  找到 <span className="font-semibold">{data?.data.pagination.total}</span> 位業務員
                </p>
                <Select value={sortBy} onValueChange={setSortBy}>
                  <SelectTrigger className="w-40">
                    <SelectValue placeholder="排序" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="relevant">相關度</SelectItem>
                    <SelectItem value="newest">最新註冊</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              {/* Results Grid */}
              {isLoading ? (
                <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                  {[...Array(6)].map((_, i) => (
                    <SalespersonCardSkeleton key={i} />
                  ))}
                </div>
              ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                  {data?.data.salespersons.map((salesperson) => (
                    <SalespersonCard key={salesperson.id} data={salesperson} />
                  ))}
                </div>
              )}

              {/* Pagination */}
              {data && (
                <div className="mt-8 flex justify-center">
                  <Pagination
                    currentPage={data.data.pagination.current_page}
                    totalPages={data.data.pagination.total_pages}
                    onPageChange={(page) => setFilters({ ...filters, page })}
                  />
                </div>
              )}
            </main>
          </div>
        </div>
      </div>

      <Footer />
    </div>
  );
}
```

**SalespersonCard Component**:
```tsx
// components/features/search/salesperson-card.tsx
interface SalespersonCardProps {
  data: SalespersonCard;
}

export function SalespersonCard({ data }: SalespersonCardProps) {
  return (
    <Link href={`/salesperson/${data.id}`}>
      <Card className="overflow-hidden hover:shadow-lg transition-shadow cursor-pointer">
        {/* Avatar & Basic Info */}
        <div className="p-6">
          <div className="flex items-center space-x-4 mb-4">
            <Avatar
              src={data.avatar_url}
              alt={data.full_name}
              fallback={data.full_name[0]}
              size="lg"
            />
            <div className="flex-1">
              <h3 className="font-semibold text-lg">{data.full_name}</h3>
              {data.company_name && (
                <p className="text-sm text-slate-600">{data.company_name}</p>
              )}
            </div>
          </div>

          {/* Industry Badge */}
          {data.industry_name && (
            <Badge variant="default" className="mb-3">
              {data.industry_name}
            </Badge>
          )}

          {/* Bio */}
          {data.bio && (
            <p className="text-sm text-slate-600 line-clamp-2 mb-4">
              {data.bio}
            </p>
          )}

          {/* Specialties */}
          {data.specialties && (
            <div className="flex flex-wrap gap-2">
              {data.specialties.split(',').slice(0, 3).map((spec, i) => (
                <Badge key={i} variant="default" className="text-xs">
                  {spec.trim()}
                </Badge>
              ))}
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="px-6 py-3 bg-slate-50 border-t border-slate-100">
          <Button variant="ghost" size="sm" className="w-full">
            查看詳細資料 →
          </Button>
        </div>
      </Card>
    </Link>
  );
}
```

---

### 3. Salesperson Detail Page (業務員詳細頁)

**Route**: `/salesperson/[id]`

**Sections**:
1. Hero Section - 頭像、姓名、公司、聯絡資訊
2. About Section - 簡介與專長
3. Experience Section - 工作經驗列表
4. Certifications Section - 專業證照（已審核）

```tsx
// app/(public)/salesperson/[id]/page.tsx
export default async function SalespersonDetailPage({
  params,
}: {
  params: { id: string };
}) {
  const data = await getSalespersonDetail(parseInt(params.id));
  const salesperson = data.data;

  return (
    <div className="min-h-screen flex flex-col">
      <Header />

      <main className="flex-1 bg-slate-50 py-12">
        <div className="container max-w-4xl">
          {/* Hero Card */}
          <Card className="p-8 mb-8">
            <div className="flex flex-col md:flex-row gap-8">
              <Avatar
                src={salesperson.avatar_url}
                alt={salesperson.full_name}
                fallback={salesperson.full_name[0]}
                size="xl"
              />
              <div className="flex-1">
                <h1 className="text-3xl font-bold mb-2">{salesperson.full_name}</h1>
                {salesperson.company && (
                  <p className="text-lg text-slate-600 mb-4">
                    {salesperson.company.name} · {salesperson.company.industry_name}
                  </p>
                )}
                {salesperson.phone && (
                  <div className="flex items-center gap-2 text-slate-600 mb-4">
                    <PhoneIcon className="h-5 w-5" />
                    <span>{salesperson.phone}</span>
                  </div>
                )}
                <div className="flex flex-wrap gap-2">
                  {salesperson.specialties?.split(',').map((spec, i) => (
                    <Badge key={i}>{spec.trim()}</Badge>
                  ))}
                </div>
              </div>
            </div>
          </Card>

          {/* About Section */}
          {salesperson.bio && (
            <Card className="p-6 mb-8">
              <h2 className="text-xl font-semibold mb-4">關於我</h2>
              <p className="text-slate-700 leading-relaxed">{salesperson.bio}</p>
            </Card>
          )}

          {/* Experience Section */}
          {salesperson.experiences.length > 0 && (
            <Card className="p-6 mb-8">
              <h2 className="text-xl font-semibold mb-4">工作經驗</h2>
              <div className="space-y-6">
                {salesperson.experiences.map((exp) => (
                  <ExperienceItem key={exp.id} experience={exp} />
                ))}
              </div>
            </Card>
          )}

          {/* Certifications Section */}
          {salesperson.certifications.filter(c => c.approval_status === 'approved').length > 0 && (
            <Card className="p-6">
              <h2 className="text-xl font-semibold mb-4">專業證照</h2>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {salesperson.certifications
                  .filter(c => c.approval_status === 'approved')
                  .map((cert) => (
                    <CertificationItem key={cert.id} certification={cert} />
                  ))}
              </div>
            </Card>
          )}
        </div>
      </main>

      <Footer />
    </div>
  );
}
```

---

### 4. Login Page (登入頁面)

**Route**: `/login`

```tsx
// app/(auth)/login/page.tsx
'use client';

import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { login } from '@/lib/api/auth';

const loginSchema = z.object({
  email: z.string().email('請輸入有效的電子郵件'),
  password: z.string().min(8, '密碼至少需要 8 個字元'),
});

type LoginFormData = z.infer<typeof loginSchema>;

export default function LoginPage() {
  const router = useRouter();
  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<LoginFormData>({
    resolver: zodResolver(loginSchema),
  });

  const onSubmit = async (data: LoginFormData) => {
    try {
      const result = await login(data);
      toast.success('登入成功！');
      router.push('/dashboard');
    } catch (error) {
      handleApiError(error);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 via-secondary-50 to-pink-50">
      <Card className="w-full max-w-md p-8">
        {/* Logo */}
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent">
            YAMU
          </h1>
          <p className="text-slate-600 mt-2">登入您的帳號</p>
        </div>

        {/* Form */}
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
          <Input
            label="電子郵件"
            type="email"
            placeholder="your@email.com"
            error={errors.email?.message}
            {...register('email')}
          />

          <Input
            label="密碼"
            type="password"
            placeholder="••••••••"
            error={errors.password?.message}
            {...register('password')}
          />

          <Button
            type="submit"
            className="w-full"
            size="lg"
            isLoading={isSubmitting}
          >
            登入
          </Button>
        </form>

        {/* Footer Links */}
        <div className="mt-6 text-center text-sm">
          <p className="text-slate-600">
            還沒有帳號？{' '}
            <Link href="/register" className="text-primary-500 hover:underline font-medium">
              立即註冊
            </Link>
          </p>
        </div>
      </Card>
    </div>
  );
}
```

---

### 5. Dashboard (業務員儀表板)

**Route**: `/dashboard`

**Layout**:
```
┌─────────────────────────────────┐
│  Header                         │
├──────────┬──────────────────────┤
│          │  Main Content        │
│  Sidebar │                      │
│          │  Profile Editor      │
│  - 個人資料│  - 基本資料          │
│  - 工作經驗│  - 頭像上傳          │
│  - 證照   │  - 公司資訊          │
│  - 審核狀態│  - 服務地區          │
│          │                      │
└──────────┴──────────────────────┘
```

**Sidebar**:
```tsx
// components/layout/dashboard-sidebar.tsx
const navItems = [
  { href: '/dashboard', label: '個人資料', icon: UserIcon },
  { href: '/dashboard/experiences', label: '工作經驗', icon: BriefcaseIcon },
  { href: '/dashboard/certifications', label: '專業證照', icon: AwardIcon },
  { href: '/dashboard/approval-status', label: '審核狀態', icon: ClockIcon },
];

export function DashboardSidebar() {
  const pathname = usePathname();

  return (
    <aside className="w-64 bg-white border-r border-slate-200 p-4">
      <nav className="space-y-2">
        {navItems.map((item) => (
          <Link
            key={item.href}
            href={item.href}
            className={cn(
              'flex items-center space-x-3 px-4 py-3 rounded-xl transition-colors',
              pathname === item.href
                ? 'bg-primary-50 text-primary-600 font-medium'
                : 'text-slate-600 hover:bg-slate-50'
            )}
          >
            <item.icon className="h-5 w-5" />
            <span>{item.label}</span>
          </Link>
        ))}
      </nav>
    </aside>
  );
}
```

---

### 6. Admin Dashboard (管理員後台)

**Route**: `/admin`

**Main Sections**:
1. Overview Cards - 統計數字
2. Pending Approvals List - 待審核項目
3. Quick Actions

```tsx
// app/(admin)/admin/page.tsx
export default function AdminDashboard() {
  const { data: stats } = useStatistics();
  const { data: pendings } = usePendingApprovals();

  return (
    <div className="min-h-screen flex flex-col">
      <Header />

      <main className="flex-1 bg-slate-50 py-8">
        <div className="container">
          <h1 className="text-3xl font-bold mb-8">管理後台</h1>

          {/* Statistics Cards */}
          <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <StatsCard
              title="總業務員數"
              value={stats?.data.total_salespersons}
              icon={<UsersIcon />}
              color="primary"
            />
            <StatsCard
              title="活躍業務員"
              value={stats?.data.active_salespersons}
              icon={<CheckCircleIcon />}
              color="success"
            />
            <StatsCard
              title="待審核"
              value={stats?.data.pending_approvals}
              icon={<ClockIcon />}
              color="warning"
            />
            <StatsCard
              title="公司總數"
              value={stats?.data.total_companies}
              icon={<BuildingIcon />}
              color="secondary"
            />
          </div>

          {/* Pending Approvals */}
          <Card className="p-6">
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-xl font-semibold">待審核項目</h2>
              <Link href="/admin/approvals">
                <Button variant="outline">查看全部</Button>
              </Link>
            </div>

            <Tabs defaultValue="users">
              <TabsList>
                <TabsTrigger value="users">
                  業務員註冊 ({pendings?.data.users.length})
                </TabsTrigger>
                <TabsTrigger value="companies">
                  公司資訊 ({pendings?.data.companies.length})
                </TabsTrigger>
                <TabsTrigger value="certifications">
                  證照 ({pendings?.data.certifications.length})
                </TabsTrigger>
              </TabsList>

              <TabsContent value="users">
                <PendingUsersList users={pendings?.data.users || []} />
              </TabsContent>

              <TabsContent value="companies">
                <PendingCompaniesList companies={pendings?.data.companies || []} />
              </TabsContent>

              <TabsContent value="certifications">
                <PendingCertificationsList certifications={pendings?.data.certifications || []} />
              </TabsContent>
            </Tabs>
          </Card>
        </div>
      </main>

      <Footer />
    </div>
  );
}
```

---

## Responsive Design Breakpoints

```typescript
// Tailwind CSS 預設斷點
{
  sm: '640px',   // 手機橫向
  md: '768px',   // 平板
  lg: '1024px',  // 桌面
  xl: '1280px',  // 大桌面
  '2xl': '1536px', // 超大桌面
}
```

**Mobile-First 設計原則**:
```tsx
// ✅ 正確: 預設手機樣式，逐步增強
<div className="flex flex-col md:flex-row gap-4">

// ❌ 錯誤: 預設桌面樣式
<div className="flex flex-row gap-4 md:flex-col">
```

---

## Loading States

### Skeleton Components

```tsx
// components/ui/skeleton.tsx
export function Skeleton({ className }: { className?: string }) {
  return (
    <div
      className={cn(
        'animate-pulse rounded-lg bg-slate-200',
        className
      )}
    />
  );
}

// Usage
export function SalespersonCardSkeleton() {
  return (
    <Card className="p-6">
      <div className="flex items-center space-x-4 mb-4">
        <Skeleton className="h-16 w-16 rounded-full" />
        <div className="flex-1 space-y-2">
          <Skeleton className="h-4 w-32" />
          <Skeleton className="h-3 w-24" />
        </div>
      </div>
      <Skeleton className="h-20 w-full mb-4" />
      <div className="flex gap-2">
        <Skeleton className="h-6 w-16" />
        <Skeleton className="h-6 w-20" />
      </div>
    </Card>
  );
}
```

---

## Animation & Transitions

```css
/* 常用過場效果 */
.transition-all {
  transition-property: all;
  transition-duration: 150ms;
}

.hover\:scale-105:hover {
  transform: scale(1.05);
}

.hover\:shadow-lg:hover {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

/* 淡入動畫 */
@keyframes fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}

.animate-fade-in {
  animation: fade-in 300ms ease-in;
}
```

---

## Accessibility (a11y)

### Focus States

```tsx
// 所有互動元素都要有明確的 focus 樣式
<button className="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2">
  Click Me
</button>
```

### Semantic HTML

```tsx
// ✅ 使用語意化標籤
<main>
  <article>
    <header>
      <h1>Title</h1>
    </header>
    <section>
      <h2>Section Title</h2>
      <p>Content...</p>
    </section>
  </article>
</main>

// ❌ 避免過度使用 div
<div>
  <div>
    <div>Title</div>
  </div>
</div>
```

### ARIA Labels

```tsx
// 為無文字的按鈕提供標籤
<button aria-label="關閉">
  <XIcon className="h-4 w-4" />
</button>

// 為載入狀態提供訊息
<div role="status" aria-live="polite">
  {isLoading && <span className="sr-only">載入中...</span>}
</div>
```

---

## Component Checklist

實作每個組件時必須確認：

### 基本功能
- [ ] 組件可正常渲染
- [ ] Props 型別定義完整
- [ ] 預設值合理

### 樣式
- [ ] 符合設計系統規範
- [ ] 響應式設計正確
- [ ] Hover/Active/Focus 狀態完整

### 可用性
- [ ] 鍵盤導航可用
- [ ] ARIA 標籤正確
- [ ] 錯誤狀態處理

### 效能
- [ ] 避免不必要的重新渲染
- [ ] 圖片使用 Next/Image 優化
- [ ] 適當使用 memo/useMemo

---

**Status**: ✅ UI Components Specification Complete
**Next Document**: State Management & Routing Specification
