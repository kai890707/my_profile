# Frontend 規格總覽 - Avatar 功能優化

**功能**: Avatar 顯示、上傳與效能優化
**日期**: 2026-01-21
**版本**: 1.0
**設計師**: Product Design Team
**開發者**: Frontend Team

---

## 📚 規格文檔索引

### 1. [UI/UX 設計規格](./ui-ux.md) 🎨

**內容概要**:
- 設計目標與原則
- 使用者研究與痛點分析
- Avatar 顯示設計（6 種尺寸）
- 上傳流程設計（7 個步驟）
- 狀態設計（Loading/Error/Success/Empty）
- 響應式設計（Mobile/Tablet/Desktop）
- 無障礙設計（WCAG AA）
- 互動設計（Hover/Click/Drag）
- 視覺規範（色彩/陰影/動畫）

**關鍵決策**:
- ✅ 採用圓形 Avatar (`rounded-full`)
- ✅ 漸層色 Fallback (`primary-400 → secondary-400`)
- ✅ Lazy Loading 提升效能
- ✅ 即時預覽上傳結果
- ✅ 友善錯誤提示

**設計系統參考**: `frontend/docs/design-system.md`

---

### 2. [組件規格](./components.md) 🧩

**內容概要**:
- Avatar 組件優化
  - ✅ 新增 Lazy Loading 支援
  - ✅ 新增 Error Boundary
  - ✅ 新增 Loading State
  - ✅ onLoad / onError 回調
- AvatarUploader 組件（新增）
  - 檔案選擇與預覽
  - 即時壓縮與提示
  - 對比顯示（當前 vs 新）
- 工具函數
  - `getAvatarFallback()` - 姓名縮寫生成
  - `processImageUpload()` - 完整上傳流程
  - `formatFileSize()` - 檔案大小格式化

**組件架構**:
```
Avatar (優化版)
├── 圖片顯示 (Data URL / HTTP URL)
├── Fallback (姓名縮寫 / 預設圖示)
├── Lazy Loading (Intersection Observer)
├── Error Handling (自動顯示 Fallback)
└── Loading State (淡入動畫)

AvatarUploader (新增)
├── 檔案選擇 (File Input)
├── 即時預覽 (Base64)
├── 壓縮提示 (節省 XX%)
└── 錯誤處理 (友善訊息)
```

**實作位置**:
- `frontend/components/ui/avatar.tsx` (優化)
- `frontend/components/ui/avatar-uploader.tsx` (新增)
- `frontend/lib/utils/avatar.ts` (現有)
- `frontend/lib/utils/image.ts` (現有)

---

### 3. [頁面規格](./pages.md) 📄

**內容概要**:
- 搜尋列表頁 (`/search`)
  - 顯示 20 個 Avatar (md 尺寸)
  - Lazy Loading 優化
  - Skeleton Loading
  - 錯誤處理
- 業務員詳細頁 (`/salesperson/[id]`)
  - 主要 Avatar (2xl 尺寸)
  - 高優先級載入
  - 淡入動畫
- Dashboard Profile 頁 (`/dashboard`)
  - Avatar 上傳功能
  - 即時預覽
  - 相機按鈕互動
  - 編輯模式切換

**頁面流程**:
```
搜尋列表 (/search)
    ↓ (點擊卡片)
業務員詳情 (/salesperson/[id])
    ↓ (如果是自己)
Dashboard (/dashboard)
    ↓ (編輯 Avatar)
上傳 → 預覽 → 儲存
    ↓
跨頁面同步更新
```

**效能指標**:
- LCP < 2.5s
- FCP < 1.8s
- Avatar 載入 < 500ms (單張)
- 列表載入 < 2s (20 個)

---

### 4. [API 整合規格](./api-integration.md) 🔌

**內容概要**:
- API 端點與資料格式
  - `GET /api/salesperson/profile` - 取得個人檔案
  - `PUT /api/salesperson/profile` - 更新檔案（含 Avatar）
  - `GET /api/search/salespeople` - 搜尋列表
  - `GET /api/salesperson/{id}` - 業務員詳情
- React Query Hooks
  - `useProfile()` - Profile Query
  - `useUpdateProfile()` - Update Mutation
  - `useSalespersonDetail()` - 詳情 Query
  - `useSalespeople()` - 列表 Query
- 檔案處理流程
  - 驗證 → 壓縮 → Base64 轉換
  - `processImageUpload()` - 完整流程
  - `compressImage()` - Canvas 壓縮
  - `fileToBase64()` - FileReader 轉換
- 錯誤處理策略
  - 前端驗證錯誤
  - API 錯誤
  - 網路錯誤
- 快取策略
  - staleTime: 5 分鐘
  - cacheTime: 10 分鐘
  - Invalidation 策略
  - 樂觀更新

