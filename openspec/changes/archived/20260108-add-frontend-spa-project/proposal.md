# Proposal: 前端 SPA 應用程式 (Next.js)

**Status**: Draft
**Created**: 2026-01-08
**Priority**: High

---

## Why (問題陳述)

**目前的問題**:
- 現有系統只有靜態 HTML 展示頁面 (index.html, portfolio.html),缺乏互動功能
- 後端 API 已完成 (31 個端點,包含認證、搜尋、業務員管理、管理員後台),但缺少對應的前端介面
- 使用者無法透過友善的 UI 使用系統核心功能 (業務員搜尋、個人資料管理、審核流程等)
- 管理員需要透過 API 工具 (如 Swagger) 才能執行審核和系統管理,效率低且不直觀

**影響範圍**:
- 影響的用戶:
  - 一般訪客 (業務員搜尋與瀏覽)
  - 業務員 (註冊、登入、個人資料管理)
  - 管理員 (審核、統計、系統設定)
- 影響的功能:
  - 整個系統的使用者體驗
  - 業務價值實現 (搜尋引擎優化、用戶轉換率)

---

## What (解決方案)

**功能概述**:
建立完整的前端 SPA 應用程式,使用 Next.js (React SSR) 整合現有 Backend API (31 個端點),提供業務員搜尋、個人檔案管理、管理員審核介面及統計報表等完整功能。

**核心價值**:
- **用戶體驗**: 提供直觀、流暢的 Web 介面,降低系統使用門檻
- **SEO 優化**: 使用 Next.js SSR,讓業務員資料可被搜尋引擎索引,提升曝光度
- **功能完整性**: 完整實現後端 API 的所有功能,發揮系統最大價值
- **角色區分**: 清楚區分訪客、業務員、管理員三種角色的介面和權限
- **可維護性**: 採用現代化技術棧,便於未來功能擴充和維護

**主要功能**:

### 1. 公開區域 (訪客可用)
- **首頁**: 系統介紹、搜尋入口、熱門業務員推薦
- **業務員搜尋**:
  - 關鍵字搜尋 (姓名、簡介、公司)
  - 進階篩選 (產業類別、服務地區、公司)
  - 分頁顯示搜尋結果
  - 搜尋結果排序 (依相關度、註冊時間等)
- **業務員詳細資料頁面**:
  - 個人基本資訊 (頭像、姓名、公司、職位)
  - 專長與簡介
  - 工作經驗列表
  - 專業證照列表 (已審核通過)
  - 聯絡資訊

### 2. 認證系統
- **註冊**: 業務員註冊表單 (username, email, password, full_name, phone)
- **登入**: 使用 email/password 登入,取得 JWT Token
- **登出**: 清除 Token 和會話狀態
- **Token 管理**:
  - Access Token (1 小時) 自動續期機制
  - Refresh Token (7 天) 管理
  - Token 過期自動導向登入頁

### 3. 業務員個人區域 (需登入)
- **個人檔案管理**:
  - 查看與編輯基本資料 (full_name, phone, bio, specialties)
  - 上傳頭像 (Base64 編碼)
  - 選擇服務地區 (多選)
  - 儲存公司資訊 (等待審核)
- **工作經驗管理**:
  - 新增工作經驗 (company, title, start_date, end_date, description)
  - 查看經驗清單
  - 刪除經驗記錄
- **專業證照管理**:
  - 上傳證照 (name, issuer, issue_date, description, 圖片 Base64)
  - 查看證照清單與審核狀態
- **審核狀態查詢**:
  - 查看個人資料、公司資訊、證照的審核狀態
  - 顯示審核歷史記錄

### 4. 管理員後台 (需 admin 角色)
- **審核管理**:
  - 待審核項目總覽 (業務員註冊、公司資訊、證照)
  - 審核詳細資訊查看
  - 審核通過/拒絕操作 (拒絕需填寫原因)
  - 審核歷史記錄
- **使用者管理**:
  - 使用者清單 (可依角色、狀態篩選)
  - 更新使用者狀態 (active/inactive)
  - 刪除使用者
- **系統設定**:
  - 產業類別管理 (新增、查看)
  - 地區管理 (新增、查看)
- **統計報表**:
  - 業務員統計 (總數、活躍、待審核)
  - 公司統計
  - 待審核項目統計
  - 資料視覺化 (圖表)

