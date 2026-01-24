# 聯繫機制功能 - 技術規格總覽

**功能代號**: 20260122-add-contact-mechanism
**版本**: 1.0
**狀態**: Specification Complete
**最後更新**: 2026-01-23

---

## 📚 規格文檔總覽

本目錄包含「聯繫機制功能」的完整技術規格，共 4 份專業文檔：

| 文檔 | 檔案 | 行數 | 大小 | 說明 |
|------|------|------|------|------|
| **API 規格** | [`api.md`](./api.md) | 920 | 23KB | 所有 API 端點、請求/回應、驗證規則、錯誤處理 |
| **資料模型規格** | [`data-model.md`](./data-model.md) | 1,078 | 29KB | 資料表設計、索引、Migration、Model 程式碼 |
| **業務規則規格** | [`business-rules.md`](./business-rules.md) | 983 | 23KB | 驗證規則、授權規則、業務流程、Rate Limiting |
| **系統架構規格** | [`architecture.md`](./architecture.md) | 1,169 | 46KB | 系統架構、技術棧、分層設計、Queue、效能優化 |

**總計**: 4,150 行，121 KB

---

## 🎯 快速導航

### 我要了解 API 設計

👉 閱讀 [`api.md`](./api.md)

**包含內容**:
- ✅ 5 個 API 端點完整規格
- ✅ 請求/回應範例（可直接複製）
- ✅ Laravel Validation 程式碼
- ✅ 錯誤處理標準
- ✅ 效能要求（P95 回應時間）
- ✅ 測試案例清單

**關鍵 API**:
1. `PUT /api/salesperson/profile/contact` - 業務員更新聯繫方式
2. `POST /api/contact-requests` - 客戶提交聯繫請求
3. `GET /api/salesperson/{id}/contact-info` - 查詢聯繫資訊
4. `POST /api/events/track` - 追蹤事件
5. `GET /api/admin/contact-requests` - Admin 管理

---

### 我要了解資料庫設計

👉 閱讀 [`data-model.md`](./data-model.md)

**包含內容**:
- ✅ 3 個資料表設計（擴充 1 個 + 新增 2 個）
- ✅ 完整的 Migration 程式碼
- ✅ Model 類別程式碼
- ✅ 索引設計與查詢優化
- ✅ ERD（實體關係圖）
- ✅ 資料範例

**關鍵資料表**:
1. `salesperson_profiles` (擴充) - 新增聯繫方式欄位
2. `contact_requests` (新增) - 聯繫請求記錄
3. `contact_events` (新增) - 事件追蹤

---

### 我要了解業務規則

👉 閱讀 [`business-rules.md`](./business-rules.md)

**包含內容**:
- ✅ 35 條業務規則（編號 BR-XXX）
- ✅ 驗證規則（VR）
- ✅ 授權規則（AR）
- ✅ 業務流程規則（BF）
- ✅ Rate Limiting 規則（RL）
- ✅ Email 發送規則（EM）
- ✅ 安全性規則（SEC）

**關鍵規則**:
- BR-VR-001: 至少提供一種聯繫方式
- BR-RL-002: 24h 內同業務員只能聯繫 1 次
- BR-RL-003: 每天最多提交 5 次
- BR-EM-001: Email 使用非同步 Queue 發送
- BR-EM-002: 失敗自動重試 3 次

---

### 我要了解系統架構

👉 閱讀 [`architecture.md`](./architecture.md)

**包含內容**:
- ✅ 系統架構圖（完整流程）
- ✅ 技術棧選擇與理由
- ✅ Controller → Service → Repository 設計
- ✅ Email Queue 架構
- ✅ Rate Limiting 多層級設計
- ✅ 效能優化策略
- ✅ 安全性設計
- ✅ 部署架構（開發/生產環境）

**關鍵架構**:
- 分層架構（Presentation → Application → Domain → Infrastructure）
- Email Queue（Laravel Queue + Redis + SendGrid）
- Rate Limiting（3 層級：Nginx → Laravel → Business Logic）
- 快取策略（Redis Cache, TTL 5 min）

---

## 🔑 核心技術決策

### ADR-001: 擴充現有資料表而非建立獨立資料表

**決策**: 在 `salesperson_profiles` 新增欄位，而非建立獨立的 `contact_methods` 資料表

**理由**:
- 業務員聯繫方式與個人檔案緊密相關
- 每個業務員僅有一組聯繫方式（MVP 階段）
- 避免 JOIN 查詢，提升效能

---

### ADR-002: IP 位址 Hash 儲存

**決策**: 使用 SHA256 hash 儲存 IP 位址

**理由**:
- 符合 GDPR/PDPA 隱私保護要求
- 仍可追蹤同一 IP 的行為模式
- 無法從 hash 反推出原始 IP

---

### ADR-003: 客戶個資加密儲存

**決策**: 使用 Laravel Encryption 加密 `customer_email` 和 `customer_phone`

**理由**:
- 保護客戶隱私
- 防止資料庫洩漏時個資外洩
- Laravel 內建支援，實作簡單

---

## 📊 規格完整性檢查

### API 規格（api.md）

- [x] 所有 API 端點都有完整文件
- [x] 請求參數定義清楚（型別、必填、驗證規則）
- [x] 回應範例可直接複製使用
- [x] 錯誤回應包含所有 HTTP 狀態碼
- [x] Laravel Validation 程式碼完整
- [x] 效能要求明確（P95 回應時間）
- [x] 測試案例清單完整

---

### 資料模型規格（data-model.md）

