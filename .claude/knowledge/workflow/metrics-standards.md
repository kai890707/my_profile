---
category: workflow
tags: [metrics, performance, quality, testing, standards]
priority: high
last_updated: 2026-01-13
applies_to: All Development
related_docs: [requirements-checklist.md, spec-validation.md, sdd-process.md]
---

# 量化指標標準

## Quick Reference

**核心目標**: 提供明確、可測量的完成標準，消除需求中的模糊性。

**關鍵指標類別**:
- API 效能: < 100ms (簡單查詢), < 200ms (列表), < 300ms (寫入)
- 前端效能: FCP < 1.8s, LCP < 2.5s, TTI < 3.8s
- 測試覆蓋: Backend >= 95% (Feature), >= 90% (Unit)
- 程式碼品質: Cyclomatic Complexity <= 10, Method Length <= 50 lines
- 可用性: WCAG AA, 色彩對比 >= 4.5:1

**使用時機**:
- Specification 階段: 定義效能要求
- Implementation 階段: 檢查指標達標
- Testing 階段: 驗證指標符合
- Review 階段: 評估品質

---

## 使用場景

### 適用於

**Specification 撰寫**:
- 需要明確效能目標
- 需要量化品質標準
- 需要測試覆蓋率目標

**Implementation 驗證**:
- 檢查是否符合效能標準
- 檢查程式碼品質
- 檢查測試覆蓋率

**Code Review**:
- 評估實作品質
- 檢查是否達標
- 提供改進建議

### 不適用於

- 探索性開發 (Prototype)
- 一次性腳本
- 臨時性修正

---

## 核心概念

### 為什麼需要量化指標?

**問題**: "效能要好"、"測試要充分"、"程式碼要乾淨" - 這些要求太模糊

**解決方案**: 定義明確的數字標準
- API 回應時間 < 200ms (不是"要快")
- 測試覆蓋率 >= 95% (不是"要充分")
- 圈複雜度 <= 10 (不是"要簡潔")

**好處**:
1. 需求明確，無爭議
2. 可自動化驗證
3. 可持續追蹤改善
4. 團隊標準一致

---

## 標準指標定義

### 1. API 效能標準

#### 1.1 回應時間 (Response Time)

**測量方式**: 從發送請求到收到完整回應的時間

**標準**:

| 類型 | P50 | P95 | P99 | 說明 |
|------|-----|-----|-----|------|
| 簡單查詢 GET | < 50ms | < 100ms | < 200ms | 單一資源查詢 (e.g., GET /api/users/123) |
| 列表查詢 GET | < 100ms | < 200ms | < 500ms | 分頁列表 (e.g., GET /api/users?page=1) |
| 複雜查詢 GET | < 150ms | < 300ms | < 1000ms | 多重關聯、聚合 (e.g., GET /api/stats) |
| 寫入操作 POST | < 150ms | < 300ms | < 500ms | 新增資源 (e.g., POST /api/users) |
| 更新操作 PUT | < 100ms | < 200ms | < 400ms | 更新資源 (e.g., PUT /api/users/123) |
| 刪除操作 DELETE | < 50ms | < 100ms | < 200ms | 刪除資源 (軟刪除) |
| 檔案上傳 | < 1s/MB | < 2s/MB | < 5s/MB | 檔案大小 <= 10MB |

**檢查方式**:
```bash
# Laravel Telescope
php artisan telescope:prune

# 或使用 Pest 測試
it('responds within acceptable time', function () {
    $start = microtime(true);
    $response = $this->getJson('/api/salespersons');
    $duration = (microtime(true) - $start) * 1000;

    expect($duration)->toBeLessThan(200); // < 200ms
    $response->assertOk();
});
```

#### 1.2 資料庫查詢效能

**標準**:

| 類型 | 查詢時間 | N+1 查詢 | 說明 |
|------|----------|----------|------|
| 簡單查詢 | < 10ms | 不允許 | SELECT with WHERE |
| JOIN 查詢 | < 50ms | 必須使用 Eager Loading | 使用 with() |
| 聚合查詢 | < 100ms | - | COUNT, SUM, AVG |
| 全文搜尋 | < 200ms | 必須有索引 | LIKE, FULLTEXT |

