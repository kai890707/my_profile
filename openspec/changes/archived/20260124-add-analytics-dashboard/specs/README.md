# Analytics Dashboard - Backend Specifications

**Feature**: Analytics Dashboard
**Version**: 1.0
**Status**: Specification Complete ✅
**Date**: 2026-01-24

---

## 📋 規格文檔總覽

本目錄包含 Analytics Dashboard 功能的完整 Backend 技術規格，共 4 份文檔：

| 文檔 | 檔案 | 大小 | 說明 |
|------|------|------|------|
| **API 規格** | [api.md](./api.md) | 23 KB | 7 個 API 端點完整定義 |
| **資料模型規格** | [data-model.md](./data-model.md) | 23 KB | 1 張新表 + 完整 Migration & Model |
| **業務規則規格** | [business-rules.md](./business-rules.md) | 26 KB | 18 條業務規則 + 測試案例 |
| **系統架構規格** | [architecture.md](./architecture.md) | 31 KB | 混合彙總策略 + 效能優化 |

**總規格量**: 103 KB，預估 25,000+ 字

---

## 🎯 規格完整性檢查

### ✅ API 規格 (api.md)

**業務員端點** (3 個):
- [x] `GET /api/salesperson/analytics/stats` - 統計數據
- [x] `GET /api/salesperson/analytics/trends` - 趨勢圖表
- [x] `GET /api/salesperson/analytics/recent-contacts` - 最近聯繫

**管理員端點** (4 個):
- [x] `GET /api/admin/analytics/overview` - 平台概覽
- [x] `GET /api/admin/analytics/top-salespersons` - Top 10 業務員
- [x] `GET /api/admin/analytics/activity` - 活躍度分析
- [x] `GET /api/admin/analytics/growth` - 成長趨勢

**每個端點包含**:
- [x] 完整路徑和 HTTP 方法
- [x] Authentication / Authorization 要求
- [x] Query Parameters 定義 (含驗證規則)
- [x] Success Response (200) - 完整 JSON 範例
- [x] Error Responses (400/401/403/500) - 含錯誤碼
- [x] 業務規則編號 (BR-XXX)
- [x] 效能要求 (P50/P95/P99 回應時間)
- [x] 範例請求 (curl)

---

### ✅ 資料模型規格 (data-model.md)

**新增資料表** (1 張):
- [x] `daily_analytics` - 每日數據彙總表

**每張表包含**:
- [x] 完整欄位定義 (名稱、型別、約束、說明)
- [x] 所有索引定義 (PRIMARY, UNIQUE, INDEX, FOREIGN KEY)
- [x] 完整 CREATE TABLE SQL
- [x] 完整 Laravel Migration 程式碼
- [x] 完整 Eloquent Model 類別 (含 fillable, casts, relationships)
- [x] Model Factory 程式碼
- [x] 查詢範例 (SQL + Eloquent)
- [x] 資料量估算

**現有資料表使用**:
- [x] `contact_events` - 事件追蹤（即時查詢）
- [x] `contact_requests` - 聯繫請求（最近聯繫列表）
- [x] `users` - 業務員資料

---

### ✅ 業務規則規格 (business-rules.md)

**業務規則總數**: 18 條

**資料彙總規則** (4 條):
- [x] BR-001: 每日彙總執行時機
- [x] BR-002: 彙總計算邏輯
- [x] BR-003: 重複執行處理 (UPSERT)
- [x] BR-004: 手動觸發彙總

**查詢規則** (3 條):
- [x] BR-005: 時間範圍處理
- [x] BR-006: 混合查詢策略
- [x] BR-007: 趨勢數據填充零值

**計算規則** (3 條):
- [x] BR-008: 轉換率計算
- [x] BR-009: 增長率計算
- [x] BR-010: 上個時段計算

**權限規則** (2 條):
- [x] BR-011: 業務員只能查看自己的數據
- [x] BR-012: 管理員可以查看所有數據

**驗證規則** (1 條):
- [x] BR-013: Range 參數驗證

**錯誤處理規則** (3 條):
- [x] BR-014: 無數據時返回零值
- [x] BR-015: 彙總任務失敗處理
- [x] BR-016: 資料一致性檢查

**特殊情境規則** (2 條):
- [x] BR-017: 業務員被刪除處理
- [x] BR-018: 只統計 Approved 業務員

**每條規則包含**:
- [x] 規則編號 (BR-XXX)
- [x] 規則描述
- [x] 實作方式 (DB 約束 / 應用層 / 排程任務)
- [x] 程式碼範例
- [x] 測試案例

---

### ✅ 系統架構規格 (architecture.md)

**架構設計**:
- [x] 系統架構圖 (完整的分層架構)
- [x] 混合彙總策略說明 (為何選擇？如何運作？)
- [x] 資料流向圖
- [x] 架構決策理由 (ADR)

