# 手動測試清單

**Feature**: 用戶註冊流程重構
**測試日期**: ___________
**測試人員**: ___________

---

## 1. 一般使用者註冊流程

### 1.1 前端註冊頁面
- [ ] 訪問 `/register`
- [ ] 看到「選擇註冊方式」畫面
- [ ] 兩個選項：「一般使用者」和「業務員」
- [ ] UI 符合設計規範（卡片樣式、hover 效果）

### 1.2 一般使用者註冊
- [ ] 點擊「一般使用者」
- [ ] 看到註冊表單（姓名、Email、密碼）
- [ ] 填寫有效資料並送出
- [ ] 看到成功訊息
- [ ] 自動登入並導向首頁 `/`
- [ ] 確認 Token 已儲存（檢查 localStorage）

### 1.3 表單驗證
- [ ] 姓名為空：顯示錯誤「姓名至少需要 2 個字元」
- [ ] Email 格式錯誤：顯示錯誤「請輸入有效的電子郵件」
- [ ] 密碼少於 8 字元：顯示錯誤「密碼至少需要 8 個字元」
- [ ] Email 重複：顯示錯誤訊息

---

## 2. 業務員註冊流程

### 2.1 業務員註冊
- [ ] 訪問 `/register` → 點擊「業務員」
- [ ] 看到業務員註冊表單（6 個欄位）
- [ ] 填寫所有必填欄位（姓名、真實姓名、Email、手機、密碼）
- [ ] 選填欄位：簡介、專長
- [ ] 送出表單
- [ ] 看到成功訊息「註冊成功，請等待管理員審核」
- [ ] 自動登入並導向 dashboard `/dashboard`

### 2.2 表單驗證
- [ ] 手機格式錯誤（非 09 開頭或不是 10 位）：顯示錯誤
- [ ] 所有一般使用者的驗證也適用

### 2.3 pending 狀態
- [ ] Dashboard 顯示「審核中」狀態徽章
- [ ] 無法建立公司（權限檢查）
- [ ] 可以瀏覽其他業務員

---

## 3. 業務員升級流程

### 3.1 一般使用者升級
- [ ] 以一般使用者身份登入
- [ ] 訪問 `/dashboard/salesperson/upgrade`
- [ ] 看到升級表單
- [ ] 填寫業務員資料並送出
- [ ] 看到成功訊息
- [ ] 狀態變為 pending

### 3.2 錯誤處理
- [ ] 已是業務員的使用者訪問升級頁面：自動重定向到 dashboard
- [ ] 表單驗證正確（與業務員註冊相同）

---

## 4. 業務員狀態顯示

### 4.1 pending 狀態
- [ ] 顯示黃色「審核中」卡片
- [ ] 顯示申請時間
- [ ] 提示：「您的業務員申請正在審核中，請耐心等待」

### 4.2 approved 狀態
- [ ] 顯示綠色「已認證業務員」徽章
- [ ] 可以建立公司
- [ ] 可以參與評分（如果實作）

### 4.3 rejected 狀態
- [ ] 顯示紅色「申請未通過」卡片
- [ ] 顯示拒絕原因
- [ ] 如果在等待期內：顯示倒數天數
- [ ] 如果等待期結束：顯示「重新申請」按鈕

---

## 5. 公司建立流程

### 5.1 選擇公司類型
- [ ] approved 業務員訪問 `/dashboard/companies/create`
- [ ] 看到兩個選項：「註冊公司」和「個人工作室」
- [ ] UI 清晰美觀

### 5.2 建立註冊公司
- [ ] 點擊「註冊公司」
- [ ] 輸入統一編號（8 位數字）
- [ ] **情境 A - 統編不存在**:
  - [ ] 顯示「未找到公司」提示
  - [ ] 輸入公司名稱
  - [ ] 點擊「建立公司」
  - [ ] 公司建立成功
  - [ ] 導向 dashboard
- [ ] **情境 B - 統編已存在**:
  - [ ] 顯示「找到既有公司」提示
  - [ ] 顯示公司名稱和統編
  - [ ] 點擊「加入此公司」
  - [ ] 成功加入公司
  - [ ] 導向 dashboard

### 5.3 建立個人工作室
- [ ] 點擊「個人工作室」
- [ ] 輸入工作室名稱
- [ ] 點擊「建立工作室」
- [ ] 工作室建立成功
- [ ] 導向 dashboard

### 5.4 權限檢查
- [ ] pending 業務員無法訪問建立公司頁面（403）
- [ ] 一般使用者無法訪問建立公司頁面（403）

---

## 6. 管理員審核流程

### 6.1 查看申請列表
- [ ] 以管理員身份登入
- [ ] 訪問 `/admin/salesperson-applications`
- [ ] 看到待審核申請列表
- [ ] 每個申請顯示：姓名、Email、手機、專長、申請時間
- [ ] 操作按鈕：「批准」、「拒絕」

### 6.2 批准申請
- [ ] 點擊「批准」按鈕
- [ ] 看到確認提示
- [ ] 確認後，申請狀態更新為 approved
- [ ] 申請從列表中移除（或更新狀態）
- [ ] 業務員端：狀態變為 approved

