# Proposal: 聯繫機制功能（Contact Mechanism）

**功能代號**: 20260122-add-contact-mechanism
**提案日期**: 2026-01-23
**目標上線**: 2026-02-23 (1 個月)
**優先級**: High (MVP Phase 1 核心功能)
**提案人**: Development Team
**狀態**: Draft

---

## 1. Why - 為什麼需要這個功能？

### 1.1 問題陳述

目前 YAMU 業務員推廣平台存在以下問題：

1. **客戶無法聯繫業務員**
   - 平台雖然提供業務員檔案展示功能
   - 但客戶無法透過平台與業務員建立聯繫
   - 導致「流量有了，但沒有轉換」的問題

2. **業務員缺乏獲客管道**
   - 業務員建立檔案後，無法接收客戶詢問
   - 缺乏明確的價值回饋，降低業務員使用意願

3. **平台無法驗證核心假設**
   - MVP 核心假設：「平台能促成客戶與業務員的實際聯繫與成交」
   - 目前缺乏聯繫機制，無法驗證此假設
   - 無法收集關鍵數據（聯繫率、轉換率）

### 1.2 MVP 驗證目標

本功能是 MVP Phase 1 的核心功能，主要驗證：

| 驗證假設 | 驗證方法 | 成功標準 |
|---------|---------|---------|
| 客戶願意使用平台尋找業務員 | 追蹤瀏覽量、聯繫請求數 | 每週 ≥ 10 個聯繫請求（第 1 個月） |
| 業務員願意建立檔案並獲客 | 追蹤業務員檔案完整度、回覆率 | 業務員 Email 回覆率 ≥ 60% |
| 平台能促成實際聯繫與成交 | 追蹤轉換漏斗 | 瀏覽 → 聯繫轉換率 ≥ 5% |

### 1.3 業務價值

- **短期價值**（1-3 個月）：
  - 驗證 PMF (Product-Market Fit)
  - 收集真實使用者行為數據
  - 建立平台與業務員的信任關係

- **長期價值**（6-12 個月）：
  - 累積成功案例，吸引更多業務員加入
  - 建立數據驅動的業務員推薦機制
  - 為未來的付費功能（例如：優先曝光）奠定基礎

---

## 2. What - 這個功能做什麼？

### 2.1 功能概述

實作完整的聯繫機制，包含三個核心流程：

```
┌─────────────────────────────────────────────────────────────┐
│                     聯繫機制完整流程                          │
└─────────────────────────────────────────────────────────────┘

1. 業務員設定聯繫方式
   ┌──────────────┐
   │ 業務員登入    │
   └──────┬───────┘
          │
          ▼
   ┌──────────────┐      ┌─────────────────────┐
   │ 編輯個人檔案  │─────>│ 設定聯繫方式         │
   └──────────────┘      │ - 電話              │
                         │ - Email             │
                         │ - LINE ID           │
                         │ - WeChat ID         │
                         └─────────────────────┘

2. 客戶聯繫業務員
   ┌──────────────┐
   │ 客戶登入      │
   └──────┬───────┘
          │
          ▼
   ┌──────────────┐      ┌─────────────────────┐
   │ 瀏覽業務員檔案│─────>│ 查看聯繫方式         │
   └──────┬───────┘      │ (直接顯示)          │
          │              └─────────────────────┘
          │
          ▼
   ┌──────────────┐      ┌─────────────────────┐
   │ 點擊「聯繫」  │─────>│ 填寫表單             │
   └──────┬───────┘      │ - 姓名 (自動填入)    │
          │              │ - Email (自動填入)   │
          │              │ - 電話 (可選)        │
          │              │ - 訊息 (必填)        │
          ▼              └─────────────────────┘
   ┌──────────────┐
   │ 提交表單      │
   └──────┬───────┘
          │
          ▼
   ┌──────────────┐
   │ 系統處理      │
   │ - 驗證資料    │
   │ - 檢查頻率限制│
   │ - 儲存請求    │
   │ - 發送 Email  │
   └──────────────┘

3. 業務員接收通知
   ┌──────────────┐
   │ 系統發送 Email│
   └──────┬───────┘
          │
          ▼
   ┌──────────────┐      ┌─────────────────────┐
   │ 業務員收到通知│─────>│ Email 內容           │
   └──────┬───────┘      │ - 客戶姓名           │
          │              │ - 客戶聯繫方式       │
          │              │ - 訊息內容           │
          ▼              │ - 回覆按鈕 (mailto)  │
   ┌──────────────┐      └─────────────────────┘
   │ 業務員回覆客戶│
   └──────────────┘
```

### 2.2 功能模組

#### 模組 1: 聯繫方式管理（Contact Methods Management）

**功能描述**：業務員可設定並管理個人聯繫方式

**使用流程**：
1. 業務員登入後進入「編輯個人檔案」頁面
2. 在「聯繫方式」區塊填寫聯繫資訊
3. 系統驗證格式後儲存
4. 聯繫方式立即生效（approved 業務員）

**資料欄位**：
| 欄位 | 型別 | 必填 | 限制 | 說明 |
|------|------|------|------|------|
| phone | string(20) | 否 | 台灣手機或市話格式 | 聯繫電話 |
| email_public | string(255) | 否 | RFC 5322 Email 格式 | 公開 Email（區別於帳號 Email） |
| line_id | string(50) | 否 | 3-20 字元，英數字+底線 | LINE ID |
| wechat_id | string(50) | 否 | 6-20 字元，英數字+底線減號 | WeChat ID |

**業務規則**：
- BR-CM-001: 業務員必須至少提供 1 種聯繫方式
- BR-CM-002: 業務員只能編輯自己的聯繫方式
- BR-CM-003: Admin 可以代為編輯任何業務員的聯繫方式
- BR-CM-004: 聯繫方式變更立即生效，無需重新審核

#### 模組 2: 聯繫請求提交（Contact Request Submission）

**功能描述**：已登入客戶可透過站內表單聯繫業務員

**使用流程**：
1. 客戶登入後瀏覽業務員檔案
2. 點擊「聯繫」按鈕開啟 Modal 表單
3. 填寫訊息內容（姓名、Email 自動填入）
4. 提交表單，系統驗證並儲存
5. 顯示成功訊息