- [x] 所有資料表都有完整定義
- [x] 欄位定義包含型別、長度、約束
- [x] 索引設計合理（支援查詢模式）
- [x] Migration 程式碼完整可執行
- [x] Model 類別程式碼完整
- [x] ERD 清晰呈現關聯
- [x] 資料範例完整

---

### 業務規則規格（business-rules.md）

- [x] 所有業務規則都有編號（BR-XXX）
- [x] 規則描述清楚明確
- [x] 實作方式明確（應用層/DB 約束）
- [x] 錯誤處理定義完整（HTTP 狀態碼、錯誤訊息）
- [x] 實作程式碼範例完整
- [x] 測試案例對應規則

---

### 系統架構規格（architecture.md）

- [x] 系統架構圖清晰
- [x] 技術棧選擇有理由說明
- [x] 分層架構設計完整
- [x] Controller/Service/Repository 程式碼範例
- [x] Email Queue 架構詳細
- [x] Rate Limiting 多層級設計
- [x] 效能優化策略明確
- [x] 安全性設計完整
- [x] 部署架構清楚

---

## 🚀 下一步

### 驗證規格完整性

使用規格驗證 Checklist：

```bash
# 參考驗證標準
cat .claude/knowledge/workflow/spec-validation.md
```

**驗證項目**:
- [ ] API 規格檢查清單（40+ 項）
- [ ] DB Schema 檢查清單（30+ 項）
- [ ] 業務規則檢查清單（25+ 項）
- [ ] 效能要求檢查清單（15+ 項）

---

### 進入 Implementation 階段

**準備工作**:
1. ✅ 所有規格文檔已完成
2. ⏳ 規格驗證 Checklist
3. ⏳ 開發環境準備
4. ⏳ 建立 Feature Branch

**實作順序**:
```
Week 1: Backend API + Database
├─ Day 1-2: Database Migration
├─ Day 3-4: API 實作
└─ Day 5: Testing

Week 2: Frontend UI
├─ Day 1-2: 業務員設定聯繫方式 UI
├─ Day 3-4: 客戶聯繫業務員 UI
└─ Day 5: E2E Testing

Week 3: Email Notification + Event Tracking
├─ Day 1-2: Email Queue 實作
├─ Day 3: Email 重試機制
├─ Day 4: Event Tracking 實作
└─ Day 5: Admin 數據查詢介面

Week 4: Testing + Optimization + Launch
├─ Day 1-2: 整合測試
├─ Day 3: Bug Fix + Optimization
├─ Day 4: Staging 部署
└─ Day 5-7: Production 部署 + 監控
```

---

## 📞 問題排查

### 找不到特定規格

**Q: 我要實作 Email 發送，該看哪份文檔？**

A:
1. 先看 [`architecture.md`](./architecture.md) 的「Email Queue 架構」章節
2. 再看 [`business-rules.md`](./business-rules.md) 的「Email 發送規則」章節
3. 最後看 [`api.md`](./api.md) 的「POST /api/contact-requests」的 Side Effects

---

### API 驗證規則不清楚

**Q: 電話號碼的驗證規則是什麼？**

A: 參考 [`business-rules.md`](./business-rules.md) → BR-VR-002: 電話號碼格式驗證

```
允許格式:
- 0912345678 (手機)
- 0912-345-678 (手機)
- 02-12345678 (市話)
- 04-1234567 (市話)

Regex: ^09\d{8}$|^0\d-\d{7,8}$
```

---

### 資料表索引不確定

**Q: contact_requests 資料表需要哪些索引？**

A: 參考 [`data-model.md`](./data-model.md) → contact_requests 資料表 → 索引設計

```sql
-- 複合索引：支援頻率限制查詢
CREATE INDEX idx_user_salesperson_created
ON contact_requests(user_id, salesperson_id, created_at);

-- 索引：業務員查詢自己的聯繫請求
CREATE INDEX idx_salesperson_status_created
ON contact_requests(salesperson_id, status, created_at);

-- 索引：按建立時間排序
CREATE INDEX idx_created_at ON contact_requests(created_at);
```

---

## 📋 規格文檔檢查清單

開始實作前，請確認：

- [ ] 已閱讀所有 4 份規格文檔
- [ ] 了解系統架構圖
- [ ] 了解資料表結構和關聯
- [ ] 了解 API 端點和驗證規則
- [ ] 了解業務規則和頻率限制
- [ ] 了解 Email Queue 流程
- [ ] 了解效能要求（P95 回應時間）
- [ ] 了解安全性要求（加密、Hash、XSS 防護）

---

## 🎓 學習資源

### Laravel 官方文檔

- [Laravel 11 Queue](https://laravel.com/docs/11.x/queues)
- [Laravel 11 Mail](https://laravel.com/docs/11.x/mail)
- [Laravel 11 Validation](https://laravel.com/docs/11.x/validation)
- [Laravel 11 Rate Limiting](https://laravel.com/docs/11.x/rate-limiting)

### 專案內部資源

- [Backend 開發規範](../../../my_profile_laravel/CLAUDE.md)
- [Frontend 開發規範](../../../frontend/CLAUDE.md)
- [OpenSpec Commands](../../../.claude/commands/README.md)

---

## 📝 變更記錄

| 版本 | 日期 | 變更內容 | 作者 |
|------|------|---------|------|
| 1.0 | 2026-01-23 | 初版技術規格總覽 | Development Team |

---

**文檔狀態**: ✅ Specification Complete
**下一步**: 規格驗證 → Implementation
**預計上線**: 2026-02-23 (1 個月)
