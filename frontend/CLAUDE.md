# Frontend 開發規範 (Next.js + React)

**專案**: YAMU Frontend SPA
**框架**: Next.js 16.1.1 + TypeScript + React 19
**開發方法**: OpenSpec Specification-Driven Development (SDD)
**最後更新**: 2026-01-20

---

## 🚀 快速開始

### 使用 OpenSpec Commands 開發新功能

```bash
# 在專案根目錄執行
/implement-frontend [功能描述]
```

這會自動執行完整的 Frontend SDD 流程:
1. Create Proposal → 確認 UI/UX 需求
2. Write Specs → UI/UX + Components + Pages + API Integration + State & Routing
3. Break Down Tasks → 拆解 UI 開發任務
4. Validate → 驗證規格完整性
5. Implement → 實作組件和頁面
6. Archive → 歸檔到規範庫

**Commands 參考**: `../.claude/commands/README.md`

---

## 🎯 Frontend 專用 Skills

本專案整合了多個專業 Skills，在 Frontend 開發時會自動或手動觸發：

### 自動觸發的 Skills

這些 Skills 會在特定情境下**自動觸發**：

#### 1. react-best-practices (Vercel 最佳實踐)

**觸發時機**:
- 撰寫新的 React 組件或 Next.js 頁面
- 實作資料獲取 (client/server-side)
- 審查程式碼效能問題
- 重構現有 React/Next.js 程式碼
- 優化 bundle size 或載入時間

**核心規則** (45 條，8 個類別):
- **CRITICAL**: 消除瀑布流、Bundle 優化
- **HIGH**: Server-Side 效能
- **MEDIUM**: Re-render 優化、Rendering 效能
- **LOW**: JavaScript 效能、進階模式

**範例**:
```typescript
// ❌ Bad: Barrel imports
import { Button, Input } from '@/components';

// ✅ Good: Direct imports
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
```

#### 2. frontend-design (UI/UX 設計專家)

**觸發時機**:
- 執行 `/implement-frontend` 時自動調用
- 設計新的使用者介面
- 改善現有 UX 體驗

**設計流程**:
1. 需求分析 → 理解使用者需求
2. 設計系統 → 定義色彩、字體、間距
3. 組件設計 → 設計 UI 組件和變體
4. 頁面設計 → 設計完整頁面佈局
5. 互動設計 → 定義使用者互動流程

### 主動調用的 Skills

這些 Skills 需要**手動調用**：

#### 3. playwright-skill (瀏覽器自動化測試)

**使用方式**:
```
"請使用 playwright-skill 測試登入流程"
"請使用 playwright-skill 檢查響應式設計"
```

**功能**:
- E2E 測試完整使用者流程
- 測試表單提交和驗證
- 檢查響應式設計 (Desktop/Tablet/Mobile)
- 測試登入流程和認證
- 捕獲瀏覽器截圖
- 驗證 UX 互動

**自動偵測開發伺服器**:
```bash
# 自動偵測 http://localhost:3001
cd ../.claude/skills/playwright-skill && \
  node -e "require('./lib/helpers').detectDevServers()"
```

#### 4. webapp-testing (Playwright Web 測試工具)

**使用方式**:
```
"請使用 webapp-testing 驗證搜尋功能"
"請使用 webapp-testing 測試前後端整合"
```

**功能**:
- 驗證前端功能正確性
- 調試 UI 行為問題
- 查看瀏覽器 console 日誌
- 測試多伺服器環境 (Backend + Frontend)

**範例**:
```bash
# 測試 Backend + Frontend 整合
python scripts/with_server.py \
  --server "cd ../my_profile_laravel && php artisan serve --port=8080" --port 8080 \
  --server "npm run dev" --port 3001 \
  -- python test_integration.py
```

### Skills 使用建議

**開發階段**:
1. 設計 UI → `frontend-design` (自動)
2. 撰寫組件 → `react-best-practices` (自動檢查)
3. 實作功能 → 遵循最佳實踐
4. 測試功能 → `playwright-skill` (手動調用)

**程式碼審查**:
1. `react-best-practices` 自動檢查效能
2. 確認是否遵循 45 條規則
3. 優化 bundle size 和載入時間

**測試驗證**:
1. `webapp-testing` 快速驗證功能
2. `playwright-skill` 完整 E2E 測試
3. 響應式設計測試 (3 個 viewport)

**詳細文檔**: `../.claude/skills/README.md`

---

## 📁 專案結構

