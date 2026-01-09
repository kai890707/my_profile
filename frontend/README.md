# YAMU Frontend - Next.js 15

> 業務員推廣系統前端應用 - 現代化、高效能、用戶友好

[![Next.js](https://img.shields.io/badge/Next.js-15-000000?logo=next.js)](https://nextjs.org)
[![React](https://img.shields.io/badge/React-19-61DAFB?logo=react)](https://react.dev)
[![TypeScript](https://img.shields.io/badge/TypeScript-5-3178C6?logo=typescript)](https://www.typescriptlang.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-3-38B2AC?logo=tailwind-css)](https://tailwindcss.com)

---

## 📋 專案簡介

這是 YAMU 業務員推廣系統的前端應用程式，提供直觀的用戶界面來訪問後端 API。

### 核心特性

- 🎨 **現代化 UI** - shadcn/ui 組件系統
- ⚡ **高效能** - Next.js 15 App Router + React Server Components
- 🔒 **安全認證** - JWT Token 管理
- 📱 **響應式設計** - 完美支援桌面與行動裝置
- 🌐 **國際化支援** - 繁體中文介面
- ♿ **無障礙設計** - WCAG 2.1 AA 標準
- 🎯 **TypeScript** - 完整的型別安全

---

## 🚀 快速開始

### 必要條件

- Node.js 18.x 或更高版本
- npm, yarn, 或 pnpm

### 安裝與啟動

```bash
# 1. 進入專案目錄
cd frontend

# 2. 安裝依賴
npm install

# 3. 設定環境變數
cp .env.example .env.local

# 編輯 .env.local 設定 API URL
# NEXT_PUBLIC_API_URL=http://localhost:8080

# 4. 啟動開發伺服器
npm run dev

# 5. 開啟瀏覽器
open http://localhost:3000
```

### 可用指令

```bash
# 開發伺服器
npm run dev

# 生產建置
npm run build

# 啟動生產伺服器
npm run start

# 類型檢查
npm run type-check

# Lint 檢查
npm run lint

# 格式化代碼
npm run format
```

---

## 🏗️ 專案架構

```
frontend/
├── app/                        # Next.js 15 App Router
│   ├── (auth)/                # 認證相關頁面
│   │   ├── login/             # 登入頁
│   │   └── register/          # 註冊頁
│   ├── (dashboard)/           # 儀表板
│   │   ├── profiles/          # 業務員列表
│   │   ├── companies/         # 公司列表
│   │   └── admin/             # 管理後台
│   ├── layout.tsx             # 根佈局
│   ├── page.tsx               # 首頁
│   └── globals.css            # 全域樣式
├── components/                 # React 組件
│   ├── ui/                    # shadcn/ui 基礎組件
│   ├── layout/                # 佈局組件
│   ├── forms/                 # 表單組件
│   └── features/              # 功能組件
├── lib/                        # 工具函式
│   ├── api.ts                 # API 客戶端
│   ├── auth.ts                # 認證邏輯
│   └── utils.ts               # 通用工具
├── hooks/                      # 自定義 Hooks
│   ├── useAuth.ts             # 認證 Hook
│   ├── useApi.ts              # API Hook
│   └── useToast.ts            # Toast 通知 Hook
├── types/                      # TypeScript 類型定義
│   ├── api.ts                 # API 響應類型
│   └── models.ts              # 資料模型類型
├── public/                     # 靜態資源
├── middleware.ts               # Next.js 中間件（認證檢查）
├── next.config.ts              # Next.js 配置
├── tailwind.config.ts          # Tailwind CSS 配置
├── tsconfig.json               # TypeScript 配置
└── package.json                # 專案依賴
```

---

## 📊 技術棧

### 核心框架

| 技術 | 版本 | 用途 |
|------|------|------|
| **Next.js** | 15.x | React 框架 |
| **React** | 19.x | UI 函式庫 |
| **TypeScript** | 5.x | 型別系統 |

### UI 組件

| 技術 | 版本 | 用途 |
|------|------|------|
| **Tailwind CSS** | 3.x | CSS 框架 |
| **shadcn/ui** | Latest | UI 組件庫 |
| **Radix UI** | Latest | 無障礙組件基礎 |
| **Lucide Icons** | Latest | 圖標系統 |

### 狀態管理 & 數據獲取

| 技術 | 用途 |
|------|------|
| **React Query** | 伺服器狀態管理 |
| **Zustand** | 客戶端狀態管理 |
| **axios** | HTTP 客戶端 |

### 開發工具

| 技術 | 用途 |
|------|------|
| **ESLint** | 代碼檢查 |
| **Prettier** | 代碼格式化 |
| **Husky** | Git Hooks |

---

## 🎨 UI 組件系統

### shadcn/ui 組件

已安裝的組件：

```
✅ Button       - 按鈕組件
✅ Card         - 卡片容器
✅ Dialog       - 對話框/彈窗
✅ Form         - 表單組件
✅ Input        - 輸入框
✅ Select       - 下拉選單
✅ Tabs         - 分頁標籤
✅ Avatar       - 頭像組件
✅ DropdownMenu - 下拉選單
```

### 添加新組件

```bash
# 使用 shadcn/ui CLI 添加組件
npx shadcn@latest add [component-name]

# 範例：添加 Table 組件
npx shadcn@latest add table
```

### 自定義主題

主題配置位於 `tailwind.config.ts`：

```typescript
// 自定義顏色
colors: {
  primary: {...},
  secondary: {...},
  accent: {...},
}
```

---

## 🔐 認證流程

### JWT Token 管理

```typescript
// lib/auth.ts
export class AuthService {
  // 登入
  async login(email: string, password: string) {
    const { access_token, refresh_token, user } = await api.post('/auth/login', {
      email,
      password,
    })

    // 儲存 Token
    localStorage.setItem('access_token', access_token)
    localStorage.setItem('refresh_token', refresh_token)

    return user
  }

  // 自動刷新 Token
  async refreshToken() {
    const refresh_token = localStorage.getItem('refresh_token')
    const { access_token } = await api.post('/auth/refresh', null, {
      headers: { Authorization: `Bearer ${refresh_token}` }
    })
    localStorage.setItem('access_token', access_token)
  }
}
```

### 受保護的路由

```typescript
// middleware.ts
export function middleware(request: NextRequest) {
  const token = request.cookies.get('access_token')

  // 需要認證的路由
  if (request.nextUrl.pathname.startsWith('/dashboard')) {
    if (!token) {
      return NextResponse.redirect(new URL('/login', request.url))
    }
  }

  return NextResponse.next()
}
```

### useAuth Hook

```typescript
// hooks/useAuth.ts
export function useAuth() {
  const [user, setUser] = useState<User | null>(null)
  const [loading, setLoading] = useState(true)

  const login = async (email: string, password: string) => {
    const user = await authService.login(email, password)
    setUser(user)
  }

  const logout = async () => {
    await authService.logout()
    setUser(null)
    router.push('/login')
  }

  return { user, loading, login, logout }
}
```

---

## 📡 API 整合

### API 客戶端設定

```typescript
// lib/api.ts
import axios from 'axios'

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL,
  timeout: 10000,
})

// Request Interceptor - 自動添加 Token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('access_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Response Interceptor - 自動刷新 Token
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      // Token 過期，嘗試刷新
      await authService.refreshToken()
      return api.request(error.config)
    }
    return Promise.reject(error)
  }
)

export default api
```

### 使用 React Query

```typescript
// hooks/useProfiles.ts
import { useQuery } from '@tanstack/react-query'

export function useProfiles(params?: ProfileSearchParams) {
  return useQuery({
    queryKey: ['profiles', params],
    queryFn: () => api.get('/profiles', { params }),
    staleTime: 5 * 60 * 1000, // 5 分鐘
  })
}

// 在組件中使用
const ProfileList = () => {
  const { data, isLoading, error } = useProfiles({ search: 'john' })

  if (isLoading) return <Loading />
  if (error) return <Error message={error.message} />

  return <ProfileCards profiles={data.data.profiles} />
}
```

---

## 🎯 主要頁面

### 首頁 (/)

- 業務員搜尋功能
- 熱門業務員推薦
- 系統功能介紹

### 業務員列表 (/profiles)

- 分頁顯示業務員列表
- 多條件篩選（產業、地區、公司）
- 關鍵字搜尋

### 業務員詳情 (/profiles/[id])

- 個人資料展示
- 工作經歷
- 證照資訊
- 公司資訊

### 個人儀表板 (/dashboard)

**業務員功能**:
- 查看/編輯個人檔案
- 管理公司資料
- 查看審核狀態

**管理員功能**:
- 查看待審核項目
- 審核/拒絕申請
- 系統統計數據

---

## 🎨 開發指南

### 組件開發規範

```typescript
// components/features/ProfileCard.tsx
import { Card, CardContent, CardHeader } from '@/components/ui/card'
import { Avatar } from '@/components/ui/avatar'
import type { SalespersonProfile } from '@/types/models'

interface ProfileCardProps {
  profile: SalespersonProfile
  onView?: (id: number) => void
}

export function ProfileCard({ profile, onView }: ProfileCardProps) {
  return (
    <Card>
      <CardHeader>
        <Avatar src={profile.avatar} alt={profile.full_name} />
        <h3>{profile.full_name}</h3>
      </CardHeader>
      <CardContent>
        <p>{profile.bio}</p>
        <Button onClick={() => onView?.(profile.id)}>查看詳情</Button>
      </CardContent>
    </Card>
  )
}
```

### 表單處理

```typescript
// 使用 react-hook-form + zod
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import * as z from 'zod'

const loginSchema = z.object({
  email: z.string().email('請輸入有效的電子郵件'),
  password: z.string().min(8, '密碼至少需要 8 個字符'),
})

type LoginFormData = z.infer<typeof loginSchema>

export function LoginForm() {
  const form = useForm<LoginFormData>({
    resolver: zodResolver(loginSchema),
  })

  const onSubmit = async (data: LoginFormData) => {
    await authService.login(data.email, data.password)
  }

  return <Form {...form} onSubmit={form.handleSubmit(onSubmit)} />
}
```

---

## 🧪 測試

### 單元測試 (開發中)

```bash
# 執行測試
npm run test

# 監聽模式
npm run test:watch

# 覆蓋率報告
npm run test:coverage
```

### E2E 測試 (開發中)

```bash
# 使用 Playwright
npm run test:e2e

# 開啟 UI 模式
npm run test:e2e:ui
```

---

## 🚢 部署

### Vercel 部署 (推薦)

```bash
# 安裝 Vercel CLI
npm i -g vercel

# 部署
vercel

# 生產部署
vercel --prod
```

### Docker 部署

```bash
# 建置 Docker 映像
docker build -t yamu-frontend .

# 執行容器
docker run -p 3000:3000 yamu-frontend
```

### 環境變數

**開發環境** (`.env.local`):
```env
NEXT_PUBLIC_API_URL=http://localhost:8080
NEXT_PUBLIC_APP_NAME=YAMU
```

**生產環境** (`.env.production`):
```env
NEXT_PUBLIC_API_URL=https://api.yourdomain.com
NEXT_PUBLIC_APP_NAME=YAMU
```

---

## 📈 效能優化

### 已實施的優化

- ✅ **圖片優化** - Next.js Image 組件
- ✅ **代碼分割** - Dynamic imports
- ✅ **字型優化** - next/font 自動優化
- ✅ **API 快取** - React Query 快取策略

### 效能檢查

```bash
# Lighthouse CI
npm run lighthouse

# Bundle 分析
npm run analyze
```

---

## 🐛 常見問題

### Q: API 請求失敗

**A**: 檢查 `.env.local` 中的 `NEXT_PUBLIC_API_URL` 設定是否正確。

### Q: Token 過期錯誤

**A**: 確認 Token 刷新邏輯正常運作，檢查 `lib/api.ts` 的 interceptor。

### Q: 樣式未生效

**A**: 清除 `.next` 快取並重新啟動開發伺服器：
```bash
rm -rf .next
npm run dev
```

### Q: TypeScript 錯誤

**A**: 重新產生型別定義：
```bash
npm run type-check
```

---

## 📚 相關文檔

- [Next.js Documentation](https://nextjs.org/docs)
- [React Documentation](https://react.dev)
- [shadcn/ui Documentation](https://ui.shadcn.com)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Backend API Documentation](../my_profile_laravel/README.md)

---

## 🤝 貢獻指南

1. Fork 專案
2. 建立功能分支 (`git checkout -b feature/amazing-feature`)
3. 提交變更 (`git commit -m 'feat: add amazing feature'`)
4. 推送到分支 (`git push origin feature/amazing-feature`)
5. 開啟 Pull Request

### Commit 規範

遵循 [Conventional Commits](https://www.conventionalcommits.org/):

```
feat: 新功能
fix: 修復 Bug
docs: 文檔更新
style: 代碼格式（不影響功能）
refactor: 重構
test: 測試相關
chore: 建置流程或輔助工具變動
```

---

## 📜 授權

此專案為個人作品集專案。

---

## 👤 維護者

**Kai Huang**
- GitHub: [@kai890707](https://github.com/kai890707)

---

**最後更新**: 2026-01-10 | **版本**: 0.1.0 (開發中)