### 5. 全站功能
- **導航列**: 根據登入狀態和角色顯示不同選單
- **響應式設計**: 支援桌面、平板、手機螢幕
- **錯誤處理**: 統一的錯誤提示機制
- **載入狀態**: API 請求時的 Loading 指示
- **表單驗證**: 前端即時驗證,提升使用者體驗

---

## Scope (範圍)

### In Scope (本次實作)

**前端技術棧**:
- ✅ Next.js 14+ (App Router)
- ✅ React 18+
- ✅ TypeScript
- ✅ Tailwind CSS (樣式框架)
- ✅ Shadcn/ui (UI 組件庫)
- ✅ Axios (HTTP 客戶端)
- ✅ React Hook Form (表單管理)
- ✅ Zod (表單驗證)
- ✅ Zustand 或 React Context (狀態管理)
- ✅ Recharts 或 Chart.js (圖表)

**專案結構**:
- ✅ 獨立前端專案目錄 `frontend/`
- ✅ 與後端 API 分離部署
- ✅ 環境變數配置 (.env.local)
- ✅ ESLint + Prettier 程式碼規範

**核心功能**:
- ✅ 整合所有 31 個後端 API 端點
- ✅ JWT 認證流程 (登入、登出、Token 續期)
- ✅ 角色權限控制 (Route Guards)
- ✅ 業務員搜尋與詳細資料頁面
- ✅ 業務員個人資料完整管理 (CRUD)
- ✅ 管理員審核介面
- ✅ 管理員統計報表
- ✅ 響應式設計 (RWD)
- ✅ 基本 SEO 優化 (meta tags, sitemap)
- ✅ 錯誤處理與 Loading 狀態
- ✅ 表單驗證 (前端 + 後端)

**資料處理**:
- ✅ 圖片 Base64 編碼上傳 (頭像、證照)
- ✅ JSON 資料序列化/反序列化
- ✅ 分頁處理 (搜尋結果)
- ✅ 日期格式化顯示

**部署相關**:
- ✅ 開發環境配置 (npm run dev)
- ✅ 生產環境建置 (npm run build)
- ✅ Docker 部署配置 (Dockerfile, docker-compose.yml)

### Out of Scope (未來擴充)

**進階功能** (Phase 2):
- ❌ 評分與評論系統 - 需額外 API 開發
- ❌ 即時通訊功能 - 需 WebSocket 支援
- ❌ 推薦演算法 - 需額外業務邏輯
- ❌ 多語言支援 (i18n) - 目前僅支援繁體中文
- ❌ 暗黑模式 - UI/UX 優化項目

**進階 SEO** (Phase 2):
- ❌ 結構化資料 (Schema.org)
- ❌ Open Graph 完整優化
- ❌ 進階 Analytics 整合

**進階安全** (Phase 2):
- ❌ CAPTCHA 驗證 - 防機器人註冊
- ❌ 雙因素認證 (2FA) - 額外安全層級
- ❌ OAuth 第三方登入 - 需額外整合

**進階 UI/UX** (Phase 2):
- ❌ 動畫與過場效果 - 體驗優化
- ❌ 骨架屏 (Skeleton Screen) - Loading 優化
- ❌ PWA 功能 - 離線支援

**測試** (Phase 2):
- ❌ E2E 測試 (Playwright/Cypress) - 時間限制
- ❌ 單元測試 (Jest/Vitest) - 時間限制
- ❌ 整合測試 - 時間限制

**原因說明**:
- 本次聚焦於**核心功能實現**,確保系統可用性
- Out of Scope 項目屬於**體驗優化**或**額外安全層級**,不影響基本功能
- 採用**漸進式開發**策略,先求有再求好

---

## Success Criteria (驗收標準)

### 功能性驗收

**認證系統**:
- [ ] 業務員可成功註冊,API 回傳 201 Created,並自動建立個人檔案
- [ ] 使用者可使用 email/password 登入,取得 Access Token 和 Refresh Token
- [ ] Token 過期時自動使用 Refresh Token 續期,無需重新登入
- [ ] 登出後 Token 被清除,無法存取需認證的頁面

**業務員搜尋**:
- [ ] 訪客可無需登入即可搜尋業務員
- [ ] 關鍵字搜尋能正確搜尋姓名、簡介、公司名稱
- [ ] 篩選條件 (產業、地區、公司) 能正確過濾結果
- [ ] 搜尋結果支援分頁,每頁顯示 10-20 筆
- [ ] 點擊業務員卡片能導向詳細資料頁面