```
frontend/
├── app/                    # Next.js App Router
│   ├── (public)/          # 公開頁面
│   ├── (auth)/            # 認證頁面
│   ├── (dashboard)/       # 業務員 Dashboard
│   └── (admin)/           # 管理員後台
├── components/
│   ├── ui/                # 基礎 UI 組件
│   ├── layout/            # Layout 組件
│   └── features/          # 功能組件
├── lib/
│   ├── api/               # API 客戶端
│   ├── auth/              # 認證邏輯
│   ├── query/             # React Query 配置
│   └── utils/             # 工具函數
├── hooks/                 # Custom Hooks
├── types/                 # TypeScript 類型
├── store/                 # Zustand Store
├── docs/                  # 技術文檔 📚
│   ├── README.md          # 文檔索引
│   ├── design-system.md   # 設計系統規範
│   ├── testing.md         # 測試指南
│   └── performance.md     # 性能優化
├── reports/               # 開發報告 📊
│   ├── README.md          # 報告索引
│   ├── project-completion.md
│   ├── phase-7-summary.md
│   └── phase-8-completion.md
└── CLAUDE.md              # 本文件
```

---

## 🛠️ 技術棧

### Core
- **Framework**: Next.js 16.1.1 (App Router, Turbopack)
- **Language**: TypeScript 5
- **UI Library**: React 19

### UI & Styling
- **CSS Framework**: Tailwind CSS 3.4.1
- **Component Library**: Radix UI
- **Icons**: Lucide React
- **Charts**: Recharts 2.x

### State Management
- **Server State**: React Query 5.65.0
- **Client State**: Zustand 5.0.4

### Forms & Validation
- **Forms**: React Hook Form 7.54.2
- **Validation**: Zod 3.24.1

### HTTP
- **Client**: Axios 1.7.9

---

## 📊 系統規格

完整的 Frontend 規格請參考 OpenSpec 規範庫:

- **UI 組件規格**: `../openspec/specs/frontend/ui-components.md` (30+ 組件)
- **API 整合規格**: `../openspec/specs/frontend/api-integration.md` (31 個端點)
- **狀態管理規格**: `../openspec/specs/frontend/state-routing.md` (18 個路由)
- **規格總覽**: `../openspec/specs/frontend/README.md`

---

## 📚 核心文檔 (必讀)

### 技術文檔 (docs/)

1. **[文檔索引](./docs/README.md)** - 所有文檔的入口
2. **[設計系統](./docs/design-system.md)** - UI/UX 設計規範
   - 色彩系統
   - 字體系統
   - 間距與圓角
   - 組件設計原則
3. **[測試指南](./docs/testing.md)** - 完整測試策略
   - 單元測試 (Vitest)
   - 組件測試 (React Testing Library)
   - 響應式測試清單
   - 瀏覽器兼容性測試
4. **[性能優化](./docs/performance.md)** - 性能優化策略
   - React Query 配置
   - 圖片 Lazy Loading
   - 性能監控

### 開發報告 (reports/)

- **[報告索引](./reports/README.md)** - 所有報告的入口
- **[專案完成報告](./reports/project-completion.md)** - 整體完成狀態
- **[Phase 7 總結](./reports/phase-7-summary.md)** - Testing & Polish
- **[Phase 8 完成報告](./reports/phase-8-completion.md)** - Recharts 整合

---

## 🔧 開發流程

### 1. 環境設置

```bash
cd frontend

# 安裝依賴
npm install

# 啟動開發伺服器
npm run dev
# 訪問: http://localhost:3000

# TypeScript 檢查
npm run typecheck

# 執行測試
npm test

# 生產構建
npm run build
```

### 2. 開發新功能

**推薦方式** - 使用 OpenSpec Commands:

```bash
cd /path/to/project/root
/implement-frontend 新增評分 UI 組件
```

**手動方式** - 按步驟執行:

1. **建立變更提案**
   ```bash
   /proposal 新增評分 UI 組件
   ```

2. **撰寫詳細規格**
   ```bash
   /spec rating-ui
   ```
   產出: `openspec/changes/rating-ui/specs/`
   - `ui-ux.md` - UI/UX 設計規格
   - `components.md` - 組件規格
   - `pages.md` - 頁面規格
   - `api-integration.md` - API 整合
   - `state-routing.md` - 狀態管理與路由

3. **實作功能**
   ```bash
   /develop rating-ui
   ```

4. **歸檔規格**
   ```bash
   /archive rating-ui
   ```

---

## 📝 開發規範

### 組件設計原則

1. **單一職責** - 一個組件只做一件事
2. **可複用** - 組件應該是通用的
3. **類型安全** - 使用 TypeScript 嚴格模式
4. **Props 驗證** - 使用 Zod 驗證 Props

### 命名規範

1. **組件**: PascalCase (`Button.tsx`, `UserCard.tsx`)
2. **Hooks**: camelCase + `use` 前綴 (`useAuth.ts`, `useSearch.ts`)
3. **Utils**: camelCase (`formatDate.ts`, `cn.ts`)
4. **類型**: PascalCase + `Props`/`Data` 後綴 (`ButtonProps`, `UserData`)

### 目錄組織

```
components/
├── ui/                  # 基礎 UI 組件 (可複用)
│   ├── button.tsx
│   ├── input.tsx
│   └── card.tsx
├── layout/              # 布局組件
│   ├── header.tsx
│   └── footer.tsx
└── features/            # 功能組件 (業務邏輯)
    ├── search/
    └── dashboard/
```

