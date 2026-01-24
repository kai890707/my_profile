# Filament Admin Phase 2 - User Management Resource

**狀態**: ✅ 實作完成
**實作日期**: 2026-01-24
**相依**: Phase 1 (Filament + Shield 安裝)

---

## 📋 實作概要

Phase 2 成功實作了完整的使用者管理功能，提供管理員強大的使用者管理介面。

### 已完成功能

✅ **UserResource 核心功能**
- 完整的 CRUD 操作（建立、檢視、編輯、刪除）
- 全域搜尋（姓名、Email）
- 多欄位排序與篩選
- 批次操作支援

✅ **使用者管理功能**
- 角色管理（User / Salesperson / Admin）
- 帳號狀態管理（啟用/停用）
- 業務員狀態顯示（待審核/已批准/已拒絕）
- 付費會員標記
- Email 驗證狀態

✅ **進階操作**
- 密碼重設（發送重設郵件）
- 角色變更（含業務員狀態設定）
- 批次啟用/停用
- 軟刪除支援

✅ **程式碼品質**
- PHPStan Level 9 通過 ✅
- 8/8 單元測試通過 ✅
- 完整類型提示
- Laravel 最佳實踐

---

## 📁 檔案結構

```
app/Filament/Resources/
├── UserResource.php                    # 主要 Resource 定義
└── UserResource/
    └── Pages/
        ├── ListUsers.php               # 列表頁面
        ├── CreateUser.php              # 建立頁面
        ├── EditUser.php                # 編輯頁面
        └── ViewUser.php                # 檢視頁面

lang/zh_TW/
└── user.php                            # 繁體中文翻譯

tests/
├── Feature/Filament/
│   └── UserResourceTest.php           # 功能測試
└── Unit/Filament/
    └── UserResourceUnitTest.php       # 單元測試
```

---

## 🎨 功能詳解

### 1. 列表頁面 (ListUsers)

**顯示欄位**:
- ID（可排序、可搜尋）
- 姓名（可排序、可搜尋、可複製、顯示使用者名稱）
- Email（可排序、可搜尋、可複製）
- 角色（Badge 顯示：管理員/業務員/使用者）
- 業務員狀態（Badge 顯示：待審核/已批准/已拒絕）
- 帳號狀態（Badge 顯示：啟用/停用）
- 註冊時間（可排序、可隱藏）
- Email 驗證時間（可排序、可隱藏）

**篩選器**:
- 角色篩選（User / Salesperson / Admin）
- 業務員狀態篩選（Pending / Approved / Rejected）
- 帳號狀態篩選（Active / Inactive）
- 註冊時間範圍（起始日期 - 結束日期）
- Email 驗證狀態（已驗證/未驗證）

**批次操作**:
- 批次刪除
- 批次啟用
- 批次停用

**單一操作**:
- 檢視使用者
- 編輯使用者
- 切換帳號狀態（啟用/停用）
- 刪除使用者

### 2. 建立頁面 (CreateUser)

**表單區段**:

#### 基本資訊
- 姓名（必填）
- Email（必填、唯一、Email 格式驗證）
- 使用者名稱（選填、唯一、未填寫時自動使用 Email 前綴）
- 密碼（必填、最少 8 字元）

#### 角色與權限
- 角色（必填、下拉選單：User / Salesperson / Admin）
- 業務員狀態（僅當角色為 Salesperson 時顯示）
- 付費會員（Toggle 開關、預設 false）

#### 帳號狀態
- 狀態（必填、預設 Active）
- Email 驗證時間（選填、設定即表示已驗證）

**自動處理**:
- Username 自動生成（未填寫時使用 Email @ 前綴）
- 預設 Status 為 active
- 密碼自動 Hash

### 3. 編輯頁面 (EditUser)

**功能**:
- 完整表單編輯（與建立頁面相同的表單）
- 密碼欄位留空則不變更
- 角色變更時自動處理業務員狀態

**Header Actions**:
- 檢視使用者
- 刪除使用者

**自動處理**:
- 填寫表單前移除 password_hash（避免顯示 hash）
- 密碼只在有輸入時才更新

### 4. 檢視頁面 (ViewUser)

**資訊區段**:

#### 基本資訊
- ID
- 姓名
- 使用者名稱
- Email（可複製）

