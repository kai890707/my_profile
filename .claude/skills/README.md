# Claude Code Skills 使用指南

**專案**: YAMU 業務員推廣系統
**更新日期**: 2026-01-20
**Skills 版本**: v1.0

---

## 📚 可用 Skills 總覽

本專案整合了 6 個專業 Skills，涵蓋前後端開發、測試和設計：

| Skill | 類型 | 用途 | 觸發時機 |
|-------|------|------|---------|
| **react-best-practices** | 前端優化 | React/Next.js 效能最佳實踐 | 撰寫/審查/重構 React 程式碼 |
| **webapp-testing** | 測試工具 | Playwright Web 應用測試 | 驗證前端功能、UI 調試 |
| **playwright-skill** | 自動化測試 | 完整瀏覽器自動化 | E2E 測試、表單測試、截圖 |
| **frontend-design** | UI/UX 設計 | 產品設計與使用者體驗 | 設計新介面、改善 UX |
| **php-pro** | 後端開發 | PHP 8.3+ 專家 | Laravel 開發、API 實作 |
| **artifacts-builder** | 複雜組件 | 多組件 HTML artifacts | 複雜的前端 artifacts |

---

## 🎯 Skills 整合策略

### Frontend 開發流程

```
需求分析 → UI/UX 設計 → 組件開發 → 效能優化 → 測試驗證
    ↓           ↓           ↓           ↓           ↓
 需求文檔   frontend-  React 實作   react-best-  playwright-
          design                   practices     skill
```

### Backend 開發流程

```
API 設計 → Laravel 實作 → 測試驗證 → 整合測試
   ↓           ↓            ↓           ↓
規格文檔    php-pro    PHPUnit Tests  webapp-testing
```

---

## 🔧 Skills 詳細說明

### 1. react-best-practices

**調用命令**: `/react-best-practices` (自動觸發)

**用途**: React 和 Next.js 效能優化指南

**使用時機**:
- ✅ 撰寫新的 React 組件或 Next.js 頁面
- ✅ 實作資料獲取 (client 或 server-side)
- ✅ 審查程式碼效能問題
- ✅ 重構現有 React/Next.js 程式碼
- ✅ 優化 bundle size 或載入時間

**主要規則類別** (45 條規則):
1. **Eliminating Waterfalls** (CRITICAL) - 消除瀑布流
2. **Bundle Size Optimization** (CRITICAL) - Bundle 大小優化
3. **Server-Side Performance** (HIGH) - 伺服器端效能
4. **Client-Side Data Fetching** (MEDIUM-HIGH) - 客戶端資料獲取
5. **Re-render Optimization** (MEDIUM) - 重新渲染優化
6. **Rendering Performance** (MEDIUM) - 渲染效能
7. **JavaScript Performance** (LOW-MEDIUM) - JavaScript 效能
8. **Advanced Patterns** (LOW) - 進階模式

**範例場景**:
```typescript
// 審查此程式碼時，react-best-practices 會自動觸發

// ❌ Bad: Barrel imports (bundle-barrel-imports)
import { Button, Input, Card } from '@/components';

// ✅ Good: Direct imports
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card } from '@/components/ui/card';

// ❌ Bad: Sequential awaits (async-defer-await)
async function getData() {
  const user = await fetchUser();
  const posts = await fetchPosts();
  return { user, posts };
}

// ✅ Good: Parallel execution (async-parallel)
async function getData() {
  const [user, posts] = await Promise.all([
    fetchUser(),
    fetchPosts()
  ]);
  return { user, posts };
}
```

**整合方式**:
- 在撰寫 React 組件時自動觸發
- 在程式碼審查時提供優化建議
- 在重構時檢查是否符合最佳實踐

---

### 2. webapp-testing

**調用命令**: `/webapp-testing`

**用途**: 使用 Playwright 測試本地 Web 應用

**使用時機**:
- ✅ 驗證前端功能正確性
- ✅ 調試 UI 行為問題
- ✅ 捕獲瀏覽器截圖
- ✅ 查看瀏覽器 console 日誌
- ✅ 測試多個伺服器環境

**核心工具**:
- `scripts/with_server.py` - 管理伺服器生命週期