**業務員個人管理**:
- [ ] 登入後的業務員可查看和編輯個人資料
- [ ] 上傳頭像成功,頭像正確顯示在個人頁面
- [ ] 新增工作經驗成功,清單正確顯示
- [ ] 刪除工作經驗成功,清單即時更新
- [ ] 上傳證照成功,審核狀態顯示為 "pending"

**管理員審核**:
- [ ] 管理員登入後可看到待審核項目總覽
- [ ] 點擊待審核項目能查看詳細資訊
- [ ] 審核通過後,項目狀態變更為 "approved"
- [ ] 審核拒絕後,項目狀態變更為 "rejected",並記錄拒絕原因

**管理員統計**:
- [ ] 統計頁面正確顯示業務員總數、活躍數、待審核數
- [ ] 圖表正確呈現統計資料
- [ ] 統計資料即時更新

**權限控制**:
- [ ] 未登入訪客嘗試存取業務員個人頁面時,自動導向登入頁
- [ ] 業務員角色嘗試存取管理員頁面時,顯示 403 Forbidden
- [ ] 管理員角色可正常存取所有管理功能

### 非功能性驗收

**效能**:
- [ ] 首頁載入時間 < 3 秒 (開發環境)
- [ ] API 請求回應時間 < 1 秒 (正常網路)
- [ ] 頁面切換流暢,無明顯延遲

**安全**:
- [ ] 所有表單輸入都有前端驗證
- [ ] Token 儲存在 httpOnly Cookie 或安全的 localStorage
- [ ] 敏感資料 (密碼) 不在前端明文顯示
- [ ] API 錯誤不洩露系統內部資訊

**相容性**:
- [ ] 支援 Chrome、Firefox、Safari、Edge 最新版本
- [ ] 響應式設計在桌面 (1920x1080)、平板 (768x1024)、手機 (375x667) 正常顯示
- [ ] 圖片在不同裝置上正確縮放

**可用性**:
- [ ] 所有頁面有清楚的導航指示
- [ ] 錯誤訊息清楚易懂,提供解決建議
- [ ] Loading 狀態明確,使用者知道系統正在處理
- [ ] 表單驗證錯誤即時顯示,指出具體欄位

**程式碼品質**:
- [ ] TypeScript 無型別錯誤
- [ ] ESLint 無錯誤,Warning < 5 個
- [ ] 程式碼遵循 Airbnb Style Guide
- [ ] 元件可複用,避免重複程式碼

---

## Dependencies (相依性)

### 必要的前置條件

**後端系統**:
- 後端 API 已完成且可正常運作 (31 個端點)
- API Base URL: `http://localhost:8080/api`
- CORS 已配置允許前端域名 (localhost:3000)
- Swagger 文件可存取: `http://localhost:8080/api/docs`

**開發環境**:
- Node.js >= 18.0.0
- npm >= 9.0.0 或 yarn >= 1.22.0
- Git

**第三方服務**:
- 無 (暫不使用外部 API 或服務)

### 技術相依

**資料庫**:
- 使用後端提供的 MySQL 資料庫,前端無直接存取

**API 格式**:
- RESTful API
- JSON 請求/回應格式
- JWT Bearer Token 認證

**圖片處理**:
- Base64 編碼圖片上傳
- 支援格式: JPEG, PNG, GIF
- 大小限制: < 2MB (由後端控制)

---

## Risks (風險評估)

| 風險 | 影響 | 機率 | 緩解措施 |
|------|------|------|----------|
| Next.js App Router 學習曲線陡峭 | Medium | Medium | 參考官方文件範例,使用 Pages Router 作為備案 |
| API CORS 配置問題導致請求失敗 | High | Medium | 在開發初期優先測試 API 連線,確認 CORS 設定 |
| Base64 圖片上傳導致請求過大 | Medium | Low | 前端限制圖片大小 < 2MB,壓縮後上傳 |
| Token 續期機制複雜度高 | Medium | Medium | 使用 Axios Interceptor 統一處理,參考業界最佳實踐 |
| 響應式設計在不同裝置測試工作量大 | Low | High | 使用 Tailwind CSS 響應式工具類,Chrome DevTools 模擬測試 |
| TypeScript 型別定義工作量 | Low | High | 使用 API Response 自動生成型別,優先定義核心介面 |
| 時間壓力導致功能不完整 | High | Medium | 採用 MVP 策略,優先實作核心流程,次要功能可後補 |