**檢查方式**:
```php
// Laravel Debugbar 檢查
DB::enableQueryLog();
$salespersons = Salesperson::with('user', 'company')->get();
$queries = DB::getQueryLog();

// 確認查詢數量 (應該是 1 個，不是 N+1)
expect($queries)->toHaveCount(1);
```

#### 1.3 並發處理能力

**標準**:

| 指標 | 目標值 | 說明 |
|------|--------|------|
| 並發請求數 | >= 100 req/s | 同時處理的請求數 |
| 平均回應時間 | < 200ms | 在負載下的平均回應 |
| 錯誤率 | < 0.1% | 5xx 錯誤比例 |
| 資料庫連線池 | 10-50 | 根據負載調整 |

---

### 2. Frontend 效能標準

#### 2.1 Core Web Vitals

**標準** (依據 Google Lighthouse):

| 指標 | Good | Needs Improvement | Poor | 說明 |
|------|------|-------------------|------|------|
| FCP (First Contentful Paint) | < 1.8s | 1.8s - 3.0s | > 3.0s | 首次內容繪製 |
| LCP (Largest Contentful Paint) | < 2.5s | 2.5s - 4.0s | > 4.0s | 最大內容繪製 |
| TTI (Time to Interactive) | < 3.8s | 3.8s - 7.3s | > 7.3s | 可互動時間 |
| TBT (Total Blocking Time) | < 200ms | 200ms - 600ms | > 600ms | 總阻塞時間 |
| CLS (Cumulative Layout Shift) | < 0.1 | 0.1 - 0.25 | > 0.25 | 累計版面位移 |

**檢查方式**:
```bash
# Lighthouse CI
npm run lighthouse

# Playwright 效能測試
npx playwright test --grep @performance
```

#### 2.2 Bundle Size

**標準**:

| 類型 | 最大值 | 說明 |
|------|--------|------|
| Initial JS Bundle | < 200KB (gzip) | 首次載入的 JS |
| Initial CSS | < 50KB (gzip) | 首次載入的 CSS |
| 單頁面 JS Chunk | < 100KB (gzip) | 動態載入的頁面 |
| 第三方庫總和 | < 300KB (gzip) | npm packages |
| 圖片 (單張) | < 100KB | 使用 WebP/AVIF |

**檢查方式**:
```bash
# Next.js Bundle Analyzer
npm run analyze

# 檢查輸出
# ✓ First Load JS shared by all    85.2 kB
# ✓ chunks/pages/_app              12.3 kB
```

#### 2.3 Runtime 效能

**標準**:

| 指標 | 目標值 | 說明 |
|------|--------|------|
| 元件渲染時間 | < 16ms | 60 FPS (1000ms/60) |
| 列表渲染 (100 項) | < 100ms | 使用虛擬化 |
| 表單驗證 | < 50ms | Debounce 300ms |
| API 請求 (含 loading) | < 300ms | React Query |
| 頁面路由切換 | < 200ms | App Router |

---

### 3. 測試覆蓋率標準

#### 3.1 Backend 測試覆蓋

**標準**:

| 類型 | 覆蓋率目標 | 檢查項目 |
|------|-----------|----------|
| Feature Tests | >= 95% | API 端點、業務流程 |
| Unit Tests | >= 90% | Service、Repository、Helper |
| Integration Tests | >= 80% | 多模組協作 |
| Line Coverage | >= 85% | 程式碼行覆蓋 |
| Branch Coverage | >= 80% | 分支覆蓋 |

**檢查方式**:
```bash
# Pest 測試覆蓋率
composer test:coverage

# 輸出範例:
# Feature Tests .... 97.5% (195/200 lines)
# Unit Tests ....... 92.3% (450/487 lines)
# Total ............ 89.1% (645/724 lines)
```

**必須測試的項目**:
- [ ] 所有 API 端點 (Happy Path + Error Cases)
- [ ] 所有 Service 方法
- [ ] 資料驗證規則
- [ ] 權限檢查
- [ ] 邊界條件
- [ ] 資料庫交易回滾

#### 3.2 Frontend 測試覆蓋

**標準**:

| 類型 | 覆蓋率目標 | 檢查項目 |
|------|-----------|----------|
| Component Tests | >= 80% | UI 組件、互動 |
| Integration Tests | >= 70% | API 整合、狀態管理 |
| E2E Tests | 關鍵流程 100% | 登入、CRUD、搜尋 |
| Line Coverage | >= 75% | 程式碼行覆蓋 |

**檢查方式**:
```bash
# Vitest 測試覆蓋率
npm run test:coverage

# Playwright E2E
npx playwright test
```

**必須測試的項目**:
- [ ] 所有 UI 組件渲染
- [ ] 使用者互動 (Click, Input, Submit)
- [ ] 表單驗證
- [ ] Loading / Error 狀態
- [ ] 關鍵使用者流程 (E2E)

---

### 4. 程式碼品質標準

#### 4.1 複雜度指標

**標準**:

| 指標 | 目標值 | 說明 |
|------|--------|------|
| Cyclomatic Complexity | <= 10 | 單一方法的複雜度 |
| Cognitive Complexity | <= 15 | 理解程式碼的難度 |
| Method Length | <= 50 lines | 單一方法的行數 |
| Class Length | <= 300 lines | 單一類別的行數 |
| Parameters Count | <= 5 | 方法參數數量 |
| Nesting Level | <= 3 | 巢狀深度 |

**檢查方式**:

Backend (PHPStan):
```bash
composer analyse

# Level 9 檢查
# ✓ No errors found
```

Frontend (ESLint):
```bash
npm run lint

# complexity: ["error", 10]
# max-lines-per-function: ["error", 50]
```

#### 4.2 Type Safety

**Backend (PHP)**:
- [ ] PHPStan Level 9 通過
- [ ] 所有方法有型別宣告
- [ ] 所有屬性有型別宣告
- [ ] 避免使用 mixed type

**Frontend (TypeScript)**:
- [ ] strict mode 啟用
- [ ] noImplicitAny: true
- [ ] strictNullChecks: true
- [ ] 避免使用 any type (< 5%)

**檢查方式**:
```bash
# TypeScript 檢查
npm run typecheck

# 輸出應該是:
# ✓ No type errors found
```

#### 4.3 程式碼重複

**標準**:

| 指標 | 目標值 | 說明 |
|------|--------|------|
| Duplication | < 3% | 重複程式碼比例 |
| Similar Code Blocks | < 5 行 | 相似程式碼段 |

**原則**:
- 重複 3 次以上 → 抽取成函式
- 相似邏輯 → 抽取成共用模組

---

### 5. 可用性標準

#### 5.1 可訪問性 (Accessibility)

**標準**: WCAG 2.1 Level AA

| 指標 | 要求 | 檢查方式 |
|------|------|----------|
| 色彩對比 | >= 4.5:1 (一般文字) | Chrome DevTools |
| 大字色彩對比 | >= 3:1 (18pt+) | Chrome DevTools |
| 鍵盤導航 | 100% 可操作 | Tab 測試 |
| Screen Reader | 完整支援 | NVDA/VoiceOver |
| ARIA 標籤 | 必要元素有標籤 | axe DevTools |
| 焦點可見性 | 明確的焦點樣式 | 視覺檢查 |

**檢查方式**:
```bash
# Playwright Accessibility 測試
npx playwright test --grep @a11y

# 使用 axe-core
it('should have no accessibility violations', async () => {
  const results = await axe(page);
  expect(results.violations).toHaveLength(0);
});
```

#### 5.2 響應式設計

**標準**:

| 裝置 | 最小寬度 | 最大寬度 | 測試瀏覽器 |
|------|----------|----------|------------|
| Mobile | 320px | 767px | Chrome Mobile |
| Tablet | 768px | 1023px | iPad |
| Desktop | 1024px | 1920px+ | Chrome/Firefox/Safari |

**檢查項目**:
- [ ] 所有頁面在 3 種尺寸下可用
- [ ] 文字可讀 (最小 14px on mobile)
- [ ] 按鈕可點擊 (最小 44x44px)
- [ ] 不需橫向滾動 (overflow-x: hidden)

---

### 6. 安全性標準