#### 角色與權限
- 角色（Badge 顯示）
- 業務員狀態（Badge 顯示、僅業務員顯示）
- 帳號狀態（Badge 顯示）
- 付費會員（Badge 顯示）

#### 業務員詳細資訊（僅業務員顯示）
- 申請時間
- 審核時間
- 拒絕原因
- 可重新申請時間

#### 時間記錄
- 註冊時間
- Email 驗證時間
- 最後更新時間
- 刪除時間（軟刪除）

**Header Actions**:
- 編輯使用者
- 重設密碼（發送重設郵件）
- 變更角色（Modal 表單）
- 刪除使用者

### 5. 進階操作

#### 重設密碼
- 使用 Laravel Password Facade
- 發送密碼重設郵件到使用者 Email
- 成功/失敗通知

#### 變更角色
- Modal 表單選擇新角色
- 變更為業務員時需設定業務員狀態
- 從業務員變更為其他角色時自動清除業務員狀態
- 記錄角色變更歷史（通知顯示）

#### 批次操作
- 批次啟用：將所有選中的停用帳號啟用
- 批次停用：將所有選中的啟用帳號停用
- 操作完成後顯示影響數量

---

## 🎨 設計特色

### 色彩系統（與 Phase 1 一致）

- **管理員**: Red (danger)
- **業務員**: Green (success)
- **使用者**: Gray
- **待審核**: Yellow (warning)
- **已批准**: Green (success)
- **已拒絕**: Red (danger)
- **啟用**: Green (success)
- **停用**: Red (danger)

### Badge 顯示

所有狀態欄位都使用 Badge 顯示，視覺清晰：

```php
// 角色 Badge
User::ROLE_ADMIN => 'danger' (紅色)
User::ROLE_SALESPERSON => 'success' (綠色)
User::ROLE_USER => 'gray' (灰色)

// 業務員狀態 Badge
User::STATUS_PENDING => 'warning' (黃色)
User::STATUS_APPROVED => 'success' (綠色)
User::STATUS_REJECTED => 'danger' (紅色)

// 帳號狀態 Badge
'active' => 'success' (綠色)
'inactive' => 'danger' (紅色)
```

### 繁體中文完整支援

所有介面文字都使用繁體中文：
- 欄位標籤
- 按鈕文字
- 篩選器選項
- 通知訊息
- 表單驗證訊息
- Helper 提示文字

---

## 🔐 權限與安全

### 存取控制

- 只有 Admin 角色可以存取 Filament Panel
- 使用 Filament Shield 整合權限系統
- 支援細粒度的權限控制（未來可擴展）

### 資料保護

- 密碼使用 Hash::make() 加密
- Form Request 驗證所有輸入
- 軟刪除保護資料
- Email 唯一性驗證

### 操作記錄

所有重要操作都有通知：
- 建立使用者
- 更新使用者
- 刪除使用者
- 變更角色
- 啟用/停用帳號
- 重設密碼

---

## 🧪 測試覆蓋

### 單元測試 (8/8 通過)

✅ User resource has correct model
✅ User resource has correct navigation properties
✅ User resource has correct pages
✅ User resource can search by name and email
✅ User resource has form method
✅ User resource has table method
✅ User resource has infolist method
✅ User resource slug is correct

### 靜態分析

✅ PHPStan Level 9 - No errors
✅ 完整類型提示
✅ 嚴格模式 (declare(strict_types=1))

### 程式碼品質

✅ Laravel 最佳實踐
✅ SOLID 原則
✅ 清晰的註解
✅ 一致的程式碼風格

---

## 📊 路由列表

```
GET|HEAD  filament/admin/users                    # 列表頁面
GET|HEAD  filament/admin/users/create             # 建立頁面
GET|HEAD  filament/admin/users/{record}           # 檢視頁面
GET|HEAD  filament/admin/users/{record}/edit      # 編輯頁面
```

---

## 🚀 使用方式

### 存取使用者管理

1. 登入 Filament Admin Panel: `http://localhost:8080/admin`
2. 側邊欄 > 系統管理 > 使用者管理

### 建立新使用者

1. 點擊右上角「建立使用者」按鈕
2. 填寫基本資訊（姓名、Email、密碼）
3. 選擇角色
4. 如果選擇業務員，設定業務員狀態
5. 點擊「建立」