---

## Open Questions (待確認問題)

- [x] ~~前端專案類型~~ → 完整 SPA 應用程式
- [x] ~~前端框架選擇~~ → Next.js (React SSR)
- [x] ~~專案目錄位置~~ → 獨立 `frontend/` 目錄
- [x] ~~核心功能優先級~~ → 業務員搜尋、個人管理、管理員審核、統計報表
- [x] ~~UI 設計風格~~ → 活潑年輕
- [x] ~~色彩主題~~ → 清新藍綠 (Sky + Teal)
- [x] ~~Logo 與 Favicon~~ → 純文字 Logo "YAMU"
- [x] ~~部署環境~~ → 本地開發 + Docker + Vercel (三種環境)
- [x] ~~系統名稱~~ → YAMU

**已確認設計規格**:
- **系統名稱**: YAMU
- **UI 風格**: 活潑年輕 - 明亮色彩、圓角設計、插圖豐富
- **色彩方案**:
  - 主色: #0EA5E9 (Sky-500) - 清新藍
  - 配色: #14B8A6 (Teal-500) - 青綠
  - 強調色: #F472B6 (Pink-400) - 點綴用
  - 背景: #F8FAFC (Slate-50) - 淺灰
  - 文字: #0F172A (Slate-900) - 深灰
  - WCAG 對比度: AAA 等級 (易讀性最佳)
- **Logo**: 純文字 "YAMU" + 特殊字體設計
- **字體**:
  - 英文: Inter (無襯線,現代感)
  - 中文: Noto Sans TC (Google Fonts,清晰易讀)
- **部署環境**:
  - 開發: `http://localhost:3000` (Next.js dev server)
  - 測試: Docker + Nginx
  - 生產: Vercel (自動 CI/CD + CDN)

**Note**: 所有問題已確認,可進入 Spec 階段

---

## Technical Architecture Preview (技術架構預覽)

```
frontend/
├── app/                          # Next.js App Router
│   ├── (public)/                # 公開路由群組
│   │   ├── page.tsx            # 首頁
│   │   ├── search/             # 搜尋頁面
│   │   └── salesperson/[id]/   # 業務員詳細頁
│   ├── (auth)/                  # 認證路由群組
│   │   ├── login/
│   │   ├── register/
│   │   └── layout.tsx          # 認證共用 Layout
│   ├── (dashboard)/             # 業務員儀表板
│   │   ├── profile/
│   │   ├── experiences/
│   │   └── certifications/
│   ├── (admin)/                 # 管理員後台
│   │   ├── approvals/
│   │   ├── users/
│   │   ├── settings/
│   │   └── statistics/
│   └── layout.tsx               # Root Layout
├── components/                   # React 組件
│   ├── ui/                      # Shadcn/ui 基礎組件
│   ├── layout/                  # Layout 組件
│   └── features/                # 功能組件
├── lib/                         # 工具函式
│   ├── api/                     # API 客戶端
│   ├── auth/                    # 認證邏輯
│   └── utils/                   # 通用工具
├── types/                       # TypeScript 型別定義
├── hooks/                       # Custom Hooks
├── store/                       # 狀態管理 (Zustand)
└── public/                      # 靜態資源
```

---

## Timeline Estimate (時程預估)

**Phase 1: 專案設置與基礎架構** (1-2 天)
- Next.js 專案初始化
- TypeScript + ESLint + Prettier 配置
- Tailwind CSS + Shadcn/ui 安裝
- API 客戶端設置
- 基礎 Layout 與導航列

**Phase 2: 認證系統** (1-2 天)
- 註冊頁面
- 登入頁面
- Token 管理機制
- Route Guards

**Phase 3: 公開區域** (2-3 天)
- 首頁
- 搜尋頁面與篩選
- 業務員詳細頁面

**Phase 4: 業務員儀表板** (2-3 天)
- 個人資料管理
- 工作經驗 CRUD
- 證照上傳與管理
- 審核狀態查詢

**Phase 5: 管理員後台** (2-3 天)
- 審核管理介面
- 使用者管理
- 系統設定
- 統計報表與圖表

**Phase 6: 整合測試與優化** (1-2 天)
- 功能測試
- 響應式調整
- 效能優化
- Bug 修復

**Total: 9-15 天** (視實際開發進度調整)

---

**Next Step**:
1. 確認 Open Questions
2. 使用 `/spec frontend-spa-project` 撰寫詳細技術規格