**資料流向**:
```
Frontend                 Backend                Database
────────                ────────               ────────
[選擇檔案]
    ↓
[驗證 + 壓縮]
    ↓
[Base64 轉換]
    ↓
PUT /api/.../profile ──→ [驗證 Base64] ──→ [儲存 DB]
(avatar: "data:...")     [檢查 MIME]        (avatar_data)
                         [檢查大小]         (avatar_mime)
    ↓                        ↓
[React Query] ←────────── [Response]
[Cache 更新]              {success, profile}
    ↓
[UI 更新]
```

---

### 5. [狀態管理與路由規格](./state-routing.md) 🗺️

**內容概要**:
- 狀態分層
  - 全域狀態 (React Query)
  - 頁面狀態 (useState)
  - 組件狀態 (useState)
- React Query 配置
  - Query Keys 定義
  - Cache 配置
  - Retry 策略
- 組件本地狀態
  - Dashboard: editMode, avatarPreview
  - Avatar: imageError, imageLoaded, isInView
  - AvatarUploader: preview, isProcessing, fileInfo
- 路由配置
  - `/search` - 公開頁面
  - `/salesperson/[id]` - 公開頁面
  - `/dashboard` - 需要認證 (Salesperson)
- 狀態同步策略
  - Cache Invalidation
  - 樂觀更新
  - 預載入

**狀態流向**:
```
使用者操作
    ↓
組件本地狀態 (useState)
    ↓
API 請求 (React Query Mutation)
    ↓
Server 處理
    ↓
Response 返回
    ↓
React Query Cache 更新 (自動)
    ↓
所有相關組件重渲染
    ↓
UI 更新完成
```

**跨頁面同步**:
```
Dashboard 更新 Avatar
    ↓
invalidateQueries([
  'salesperson/profile',
  'salespeople',
  'salesperson/[id]'
])
    ↓
所有頁面自動重新獲取資料
    ↓
Avatar 同步更新
```

---

## 🎯 核心功能總結

### 1. Avatar 顯示

**支援的來源**:
- ✅ Data URL (Base64)
- ✅ HTTP URL
- ✅ Fallback (姓名縮寫)
- ✅ 預設圖示

**6 種尺寸**:
- `xs`: 32px - 評論、標籤
- `sm`: 40px - 列表項目
- `md`: 48px - 搜尋結果 ⭐
- `lg`: 64px - 詳細頁面
- `xl`: 80px - 個人檔案
- `2xl`: 96px - Dashboard 編輯 ⭐

**效能優化**:
- ✅ Lazy Loading (Intersection Observer)
- ✅ Skeleton Loading
- ✅ 淡入動畫
- ✅ 錯誤自動 Fallback

### 2. Avatar 上傳

**完整流程**:
1. 點擊相機按鈕
2. 選擇檔案
3. 前端驗證（類型、大小）
4. 圖片壓縮（Canvas API）
5. Base64 轉換
6. 即時預覽
7. 儲存到 Backend
8. 跨頁面同步更新

**檔案處理**:
- ✅ 支援格式: JPG, PNG, WebP, GIF
- ✅ 最大大小: 2MB
- ✅ 自動壓縮: 到 2048×2048
- ✅ 品質調整: 0.8 → 0.5 (遞減)
- ✅ Base64 轉換: Data URL

**友善體驗**:
- ✅ 即時預覽
- ✅ 壓縮提示（節省 XX%）
- ✅ 對比顯示（當前 vs 新）
- ✅ 友善錯誤訊息
- ✅ 可重新選擇

### 3. 跨頁面同步

**同步機制**:
```
Dashboard 更新 Avatar
    ↓
React Query Cache Invalidation
    ↓
├─ Profile Query 重新獲取
├─ Search List Query 重新獲取
└─ Detail Query 重新獲取
    ↓
所有頁面的 Avatar 自動更新
```

**無需手動刷新頁面**

---

## 📊 效能目標

### Core Web Vitals

| 指標 | 目標值 | 頁面 |
|------|--------|------|
| **LCP** | < 2.5s | 所有頁面 |
| **FCP** | < 1.8s | 所有頁面 |
| **FID** | < 100ms | 所有頁面 |
| **CLS** | < 0.1 | 所有頁面 |

### Avatar 專屬指標

| 指標 | 目標值 | 場景 |
|------|--------|------|
| **單張 Avatar 載入** | < 500ms | 詳細頁面 |
| **20 個 Avatar 載入** | < 2s | 搜尋列表 |
| **上傳處理時間** | < 3s | Dashboard |
| **壓縮時間** | < 2s | 2MB 檔案 |

### 優化策略