### 設計系統參考

**完整規範**: [docs/design-system.md](./docs/design-system.md)

**快速參考**:
- **主色**: #0EA5E9 (Sky-500)
- **配色**: #14B8A6 (Teal-500)
- **圓角**: rounded-lg (16px) for cards
- **間距**: 4px 網格系統

---

## 🧪 測試策略

**完整指南**: [docs/testing.md](./docs/testing.md)

### 測試命令

```bash
# 運行所有測試
npm test

# 測試覆蓋率
npm run test:coverage

# 測試 UI
npm run test:ui
```

### 測試類型

1. **單元測試** (Vitest) - 70%
   - 測試 Hooks
   - 測試工具函數

2. **組件測試** (React Testing Library) - 20%
   - 測試 UI 組件
   - 測試用戶交互

3. **E2E 測試** (Playwright - 建議) - 10%
   - 測試完整流程
   - 測試關鍵路徑

### 測試覆蓋目標

- UI 組件: 80%+
- Custom Hooks: 90%+
- Utils: 90%+

---

## 🎨 UI/UX 開發

### 設計系統使用

參考: [docs/design-system.md](./docs/design-system.md)

**色彩使用**:
```tsx
// Primary
className="bg-primary-500 text-white"

// Success
className="bg-success-500 text-white"

// Error
className="bg-error-500 text-white"
```

**組件示例**:
```tsx
// Button
<Button variant="primary" size="lg">
  點擊我
</Button>

// Card
<Card>
  <CardHeader>標題</CardHeader>
  <CardContent>內容</CardContent>
</Card>
```

---

## 🔐 認證與路由

### 認證流程

- **登入** → 取得 Tokens → 儲存到 localStorage + Cookies
- **API 請求** → Axios 自動帶 Token
- **Token 過期** → 自動使用 Refresh Token 更新
- **未登入** → Middleware 重定向到 /login

### 路由守衛

位置: `middleware.ts`

- `/dashboard/*` → 需要 `salesperson` 角色
- `/admin/*` → 需要 `admin` 角色
- 未登入 → 重定向到 `/login`

---

## 📚 參考文檔

### 專案文檔
- [docs/README.md](./docs/README.md) - 技術文檔索引
- [reports/README.md](./reports/README.md) - 開發報告索引
- [../README.md](../README.md) - 專案總覽

### OpenSpec 規範
- [Frontend 規格總覽](../openspec/specs/frontend/README.md)
- [UI 組件規格](../openspec/specs/frontend/ui-components.md)
- [API 整合規格](../openspec/specs/frontend/api-integration.md)

### Commands 使用
- [Commands README](../.claude/commands/README.md)
- [工作流程圖](../.claude/commands/WORKFLOW.md)

---

## 🐛 常見問題

### Q: 如何新增頁面?

A:
1. 在 `app/` 目錄下建立路由檔案
2. 參考設計系統建立 UI
3. 使用 React Query 整合 API
4. 添加到路由規格 (`openspec/specs/frontend/state-routing.md`)

### Q: 如何整合新的 API?

A:
1. 在 `lib/api/` 新增 API 函數
2. 定義 TypeScript 類型
3. 建立 React Query Hook
4. 更新 API 整合規格

### Q: 如何處理表單?

A:
```tsx
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';

const schema = z.object({
  email: z.string().email(),
  password: z.string().min(6),
});

const { register, handleSubmit } = useForm({
  resolver: zodResolver(schema),
});
```

---

## ⚠️ 重要原則

### 規範驅動開發

❌ **禁止**:
- 未撰寫 UI 規格就開始寫組件
- 規格模糊就開始實作
- 實作過程中隨意偏離設計系統
- 忽略響應式設計

✅ **必須**:
- 先撰寫完整的 UI/UX 規格
- 遵循設計系統規範
- 組件要可複用
- 響應式設計 (Mobile/Tablet/Desktop)

### 代碼品質

❌ **禁止**:
- Any 類型 (使用 TypeScript strict mode)
- 內聯樣式 (使用 Tailwind)
- 硬編碼文字 (考慮國際化)
- 缺少錯誤處理

✅ **必須**:
- TypeScript 嚴格模式
- 組件要有 Props 類型
- 錯誤邊界 (Error Boundaries)
- Loading 狀態處理

---

## 🎯 開發檢查清單

開發新功能前檢查:
- [ ] UI/UX 規格已完整
- [ ] 組件規格已明確
- [ ] API 整合已定義
- [ ] 設計系統已參考

開發完成後檢查:
- [ ] TypeScript 無錯誤
- [ ] 測試已通過
- [ ] 響應式設計正常
- [ ] 無障礙性檢查
- [ ] 性能優化完成
- [ ] 規格已歸檔

---

**維護者**: Development Team
**最後更新**: 2026-01-09
**版本**: 1.0