#### 6.1 輸入驗證

**標準**:
- [ ] 所有使用者輸入必須驗證
- [ ] SQL Injection 防護 (使用 ORM/Prepared Statements)
- [ ] XSS 防護 (HTML Escape)
- [ ] CSRF Token 驗證

**檢查方式**:
```php
// Laravel FormRequest 驗證
public function rules(): array
{
    return [
        'email' => ['required', 'email', 'max:255'],
        'name' => ['required', 'string', 'max:100'],
    ];
}
```

#### 6.2 認證與授權

**標準**:
- [ ] JWT Token 過期時間 <= 1 hour (Access Token)
- [ ] Refresh Token 過期時間 <= 7 days
- [ ] 密碼強度 >= 8 字元 (含大小寫、數字)
- [ ] 登入失敗鎖定 (5 次失敗 → 15 分鐘鎖定)

---

## 實際應用範例

### 範例 1: API 效能需求

**需求**: "業務員列表 API 要快"

**量化後**:
```markdown
### API 效能要求

GET /api/v1/salespersons

**效能標準**:
- P50 回應時間: < 100ms
- P95 回應時間: < 200ms
- P99 回應時間: < 500ms
- 並發處理: >= 100 req/s
- 資料庫查詢: <= 2 次 (使用 Eager Loading)

**測試方式**:
- 使用 Pest 測試回應時間
- 使用 Laravel Debugbar 檢查查詢數量
- 使用 Apache Bench 測試並發
```

### 範例 2: Frontend 效能需求

**需求**: "搜尋頁面要流暢"

**量化後**:
```markdown
### 前端效能要求

業務員搜尋頁面 (/salespersons)

**Core Web Vitals**:
- LCP: < 2.5s (Largest Contentful Paint)
- FID: < 100ms (First Input Delay)
- CLS: < 0.1 (Cumulative Layout Shift)

**互動效能**:
- 搜尋輸入 Debounce: 300ms
- 搜尋結果渲染: < 100ms (100 項)
- 分頁切換: < 200ms

**Bundle Size**:
- Initial Load: < 200KB (gzip)
- Page Chunk: < 50KB (gzip)

**測試方式**:
- 使用 Lighthouse 檢查 Core Web Vitals
- 使用 Playwright 測試互動效能
- 使用 webpack-bundle-analyzer 檢查 Bundle Size
```

### 範例 3: 測試覆蓋率需求

**需求**: "要有足夠的測試"

**量化後**:
```markdown
### 測試覆蓋率要求

**Backend**:
- Feature Tests: >= 95% (所有 API 端點)
- Unit Tests: >= 90% (Service + Repository)
- Line Coverage: >= 85%
- Branch Coverage: >= 80%

**Frontend**:
- Component Tests: >= 80%
- E2E Tests: 100% (關鍵流程)
  - 登入流程
  - 業務員搜尋與篩選
  - 業務員資料 CRUD
- Line Coverage: >= 75%

**測試方式**:
- Backend: `composer test:coverage`
- Frontend: `npm run test:coverage`
- E2E: `npx playwright test`

**不通過標準**: 任一指標未達標即為不通過
```

---

## 驗證 Checklist

### Specification 階段

**效能需求**:
- [ ] 定義明確的回應時間標準 (P50/P95/P99)
- [ ] 定義資料庫查詢效能要求
- [ ] 定義並發處理能力要求
- [ ] 定義前端 Core Web Vitals 目標

**測試需求**:
- [ ] 定義測試覆蓋率目標 (Backend >= 95%, Frontend >= 80%)
- [ ] 列出必須測試的項目
- [ ] 定義測試驗證方式

**品質需求**:
- [ ] 定義程式碼複雜度限制 (Complexity <= 10)
- [ ] 定義 Type Safety 要求 (PHPStan Level 9, TS Strict)
- [ ] 定義可訪問性標準 (WCAG AA)

### Implementation 階段

**效能檢查**:
- [ ] API 回應時間符合標準 (使用 Telescope)
- [ ] 資料庫查詢效能符合標準 (使用 Debugbar)
- [ ] 前端效能符合標準 (使用 Lighthouse)
- [ ] Bundle Size 符合限制