**排程任務設計**:
- [x] Command 類別完整程式碼
- [x] Laravel Scheduler 配置
- [x] 錯誤處理與重試邏輯
- [x] 執行監控設計

**效能優化**:
- [x] 資料庫索引策略
- [x] 查詢優化技巧 (避免 N+1、使用 selectRaw)
- [x] 快取層設計 (可選)
- [x] 效能比較表 (純即時查詢 vs 混合模式)

**安全性架構**:
- [x] Rate Limiting 設定
- [x] 資料權限控制 (Middleware + Policy)
- [x] 敏感資料保護 (IP hash, Encrypted Cast)
- [x] API 安全措施

**擴展性設計**:
- [x] 資料成長預估 (第 1-3 年)
- [x] 水平擴展策略 (讀寫分離)
- [x] 資料清理策略

**監控與告警**:
- [x] 監控指標定義
- [x] 告警閾值設定

---

## 📊 規格統計

### 量化指標

**API 端點**:
- 業務員端點: 3 個
- 管理員端點: 4 個
- 總計: 7 個 API

**資料庫**:
- 新增資料表: 1 張 (`daily_analytics`)
- 新增索引: 4 個
- Migration 程式碼: 1 個檔案
- Model 類別: 1 個檔案

**業務規則**:
- 總規則數: 18 條
- 測試案例: 18+ 個

**程式碼範例**:
- Controller 範例: 7 個方法
- Service 範例: 5 個方法
- Migration 範例: 1 個完整檔案
- Model 範例: 1 個完整類別
- Command 範例: 1 個完整類別
- 測試範例: 18+ 個

### 效能要求

**API 回應時間**:
- 業務員統計 API: P95 < 200ms
- 業務員趨勢 API: P95 < 300ms
- 業務員聯繫列表 API: P95 < 100ms
- 管理員概覽 API: P95 < 500ms
- 管理員 Top 10 API: P95 < 400ms
- 管理員活躍度 API: P95 < 300ms
- 管理員成長趨勢 API: P95 < 500ms

**併發處理**:
- 業務員端點: 60 req/min/user
- 管理員端點: 120 req/min/user

**資料準確性**:
- 統計數據: 100% 準確 (與實際事件一致)
- 彙總一致性: 允許誤差 < 1%

---

## 🔧 技術棧

**Backend**:
- Laravel 11
- PHP 8.4
- MySQL 8.0
- Redis (可選，用於快取)

**認證**:
- JWT (Access + Refresh Token)

**排程**:
- Laravel Scheduler (Cron Jobs)

**測試**:
- Pest 3.x
- PHPStan Level 9

---

## 📝 開發準備

規格已完整，可直接進入實作階段。

**下一步**:
1. ✅ 規格驗證 (使用 `spec-validation.md`)
2. ⏳ 任務拆解 (拆解為 40-50 個可執行任務)
3. ⏳ 實作開發 (自動執行所有任務)
4. ⏳ 測試驗證 (Feature Tests >= 95% 覆蓋率)
5. ⏳ 歸檔規格 (歸檔到 `openspec/specs/backend/`)

**預估開發時間**:
- Migration + Model: 2 小時
- Command + Scheduler: 3 小時
- Service Layer: 4 小時
- Controllers + Routes: 4 小時
- Tests: 6 小時
- **總計**: ~19 小時 (約 2-3 個工作天)

---

## 📚 相關文檔

- [Proposal](../proposal.md) - 功能需求和驗收標準
- [API 規格](./api.md) - 完整 API 端點定義
- [資料模型規格](./data-model.md) - 資料庫設計
- [業務規則規格](./business-rules.md) - 業務邏輯規則
- [系統架構規格](./architecture.md) - 技術架構設計

---

## ✅ 規格品質檢查

### 完整性 ✅
- [x] 所有 API 端點都有完整文件
- [x] 所有資料表都有完整 Schema + Migration + Model
- [x] 所有業務規則都有明確定義和測試案例
- [x] 所有架構決策都有理由說明

### 明確性 ✅
- [x] Request/Response 範例可直接複製使用
- [x] SQL 和 Migration 程式碼可直接執行
- [x] 業務規則無歧義，開發人員無需猜測
- [x] 所有「快」、「好」等詞已量化 (P95 < Xms)

### 可測試性 ✅
- [x] 每個 API 都有測試案例
- [x] 每條業務規則都可驗證
- [x] 效能指標已量化 (P50/P95/P99)
- [x] 測試覆蓋率目標已定義 (>= 95%)

### 一致性 ✅
- [x] 遵循 Laravel 11 conventions
- [x] 遵循 PSR-12 程式碼風格
- [x] API Response 格式統一
- [x] 錯誤處理模式一致

---

**規格狀態**: ✅ 完整且可執行
**可進入階段**: Implementation (Step 4)

**最後更新**: 2026-01-24
**維護者**: Backend Team