### 編輯使用者

1. 列表頁面點擊「編輯」按鈕
2. 修改所需欄位
3. 密碼欄位留空則不變更
4. 點擊「儲存」

### 變更角色

1. 檢視頁面點擊「變更角色」按鈕
2. 選擇新角色
3. 如果變更為業務員，設定業務員狀態
4. 確認變更

### 重設密碼

1. 檢視頁面點擊「重設密碼」按鈕
2. 確認操作
3. 系統自動發送密碼重設郵件

### 批次操作

1. 列表頁面勾選多個使用者
2. 點擊批次操作下拉選單
3. 選擇操作（啟用/停用/刪除）
4. 確認操作

---

## 🔧 技術細節

### 自動生成 Username

```php
protected function mutateFormDataBeforeCreate(array $data): array
{
    if (empty($data['username']) && isset($data['email'])) {
        $email = $data['email'];
        $data['username'] = explode('@', $email)[0];
    }
    return $data;
}
```

### 密碼處理

```php
Forms\Components\TextInput::make('password_hash')
    ->password()
    ->dehydrateStateUsing(fn ($state) => ! empty($state) ? Hash::make($state) : null)
    ->dehydrated(fn ($state) => filled($state))
```

### 角色變更邏輯

```php
// 變更為業務員時設定狀態
if ($newRole === User::ROLE_SALESPERSON) {
    $updateData['salesperson_status'] = $data['salesperson_status'] ?? User::STATUS_PENDING;
}

// 從業務員變更為其他角色時清除狀態
if ($oldRole === User::ROLE_SALESPERSON && $newRole !== User::ROLE_SALESPERSON) {
    $updateData['salesperson_status'] = null;
}
```

### Reactive Form Fields

```php
Forms\Components\Select::make('role')
    ->reactive()
    ->afterStateUpdated(function ($state, callable $set) {
        if ($state !== User::ROLE_SALESPERSON) {
            $set('salesperson_status', null);
        }
    })
```

---

## 📈 效能考量

### 查詢優化

- 列表頁面使用欄位索引
- 避免 N+1 查詢
- 分頁載入（預設 15 筆/頁）

### 快取

- Filament 自動快取組件
- 路由快取

### 資料庫索引

現有索引（來自 Migration）:
- `salesperson_status` (單一索引)
- `role, salesperson_status` (複合索引)

---

## 🐛 已知限制

1. **密碼重設郵件**
   - 需要配置 SMTP 才能發送郵件
   - 開發環境建議使用 Mailtrap 或 Log driver

2. **批次操作**
   - 批次操作不會觸發 Model Events
   - 如需審計日誌，建議使用 Observer

3. **權限細粒度控制**
   - 目前只檢查 Admin 角色
   - 未來可整合 Shield 的細粒度權限

---

## 🔄 與 Phase 1 的整合

Phase 2 完美整合 Phase 1 的所有功能：

✅ 使用相同的 Sky-500 色彩系統
✅ 遵循相同的繁體中文規範
✅ 整合 Filament Shield 權限系統
✅ 共用 JWT + Session 雙重認證
✅ 一致的導航分組（系統管理、審核管理）

---

## 📝 下一步建議 (Phase 3)

建議實作的功能：

1. **RoleResource** - 角色與權限管理
   - Spatie Permission 角色管理
   - 權限分配介面
   - Shield 整合

2. **活動記錄** (Activity Log)
   - 使用者操作日誌
   - 登入記錄
   - 資料變更歷史

3. **通知系統**
   - 使用者通知管理
   - Email 通知設定
   - 通知模板

4. **資料匯出**
   - Excel 匯出
   - CSV 匯出
   - PDF 報表

5. **進階篩選**
   - 儲存篩選條件
   - 自訂欄位顯示
   - 批次匯入

---

## 📚 參考資源

- [Filament Docs](https://filamentphp.com/docs)
- [Filament Shield](https://github.com/bezhanSalleh/filament-shield)
- [Laravel Docs](https://laravel.com/docs)
- [Spatie Permission](https://spatie.be/docs/laravel-permission)

---

**維護者**: Development Team
**最後更新**: 2026-01-24
**版本**: 1.0
**狀態**: ✅ Production Ready