**表單欄位**：
| 欄位 | 型別 | 必填 | 限制 | 說明 |
|------|------|------|------|------|
| customer_name | string(100) | 是 | 自動填入（auth.user.name） | 客戶姓名 |
| customer_email | string(255) | 是 | 自動填入（auth.user.email） | 客戶 Email |
| customer_phone | string(20) | 否 | 台灣手機格式 | 客戶電話 |
| message | text | 是 | 10-500 字元 | 訊息內容 |

**業務規則**：
- BR-CR-001: 客戶必須登入才能提交聯繫請求
- BR-CR-002: 只有 approved 狀態的業務員接受聯繫請求
- BR-CR-003: 同一客戶對同一業務員 24 小時內只能提交 1 次
- BR-CR-004: 同一客戶每天最多提交 5 次聯繫請求（跨業務員）
- BR-CR-005: 訊息內容最少 10 字，最多 500 字
- BR-CR-006: 業務員無聯繫方式時，隱藏「聯繫」按鈕

#### 模組 3: Email 通知（Email Notification）

**功能描述**：業務員收到新聯繫請求時的 Email 通知

**觸發時機**：客戶成功提交聯繫表單後

**Email 內容**：
```
主旨：您收到一個新的客戶諮詢 - {客戶姓名}

親愛的 {業務員姓名}，

您在 YAMU 平台收到一個新的客戶諮詢：

客戶資訊：
- 姓名：{客戶姓名}
- Email：{客戶 Email}
- 電話：{客戶電話}（如有填寫）
- 諮詢時間：{提交時間}

訊息內容：
{訊息內容}

[回覆客戶] (mailto: 連結)

---
YAMU 業務員推廣平台
```

**業務規則**：
- BR-EN-001: Email 使用非同步 Queue 發送（Laravel Queue）
- BR-EN-002: 發送失敗時自動重試 3 次（間隔 1min, 5min, 15min）
- BR-EN-003: 業務員強制接收通知（MVP 階段不提供關閉選項）
- BR-EN-004: Email 寄件者：noreply@yamu.com
- BR-EN-005: Email 包含「回覆客戶」mailto 連結

#### 模組 4: 聯繫事件追蹤（Contact Event Tracking）

**功能描述**：追蹤客戶與業務員的互動事件，用於數據分析

**追蹤事件**：
| 事件類型 | 觸發時機 | 資料欄位 |
|---------|---------|---------|
| profile_view | 客戶查看業務員檔案頁 | user_id, salesperson_id, ip, user_agent |
| contact_form_submission | 客戶提交聯繫表單 | user_id, salesperson_id, ip, user_agent |

**資料結構**：
```sql
contact_events (
    id,
    user_id (nullable),
    salesperson_id,
    event_type,
    ip_address_hash,
    user_agent,
    created_at
)
```

**業務規則**：
- BR-ET-001: IP 位址必須 hash 後儲存（隱私保護）
- BR-ET-002: 追蹤事件寫入必須 < 100ms
- BR-ET-003: 未登入用戶的 user_id 為 null
- BR-ET-004: 事件僅用於數據分析，不影響業務邏輯

---

## 3. Scope - 功能範圍

### 3.1 In Scope（本次實作）

#### Backend API

- ✅ **API-001**: 業務員更新聯繫方式
  - `PATCH /api/salesperson/profile/contact-methods`
  - 驗證聯繫方式格式
  - 至少提供 1 種聯繫方式

- ✅ **API-002**: 客戶提交聯繫請求
  - `POST /api/contact-requests`
  - 驗證頻率限制（24h 同業務員 1 次、每天 5 次）
  - 非同步發送 Email

- ✅ **API-003**: 查詢業務員檔案（含聯繫方式）
  - `GET /api/salespersons/{id}`
  - 回傳 approved 業務員的聯繫方式
  - 追蹤 profile_view 事件

- ✅ **API-004**: Admin 管理聯繫請求
  - `GET /api/admin/contact-requests`
  - 分頁、篩選、排序

#### Database Schema

- ✅ **DB-001**: 擴充 `salesperson_profiles` 資料表
  - 新增欄位: phone, email_public, line_id, wechat_id

- ✅ **DB-002**: 新增 `contact_requests` 資料表
  - 儲存聯繫請求記錄
  - 支援頻率限制查詢

- ✅ **DB-003**: 新增 `contact_events` 資料表
  - 儲存追蹤事件
  - 支援數據分析查詢

#### Frontend UI

- ✅ **UI-001**: 業務員編輯聯繫方式
  - 整合在「編輯個人檔案」頁面
  - 表單驗證（格式、必填至少 1 項）

- ✅ **UI-002**: 客戶查看聯繫方式
  - 在業務員檔案頁直接顯示
  - 電話、Email 可點擊（tel:, mailto:）
  - LINE/WeChat 顯示 ID + 複製按鈕

- ✅ **UI-003**: 客戶提交聯繫表單
  - Modal 彈出視窗
  - 姓名、Email 自動填入
  - 訊息欄位驗證（10-500 字）
  - Loading 狀態、成功/失敗訊息

- ✅ **UI-004**: 錯誤處理與空狀態
  - 業務員無聯繫方式：隱藏「聯繫」按鈕 + 提示訊息
  - 頻率限制：顯示「您已在 24 小時內聯繫過此業務員」
  - 網路錯誤：顯示「網路連線失敗，請稍後再試」

#### Email & Queue

- ✅ **EMAIL-001**: 聯繫請求通知 Email
  - 使用 Laravel Mailable
  - 非同步 Queue 發送
  - 失敗重試機制

#### Security & Performance

- ✅ **SEC-001**: 權限控制
  - 聯繫方式: 業務員本人 + Admin 可編輯
  - 聯繫請求: 僅登入用戶可提交
  - 聯繫記錄: Admin 可查看全部

- ✅ **SEC-002**: Rate Limiting
  - 提交表單: 每個 IP 每小時最多 5 次
  - 查看檔案: 每個 IP 每分鐘最多 60 次

