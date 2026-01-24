# 聯繫機制功能 (Contact Mechanism)

**功能代號**: 20260122-add-contact-mechanism
**狀態**: ✅ Proposal 驗證通過，待進入 Specification 階段
**優先級**: High (MVP Phase 1 核心功能)
**預計上線**: 2026-02-23 (1 個月)

---

## 快速導航

| 文檔 | 狀態 | 說明 |
|------|------|------|
| [proposal.md](./proposal.md) | ✅ 完成 | 完整的功能提案（13 章節，58 頁） |
| [validation-report.md](./validation-report.md) | ✅ 完成 | 規格驗證報告（通過率 100%） |
| `specification.md` | 📝 待建立 | 詳細技術規格（Backend + Frontend） |
| `api-spec.yaml` | 📝 待建立 | OpenAPI 3.1 規格 |
| `test-plan.md` | 📝 待建立 | 測試計劃 |

---

## 功能概述

實作客戶與業務員之間的聯繫機制，包含：

1. **業務員設定聯繫方式**
   - 電話、Email、LINE ID、WeChat ID
   - 整合在「編輯個人檔案」頁面
   - 至少提供 1 種聯繫方式

2. **客戶透過站內表單聯繫業務員**
   - 必須登入才能聯繫
   - Modal 表單（姓名、Email 自動填入）
   - 訊息內容 10-500 字
   - 頻率限制（24h 內同業務員 1 次，每天最多 5 次）

3. **業務員收到 Email 通知**
   - 包含客戶資訊和訊息內容
   - 非同步 Queue 發送
   - 失敗自動重試 3 次

4. **追蹤聯繫事件用於數據分析**
   - profile_view: 查看業務員檔案
   - contact_form_submission: 提交聯繫表單
   - IP 位址 hash 儲存（隱私保護）

---

## MVP 驗證目標

| 驗證假設 | 驗證方法 | 成功標準 |
|---------|---------|---------|
| 客戶願意使用平台尋找業務員 | 追蹤瀏覽量、聯繫請求數 | 每週 ≥ 10 個聯繫請求（第 1 個月） |
| 業務員願意建立檔案並獲客 | 追蹤業務員檔案完整度、回覆率 | 業務員 Email 回覆率 ≥ 60% |
| 平台能促成實際聯繫與成交 | 追蹤轉換漏斗 | 瀏覽 → 聯繫轉換率 ≥ 5% |

---

## 技術架構

### Backend API

- **Framework**: Laravel 11 + PHP 8.4
- **Database**: MySQL 8.0
- **Queue**: Redis + Laravel Queue
- **Email**: Laravel Mailable + SMTP (SendGrid)

**主要 API 端點**:
- `PATCH /api/salesperson/profile/contact-methods` - 業務員更新聯繫方式
- `POST /api/contact-requests` - 客戶提交聯繫請求
- `GET /api/salespersons/{id}` - 查詢業務員檔案（含聯繫方式）
- `GET /api/admin/contact-requests` - Admin 管理聯繫請求

**資料表變更**:
- 擴充 `salesperson_profiles` 資料表（新增 4 個聯繫方式欄位）
- 新增 `contact_requests` 資料表
- 新增 `contact_events` 資料表

### Frontend UI

- **Framework**: Next.js 15 + React 19
- **UI Components**: shadcn/ui
- **Form Validation**: Zod + React Hook Form
- **API Client**: React Query

**主要 UI 模組**:
- 業務員編輯聯繫方式（整合在編輯個人檔案頁面）
- 客戶查看聯繫方式（業務員檔案頁）
- 客戶提交聯繫表單（Modal）
- Admin 管理聯繫請求列表

---

## 時程規劃

**總週期**: 4 週（2026-01-23 ~ 2026-02-23）

| 週次 | 主要工作 | 交付物 |
|------|---------|-------|
| **Week 1** | Backend API + Database | API 完成，測試通過（覆蓋率 ≥ 95%） |
| **Week 2** | Frontend UI | UI 完成，E2E 測試通過 |
| **Week 3** | Email Notification + Event Tracking | Email 通知正常，事件追蹤正常 |
| **Week 4** | Testing + Optimization + Launch | 功能上線，監控正常 |

---

## 驗收標準

### 功能性標準（節選）

- ✅ 業務員可設定至少 1 種聯繫方式
- ✅ 客戶登入後可透過表單聯繫 approved 業務員
- ✅ 24 小時內同一客戶不可重複聯繫同一業務員
- ✅ 業務員收到 Email 通知（包含客戶資訊和訊息）
- ✅ 追蹤 profile_view 和 contact_form_submission 事件

### 非功能性標準（節選）

- ✅ 提交表單 API 回應時間 < 500ms (P95)
- ✅ 追蹤事件寫入 < 100ms
- ✅ Email 發送成功率 ≥ 99%
- ✅ Backend 測試覆蓋率 ≥ 95%
- ✅ Frontend E2E 測試覆蓋關鍵流程

### 量化成功標準（第 1 個月）