**使用模式**: Reconnaissance-Then-Action
```python
# 1. 啟動並檢查頁面
page.goto('http://localhost:3001')
page.wait_for_load_state('networkidle')  # 關鍵：等待 JS 執行完成

# 2. 偵察渲染後的 DOM
page.screenshot(path='/tmp/inspect.png', full_page=True)
content = page.content()

# 3. 識別 selectors
buttons = page.locator('button').all()

# 4. 執行動作
page.click('button[type="submit"]')
```

**範例場景**:
```bash
# 單一伺服器測試
python scripts/with_server.py \
  --server "npm run dev" \
  --port 3001 \
  -- python test_frontend.py

# 多伺服器測試 (Backend + Frontend)
python scripts/with_server.py \
  --server "cd my_profile_laravel && php artisan serve --port=8080" --port 8080 \
  --server "cd frontend && npm run dev" --port 3001 \
  -- python test_integration.py
```

**整合方式**:
- 在完成前端開發後進行功能驗證
- 在 CI/CD 流程中執行自動化測試
- 在調試時快速檢查 UI 狀態

---

### 3. playwright-skill

**調用命令**: `/playwright-skill`

**用途**: 完整的瀏覽器自動化測試

**使用時機**:
- ✅ E2E 測試完整使用者流程
- ✅ 測試頁面載入和渲染
- ✅ 測試表單提交
- ✅ 檢查響應式設計 (Desktop/Tablet/Mobile)
- ✅ 測試登入流程和認證
- ✅ 檢查斷開的連結
- ✅ 驗證 UX 互動

**核心特性**:
- 自動偵測運行中的開發伺服器
- 測試腳本寫入 `/tmp` (自動清理)
- 預設使用可見瀏覽器 (除非指定 headless)
- URL 參數化設計

**執行流程**:
```bash
# Step 1: 自動偵測開發伺服器
cd .claude/skills/playwright-skill && \
  node -e "require('./lib/helpers').detectDevServers().then(s => console.log(JSON.stringify(s)))"
# 輸出: [{"name":"Frontend","port":3001,"url":"http://localhost:3001"}]

# Step 2: 撰寫測試腳本到 /tmp
# (Claude 自動生成)

# Step 3: 執行測試
cd .claude/skills/playwright-skill && node run.js /tmp/playwright-test-*.js
```

**常用測試模式**:

1. **測試頁面載入**:
```javascript
const { chromium } = require('playwright');
const TARGET_URL = 'http://localhost:3001';

(async () => {
  const browser = await chromium.launch({ headless: false });
  const page = await browser.newPage();
  await page.goto(TARGET_URL);
  console.log('頁面標題:', await page.title());
  await page.screenshot({ path: '/tmp/screenshot.png', fullPage: true });
  await browser.close();
})();
```

2. **測試登入流程**:
```javascript
await page.goto('http://localhost:3001/login');
await page.fill('input[name="email"]', 'test@example.com');
await page.fill('input[name="password"]', 'password123');
await page.click('button[type="submit"]');
await page.waitForURL('**/dashboard');
console.log('✅ 登入成功');
```

3. **響應式設計測試**:
```javascript
const viewports = [
  { name: 'Desktop', width: 1920, height: 1080 },
  { name: 'Tablet', width: 768, height: 1024 },
  { name: 'Mobile', width: 375, height: 667 }
];

for (const viewport of viewports) {
  await page.setViewportSize(viewport);
  await page.goto(TARGET_URL);
  await page.screenshot({ path: `/tmp/${viewport.name}.png`, fullPage: true });
}
```

**整合方式**:
- 在完成功能開發後執行 E2E 測試
- 在 PR 審查前驗證所有流程
- 在部署前進行冒煙測試

---

### 4. frontend-design

**調用命令**: `/frontend-design`

**用途**: 資深產品設計師，專精 UI/UX 設計

**使用時機**:
- ✅ 設計新的使用者介面
- ✅ 改善現有 UX 體驗
- ✅ 創建設計系統
- ✅ 解決商業問題的設計方案
- ✅ 制定互動設計和視覺設計

**設計流程**:
1. 需求分析 - 理解使用者需求和商業目標
2. 設計系統 - 定義色彩、字體、間距
3. 組件設計 - 設計 UI 組件和變體
4. 頁面設計 - 設計完整頁面佈局
5. 互動設計 - 定義使用者互動流程

**整合方式**:
- 在 `/implement-frontend` 的 Specification 階段自動調用
- 在設計新功能時主動調用