- ✅ **PERF-001**: 效能標準
  - 提交表單 API: < 500ms (P95)
  - Email 發送: 非同步 Queue
  - 追蹤事件寫入: < 100ms

#### Testing

- ✅ **TEST-001**: Backend Feature Tests
  - 聯繫方式 CRUD 測試
  - 聯繫請求提交測試（含頻率限制）
  - Email 發送測試（Queue）
  - 權限測試

- ✅ **TEST-002**: Frontend E2E Tests
  - 業務員設定聯繫方式流程
  - 客戶提交聯繫表單流程
  - 錯誤處理測試

### 3.2 Out of Scope（不在本次範圍）

#### 功能面

- ❌ **站內即時通訊**
  - 原因: MVP 階段僅驗證聯繫意願，不需要即時通訊
  - 未來規劃: Phase 2 考慮

- ❌ **業務員回覆管理功能**
  - 原因: MVP 階段透過 Email 回覆即可
  - 未來規劃: Phase 2 新增「聯繫請求列表」頁面

- ❌ **聯繫記錄查詢（業務員端）**
  - 原因: MVP 階段業務員僅透過 Email 查看
  - 未來規劃: Phase 2 新增「我的客戶」頁面

- ❌ **評分與評論功能**
  - 原因: 需要先累積足夠的聯繫案例
  - 未來規劃: Phase 3

- ❌ **聯繫方式隱私控制**
  - 原因: MVP 階段直接顯示，降低操作複雜度
  - 未來規劃: Phase 2 考慮「點擊後顯示」選項

- ❌ **多組聯繫方式**
  - 原因: MVP 階段每種聯繫方式只需 1 組
  - 未來規劃: Phase 3 考慮（例如：工作手機、個人手機）

#### 數據面

- ❌ **聯繫方式點擊追蹤**
  - 原因: MVP 階段直接顯示，無法追蹤點擊
  - 未來規劃: Phase 2 改為「點擊後顯示」時追蹤

- ❌ **業務員回覆率追蹤**
  - 原因: 需要站內回覆功能才能追蹤
  - 未來規劃: Phase 2

- ❌ **轉換漏斗完整追蹤**
  - 原因: MVP 階段僅追蹤「瀏覽」和「聯繫」
  - 未來規劃: Phase 2 追蹤「成交」事件

#### 整合面

- ❌ **LINE/WeChat API 整合**
  - 原因: MVP 階段僅顯示 ID，由用戶手動新增好友
  - 未來規劃: Phase 3 考慮深度整合

- ❌ **CRM 系統整合**
  - 原因: MVP 階段不需要外部 CRM
  - 未來規劃: Phase 4（付費功能）

---

## 4. Success Criteria - 驗收標準

### 4.1 功能性驗收標準

#### 業務員設定聯繫方式

- [ ] **AC-CM-001**: 業務員可在「編輯個人檔案」頁面設定聯繫方式
  - 提供 4 個欄位: phone, email_public, line_id, wechat_id
  - 至少填寫 1 個欄位才能儲存
  - 驗證格式正確（電話、Email、LINE ID、WeChat ID）

- [ ] **AC-CM-002**: 聯繫方式變更立即生效
  - 儲存後立即在業務員檔案頁顯示
  - 無需重新審核

- [ ] **AC-CM-003**: Admin 可代為編輯業務員聯繫方式
  - Admin 可在後台編輯任何業務員的聯繫方式

#### 客戶聯繫業務員

- [ ] **AC-CR-001**: 客戶可查看 approved 業務員的聯繫方式
  - 聯繫方式直接顯示在業務員檔案頁
  - 電話可點擊撥打（tel: link）
  - Email 可點擊開啟郵件（mailto: link）
  - LINE/WeChat 顯示 ID + 複製按鈕

- [ ] **AC-CR-002**: 客戶可透過表單聯繫業務員
  - 點擊「聯繫」按鈕開啟 Modal
  - 姓名、Email 自動填入
  - 訊息欄位必填（10-500 字）
  - 提交成功顯示確認訊息

- [ ] **AC-CR-003**: 未登入用戶無法聯繫業務員
  - 未登入用戶點擊「聯繫」按鈕導向登入頁
  - 登入後返回業務員檔案頁

- [ ] **AC-CR-004**: pending/rejected 業務員不顯示聯繫按鈕
  - 僅 approved 業務員顯示「聯繫」按鈕

- [ ] **AC-CR-005**: 業務員無聯繫方式時的錯誤處理
  - 隱藏「聯繫」按鈕
  - 顯示訊息:「此業務員尚未提供聯繫方式」

#### 頻率限制

- [ ] **AC-RL-001**: 24 小時內同一客戶不可重複聯繫同一業務員
  - 提交失敗，顯示錯誤:「您已在 24 小時內聯繫過此業務員」

- [ ] **AC-RL-002**: 同一客戶每天最多提交 5 次聯繫請求
  - 提交失敗，顯示錯誤:「您今日的聯繫次數已達上限」

- [ ] **AC-RL-003**: 每個 IP 每小時最多提交 5 次
  - 提交失敗，顯示錯誤:「您的操作過於頻繁，請稍後再試」

#### Email 通知

- [ ] **AC-EN-001**: 業務員收到聯繫請求的 Email 通知
  - 包含客戶姓名、Email、電話、訊息內容
  - 包含「回覆客戶」mailto 連結
  - 主旨:「您收到一個新的客戶諮詢 - {客戶姓名}」

- [ ] **AC-EN-002**: Email 非同步發送
  - 表單提交後立即回應客戶（不等待 Email 發送）
  - Email 透過 Queue 發送

- [ ] **AC-EN-003**: Email 發送失敗自動重試
  - 失敗後自動重試 3 次（1min, 5min, 15min 間隔）

#### 追蹤事件

- [ ] **AC-ET-001**: 追蹤業務員檔案瀏覽事件
  - 客戶查看業務員檔案時記錄 profile_view 事件
  - 包含 user_id, salesperson_id, ip_hash, user_agent