1. **Lazy Loading** - 減少初始載入 60%
2. **圖片壓縮** - 減少檔案大小 70%
3. **React Query Cache** - 減少 API 請求 80%
4. **Skeleton Loading** - 感知載入更快 30%

---

## 🧪 測試計畫

### E2E 測試 (Playwright)

**測試案例**:
- ✅ 搜尋列表顯示 Avatar
- ✅ 詳細頁面顯示 Avatar
- ✅ Dashboard 上傳 Avatar
- ✅ 跨頁面同步驗證
- ✅ Lazy Loading 驗證
- ✅ 錯誤處理驗證

### Visual Regression 測試

**測試案例**:
- ✅ Avatar 各尺寸快照
- ✅ Fallback 顯示快照
- ✅ Loading 狀態快照
- ✅ Error 狀態快照

### 效能測試

**測試工具**:
- Lighthouse CI
- Chrome DevTools
- React DevTools Profiler

**測試案例**:
- ✅ LCP 測量
- ✅ FCP 測量
- ✅ Avatar 載入時間
- ✅ 大量 Avatar 渲染

---

## 🚀 實作優先順序

### Phase 1: Avatar 組件優化 (1 天)

- [ ] 新增 Lazy Loading 支援
- [ ] 新增 Error Boundary
- [ ] 新增 Loading State
- [ ] 新增 onLoad/onError 回調
- [ ] 測試各種尺寸和狀態

### Phase 2: 頁面整合 (1 天)

- [ ] 搜尋列表頁啟用 Lazy Loading
- [ ] 詳細頁面高優先級載入
- [ ] Dashboard 優化上傳流程
- [ ] Skeleton Loading 整合

### Phase 3: 效能優化 (1 天)

- [ ] 圖片壓縮優化
- [ ] React Query Cache 配置
- [ ] 預載入策略實作
- [ ] 效能測試驗證

### Phase 4: 測試與驗證 (1 天)

- [ ] E2E 測試撰寫
- [ ] Visual Regression 測試
- [ ] 跨頁面同步測試
- [ ] 效能測試報告

---

## 📋 驗收標準

### 功能驗收

- [ ] 所有頁面 Avatar 正確顯示
- [ ] Fallback 機制正常運作
- [ ] 上傳流程完整且友善
- [ ] 跨頁面同步更新
- [ ] 錯誤處理完善

### 效能驗收

- [ ] LCP < 2.5s
- [ ] FCP < 1.8s
- [ ] Avatar 載入 < 500ms (單張)
- [ ] 列表載入 < 2s (20 個)
- [ ] CLS < 0.1

### 無障礙驗收

- [ ] ARIA 屬性完整
- [ ] 鍵盤導航支援
- [ ] Screen Reader 友善
- [ ] 色彩對比達標 (≥ 4.5:1)
- [ ] Alt text 正確設定

### 測試驗收

- [ ] E2E 測試覆蓋率 ≥ 80%
- [ ] Visual Regression 測試建立
- [ ] 效能測試通過
- [ ] 跨瀏覽器測試通過

---

## 📚 參考資源

### 內部文檔

- [設計系統](../../../../frontend/docs/design-system.md)
- [測試指南](../../../../frontend/docs/testing.md)
- [效能優化](../../../../frontend/docs/performance.md)
- [Frontend README](../../../../frontend/CLAUDE.md)

### 現有實作

- Avatar 組件: `frontend/components/ui/avatar.tsx`
- Dashboard: `frontend/app/(dashboard)/dashboard/page.tsx`
- Search: `frontend/app/search/page.tsx`
- API Hooks: `frontend/hooks/useSalesperson.ts`
- Image Utils: `frontend/lib/utils/image.ts`

### 外部資源

- [React Query](https://tanstack.com/query/latest)
- [Next.js Image](https://nextjs.org/docs/app/building-your-application/optimizing/images)
- [Intersection Observer API](https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)

---

## 🤝 協作流程

### 設計師 → 開發者

1. 設計師完成 UI/UX 規格
2. 開發者審查規格可行性
3. 確認設計系統一致性
4. 開始實作

### 開發者 → QA

1. 開發者完成實作
2. Self-test 通過
3. 提交 QA 測試
4. QA 驗收

### QA → 上線

1. QA 測試通過
2. Code Review
3. Merge to main
4. 部署到 Production

---

## 📞 聯絡資訊

**設計團隊**: Product Design Team
**開發團隊**: Frontend Team
**QA 團隊**: QA Engineer

**問題回報**: GitHub Issues
**文檔更新**: 提交 PR

---

**版本**: 1.0
**建立日期**: 2026-01-21
**最後更新**: 2026-01-21
**維護者**: Frontend Team

**狀態**: ✅ 規格完成，等待實作
