# Filament Phase 1: Theme 客製化規格

**專案**: YAMU Backend - Filament Admin Panel
**Phase**: 1 - 品牌色彩與 UI 設定
**預估時間**: 15 分鐘

---

## 🎨 Brand Colors

### Primary Color

**顏色**: Sky-500 (#0EA5E9)

**選擇理由**:
- 符合現有 YAMU 設計系統
- 與 Next.js Frontend 保持一致
- 專業且易於識別

### Color Palette

```php
// app/Providers/Filament/AdminPanelProvider.php

use Filament\Support\Colors\Color;

->colors([
    'primary' => Color::hex('#0EA5E9'),    // Sky-500 (品牌色)
    'secondary' => Color::hex('#14B8A6'),  // Teal-500
    'success' => Color::hex('#10B981'),    // Green-500
    'warning' => Color::hex('#F59E0B'),    // Amber-500
    'danger' => Color::hex('#EF4444'),     // Red-500
    'info' => Color::hex('#3B82F6'),       // Blue-500
    'gray' => Color::Slate,                // Slate (系統灰)
])
```

### Color Usage

| Color | 使用場景 | 範例 |
|-------|---------|------|
| **Primary** (#0EA5E9) | 主要按鈕、連結、強調項目 | Login 按鈕、Active Nav Item |
| **Secondary** (#14B8A6) | 次要動作、資訊提示 | Info Badges |
| **Success** (#10B981) | 成功訊息、批准操作 | Approve Button, Success Badge |
| **Warning** (#F59E0B) | 警告訊息、待審核狀態 | Pending Badge |
| **Danger** (#EF4444) | 錯誤訊息、拒絕操作、刪除按鈕 | Reject Button, Error Message |
| **Info** (#3B82F6) | 資訊性內容、提示 | View Button |
| **Gray** (Slate) | 禁用狀態、次要文字 | Disabled Button |

---

## 🖼️ Logo

### Logo Configuration

**方法 1: 文字 Logo** (最簡單，推薦 Phase 1)

```php
// app/Providers/Filament/AdminPanelProvider.php

->brandName('YAMU Admin')
->brandLogo(null)  // 使用文字
->brandLogoHeight('2rem')
```

---

**方法 2: SVG Logo** (如果有 Logo 檔案)

```php
->brandLogo(asset('images/logo.svg'))
->darkModeBrandLogo(asset('images/logo-dark.svg'))  // Dark mode logo
->brandLogoHeight('2rem')
```

**Logo 檔案位置**:
- Light mode: `public/images/logo.svg`
- Dark mode: `public/images/logo-dark.svg`

**Logo 規格**:
- 格式: SVG (推薦) 或 PNG
- 尺寸: 高度 32px (2rem), 寬度自適應
- 背景: 透明

---

**方法 3: 自訂 Logo View** (完全客製化)

```php
->brandLogo(fn () => view('filament.admin.logo'))
```

**檔案**: `resources/views/filament/admin/logo.blade.php`

```blade
<div class="flex items-center gap-3">
    <svg class="h-8 w-8" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <!-- YAMU Logo SVG Path -->
        <circle cx="50" cy="50" r="40" fill="#0EA5E9"/>
        <text x="50" y="60" text-anchor="middle" fill="white" font-size="40" font-weight="bold">Y</text>
    </svg>
    <span class="text-xl font-bold text-gray-900 dark:text-white">
        YAMU Admin
    </span>
</div>
```

---

## 🌐 Locale (語言)

### 繁體中文設定

```php
// app/Providers/Filament/AdminPanelProvider.php

->locale('zh_TW')
->locales(['zh_TW'])  // 只提供繁體中文
```

### 日期時間格式

```php
// config/app.php

'timezone' => 'Asia/Taipei',  // 台灣時區
'locale' => 'zh_TW',           // 繁體中文
'faker_locale' => 'zh_TW',     // Faker 使用繁體中文
```

---

## 🧭 Navigation

### Navigation Groups

```php
// app/Filament/Resources/SalespersonApplicationResource.php

protected static ?string $navigationGroup = '審核管理';
protected static ?int $navigationSort = 1;
```

**Navigation 結構**:

```
YAMU Admin
├── Dashboard (首頁)
│
├── 📋 審核管理 (Group)
│   ├── 業務員申請 (Badge: 5)  ← Phase 1
│   ├── 工作經驗審核           ← Phase 2
│   └── 證照審核               ← Phase 2
│
├── 👥 使用者管理 (Group)     ← Phase 2+
│   ├── 使用者列表
│   └── 業務員列表
│
├── 🏢 公司管理 (Group)       ← Phase 2+
│   └── 公司列表
│
├── ⚙️ 系統設定 (Group)        ← Phase 2+
│   ├── 地區管理
│   └── 產業管理
│
└── 🛡️ Shield (權限管理)      ← Shield Plugin
    └── Roles
```

### Navigation Badge

**顯示待審核數量**:

```php
// SalespersonApplicationResource.php

public static function getNavigationBadge(): ?string
{
    return static::getEloquentQuery()->count();
}

public static function getNavigationBadgeColor(): ?string
{
    $count = static::getEloquentQuery()->count();

    if ($count === 0) {
        return 'success';  // 綠色 - 沒有待審核
    }

    if ($count > 10) {
        return 'danger';   // 紅色 - 超過 10 個待審核
    }

    return 'warning';      // 黃色 - 有待審核
}
```

---

## 🎭 Dark Mode

### Dark Mode Configuration

**Phase 1: 禁用 Dark Mode** (簡化設計)

```php
// app/Providers/Filament/AdminPanelProvider.php

->darkMode(false)  // 禁用 Dark Mode
```

**Phase 2+: 啟用 Dark Mode** (未來功能)

```php
->darkMode(true)  // 啟用 Dark Mode (用戶可切換)
```

---

## 📐 Layout

### Page Layout

**預設 Layout** (Filament 預設，無需修改):

```
┌─────────────────────────────────────────────────────┐
│  Topbar (Logo, User Menu, Notifications)           │
├──────────┬──────────────────────────────────────────┤
│          │                                          │
│ Sidebar  │  Page Content                           │
│          │                                          │
│ - 首頁   │  ┌────────────────────────────────────┐ │
│ - 審核   │  │  Page Header                       │ │
│ - 使用者 │  ├────────────────────────────────────┤ │
│ - 設定   │  │  Filters & Search                  │ │
│          │  ├────────────────────────────────────┤ │
│          │  │  Table / Form / Content            │ │
│          │  │                                    │ │
│          │  │                                    │ │
│          │  └────────────────────────────────────┘ │
│          │                                          │
└──────────┴──────────────────────────────────────────┘
```

### Responsive

**自動響應式** (Filament 內建):
- Desktop (>= 1024px): Sidebar 展開
- Tablet (768px - 1023px): Sidebar 可收合
- Mobile (< 768px): Sidebar 隱藏，使用 Hamburger Menu

---

## ✨ Custom Styles

### Global Styles

**檔案**: `resources/css/filament/admin/theme.css` (可選，Phase 2+)

**Phase 1 不需要自訂 CSS**，使用 Filament 預設樣式即可。

**未來 (Phase 2+) 可客製化**:

```css
/* resources/css/filament/admin/theme.css */

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Custom focus ring color */
*:focus-visible {
    outline: 2px solid #0EA5E9 !important;
    outline-offset: 2px;
}

/* Custom card shadow */
.fi-section {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
}
```

**註冊 Custom Styles**:

```php
// app/Providers/Filament/AdminPanelProvider.php

->viteTheme('resources/css/filament/admin/theme.css')
```

---

## 🔔 Notifications

### Notification Styling

**使用 Filament 預設 Notification**:

```php
use Filament\Notifications\Notification;

// 成功通知 (綠色)
Notification::make()
    ->success()
    ->title('已批准業務員申請')
    ->body('業務員已成功批准')
    ->send();

// 警告通知 (黃色)
Notification::make()
    ->warning()
    ->title('已拒絕業務員申請')
    ->body('已拒絕該業務員申請')
    ->send();

// 錯誤通知 (紅色)
Notification::make()
    ->danger()
    ->title('操作失敗')
    ->body('無法完成此操作')
    ->send();

// 資訊通知 (藍色)
Notification::make()
    ->info()
    ->title('資訊')
    ->body('這是一則資訊')
    ->send();
```

### Notification Duration

```php
->duration(5000)  // 5 秒後自動消失 (預設)
->persistent()    // 不自動消失，需手動關閉
```

---

## 📱 Favicon

### Favicon Configuration

```php
// app/Providers/Filament/AdminPanelProvider.php

->favicon(asset('favicon.ico'))
```

**Favicon 檔案**:
- 位置: `public/favicon.ico`
- 格式: ICO 或 PNG
- 尺寸: 32x32px 或 64x64px

**產生 Favicon** (可選):
```bash
# 使用線上工具將 Logo 轉換為 Favicon
# https://favicon.io/
# https://realfavicongenerator.net/
```

---

## 🎯 Login Page

### Login Page Customization

**Phase 1: 使用預設登入頁面**

**未來 (Phase 2+) 可客製化**:

```php
// app/Filament/Pages/Auth/Login.php

<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getHeading(): string
    {
        return 'YAMU 管理後台';
    }

    protected function getSubheading(): string
    {
        return '請使用您的管理員帳號登入';
    }
}
```

**註冊 Custom Login Page**:

```php
// app/Providers/Filament/AdminPanelProvider.php

->login(\App\Filament\Pages\Auth\Login::class)
```

---

## 🖼️ Empty State

### Custom Empty State

**當列表為空時顯示**:

```php
// SalespersonApplicationResource.php

->emptyStateHeading('沒有待審核的業務員申請')
->emptyStateDescription('目前沒有待審核的業務員申請')
->emptyStateIcon('heroicon-o-user-group')
->emptyStateActions([
    // 可新增 Actions (Phase 2+)
])
```

---

## ✅ Configuration Checklist

### Brand Identity
- [ ] Primary Color 設為 #0EA5E9 (Sky-500)
- [ ] Logo 已設定 (文字或圖片)
- [ ] Favicon 已設定
- [ ] Brand Name 設為 "YAMU Admin"

### Locale & Timezone
- [ ] Locale 設為 zh_TW (繁體中文)
- [ ] Timezone 設為 Asia/Taipei
- [ ] 日期格式正確顯示

### Navigation
- [ ] Navigation Groups 已定義
- [ ] Navigation Sort 已設定
- [ ] Navigation Badge 正確顯示待審核數量
- [ ] Badge 顏色根據數量變化

### Layout & UI
- [ ] Dark Mode 已禁用 (Phase 1)
- [ ] Responsive Layout 正常運作
- [ ] 登入頁面正常顯示

### Notifications
- [ ] Success 通知使用綠色
- [ ] Warning 通知使用黃色
- [ ] Danger 通知使用紅色
- [ ] Info 通知使用藍色
- [ ] 通知 5 秒後自動消失

### Empty States
- [ ] 空白狀態顯示友善訊息
- [ ] 空白狀態有合適的 Icon

---

## 🎨 Design System Consistency

### With Next.js Frontend

**確保一致性**:

| 元素 | Next.js Frontend | Filament Admin | 狀態 |
|------|------------------|----------------|------|
| Primary Color | #0EA5E9 | #0EA5E9 | ✅ 一致 |
| Success Color | #10B981 | #10B981 | ✅ 一致 |
| Warning Color | #F59E0B | #F59E0B | ✅ 一致 |
| Danger Color | #EF4444 | #EF4444 | ✅ 一致 |
| Font Family | System Font | System Font | ✅ 一致 |
| Border Radius | Tailwind Default | Tailwind Default | ✅ 一致 |

---

## 📊 Performance

### CSS Bundle Size

**目標** (參考 metrics-standards.md):
- Initial CSS: < 50KB (gzip)
- Filament 預設: ~30KB (gzip) ✅

### Render Performance

**目標**:
- Login Page LCP: < 1s
- Dashboard LCP: < 1.5s
- Table Page LCP: < 2s

---

## 🐛 Common Issues

### Issue: Colors not applying

**解決**:
```bash
# 清除 Filament 快取
php artisan filament:cache-components

# 清除 Laravel 快取
php artisan optimize:clear
```

---

### Issue: Logo not showing

**檢查**:
```bash
# 確認檔案存在
ls -la public/images/logo.svg

# 確認檔案權限
chmod 644 public/images/logo.svg
```

---

**文檔版本**: 1.0
**最後更新**: 2026-01-24
**負責人**: Backend Team
