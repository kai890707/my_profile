# Filament Phase 1: 完整技術規格

**專案**: YAMU Backend - Filament Admin Panel Migration
**Phase**: 1 - 基礎設施與業務員申請審核功能
**狀態**: ✅ 規格完成，待實作
**預估時間**: 1.5 小時

---

## 📋 Phase 1 範圍總覽

### 目標

建立 Filament Admin Panel 基礎架構，實作**業務員申請審核**功能 Resource，作為後續 Phase 的基礎。

### 核心功能

✅ **已規劃功能**:
1. Filament v3 安裝與配置
2. 雙認證整合 (JWT API + Session Admin)
3. Permission System (Spatie + Shield)
4. SalespersonApplicationResource (審核功能)
5. Theme 客製化 (品牌色彩、Logo)
6. 完整測試覆蓋 (>= 80%)

❌ **Phase 2+ 功能** (不在範圍內):
- 其他 Resources (Company, Experience, Certification, User)
- Dashboard Widgets
- Email 通知
- 審核歷史記錄
- 進階 Analytics

---

## 📚 規格文檔索引

### 1. [installation.md](./installation.md) - 安裝與設定

**包含**:
- Composer Packages 清單
- 逐步安裝指南
- Filament Panel 配置
- Auth Guard 配置
- 環境變數設定
- 驗證步驟

**預估時間**: 30 分鐘

**產出**:
- Filament 3.2+ 已安裝
- Shield 3.2+ 已安裝
- `admin_session` guard 已配置
- Admin 使用者已創建
- 可訪問 `/filament/admin`

---

### 2. [authentication.md](./authentication.md) - 認證架構

**包含**:
- 雙認證策略 (JWT vs Session)
- Guards 配置詳解
- User Model 整合 (FilamentUser 介面)
- Permission System 配置
- Role & Permission Seeder
- 安全最佳實踐

**關鍵設計**:
```
JWT Auth (API)           Session Auth (Filament)
     ↓                           ↓
  api guard              admin_session guard
     ↓                           ↓
  jwt driver             session driver
     ↓                           ↓
        ───────→ users table ←───────
                 (共用 Provider)
```

**驗收標準**:
- [ ] JWT API 認證不受影響
- [ ] Filament Session 認證正常運作
- [ ] 兩個 Guard 完全隔離
- [ ] Permission 系統正常運作

---

### 3. [salesperson-application-resource.md](./salesperson-application-resource.md) - Resource 規格

**包含**:
- Resource 定義與配置
- Table Columns (7 個欄位)
- Filters (3 個篩選器)
- Actions (Approve, Reject, View)
- Bulk Actions (Bulk Approve)
- Infolist (詳情頁面，5 個 Section)
- Page Classes (List, View)
- Permissions 定義

**核心功能**:
1. **列表頁面**:
   - 顯示 `salesperson_status = 'pending'` 的申請
   - 搜尋: 姓名、Email、電話
   - 篩選: 日期範圍、等待時間、是否有公司
   - 排序: 按申請時間 (FIFO)
   - Navigation Badge: 顯示待審核數量

2. **審核 Actions**:
   - **Approve**: 批准業務員申請
   - **Reject**: 拒絕申請（需填寫原因 + 可重新申請天數）
   - **Bulk Approve**: 批量批准

3. **詳情頁面**:
   - 基本資料、業務員資訊、聯絡方式、審核狀態、系統資訊

**預估時間**: 1 小時

**驗收標準**:
- [ ] 列表正確顯示待審核申請
- [ ] 搜尋、篩選、排序功能正常
- [ ] 批准/拒絕功能正常
- [ ] 批量批准功能正常
- [ ] 詳情頁面顯示完整資訊
- [ ] Navigation Badge 正確顯示數量

---

### 4. [theme.md](./theme.md) - Theme 客製化