---

### 5. php-pro

**調用命令**: `/php-pro`

**用途**: PHP 8.3+ 專家，精通 Laravel 和現代 PHP 模式

**使用時機**:
- ✅ 開發 Laravel 應用
- ✅ 實作 API 端點
- ✅ 設計資料庫 Schema
- ✅ 效能優化
- ✅ 程式碼審查

**專長領域**:
- Laravel 11 最佳實踐
- Eloquent ORM 優化
- API Resource 設計
- 認證與授權
- 測試驅動開發

**整合方式**:
- 在處理 Laravel 程式碼時自動觸發
- 在程式碼審查時提供專業建議

---

### 6. artifacts-builder

**調用命令**: `/artifacts-builder`

**用途**: 創建複雜的多組件 HTML artifacts

**使用時機**:
- ✅ 需要複雜的前端 artifacts
- ✅ 需要 React、Tailwind CSS、shadcn/ui
- ✅ 需要狀態管理、路由
- ✅ 不適合簡單的單文件 HTML/JSX

**整合方式**:
- 在創建複雜組件時主動調用

---

## 🔄 Skills 工作流程整合

### 完整 Frontend 開發流程

```
1. 需求分析
   └─> /implement-frontend "新增評分功能"

2. UI/UX 設計 (自動)
   └─> frontend-design Agent 設計介面

3. 組件開發
   └─> 撰寫 React 組件
   └─> react-best-practices 自動審查優化

4. 功能測試
   └─> /webapp-testing 驗證功能
   └─> /playwright-skill E2E 測試

5. 效能優化
   └─> react-best-practices 檢查效能

6. 最終驗證
   └─> /playwright-skill 完整流程測試
```

### 完整 Backend 開發流程

```
1. 需求分析
   └─> /implement "新增評分 API"

2. API 設計 (自動)
   └─> software-architect Agent 設計架構

3. Laravel 實作
   └─> php-pro 自動觸發
   └─> 實作 Controller、Model、Tests

4. 測試驗證
   └─> PHPUnit 測試
   └─> PHPStan Level 9 檢查

5. 整合測試
   └─> /webapp-testing 前後端整合測試
```

---

## 📝 使用建議

### 主動觸發 Skills

某些情況下，建議主動觸發 Skills：

```bash
# 審查 React 程式碼效能
"請使用 react-best-practices skill 審查這個組件的效能"

# 測試前端功能
"請使用 playwright-skill 測試登入流程"

# 設計新介面
"請使用 frontend-design skill 設計評分介面"

# Laravel 程式碼審查
"請使用 php-pro skill 審查這個 Controller"
```

### 自動觸發時機

以下情況 Skills 會自動觸發：

- 撰寫 React 組件時 → `react-best-practices`
- 處理 Laravel 程式碼時 → `php-pro`
- 執行 `/implement-frontend` 時 → `frontend-design`
- 程式碼審查時 → 相關的專業 Skills

---

## 🎯 最佳實踐

1. **前端開發**:
   - 先用 `frontend-design` 設計 UI
   - 開發時讓 `react-best-practices` 自動審查
   - 完成後用 `playwright-skill` 測試

2. **後端開發**:
   - 讓 `php-pro` 協助 Laravel 開發
   - 用 PHPUnit 和 PHPStan 確保品質
   - 用 `webapp-testing` 進行整合測試

3. **全棧開發**:
   - 使用 `/implement` 開發 Backend
   - 使用 `/implement-frontend` 開發 Frontend
   - 使用相關 Skills 在開發過程中自動優化和測試

4. **測試驗證**:
   - 開發中: `webapp-testing` 快速驗證
   - 完成後: `playwright-skill` 完整測試
   - 部署前: 兩者結合進行冒煙測試

---

## 🔗 相關文檔

- **專案 CLAUDE.md**: `/Users/kai/KAA/my_profile/CLAUDE.md`
- **Frontend CLAUDE.md**: `/Users/kai/KAA/my_profile/frontend/CLAUDE.md`
- **Backend CLAUDE.md**: `/Users/kai/KAA/my_profile/my_profile_laravel/CLAUDE.md`
- **OpenSpec Commands**: `/Users/kai/KAA/my_profile/.claude/commands/README.md`

---

**最後更新**: 2026-01-20
**維護者**: Development Team