**測試檢查**:
- [ ] 測試覆蓋率達標
- [ ] 所有關鍵流程有 E2E 測試
- [ ] 所有 API 端點有測試

**品質檢查**:
- [ ] PHPStan Level 9 通過
- [ ] ESLint 無錯誤
- [ ] TypeScript 無型別錯誤
- [ ] 無程式碼重複 (< 3%)

### Review 階段

**指標報告**:
- [ ] 產生效能測試報告
- [ ] 產生測試覆蓋率報告
- [ ] 產生程式碼品質報告
- [ ] 所有指標達標

---

## 常見問題

### Q1: 所有功能都要符合這些標準嗎?

**A**: 不一定。標準可根據功能類型調整:

**關鍵功能** (登入、搜尋、CRUD):
- 嚴格遵守所有標準
- 測試覆蓋率 >= 95%
- 效能要求嚴格

**次要功能** (統計、報表):
- 可放寬效能要求 (回應時間 +50%)
- 測試覆蓋率 >= 80%

**內部工具**:
- 以功能性為主
- 測試覆蓋率 >= 70%

### Q2: 如果無法達標怎麼辦?

**A**: 有三種方式:

1. **技術優化**: 改善實作方式
2. **調整標準**: 與團隊討論是否合理
3. **分階段**: 先達到最低標準，後續優化

**重要**: 不達標需要記錄原因和改善計畫

### Q3: 如何持續追蹤這些指標?

**A**: 使用自動化工具:

**CI/CD 整合**:
```yaml
# .github/workflows/quality.yml
- name: Test Coverage
  run: composer test:coverage
- name: PHPStan Analysis
  run: composer analyse
- name: Lighthouse CI
  run: npm run lighthouse
```

**監控 Dashboard**:
- Laravel Telescope (API 效能)
- Sentry (錯誤追蹤)
- Google Analytics (Core Web Vitals)

---

## 最佳實踐

### 1. 在 Specification 階段定義

不要等到 Implementation 才討論效能和品質要求。

### 2. 自動化驗證

所有可量化的指標都應該有自動化檢查。

### 3. 持續追蹤

定期檢視指標趨勢，而不是只在發布前檢查。

### 4. 團隊共識

標準應該是團隊共同制定和認可的。

### 5. 記錄例外

無法達標時，記錄原因和後續計畫。

---

## 相關知識

- [需求分析 Checklist](./requirements-checklist.md) - 如何收集量化需求
- [規格驗證 Checklist](./spec-validation.md) - 如何驗證規格完整性
- [SDD 流程](./sdd-process.md) - 在開發流程中應用指標
- [Backend 測試](../backend/testing.md) - Backend 測試策略
- [Frontend 測試](../frontend/testing.md) - Frontend 測試策略

---

## 指標模板

### API 效能模板

```markdown
### API 效能要求

{METHOD} {ENDPOINT}

**回應時間**:
- P50: < {X}ms
- P95: < {Y}ms
- P99: < {Z}ms

**資料庫查詢**:
- 查詢數量: <= {N} 次
- 查詢時間: < {X}ms

**並發處理**:
- 並發請求數: >= {N} req/s
- 錯誤率: < {X}%

**測試方式**: {描述測試方法}
```

### Frontend 效能模板

```markdown
### 前端效能要求

頁面: {PAGE_NAME}

**Core Web Vitals**:
- LCP: < 2.5s
- FID: < 100ms
- CLS: < 0.1

**Bundle Size**:
- Initial Load: < {X}KB (gzip)
- Page Chunk: < {Y}KB (gzip)

**測試方式**: Lighthouse CI
```

### 測試覆蓋率模板

```markdown
### 測試覆蓋率要求

**Backend**:
- Feature Tests: >= 95%
- Unit Tests: >= 90%
- Line Coverage: >= 85%

**Frontend**:
- Component Tests: >= 80%
- E2E Tests: 100% (關鍵流程)

**必測項目**:
- [ ] {項目 1}
- [ ] {項目 2}

**測試方式**: {描述測試方法}
```

---

**維護者**: Development Team
**最後更新**: 2026-01-13
**版本**: 1.0