- [ ] **AC-ET-002**: 追蹤聯繫表單提交事件
  - 客戶提交表單時記錄 contact_form_submission 事件
  - 包含 user_id, salesperson_id, ip_hash, user_agent

- [ ] **AC-ET-003**: IP 位址 hash 儲存
  - IP 位址使用 SHA256 hash 後儲存
  - 保護用戶隱私

### 4.2 非功能性驗收標準

#### 效能

- [ ] **AC-PERF-001**: 提交表單 API 回應時間 < 500ms (P95)
- [ ] **AC-PERF-002**: 追蹤事件寫入 < 100ms
- [ ] **AC-PERF-003**: 業務員檔案頁載入時間 < 2s (LCP)

#### 安全性

- [ ] **AC-SEC-001**: 所有 API 端點都需要認證（除了查看業務員檔案）
- [ ] **AC-SEC-002**: 業務員只能編輯自己的聯繫方式
- [ ] **AC-SEC-003**: 客戶個資加密儲存（contact_requests 資料表）
- [ ] **AC-SEC-004**: Rate Limiting 正確運作

#### 可用性

- [ ] **AC-UX-001**: 表單驗證即時回饋
  - 填寫時即時驗證格式
  - 顯示清楚的錯誤訊息

- [ ] **AC-UX-002**: Loading 狀態明確
  - 提交按鈕顯示 spinner
  - 禁用重複提交

- [ ] **AC-UX-003**: 成功/失敗訊息清楚
  - 成功:「已成功送出聯繫請求！業務員將透過 Email 與您聯繫。」
  - 失敗: 顯示具體錯誤原因

#### 測試覆蓋率

- [ ] **AC-TEST-001**: Backend Feature Tests 覆蓋率 ≥ 95%
- [ ] **AC-TEST-002**: Frontend E2E Tests 覆蓋關鍵流程
  - 業務員設定聯繫方式
  - 客戶提交聯繫表單
  - 錯誤處理

### 4.3 量化成功標準（KPI）

#### 第 1 個月目標

| 指標 | 目標值 | 監測方法 |
|------|-------|---------|
| 每週聯繫請求數 | ≥ 10 個 | contact_requests 資料表 |
| 業務員 Email 回覆率 | ≥ 60% | 人工追蹤（MVP 階段） |
| 瀏覽 → 聯繫轉換率 | ≥ 5% | profile_views vs contact_form_submissions |
| Email 發送成功率 | ≥ 99% | Laravel Queue 監控 |
| API 錯誤率 | < 1% | Laravel Log |

#### 第 2 個月目標

| 指標 | 目標值 |
|------|-------|
| 每週聯繫請求數 | ≥ 20 個 |
| 業務員回覆率 | ≥ 70% |
| 瀏覽 → 聯繫轉換率 | ≥ 8% |

---

## 5. Technical Considerations - 技術考量

### 5.1 技術選型

#### Backend

| 技術 | 選擇 | 理由 |
|------|------|------|
| **Framework** | Laravel 11 | 現有技術棧 |
| **Database** | MySQL 8.0 | 現有技術棧 |
| **Queue** | Redis + Laravel Queue | 非同步 Email 發送 |
| **Email** | Laravel Mailable + SMTP | 穩定可靠 |
| **Rate Limiting** | Laravel Cache + Middleware | 內建支援 |
| **Validation** | Laravel Validation | 內建支援 |

#### Frontend

| 技術 | 選擇 | 理由 |
|------|------|------|
| **Framework** | Next.js 15 + React 19 | 現有技術棧 |
| **UI Components** | shadcn/ui | 現有技術棧 |
| **Form Validation** | Zod + React Hook Form | Type-safe 驗證 |
| **API Client** | React Query | 現有技術棧 |
| **Modal** | shadcn/ui Dialog | 一致的 UI |

### 5.2 資料庫設計

#### 5.2.1 擴充 `salesperson_profiles` 資料表

```sql
ALTER TABLE salesperson_profiles
ADD COLUMN phone VARCHAR(20) NULL COMMENT '聯繫電話',
ADD COLUMN email_public VARCHAR(255) NULL COMMENT '公開 Email',
ADD COLUMN line_id VARCHAR(50) NULL COMMENT 'LINE ID',
ADD COLUMN wechat_id VARCHAR(50) NULL COMMENT 'WeChat ID',
ADD INDEX idx_phone (phone),
ADD INDEX idx_email_public (email_public);
```

**設計考量**:
- 所有聯繫方式欄位都是 nullable（至少填 1 個即可）
- 新增索引加速查詢（未來可能需要「有提供電話的業務員」篩選）

#### 5.2.2 新增 `contact_requests` 資料表