**包含**:
- 品牌色彩 (Primary: #0EA5E9 Sky-500)
- Logo 設定 (文字或圖片)
- 繁體中文 Locale
- Navigation 結構
- Dark Mode 設定 (Phase 1 禁用)
- Notification 樣式
- Favicon 設定

**設計系統一致性**:
| 元素 | Next.js | Filament | 狀態 |
|------|---------|----------|------|
| Primary | #0EA5E9 | #0EA5E9 | ✅ 一致 |
| Success | #10B981 | #10B981 | ✅ 一致 |
| Warning | #F59E0B | #F59E0B | ✅ 一致 |
| Danger | #EF4444 | #EF4444 | ✅ 一致 |

**預估時間**: 15 分鐘

---

### 5. [database.md](./database.md) - 資料庫規格

**包含**:
- 現有資料表參考 (users, salesperson_profiles)
- 新增資料表 (Spatie Permission: roles, permissions, etc.)
- 索引設計
- 關鍵查詢 SQL
- Performance 優化策略
- FilamentAdminSeeder

**新增資料表** (5 個):
1. `roles` - 角色表
2. `permissions` - 權限表
3. `model_has_roles` - User-Role 關聯
4. `model_has_permissions` - User-Permission 關聯
5. `role_has_permissions` - Role-Permission 關聯

**Default Roles**:
- `super_admin` - 所有權限
- `admin` - 除刪除外的所有權限
- `reviewer` - 僅查看和審核權限

**Default Permissions** (9 個):
- `view_salesperson::application`
- `view_any_salesperson::application`
- `create_salesperson::application`
- `update_salesperson::application`
- `delete_salesperson::application`
- `delete_any_salesperson::application`
- `approve_salesperson::application`
- `reject_salesperson::application`
- `bulk_approve_salesperson::application`

---

### 6. [testing.md](./testing.md) - 測試規格

**包含**:
- Testing Strategy (Test Pyramid)
- 5 類測試案例 (Installation, Auth, Resource, Permission, Performance)
- Manual Testing Checklist
- Test Coverage Goals (>= 80%)

**測試類別**:
1. **Installation Tests** (5 個測試)
2. **Authentication Tests** (6 個測試)
3. **Salesperson Application Resource Tests** (11 個測試)
4. **Permission Tests** (4 個測試)
5. **Performance Tests** (3 個測試)

**Total**: 29 個自動化測試

**覆蓋率目標**:
- Feature Tests: >= 80%
- Unit Tests: >= 90%
- Overall: >= 80%

---

## 🎯 實作順序 (建議)

### Step 1: 環境準備 (30 分鐘)

**依據**: [installation.md](./installation.md)

```bash
# 1. 安裝 Filament Packages
composer require filament/filament:"^3.2"
composer require bezhansalleh/filament-shield:"^3.2"

# 2. 執行安裝
php artisan filament:install --panels
php artisan shield:install

# 3. 執行 Migrations
php artisan migrate

# 4. 建立 Admin User
php artisan make:filament-user

# 5. 驗證
訪問 http://localhost:8080/filament/admin
```

**檢查點**:
- [ ] 可訪問 Filament 登入頁面
- [ ] Admin 帳號可登入
- [ ] API JWT 認證仍正常運作

---

### Step 2: 認證整合 (已包含在 Step 1)

**依據**: [authentication.md](./authentication.md)

```php
// 1. 更新 config/auth.php (新增 admin_session guard)
'guards' => [
    'admin_session' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
],

// 2. 更新 User Model (實作 FilamentUser 介面)
class User extends Authenticatable implements JWTSubject, FilamentUser
{
    use HasRoles;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === self::ROLE_ADMIN && $this->status === 'active';
    }
}

// 3. 執行 Seeder
php artisan db:seed --class=FilamentAdminSeeder
```

**檢查點**:
- [ ] `admin_session` guard 已新增
- [ ] User Model 實作 FilamentUser
- [ ] Roles 和 Permissions 已建立

---

### Step 3: Resource 開發 (1 小時)

**依據**: [salesperson-application-resource.md](./salesperson-application-resource.md)

```bash
# 1. 建立 Resource
php artisan make:filament-resource SalespersonApplication \
    --model=User \
    --view

# 2. 實作 Resource
# - Table Columns
# - Filters
# - Actions (Approve, Reject)
# - Bulk Actions
# - Infolist

# 3. 建立 Page Classes
# - ListSalespersonApplications
# - ViewSalespersonApplication
```

**檢查點**:
- [ ] Resource 檔案已建立
- [ ] Table 正確顯示待審核申請
- [ ] Actions 正常運作
- [ ] Permissions 檢查正確

---

### Step 4: Theme 客製化 (15 分鐘)

**依據**: [theme.md](./theme.md)

```php
// 更新 AdminPanelProvider.php
->colors([
    'primary' => Color::hex('#0EA5E9'),
])
->brandName('YAMU Admin')
->locale('zh_TW')
->darkMode(false)
```

**檢查點**:
- [ ] Primary Color 設為 #0EA5E9
- [ ] Brand Name 顯示正確
- [ ] Locale 設為繁體中文

---

### Step 5: 測試 (30 分鐘)

**依據**: [testing.md](./testing.md)

```bash
# 1. 執行自動化測試
php artisan test --filter=Filament

# 2. 檢查覆蓋率
php artisan test --coverage

# 3. Manual Testing
# - 登入流程
# - 列表頁面
# - 審核操作
# - 效能測試
```

**檢查點**:
- [ ] 所有自動化測試通過
- [ ] 覆蓋率 >= 80%
- [ ] Manual Testing 全部通過

---

## ✅ Phase 1 完成標準

### Functional Requirements

- [ ] Filament Admin Panel 可正常訪問
- [ ] Admin 可登入 Filament
- [ ] JWT API 認證不受影響
- [ ] 待審核業務員列表正確顯示
- [ ] 搜尋、篩選、排序功能正常
- [ ] 批准功能正常 (狀態更新、時間記錄)
- [ ] 拒絕功能正常 (需填寫原因、可重新申請時間)
- [ ] 批量批准功能正常
- [ ] 詳情頁面顯示完整資訊
- [ ] Navigation Badge 正確顯示待審核數量

### Non-Functional Requirements

- [ ] 列表頁面載入 < 2s
- [ ] 操作回應時間 < 300ms
- [ ] 無 N+1 Query 問題
- [ ] 查詢數 <= 15 queries/page
- [ ] Permission 檢查使用快取
- [ ] Theme 符合品牌設計
- [ ] 繁體中文介面

### Testing Requirements

- [ ] 29+ 自動化測試全部通過
- [ ] Test Coverage >= 80%
- [ ] Manual Testing Checklist 全部通過
- [ ] PHPStan Level 9 無錯誤

### Documentation Requirements

- [ ] 所有規格文件完整
- [ ] README 索引清晰
- [ ] Installation Guide 可執行
- [ ] Testing Guide 可複製

---

## 📊 Performance Benchmarks

### Target Metrics (參考 metrics-standards.md)

| 指標 | 目標值 | 測量工具 |
|------|--------|----------|
| Login Page LCP | < 1s | Chrome DevTools |
| List Page Load | < 2s | Laravel Debugbar |
| Approve Action | < 300ms | Laravel Debugbar |
| Reject Action | < 300ms | Laravel Debugbar |
| DB Queries/Page | <= 15 | Laravel Debugbar |
| Permission Check | < 10ms | Spatie Cache |

---

## 🐛 Known Limitations (Phase 1)

**Phase 1 刻意不實作**的功能 (留待 Phase 2+):

1. **Dashboard Widgets** - 統計卡片、圖表 (Phase 2)
2. **Email Notifications** - 審核結果通知 (Phase 2)
3. **Audit Log** - 操作記錄查看 (Phase 3)
4. **Other Resources** - Company, Experience, Certification (Phase 2-3)
5. **Advanced Analytics** - 複雜統計圖表 (Phase 3)
6. **Export/Import** - 批量匯入匯出 (Phase 4)
7. **Dark Mode** - 深色主題 (Phase 2)

**理由**:
- 保守策略，確保 1.5 小時內完成
- 先建立穩固基礎，再逐步擴展
- 避免過度複雜化，降低失敗風險

---

## 🔄 Next Steps (Phase 2 Preview)

**Phase 2 預計功能**:
1. Dashboard Widgets (Stats Cards, Charts)
2. CompanyResource (公司管理與審核)
3. ExperienceResource (工作經驗審核)
4. CertificationResource (證照審核)
5. Email Notifications (審核結果通知)

**預估時間**: Phase 2 需要 2 小時

---

## 📞 Support & References

### Internal Resources

- **Proposal**: `../proposal.md`
- **Project CLAUDE.md**: `../../CLAUDE.md`
- **Backend CLAUDE.md**: `../../../my_profile_laravel/CLAUDE.md`

### External Resources

- [Filament v3 Documentation](https://filamentphp.com/docs/3.x/panels)
- [Filament Shield Plugin](https://github.com/bezhanSalleh/filament-shield)
- [Spatie Permission](https://spatie.be/docs/laravel-permission/v6)

### Team Contacts

- **Backend Lead**: Backend Team
- **QA Lead**: QA Team
- **PM**: Product Team

---

## 📝 Change Log

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2026-01-24 | Backend Team | 初始版本，Phase 1 完整規格 |

---

**文檔狀態**: ✅ 規格完成
**實作狀態**: ⏳ 待開始
**預計完成時間**: 1.5 小時

**準備開始實作？請確認以上所有規格文件已完整閱讀並理解。**

---

**版本**: 1.0
**最後更新**: 2026-01-24
**負責人**: Backend Team