- ✅ 每週聯繫請求數 ≥ 10 個
- ✅ 業務員 Email 回覆率 ≥ 60%
- ✅ 瀏覽 → 聯繫轉換率 ≥ 5%

完整驗收標準請參考 [proposal.md](./proposal.md) Section 4。

---

## In Scope vs Out of Scope

### ✅ In Scope（本次實作）

- ✅ 業務員設定多種聯繫方式（電話、Email、LINE、WeChat）
- ✅ 客戶透過站內表單聯繫業務員（必須登入）
- ✅ 業務員收到 Email 通知
- ✅ 追蹤聯繫事件（profile_view, contact_form_submission）
- ✅ Admin 可查看所有聯繫請求
- ✅ Rate Limiting（防止濫用）
- ✅ 客戶個資加密儲存

### ❌ Out of Scope（本次不做）

- ❌ 站內即時通訊（Phase 2）
- ❌ 業務員回覆管理功能（Phase 2）
- ❌ 聯繫記錄查詢（業務員端）（Phase 2）
- ❌ 評分與評論功能（Phase 3）
- ❌ 聯繫方式隱私控制（點擊後顯示）（Phase 2）
- ❌ 多組聯繫方式（Phase 3）
- ❌ LINE/WeChat API 整合（Phase 3）
- ❌ CRM 系統整合（Phase 4）

---

## 關鍵決策記錄

| 決策 | 選擇 | 理由 |
|------|------|------|
| **未登入聯繫** | ❌ 必須登入 | 保護雙方隱私、防止垃圾訊息、追蹤真實用戶行為 |
| **聯繫方式顯示** | ✅ 直接顯示 | 降低操作步驟，提升轉換率（Phase 2 可選「點擊後顯示」） |
| **請求管理** | ❌ 無站內列表 | MVP 階段僅 Email 通知（Phase 2 新增站內列表） |
| **Email 發送** | ✅ 非同步 Queue | 不影響 API 回應速度，支援失敗重試 |
| **IP 位址儲存** | ✅ Hash 後儲存 | 保護用戶隱私（GDPR/PDPA 考量） |
| **聯繫方式必填** | ✅ 至少 1 種 | 確保客戶能實際聯繫到業務員 |
| **頻率限制** | ✅ 24h + 每天 5 次 | 防止惡意騷擾和垃圾訊息 |

---

## 風險管理

### 高影響風險 & 緩解措施

| 風險 | 機率 | 影響 | 緩解措施 |
|------|------|------|---------|
| **Email 發送失敗** | 中 | 高 | Queue + 自動重試 3 次 + 備用服務商（SendGrid） |
| **業務員不回覆客戶** | 高 | 高 | Email 明確提示 + 未來追蹤回覆率並公開顯示 |
| **垃圾訊息攻擊** | 高 | 中 | Rate Limiting（IP + User） + 必須登入 + 24h 限制 |
| **客戶隱私洩漏** | 低 | 高 | 客戶個資加密 + IP hash + 權限控制 |

完整風險列表請參考 [proposal.md](./proposal.md) Section 6。

---

## 監控指標

### 關鍵指標（每日監控）

- 聯繫請求數（異常閾值: < 2 個/天）
- Email 發送成功率（異常閾值: < 95%）
- API 錯誤率（異常閾值: > 5%）
- 提交表單 P95 回應時間（異常閾值: > 500ms）

### 業務指標（每週監控）

- 每週聯繫請求數（目標: ≥ 10 個）
- 瀏覽 → 聯繫轉換率（目標: ≥ 5%）
- 業務員回覆率（目標: ≥ 60%）
- 有聯繫方式的業務員比例（目標: ≥ 80%）

---

## 相關資源

### 文檔連結

- [Proposal 完整版](./proposal.md) - 13 章節，涵蓋所有細節
- [驗證報告](./validation-report.md) - 規格驗證結果（通過率 100%）
- [Laravel 11 文檔](https://laravel.com/docs/11.x)
- [Next.js 15 文檔](https://nextjs.org/docs)

### 專案架構

- Backend: `/my_profile_laravel`
- Frontend: `/frontend`
- OpenSpec: `/openspec`
- Claude Commands: `/.claude/commands`

---

## 下一步行動

1. ✅ **Proposal 已完成並驗證通過**（2026-01-23）

2. 📝 **撰寫詳細 Specification**（預計 2-3 天）
   - Backend Specification（API、DB、Business Logic、Tests）
   - Frontend Specification（UI/UX、Components、API Integration、E2E Tests）
   - OpenAPI 3.1 規格
   - 測試計劃

3. 🔍 **Specification 驗證**（預計 1 天）
   - 使用規格驗證 Checklist
   - 確保可直接實作

4. 🚀 **開始實作**（2026-01-23 起，Week 1）
   - Backend API + Database
   - 測試覆蓋率 ≥ 95%

---

## 聯絡資訊

- **功能負責人**: Product Team
- **技術負責人**: Development Team
- **問題回報**: GitHub Issues

---

**最後更新**: 2026-01-23
**版本**: 1.0
**狀態**: ✅ Proposal 驗證通過，待進入 Specification 階段