```sql
CREATE TABLE contact_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    salesperson_id BIGINT UNSIGNED NOT NULL COMMENT '業務員 ID',
    user_id BIGINT UNSIGNED NOT NULL COMMENT '客戶 ID',
    customer_name VARCHAR(100) NOT NULL COMMENT '客戶姓名',
    customer_email VARCHAR(255) NOT NULL COMMENT '客戶 Email',
    customer_phone VARCHAR(20) NULL COMMENT '客戶電話',
    message TEXT NOT NULL COMMENT '訊息內容',
    ip_address_hash CHAR(64) NOT NULL COMMENT 'IP 位址 Hash (SHA256)',
    user_agent VARCHAR(255) NULL COMMENT 'User Agent',
    email_sent_at TIMESTAMP NULL COMMENT 'Email 發送時間',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (salesperson_id) REFERENCES salesperson_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_salesperson_created (salesperson_id, created_at),
    INDEX idx_user_created (user_id, created_at),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**設計考量**:
- `salesperson_id` + `user_id` + `created_at` 複合索引支援頻率限制查詢
- `ip_address_hash` 儲存 SHA256 hash（保護隱私）
- `email_sent_at` 追蹤 Email 發送狀態
- `customer_email`, `customer_phone` 儲存時加密（Laravel Encryption）

#### 5.2.3 新增 `contact_events` 資料表

```sql
CREATE TABLE contact_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL COMMENT '用戶 ID (未登入為 NULL)',
    salesperson_id BIGINT UNSIGNED NOT NULL COMMENT '業務員 ID',
    event_type ENUM('profile_view', 'contact_form_submission') NOT NULL COMMENT '事件類型',
    ip_address_hash CHAR(64) NOT NULL COMMENT 'IP 位址 Hash (SHA256)',
    user_agent VARCHAR(255) NULL COMMENT 'User Agent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (salesperson_id) REFERENCES salesperson_profiles(id) ON DELETE CASCADE,

    INDEX idx_salesperson_type_created (salesperson_id, event_type, created_at),
    INDEX idx_user_type_created (user_id, event_type, created_at),
    INDEX idx_event_type_created (event_type, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**設計考量**:
- `user_id` nullable（未登入用戶也可追蹤）
- `event_type` ENUM（限制事件類型，避免錯誤）
- 複合索引支援數據分析查詢
- 僅儲存 `created_at`（追蹤事件不需要 `updated_at`）

### 5.3 API 設計

#### 5.3.1 業務員更新聯繫方式

```http
PATCH /api/salesperson/profile/contact-methods
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "phone": "0912-345-678",
  "email_public": "salesperson@example.com",
  "line_id": "my_line_id",
  "wechat_id": "my_wechat"
}
```

**回應**:
```json
{
  "success": true,
  "data": {
    "phone": "0912-345-678",
    "email_public": "salesperson@example.com",
    "line_id": "my_line_id",
    "wechat_id": "my_wechat",
    "updated_at": "2026-01-23T10:30:00Z"
  }
}
```

**驗證規則**:
- 至少提供 1 個聯繫方式（phone, email_public, line_id, wechat_id）
- phone: `regex:/^09\d{8}$|^0\d-\d{7,8}$/`
- email_public: `email:rfc,dns`
- line_id: `regex:/^[a-zA-Z0-9_]{3,20}$/`
- wechat_id: `regex:/^[a-zA-Z0-9_-]{6,20}$/`

#### 5.3.2 客戶提交聯繫請求

```http
POST /api/contact-requests
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "salesperson_id": 123,
  "customer_phone": "0912-345-678",
  "message": "我想了解保險規劃..."
}
```

**回應**:
```json
{
  "success": true,
  "message": "已成功送出聯繫請求！業務員將透過 Email 與您聯繫。",
  "data": {
    "id": 456,
    "salesperson_id": 123,
    "created_at": "2026-01-23T10:30:00Z"
  }
}
```

**錯誤回應**:
```json
{
  "success": false,
  "message": "您已在 24 小時內聯繫過此業務員",
  "errors": {
    "salesperson_id": ["您已在 24 小時內聯繫過此業務員"]
  }
}
```

**驗證規則**:
- salesperson_id: `required|exists:salesperson_profiles,id`
- customer_phone: `nullable|regex:/^09\d{8}$/`
- message: `required|min:10|max:500`

**業務邏輯檢查**:
1. 檢查業務員是否為 approved 狀態
2. 檢查 24 小時內是否已聯繫過（同 user_id + salesperson_id）
3. 檢查當天是否已聯繫 5 次（同 user_id）
4. 檢查 IP Rate Limiting（每小時 5 次）

#### 5.3.3 查詢業務員檔案（含聯繫方式）

```http
GET /api/salespersons/123
Authorization: Bearer {access_token} (optional)
```

**回應**:
```json
{
  "success": true,
  "data": {
    "id": 123,
    "user_id": 456,
    "name": "張業務",
    "bio": "專業保險顧問...",
    "specialties": ["壽險", "醫療險"],
    "years_of_experience": 5,
    "avatar_url": "https://...",
    "approval_status": "approved",
    "contact_methods": {
      "phone": "0912-345-678",
      "email_public": "salesperson@example.com",
      "line_id": "my_line_id",
      "wechat_id": "my_wechat"
    },
    "has_contact_methods": true
  }
}
```

**業務邏輯**:
- 僅回傳 approved 業務員的聯繫方式
- pending/rejected 業務員回傳 `contact_methods: null`
- 追蹤 `profile_view` 事件

#### 5.3.4 Admin 查詢聯繫請求列表

```http
GET /api/admin/contact-requests?page=1&per_page=20&salesperson_id=123
Authorization: Bearer {access_token}
X-Role: admin
```

**回應**:
```json
{
  "success": true,
  "data": [
    {
      "id": 456,
      "salesperson": {
        "id": 123,
        "name": "張業務"
      },
      "customer": {
        "id": 789,
        "name": "王客戶",
        "email": "customer@example.com",
        "phone": "0912-345-678"
      },
      "message": "我想了解保險規劃...",
      "email_sent_at": "2026-01-23T10:30:00Z",
      "created_at": "2026-01-23T10:30:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 100
  }
}
```

### 5.4 Email Queue 設計

#### 5.4.1 Queue Job

```php
class SendContactRequestNotification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    public function __construct(
        private ContactRequest $contactRequest
    ) {}

    public function handle(): void
    {
        Mail::to($this->contactRequest->salesperson->user->email)
            ->send(new ContactRequestNotificationMail($this->contactRequest));

        $this->contactRequest->update([
            'email_sent_at' => now()
        ]);
    }
}
```

#### 5.4.2 Mailable

```php
class ContactRequestNotificationMail extends Mailable
{
    public function __construct(
        private ContactRequest $contactRequest
    ) {}

    public function build(): self
    {
        return $this->subject("您收到一個新的客戶諮詢 - {$this->contactRequest->customer_name}")
                    ->markdown('emails.contact-request-notification')
                    ->with([
                        'contactRequest' => $this->contactRequest,
                        'replyUrl' => "mailto:{$this->contactRequest->customer_email}?subject=回覆：您的諮詢"
                    ]);
    }
}
```

### 5.5 Rate Limiting 設計

#### 5.5.1 Middleware

```php
// 表單提交頻率限制
Route::post('/contact-requests', [ContactRequestController::class, 'store'])
    ->middleware(['auth', 'throttle:5,60']); // 每小時 5 次

// 業務員檔案查看頻率限制
Route::get('/salespersons/{id}', [SalespersonController::class, 'show'])
    ->middleware(['throttle:60,1']); // 每分鐘 60 次
```

#### 5.5.2 Business Logic Rate Limiting

```php
// 24 小時內同一客戶不可重複聯繫同一業務員
$existingRequest = ContactRequest::where('user_id', $userId)
    ->where('salesperson_id', $salespersonId)
    ->where('created_at', '>=', now()->subHours(24))
    ->exists();

if ($existingRequest) {
    throw ValidationException::withMessages([
        'salesperson_id' => ['您已在 24 小時內聯繫過此業務員']
    ]);
}

// 同一客戶每天最多 5 次
$todayCount = ContactRequest::where('user_id', $userId)
    ->whereDate('created_at', today())
    ->count();

if ($todayCount >= 5) {
    throw ValidationException::withMessages([
        'user_id' => ['您今日的聯繫次數已達上限']
    ]);
}
```

### 5.6 效能優化

#### 5.6.1 資料庫查詢最佳化

```php
// 使用 Eager Loading 避免 N+1 問題
$salesperson = SalespersonProfile::with('user')
    ->findOrFail($id);

// 使用索引加速頻率限制查詢
// 索引: (user_id, salesperson_id, created_at)
```

#### 5.6.2 快取策略

```php
// 業務員聯繫方式快取（5 分鐘）
Cache::remember("salesperson.{$id}.contact_methods", 300, function () use ($id) {
    return SalespersonProfile::find($id)->only([
        'phone', 'email_public', 'line_id', 'wechat_id'
    ]);
});
```

#### 5.6.3 非同步處理

- Email 發送: Queue
- 追蹤事件寫入: 同步（但僅寫入一筆記錄，< 100ms）

### 5.7 安全性設計

#### 5.7.1 資料加密

```php
// 客戶個資加密儲存
protected $casts = [
    'customer_email' => 'encrypted',
    'customer_phone' => 'encrypted',
];
```

#### 5.7.2 IP 位址 Hash

```php
// SHA256 hash
$ipHash = hash('sha256', $request->ip());
```

#### 5.7.3 XSS 防護

```php
// 訊息內容過濾
$message = strip_tags($request->input('message'));
$message = e($message); // HTML entities encode
```

#### 5.7.4 CSRF 防護

```php
// Next.js API Client 自動帶入 CSRF Token
// Laravel 自動驗證
```

---

## 6. Risks & Mitigations - 風險與緩解

### 6.1 技術風險

| 風險 | 機率 | 影響 | 緩解措施 |
|------|------|------|---------|
| **Email 發送失敗** | 中 | 高 | - 使用 Queue + 自動重試 3 次<br>- 監控 Queue Failed Jobs<br>- 備用 Email 服務商（SendGrid） |
| **垃圾訊息攻擊** | 高 | 中 | - Rate Limiting（IP + User）<br>- 必須登入才能聯繫<br>- 24 小時內同業務員只能聯繫 1 次<br>- 訊息內容長度限制（10-500 字） |
| **資料庫效能瓶頸** | 低 | 中 | - 適當索引（已設計）<br>- 追蹤事件非同步寫入（如需要）<br>- 定期歸檔舊資料（> 1 年） |
| **併發寫入衝突** | 低 | 低 | - Optimistic Locking (updated_at 檢查)<br>- MySQL InnoDB 事務隔離 |

### 6.2 業務風險

| 風險 | 機率 | 影響 | 緩解措施 |
|------|------|------|---------|
| **業務員不回覆客戶** | 高 | 高 | - Email 通知明確提示回覆<br>- 未來追蹤回覆率並公開顯示<br>- 回覆率低的業務員降低曝光 |
| **客戶隱私洩漏** | 低 | 高 | - 客戶個資加密儲存<br>- IP 位址 hash<br>- 僅業務員和 Admin 可查看聯繫記錄 |
| **聯繫轉換率低** | 中 | 高 | - A/B Testing 表單設計<br>- 優化聯繫方式顯示<br>- 提供聯繫範本（未來功能） |
| **業務員不填寫聯繫方式** | 中 | 中 | - Onboarding 流程引導<br>- Email 提醒未填寫聯繫方式的業務員<br>- 未來考慮強制至少填 1 種才能通過審核 |

### 6.3 產品風險

| 風險 | 機率 | 影響 | 緩解措施 |
|------|------|------|---------|
| **用戶不願意登入** | 中 | 中 | - 說明登入保護雙方隱私<br>- 提供快速社群登入（Google, Facebook）<br>- 未來考慮「半匿名」模式 |
| **業務員偏好其他聯繫方式** | 中 | 低 | - 提供多種聯繫方式選項（電話、Email、LINE、WeChat）<br>- 未來新增更多選項（Telegram, Messenger） |
| **客戶濫用聯繫功能** | 中 | 中 | - Rate Limiting<br>- 訊息內容審核（未來功能）<br>- 業務員可封鎖特定客戶（未來功能） |

### 6.4 時程風險

| 風險 | 機率 | 影響 | 緩解措施 |
|------|------|------|---------|
| **Email Queue 整合延遲** | 低 | 中 | - Redis Queue 已存在於專案<br>- Laravel Queue 文檔完整<br>- 預留 2 天緩衝時間 |
| **前端 Modal UX 複雜度** | 低 | 低 | - 使用現有 shadcn/ui Dialog<br>- 參考 Best Practice 範例 |
| **測試撰寫時間超出預期** | 中 | 低 | - 優先實作核心功能測試<br>- Edge Cases 測試可後補 |

---

## 7. Timeline - 時程規劃

### 7.1 總覽

**開發週期**: 4 週（2026-01-23 ~ 2026-02-23）

```
Week 1: Backend API + Database
Week 2: Frontend UI
Week 3: Email Notification + Event Tracking
Week 4: Testing + Optimization + Launch
```

### 7.2 詳細時程

#### Week 1: Backend API + Database（2026-01-23 ~ 2026-01-29）

**Day 1-2: 資料庫設計與實作**
- [ ] 撰寫 Migration: 擴充 `salesperson_profiles` 資料表
- [ ] 撰寫 Migration: 新增 `contact_requests` 資料表
- [ ] 撰寫 Migration: 新增 `contact_events` 資料表
- [ ] 執行 Migration 並驗證
- [ ] 撰寫 Model + Relationships

**Day 3-4: API 實作**
- [ ] 實作 `PATCH /api/salesperson/profile/contact-methods`
  - Request Validation
  - Business Logic (至少 1 種聯繫方式)
  - Response
- [ ] 實作 `POST /api/contact-requests`
  - Request Validation
  - Rate Limiting 檢查
  - 儲存記錄
  - 觸發 Email Queue
- [ ] 實作 `GET /api/salespersons/{id}` (擴充聯繫方式)
- [ ] 實作 `GET /api/admin/contact-requests`

**Day 5: Testing**
- [ ] Feature Tests: 聯繫方式 CRUD
- [ ] Feature Tests: 聯繫請求提交
- [ ] Feature Tests: 頻率限制
- [ ] Feature Tests: 權限控制
- [ ] 測試覆蓋率 ≥ 95%

**交付物**: Backend API 完成，測試通過

---

#### Week 2: Frontend UI（2026-01-30 ~ 2026-02-05）

**Day 1-2: 業務員設定聯繫方式 UI**
- [ ] 新增「聯繫方式」區塊到編輯個人檔案頁面
- [ ] 表單設計（phone, email_public, line_id, wechat_id）
- [ ] Zod Schema 驗證
- [ ] API 整合（PATCH /api/salesperson/profile/contact-methods）
- [ ] Loading/Success/Error 狀態處理

**Day 3-4: 客戶聯繫業務員 UI**
- [ ] 業務員檔案頁顯示聯繫方式
  - 直接顯示（電話、Email、LINE、WeChat）
  - 點擊撥打/開啟郵件/複製 ID
- [ ] 「聯繫」按鈕設計
  - 僅 approved 業務員顯示
  - 無聯繫方式時隱藏
- [ ] Modal 表單設計
  - 姓名、Email 自動填入
  - 訊息欄位（10-500 字驗證）
  - 提交 API 整合
- [ ] 成功/失敗訊息處理

**Day 5: E2E Testing**
- [ ] Playwright: 業務員設定聯繫方式流程
- [ ] Playwright: 客戶提交聯繫表單流程
- [ ] Playwright: 錯誤處理（頻率限制、驗證失敗）

**交付物**: Frontend UI 完成，E2E 測試通過

---

#### Week 3: Email Notification + Event Tracking（2026-02-06 ~ 2026-02-12）

**Day 1-2: Email Queue 實作**
- [ ] 建立 Queue Job: `SendContactRequestNotification`
- [ ] 建立 Mailable: `ContactRequestNotificationMail`
- [ ] Email Template 設計（Markdown）
- [ ] 測試 Email 發送（本地 Mailtrap）
- [ ] 配置 Production Email 服務（SendGrid）

**Day 3: Email 重試機制**
- [ ] 配置 Queue 重試策略（3 次，間隔 1/5/15 分鐘）
- [ ] Failed Jobs 監控
- [ ] Email 發送狀態追蹤（email_sent_at）

**Day 4: Event Tracking 實作**
- [ ] 實作 `profile_view` 事件追蹤
- [ ] 實作 `contact_form_submission` 事件追蹤
- [ ] IP 位址 Hash 處理
- [ ] 測試事件寫入效能（< 100ms）

**Day 5: Admin 數據查詢介面**
- [ ] 新增 Admin 頁面: 聯繫請求列表
- [ ] 篩選、排序、分頁
- [ ] 匯出 CSV（未來功能，可選）

**交付物**: Email 通知正常運作，事件追蹤正常運作

---

#### Week 4: Testing + Optimization + Launch（2026-02-13 ~ 2026-02-23）

**Day 1-2: 整合測試**
- [ ] 端到端測試（Frontend → Backend → Email）
- [ ] 效能測試
  - 提交表單 API < 500ms (P95)
  - 追蹤事件寫入 < 100ms
  - 業務員檔案頁載入 < 2s (LCP)
- [ ] 壓力測試
  - 併發提交表單（50 req/s）
  - Rate Limiting 正確運作

**Day 3: Bug Fix + Optimization**
- [ ] 修復測試發現的 Bug
- [ ] 優化查詢效能（如需要）
- [ ] 優化前端載入速度（如需要）

**Day 4: Staging 部署**
- [ ] 部署到 Staging 環境
- [ ] Staging 環境測試
- [ ] UAT (User Acceptance Testing)

**Day 5: Production 部署**
- [ ] 資料庫 Migration (Production)
- [ ] Backend 部署
- [ ] Frontend 部署
- [ ] 監控 Logs、Queue、Email 發送

**Day 6-7: 監控與調整**
- [ ] 監控 KPI（聯繫請求數、Email 發送成功率）
- [ ] 收集用戶回饋
- [ ] 快速修復問題（如有）

**交付物**: 功能上線，監控正常

---

## 8. Dependencies - 依賴項目

### 8.1 技術依賴

| 依賴項目 | 狀態 | 備註 |
|---------|------|------|
| Laravel 11 | ✅ 已存在 | Backend Framework |
| Next.js 15 | ✅ 已存在 | Frontend Framework |
| MySQL 8.0 | ✅ 已存在 | Database |
| Redis | ✅ 已存在 | Queue + Cache |
| SMTP 服務 | ⚠️ 需配置 | Production Email (SendGrid) |

### 8.2 功能依賴

| 依賴功能 | 狀態 | 備註 |
|---------|------|------|
| 業務員檔案系統 | ✅ 已完成 | `salesperson_profiles` 資料表 |
| 使用者認證系統 | ✅ 已完成 | JWT 雙令牌機制 |
| 業務員審核系統 | ✅ 已完成 | `approval_status` 欄位 |

### 8.3 外部依賴

| 依賴服務 | 用途 | 備註 |
|---------|------|------|
| SendGrid | Production Email 發送 | 需申請帳號並配置 API Key |
| Mailtrap | Development Email 測試 | 已有帳號 |

---

## 9. Future Enhancements - 未來規劃

### Phase 2（上線後 2-3 個月）

- **站內聯繫請求列表**（業務員端）
  - 業務員可查看所有收到的聯繫請求
  - 標記「已讀/未讀」、「已回覆/未回覆」
  - 追蹤回覆率

- **聯繫方式隱私控制**
  - 業務員可選擇「直接顯示」或「點擊後顯示」
  - 追蹤聯繫方式點擊事件

- **客戶聯繫記錄查詢**
  - 客戶可查看自己發送的聯繫請求歷史

- **業務員回覆功能**
  - 業務員可在平台內直接回覆客戶
  - 追蹤回覆時間、回覆率

### Phase 3（上線後 6 個月）

- **評分與評論功能**
  - 客戶可對業務員進行評分和評論
  - 評分影響業務員排序和推薦

- **多組聯繫方式**
  - 支援多組電話、Email（工作/個人）

- **聯繫方式驗證**
  - Email 驗證（發送驗證碼）
  - 電話驗證（SMS OTP）

- **LINE/WeChat 深度整合**
  - LINE Official Account API
  - WeChat Mini Program

### Phase 4（上線後 12 個月）

- **站內即時通訊**
  - WebSocket 即時訊息
  - 訊息已讀狀態
  - 檔案傳輸

- **CRM 系統整合**
  - 業務員可將聯繫請求同步到 CRM
  - Salesforce、HubSpot 整合

- **付費功能**
  - 業務員付費獲得更多曝光
  - 優先推薦
  - 聯繫請求優先通知

---

## 10. Metrics & Monitoring - 監控指標

### 10.1 關鍵指標（每日監控）

| 指標 | 監控方法 | 異常閾值 | 處理方式 |
|------|---------|---------|---------|
| **聯繫請求數** | `SELECT COUNT(*) FROM contact_requests WHERE DATE(created_at) = CURDATE()` | < 2 個/天 | 檢查 UI、發送提醒 Email |
| **Email 發送成功率** | Laravel Queue Failed Jobs | < 95% | 檢查 SMTP 服務、Queue 配置 |
| **API 錯誤率** | Laravel Log | > 5% | 檢查錯誤日誌、修復 Bug |
| **提交表單 P95 回應時間** | Laravel Telescope | > 500ms | 優化查詢、增加快取 |

### 10.2 業務指標（每週監控）

| 指標 | 計算方法 | 目標值（第 1 個月） |
|------|---------|-------------------|
| **每週聯繫請求數** | 每週 `contact_requests` 新增筆數 | ≥ 10 個 |
| **瀏覽 → 聯繫轉換率** | `contact_form_submissions / profile_views` | ≥ 5% |
| **業務員回覆率** | 人工追蹤（Email 回覆）| ≥ 60% |
| **有聯繫方式的業務員比例** | `(有至少 1 種聯繫方式的業務員數) / (approved 業務員總數)` | ≥ 80% |

### 10.3 監控工具

| 工具 | 用途 |
|------|------|
| **Laravel Telescope** | API 效能監控、錯誤追蹤 |
| **Laravel Horizon** | Queue 監控、Failed Jobs |
| **MySQL Slow Query Log** | 慢查詢分析 |
| **Google Analytics** | 前端用戶行為分析 |
| **Sentry** | 錯誤追蹤與報警 |

---

## 11. Approval & Sign-off - 審批

| 角色 | 姓名 | 審批項目 | 狀態 | 日期 |
|------|------|---------|------|------|
| Product Manager | - | 功能範圍、業務邏輯 | Pending | - |
| Tech Lead | - | 技術架構、效能要求 | Pending | - |
| QA Lead | - | 測試策略、驗收標準 | Pending | - |
| Security Lead | - | 安全性要求 | Pending | - |

---

## 12. Appendix - 附錄

### 12.1 參考資料

- [Laravel 11 Documentation](https://laravel.com/docs/11.x)
- [Next.js 15 Documentation](https://nextjs.org/docs)
- [Laravel Queue Documentation](https://laravel.com/docs/11.x/queues)
- [Laravel Mail Documentation](https://laravel.com/docs/11.x/mail)

### 12.2 競品分析

| 平台 | 聯繫方式 | 優點 | 缺點 |
|------|---------|------|------|
| LinkedIn | 站內訊息 + InMail | 專業、隱私保護 | 需要付費 Premium |
| 104 人力銀行 | 站內訊息 | 簡單易用 | 僅限求職情境 |
| Facebook Marketplace | Messenger | 即時回應 | 隱私問題、垃圾訊息 |

**YAMU 差異化**:
- 垂直領域（業務員推廣）
- 低門檻（Email 通知即可，不需要即時通訊）
- 隱私保護（必須登入、頻率限制）

### 12.3 FAQ

**Q1: 為什麼不做站內即時通訊？**
A: MVP 階段優先驗證「客戶願意聯繫業務員」，Email 通知足以滿足需求。即時通訊增加開發複雜度（WebSocket、訊息已讀狀態、離線訊息），且需要業務員頻繁登入平台，使用門檻高。

**Q2: 為什麼必須登入才能聯繫？**
A: 保護雙方隱私、防止垃圾訊息、追蹤真實用戶行為。未來可考慮「半匿名」模式（僅提供 Email 即可聯繫）。

**Q3: 為什麼聯繫方式直接顯示而非點擊後顯示？**
A: 降低用戶操作步驟，提升轉換率。未來 Phase 2 可提供業務員選擇「直接顯示」或「點擊後顯示」。

**Q4: 如何防止業務員不回覆客戶？**
A: MVP 階段透過 Email 明確提示回覆，未來追蹤回覆率並公開顯示，回覆率低的業務員降低曝光。

**Q5: 如何追蹤成交率？**
A: MVP 階段暫不追蹤成交（需要業務員手動回報），Phase 2 新增「成交回報」功能。

---

## 13. Changelog - 變更記錄

| 版本 | 日期 | 變更內容 | 作者 |
|------|------|---------|------|
| 1.0 | 2026-01-23 | 初版 Proposal | Development Team |

---

**文檔狀態**: Draft
**下一步**: 等待審批 → 進入 Specification 階段
**相關文檔**: 待建立 `specification.md`