### 6.3 拒絕申請
- [ ] 點擊「拒絕」按鈕
- [ ] 看到拒絕對話框
- [ ] 輸入拒絕原因（必填）
- [ ] 輸入等待天數（預設 7）
- [ ] 確認拒絕
- [ ] 申請狀態更新為 rejected
- [ ] 業務員端：降回 user 角色，狀態為 rejected

### 6.4 拒絕後重新申請
- [ ] 業務員查看狀態：顯示拒絕原因和等待天數
- [ ] 等待期內：無法重新申請
- [ ] 等待期後：顯示「重新申請」按鈕
- [ ] 點擊重新申請 → 導向升級頁面

---

## 7. 搜尋功能

### 7.1 業務員搜尋
- [ ] 訪問 `/search` 或業務員搜尋頁面
- [ ] 僅顯示 approved 狀態的業務員
- [ ] pending 和 rejected 不出現在搜尋結果
- [ ] 分頁功能正常
- [ ] 篩選功能正常（如有）

---

## 8. API 測試

### 8.1 註冊 APIs
```bash
# 一般使用者註冊
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123"}'

# 業務員註冊
curl -X POST http://localhost:8000/api/auth/register-salesperson \
  -H "Content-Type: application/json" \
  -d '{"name":"Jane","email":"jane@example.com","password":"password123","full_name":"Jane Doe","phone":"0912345678"}'
```

- [ ] 一般使用者註冊：201 status, 返回 token
- [ ] 業務員註冊：201 status, 返回 token + profile
- [ ] 驗證錯誤：422 status

### 8.2 業務員 APIs
```bash
# 升級為業務員
curl -X POST http://localhost:8000/api/salesperson/upgrade \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"full_name":"John Doe","phone":"0912345678"}'

# 查詢狀態
curl http://localhost:8000/api/salesperson/status \
  -H "Authorization: Bearer YOUR_TOKEN"

# 搜尋業務員
curl http://localhost:8000/api/salespeople
```

- [ ] 升級：200 status
- [ ] 查詢狀態：返回 role, salesperson_status
- [ ] 搜尋：僅返回 approved

### 8.3 管理員 APIs
```bash
# 查看申請
curl http://localhost:8000/api/admin/salesperson-applications \
  -H "Authorization: Bearer ADMIN_TOKEN"

# 批准
curl -X POST http://localhost:8000/api/admin/salesperson-applications/1/approve \
  -H "Authorization: Bearer ADMIN_TOKEN"

# 拒絕
curl -X POST http://localhost:8000/api/admin/salesperson-applications/1/reject \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"rejection_reason":"Insufficient experience","reapply_days":7}'
```

- [ ] 查看申請：返回 pending 列表
- [ ] 批准：狀態更新為 approved
- [ ] 拒絕：狀態更新為 rejected，設定等待期

### 8.4 公司 APIs
```bash
# 搜尋公司
curl "http://localhost:8000/api/companies/search?tax_id=12345678"

# 建立公司
curl -X POST http://localhost:8000/api/companies \
  -H "Authorization: Bearer APPROVED_SALESPERSON_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test Company","tax_id":"12345678","is_personal":false}'

# 建立個人工作室
curl -X POST http://localhost:8000/api/companies \
  -H "Authorization: Bearer APPROVED_SALESPERSON_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"My Studio","is_personal":true}'
```

- [ ] 搜尋：返回 exists + companies
- [ ] 建立公司：需 approved salesperson
- [ ] 建立工作室：tax_id 可為 null

---

## 9. 資料庫驗證

### 9.1 Users Table
```sql
SELECT id, name, email, role, salesperson_status, salesperson_applied_at,
       salesperson_approved_at, rejection_reason, can_reapply_at
FROM users
WHERE role = 'salesperson';
```

- [ ] role 正確（user/salesperson/admin）
- [ ] salesperson_status 正確（pending/approved/rejected）
- [ ] 時間戳記正確
- [ ] rejection_reason 在 rejected 時有值
- [ ] can_reapply_at 計算正確

### 9.2 Companies Table
```sql
SELECT id, name, tax_id, is_personal, created_by
FROM companies;
```

- [ ] is_personal 正確
- [ ] tax_id 在註冊公司時有值，個人工作室時為 null
- [ ] tax_id 唯一性約束正常

### 9.3 SalespersonProfiles Table
```sql
SELECT id, user_id, company_id, full_name, phone, bio, specialties
FROM salesperson_profiles;
```

- [ ] company_id 可以為 null
- [ ] 每個 salesperson 都有對應的 profile

---

## 10. 邊界情況測試

### 10.1 同時註冊相同 Email
- [ ] 兩個人同時註冊相同 Email
- [ ] 系統正確處理（其中一個失敗）

### 10.2 統編重複建立
- [ ] 嘗試建立已存在統編的公司
- [ ] 系統阻止並提示已存在

### 10.3 權限邊界
- [ ] pending 業務員嘗試建立公司 → 403
- [ ] rejected 業務員嘗試建立公司 → 403
- [ ] 一般使用者嘗試訪問管理員頁面 → 403

### 10.4 等待期計算
- [ ] 拒絕時設定 7 天等待期
- [ ] 7 天內無法重新申請
- [ ] 7 天後可以重新申請
- [ ] 倒數計時顯示正確

---

## 測試結果總結

**通過**: ___ / ___
**失敗**: ___ / ___
**備註**: _________________________

---

**測試完成日期**: ___________
**簽名**: ___________
