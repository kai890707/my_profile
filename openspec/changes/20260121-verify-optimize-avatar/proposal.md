# Proposal: Avatar 功能完整驗證與優化

**日期**: 2026-01-21
**狀態**: Proposal
**類型**: Feature Enhancement + Verification
**優先級**: High

---

## 1. 背景與目標

### 1.1 業務背景

Avatar（頭像）功能是業務員檔案管理系統的重要視覺元素，直接影響：
- **使用者識別度**: 快速識別業務員身份
- **專業形象**: 提升業務員的專業可信度
- **使用者體驗**: 提供視覺化的個人化體驗

目前系統已實作基礎 Avatar 功能，但需要進行全面驗證和效能優化，確保在所有情境下都能正確運作。

### 1.2 為什麼需要驗證和優化？

**現狀問題**:
1. **功能完整性未確認**: 尚未驗證所有頁面的 Avatar 是否正確顯示
2. **效能問題**: 未驗證大量 Avatar 載入時的效能影響
3. **邊界情況未測試**: 未測試超大檔案、損壞檔案、惡意檔案等情境
4. **安全性未加強**: 檔案上傳安全檢查不夠嚴謹
5. **使用者體驗可優化**: 上傳流程、錯誤提示、Loading 狀態可改善

**影響**:
- 可能出現 Avatar 無法顯示的情況
- 大量 Avatar 載入時可能造成效能問題
- 惡意檔案上傳可能造成安全風險
- 使用者體驗不夠友善

### 1.3 目標使用者

- **業務員**: 上傳和管理自己的頭像
- **一般使用者**: 瀏覽業務員檔案時查看頭像
- **管理員**: 審核和管理業務員頭像（未來功能）

### 1.4 成功指標

#### 功能完整性指標
- ✅ 所有頁面（search、salesperson detail、dashboard profile）正確顯示 Avatar
- ✅ Fallback 機制正常運作（顯示姓名縮寫）
- ✅ data URL 和 http URL 都能正確顯示
- ✅ 上傳後立即預覽
- ✅ 儲存後立即更新（不需重新整理頁面）
- ✅ 跨頁面同步更新（React Query cache 機制）

#### 效能指標（參考 metrics-standards.md）
- ✅ Avatar 載入時間 < 500ms（單張）
- ✅ 列表頁面（20 個 Avatar）載入時間 < 2s
- ✅ 檔案上傳限制 2MB
- ✅ 自動壓縮到 400x400px
- ✅ Lazy loading（viewport 外的 Avatar 延遲載入）

#### 安全性指標
- ✅ 檔案類型白名單驗證（JPG, PNG, WebP, GIF）
- ✅ MIME type 檢查（防止偽造副檔名）
- ✅ 檔案內容驗證（防止惡意檔案）
- ✅ 檔案大小限制（前端 2MB + Backend 驗證）
- ✅ 防止 XSS 攻擊（data URL 安全處理）

#### 使用者體驗指標
- ✅ 上傳流程友善（點擊 → 選擇 → 預覽 → 確認）
- ✅ Loading 狀態明確（上傳中、壓縮中）
- ✅ 錯誤訊息清晰（具體說明問題和解決方法）
- ✅ 檔案過大時自動壓縮（無需手動處理）
- ✅ 預覽即時更新（選擇檔案後立即顯示）

---

## 2. 現有實作分析

### 2.1 Frontend Avatar Component

**位置**: `frontend/components/ui/avatar.tsx`

**功能分析**:
```typescript
// ✅ 已實作功能
- Data URL 和 HTTP URL 雙支援
- Fallback 機制（姓名縮寫 + 預設圖示）
- 多尺寸支援（xs, sm, md, lg, xl, 2xl）
- 狀態指示器（online, offline, away, busy）
- 漸層色背景（primary-400 → secondary-400）

// ⚠️ 待優化項目
- 無 Lazy loading（所有 Avatar 立即載入）
- 無 Error handling（圖片載入失敗時無處理）
- 無 Loading state（圖片載入中無顯示）
```

### 2.2 Frontend Profile Upload

**位置**: `frontend/app/(dashboard)/dashboard/page.tsx`

**功能分析**:
```typescript
// ✅ 已實作功能
- 檔案選擇和預覽
- Base64 轉換
- 2MB 檔案大小限制
- 圖片壓縮（使用 processImageUpload）
- 即時預覽（avatarPreview state）
- React Query cache 更新

// ⚠️ 待優化項目
- 檔案類型驗證不夠嚴謹
- MIME type 檢查缺失
- 錯誤提示不夠具體
- 壓縮策略未優化（可能過度壓縮或不足）
```

### 2.3 Frontend Image Utility

**位置**: `frontend/lib/utils/image.ts`

**需要檢查**:
- `processImageUpload()` 函數的實作
- 壓縮演算法和參數
- 檔案驗證邏輯

### 2.4 Backend Avatar Storage

**位置**: `my_profile_laravel/app/Http/Controllers/Api/SalespersonController.php`

**功能分析**:
```php
// ✅ 已實作功能
- 接收 Base64 avatar 資料（line 65）
- 儲存到 salesperson_profiles 表（avatar_data + avatar_mime）
- 建構 data URL 回傳給前端（line 257-260）

// ⚠️ 待優化項目
- 無檔案大小限制驗證
- 無 MIME type 白名單驗證
- 無檔案內容安全檢查
- 未實作獨立的 updateAvatar 端點
```

### 2.5 Backend Database Schema

**表**: `salesperson_profiles`

```sql
avatar_data  TEXT         # Base64 編碼的圖片資料
avatar_mime  VARCHAR(50)  # MIME type (image/jpeg, image/png)
```

**問題**:
- 未建立索引（如果需要按 avatar 查詢）
- TEXT 欄位可能過大（應考慮檔案大小限制）

---

## 3. 功能範圍定義

### 3.1 In Scope（本次實作）

#### Phase 1: 驗證現有功能（高優先級）
- ✅ 驗證所有頁面 Avatar 正確顯示
  - Search 列表頁（20 個業務員）
  - Salesperson Detail 頁
  - Dashboard Profile 頁
- ✅ 驗證 Fallback 機制
  - 無 Avatar 時顯示姓名縮寫
  - 姓名縮寫算法正確性（getAvatarFallback）
- ✅ 驗證上傳流程
  - 檔案選擇 → 預覽 → 上傳 → 儲存
  - React Query cache 更新機制
  - 跨頁面同步更新

#### Phase 2: 安全性加強（高優先級）
- ✅ Frontend 檔案驗證增強
  - 檔案類型白名單（JPG, PNG, WebP, GIF）
  - MIME type 檢查（防止偽造副檔名）
  - 檔案內容檢查（讀取檔案 header）
  - 檔案大小前端驗證（2MB）
- ✅ Backend 檔案驗證增強
  - Base64 解碼驗證
  - MIME type 白名單驗證
  - 檔案大小後端驗證（2MB 解碼後）
  - 圖片內容驗證（使用 GD/Imagick）

#### Phase 3: 效能優化（中優先級）
- ✅ Frontend 優化
  - 圖片壓縮優化（智能壓縮策略）
  - Lazy loading（Intersection Observer）
  - React Query staleTime 配置
  - 預載入策略（hover 時預載入）
- ✅ Backend 優化
  - 圖片壓縮（Backend 端二次壓縮）
  - 快取機制（考慮 CDN）

#### Phase 4: 使用者體驗優化（中優先級）
- ✅ 上傳流程優化
  - Loading 狀態明確化
  - 錯誤提示具體化
  - 壓縮進度顯示
- ✅ 預覽功能優化
  - 拖放上傳支援
  - 圖片裁切功能（可選）
  - 縮放和旋轉（可選）

#### Phase 5: 測試與驗證（高優先級）
- ✅ E2E 測試（Playwright）
  - 上傳流程測試
  - 跨頁面同步測試
  - 響應式設計測試
- ✅ Visual Regression 測試
  - Avatar 元件快照測試
  - 不同尺寸和狀態測試
- ✅ Performance 測試
  - 載入時間測試
  - 大量 Avatar 渲染測試
- ✅ Security 測試
  - 惡意檔案上傳測試
  - XSS 攻擊測試

### 3.2 Out of Scope（不在範圍內）

#### 暫不實作的功能
- ❌ 管理員審核 Avatar 功能
  - **原因**: 業務需求未明確
  - **未來**: Phase 6 考慮

- ❌ Avatar 歷史版本管理
  - **原因**: 資料庫空間考量
  - **未來**: 根據需求評估

- ❌ 多頭像切換
  - **原因**: 業務場景不需要
  - **未來**: 暫不考慮

- ❌ Avatar 社交分享功能
  - **原因**: 超出核心功能範圍
  - **未來**: Phase 7 考慮

- ❌ AI 背景移除/美顏
  - **原因**: 技術複雜度高，成本大
  - **未來**: 根據預算評估

#### 暫不調整的架構
- ❌ 改用 S3/CDN 儲存
  - **原因**: 目前 Base64 方案可滿足需求
  - **未來**: 當流量增長時考慮

- ❌ 改用獨立的圖片服務
  - **原因**: 增加系統複雜度
  - **未來**: 微服務化時考慮

---

## 4. 詳細需求

### 4.1 功能需求

#### FR-001: Avatar 顯示功能驗證
**描述**: 驗證所有頁面的 Avatar 正確顯示
**優先級**: Must Have
**驗收標準**:
- [ ] Search 列表頁：20 個業務員的 Avatar 正確顯示
- [ ] Salesperson Detail 頁：單一業務員 Avatar 正確顯示
- [ ] Dashboard Profile 頁：當前使用者 Avatar 正確顯示
- [ ] 所有頁面的 Fallback 機制正常運作
- [ ] Data URL 和 HTTP URL 都能正確顯示
- [ ] 不同尺寸（xs, sm, md, lg, xl, 2xl）正確渲染

#### FR-002: Avatar 上傳流程優化
**描述**: 優化檔案上傳流程，提升使用者體驗
**優先級**: Must Have
**驗收標準**:
- [ ] 點擊相機圖示可選擇檔案
- [ ] 選擇檔案後立即預覽
- [ ] 顯示壓縮進度（如果需要壓縮）
- [ ] 上傳成功後顯示成功訊息
- [ ] 錯誤時顯示具體錯誤訊息
- [ ] 上傳中禁用提交按鈕

#### FR-003: 檔案驗證增強
**描述**: 加強檔案類型和內容驗證
**優先級**: Must Have
**驗收標準**:
- [ ] 檔案類型白名單驗證（JPG, PNG, WebP, GIF）
- [ ] MIME type 檢查（防止偽造副檔名）
- [ ] 檔案大小限制（2MB）
- [ ] 圖片內容驗證（讀取檔案 header）
- [ ] 拒絕非圖片檔案
- [ ] 拒絕損壞的圖片檔案

#### FR-004: 圖片壓縮優化
**描述**: 智能壓縮策略，平衡檔案大小和畫質
**優先級**: Should Have
**驗收標準**:
- [ ] 圖片尺寸 > 400x400 時壓縮到 400x400
- [ ] 檔案大小 > 2MB 時自動壓縮
- [ ] 保持寬高比
- [ ] 壓縮後畫質可接受（≥ 80%）
- [ ] 顯示壓縮前後的檔案大小

#### FR-005: Lazy Loading 實作
**描述**: 實作 Avatar 延遲載入，提升頁面效能
**優先級**: Should Have
**驗收標準**:
- [ ] 使用 Intersection Observer API
- [ ] Viewport 外的 Avatar 延遲載入
- [ ] 進入 Viewport 時才載入圖片
- [ ] Loading 狀態顯示（Skeleton）
- [ ] 載入失敗時顯示 Fallback

#### FR-006: Error Handling 完善
**描述**: 完善錯誤處理和提示
**優先級**: Must Have
**驗收標準**:
- [ ] 圖片載入失敗時顯示 Fallback
- [ ] 上傳失敗時顯示具體錯誤訊息
- [ ] 檔案過大時提示「檔案過大，已自動壓縮」
- [ ] 檔案類型錯誤時提示「不支援的檔案格式」
- [ ] 網路錯誤時提示「網路連線失敗，請稍後再試」

### 4.2 非功能需求

#### NFR-001: 效能需求
**描述**: Avatar 載入效能要求
**優先級**: Must Have
**驗收標準**:
- [ ] 單張 Avatar 載入時間 < 500ms
- [ ] 列表頁面（20 個 Avatar）載入時間 < 2s
- [ ] LCP (Largest Contentful Paint) < 2.5s
- [ ] FID (First Input Delay) < 100ms
- [ ] CLS (Cumulative Layout Shift) < 0.1

#### NFR-002: 安全性需求
**描述**: 檔案上傳安全要求
**優先級**: Must Have
**驗收標準**:
- [ ] 防止 XSS 攻擊（data URL 安全處理）
- [ ] 防止 SQL Injection（參數化查詢）
- [ ] 防止 CSRF 攻擊（使用 Laravel CSRF Token）
- [ ] 防止檔案偽造（MIME type 和內容檢查）
- [ ] 防止過大檔案上傳（2MB 限制）

#### NFR-003: 相容性需求
**描述**: 瀏覽器和裝置相容性
**優先級**: Should Have
**驗收標準**:
- [ ] 支援 Chrome/Edge/Safari/Firefox 最新版
- [ ] 支援 iOS Safari 和 Android Chrome
- [ ] 響應式設計（Desktop/Tablet/Mobile）
- [ ] 觸控裝置友善（按鈕大小 ≥ 44x44px）

#### NFR-004: 可訪問性需求
**描述**: 無障礙性要求
**優先級**: Should Have
**驗收標準**:
- [ ] Alt text 正確設定
- [ ] 鍵盤導航支援
- [ ] Screen reader 友善
- [ ] 色彩對比符合 WCAG AA（≥ 4.5:1）

### 4.3 資料需求

#### 現有資料結構（不變）
```sql
-- salesperson_profiles 表
avatar_data  TEXT         # Base64 編碼的圖片資料
avatar_mime  VARCHAR(50)  # MIME type (image/jpeg, image/png, etc.)
```

#### 新增驗證規則
```typescript
// Frontend 驗證
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB
const TARGET_SIZE = 400; // 400x400px
const COMPRESSION_QUALITY = 0.8; // 80%
```

```php
// Backend 驗證
const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
const MAX_FILE_SIZE = 2097152; // 2MB in bytes
```

### 4.4 權限需求

**權限矩陣**:

| 角色 | 檢視自己 Avatar | 更新自己 Avatar | 檢視其他人 Avatar | 更新其他人 Avatar |
|------|----------------|----------------|------------------|------------------|
| 業務員 | ✅ | ✅ | ✅ | ❌ |
| 一般使用者 | N/A | N/A | ✅（搜尋頁面） | ❌ |
| 管理員 | ✅ | ✅ | ✅ | ⚠️（未實作，Out of Scope） |

**權限檢查邏輯**:
```php
// Backend: SalespersonController@updateProfile
- 檢查使用者是否已認證
- 檢查使用者是否為業務員
- 只能更新自己的 Avatar（user_id 匹配）
```

### 4.5 API 需求

#### 現有 API（不變）
```
PUT /api/salesperson/profile
Request:
{
  "full_name": "王小明",
  "phone": "0912345678",
  "avatar": "data:image/jpeg;base64,..."  // Base64 string
}

Response:
{
  "success": true,
  "profile": {
    "id": 1,
    "full_name": "王小明",
    "avatar": "data:image/jpeg;base64,..."
  }
}
```

#### 新增驗證邏輯（Backend）
```php
// UpdateSalespersonProfileRequest
public function rules(): array
{
    return [
        'avatar' => [
            'nullable',
            'string',
            function ($attribute, $value, $fail) {
                // 驗證 Base64 格式
                if (!preg_match('/^data:image\/(jpeg|png|webp|gif);base64,/', $value)) {
                    $fail('Invalid avatar format.');
                    return;
                }

                // 解碼並檢查大小
                $base64 = explode(',', $value)[1];
                $decoded = base64_decode($base64);
                if (strlen($decoded) > 2097152) { // 2MB
                    $fail('Avatar file size exceeds 2MB.');
                }

                // 驗證圖片內容
                // ... (使用 GD 或 Imagick 驗證)
            }
        ]
    ];
}
```

### 4.6 UI/UX 需求

#### 上傳流程設計

**步驟 1: 觸發上傳**
```
[Avatar]  [📷 相機圖示]
         (Hover: 提示「點擊更換頭像」)
```

**步驟 2: 選擇檔案**
```
檔案選擇對話框
- 支援格式: JPG, PNG, WebP, GIF
- 大小限制: 2MB
```

**步驟 3: 預覽與壓縮**
```
[預覽區域]
├─ [新頭像預覽]
├─ 檔案資訊: example.jpg (1.5MB)
├─ 壓縮中... (如果需要)
└─ 壓縮完成: 400x400, 300KB
```

**步驟 4: 確認上傳**
```
[儲存變更] 按鈕
├─ Loading 狀態: 上傳中...
└─ 成功: ✓ 頭像已更新
```

#### 錯誤提示設計

**檔案過大**:
```
❌ 檔案過大（3.5MB），請選擇小於 2MB 的圖片
```

**檔案類型錯誤**:
```
❌ 不支援的檔案格式，請選擇 JPG、PNG、WebP 或 GIF 圖片
```

**圖片損壞**:
```
❌ 圖片檔案損壞，無法讀取，請選擇其他圖片
```

**網路錯誤**:
```
❌ 上傳失敗，請檢查網路連線後重試
```

#### 響應式設計

**Desktop (≥ 1024px)**:
- Avatar 尺寸: 96x96 (2xl)
- 相機按鈕: 右下角，32x32

**Tablet (768px - 1023px)**:
- Avatar 尺寸: 80x80 (xl)
- 相機按鈕: 右下角，28x28

**Mobile (< 768px)**:
- Avatar 尺寸: 64x64 (lg)
- 相機按鈕: 右下角，24x24

---

## 5. 邊界情境處理

### 5.1 檔案驗證

| 情境 | 系統行為 | 錯誤訊息 |
|-----|---------|---------|
| 檔案 > 2MB | 嘗試壓縮，壓縮後仍 > 2MB 則拒絕 | "檔案過大（X MB），請選擇小於 2MB 的圖片" |
| 檔案類型錯誤（.txt 改名 .jpg） | MIME type 檢查失敗，拒絕 | "不支援的檔案格式，請選擇 JPG、PNG、WebP 或 GIF 圖片" |
| 圖片尺寸超大（8000x6000） | 自動壓縮到 400x400 | "圖片已自動壓縮至 400x400" |
| 圖片損壞 | 讀取失敗，拒絕 | "圖片檔案損壞，無法讀取，請選擇其他圖片" |
| 空檔案 | 檔案大小為 0，拒絕 | "檔案為空，請選擇有效的圖片" |

### 5.2 上傳流程

| 情境 | 系統行為 | 處理方式 |
|-----|---------|---------|
| 上傳中網路斷線 | API 請求失敗 | 顯示錯誤訊息，保留預覽狀態，允許重試 |
| 上傳中關閉頁面 | 未儲存 | （建議）顯示確認對話框「您有未儲存的變更」 |
| 重複上傳（快速點擊） | 使用 debounce 防抖 | 只處理最後一次上傳 |
| API 返回錯誤 | Catch error，顯示訊息 | 顯示後端返回的錯誤訊息 |

### 5.3 顯示與快取

| 情境 | 系統行為 | 處理方式 |
|-----|---------|---------|
| Avatar data URL 過長 | 正常顯示（瀏覽器支援） | 無需特殊處理 |
| Avatar 載入失敗（404） | 顯示 Fallback（姓名縮寫） | 使用 img onError 事件 |
| 無 Avatar（null） | 顯示 Fallback（姓名縮寫） | Avatar 組件內建邏輯 |
| 跨頁面更新不同步 | React Query cache 更新 | invalidateQueries(['salesperson', 'profile']) |
| 重新整理頁面後 Avatar 消失 | 從 API 重新載入 | React Query 自動處理 |

### 5.4 效能與資源

| 情境 | 系統行為 | 處理方式 |
|-----|---------|---------|
| 列表頁 100 個業務員 | 分頁顯示（每頁 20 個） | 已實作分頁機制 |
| 快速滾動列表 | Lazy loading 延遲載入 | 使用 Intersection Observer |
| 同時上傳多個業務員 Avatar（管理員） | （Out of Scope）暫不支援批次上傳 | 未來功能 |
| 瀏覽器記憶體不足 | 瀏覽器自動處理 | 監控 LCP/CLS 指標 |

### 5.5 安全性

| 情境 | 系統行為 | 防護措施 |
|-----|---------|---------|
| 上傳惡意 SVG（含 JavaScript） | 拒絕 SVG 格式 | 白名單驗證（不包含 SVG） |
| 上傳 HTML 檔案偽裝成圖片 | MIME type 和內容檢查失敗 | 雙重驗證 |
| XSS 攻擊（注入 script 到 data URL） | data URL 不會執行 script | 瀏覽器內建保護 |
| CSRF 攻擊 | Laravel CSRF Token 驗證 | 已內建保護 |

---

## 6. 技術方案

### 6.1 Frontend 技術方案

#### 6.1.1 圖片壓縮方案

**推薦方案**: 使用 `browser-image-compression` 套件

**安裝**:
```bash
npm install browser-image-compression
```

**實作範例**:
```typescript
// frontend/lib/utils/image.ts
import imageCompression from 'browser-image-compression';

export async function processImageUpload(
  file: File,
  maxSizeMB: number = 2
): Promise<string> {
  // 1. 驗證檔案類型
  const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
  if (!allowedTypes.includes(file.type)) {
    throw new Error('不支援的檔案格式，請選擇 JPG、PNG、WebP 或 GIF 圖片');
  }

  // 2. 驗證檔案大小（前端初步檢查）
  if (file.size > maxSizeMB * 1024 * 1024) {
    // 嘗試壓縮
    const options = {
      maxSizeMB,
      maxWidthOrHeight: 400,
      useWebWorker: true,
      fileType: file.type,
    };

    try {
      file = await imageCompression(file, options);
    } catch (error) {
      throw new Error(`檔案過大（${formatFileSize(file.size)}），壓縮失敗`);
    }
  }

  // 3. 轉換為 Base64
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => {
      const base64 = reader.result as string;
      resolve(base64);
    };
    reader.onerror = () => reject(new Error('讀取檔案失敗'));
    reader.readAsDataURL(file);
  });
}
```

#### 6.1.2 Lazy Loading 方案

**推薦方案**: 使用 Next.js Image 組件（自帶 Lazy loading）

**實作範例**:
```typescript
// frontend/components/ui/avatar.tsx
import Image from 'next/image';

export function Avatar({ src, alt, size = 'md', fallback }: AvatarProps) {
  const [imageError, setImageError] = useState(false);

  if (!src || imageError) {
    return <FallbackAvatar fallback={fallback} size={size} />;
  }

  return (
    <div className={cn('relative inline-block', sizes[size])}>
      {src.startsWith('data:') ? (
        // Data URL: 使用原生 img（已載入，無需 lazy）
        <img
          src={src}
          alt={alt}
          className="object-cover w-full h-full rounded-full"
          onError={() => setImageError(true)}
        />
      ) : (
        // HTTP URL: 使用 Next.js Image（自動 lazy loading）
        <Image
          src={src}
          alt={alt}
          width={96}
          height={96}
          className="object-cover w-full h-full rounded-full"
          loading="lazy"
          onError={() => setImageError(true)}
        />
      )}
    </div>
  );
}
```

#### 6.1.3 React Query Cache 優化

**配置**:
```typescript
// frontend/lib/query/client.ts
export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000, // 5 分鐘
      cacheTime: 10 * 60 * 1000, // 10 分鐘
      retry: 1,
    },
  },
});
```

**更新策略**:
```typescript
// frontend/hooks/useSalesperson.ts
export function useUpdateProfile() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: salespersonApi.updateProfile,
    onSuccess: (response) => {
      // 立即更新 cache
      queryClient.invalidateQueries({ queryKey: salespersonKeys.profile });

      // 更新搜尋列表 cache（如果存在）
      queryClient.invalidateQueries({ queryKey: ['salespeople'] });

      toast.success('個人資料已更新');
    },
  });
}
```

### 6.2 Backend 技術方案

#### 6.2.1 檔案驗證方案

**實作位置**: `app/Http/Requests/UpdateSalespersonProfileRequest.php`

**驗證邏輯**:
```php
public function rules(): array
{
    return [
        'avatar' => [
            'nullable',
            'string',
            function ($attribute, $value, $fail) {
                // 1. 驗證 Base64 格式
                if (!preg_match('/^data:image\/(jpeg|png|webp|gif);base64,/', $value)) {
                    $fail('不支援的圖片格式');
                    return;
                }

                // 2. 提取 MIME type 和 Base64 資料
                preg_match('/^data:(image\/[a-z]+);base64,(.+)$/', $value, $matches);
                $mimeType = $matches[1] ?? null;
                $base64Data = $matches[2] ?? null;

                // 3. 驗證 MIME type
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                if (!in_array($mimeType, $allowedTypes)) {
                    $fail('不支援的圖片格式');
                    return;
                }

                // 4. 解碼並檢查大小
                $decoded = base64_decode($base64Data, true);
                if ($decoded === false) {
                    $fail('圖片資料格式錯誤');
                    return;
                }

                if (strlen($decoded) > 2097152) { // 2MB
                    $fail('圖片檔案過大（超過 2MB）');
                    return;
                }

                // 5. 驗證圖片內容（使用 GD）
                try {
                    $image = imagecreatefromstring($decoded);
                    if ($image === false) {
                        $fail('圖片檔案損壞或無效');
                        return;
                    }
                    imagedestroy($image);
                } catch (\Exception $e) {
                    $fail('圖片驗證失敗');
                }
            }
        ]
    ];
}
```

#### 6.2.2 圖片壓縮方案（Backend 端）

**實作位置**: `app/Services/ImageService.php`（新增）

```php
namespace App\Services;

class ImageService
{
    public function compressImage(string $base64Data, string $mimeType): string
    {
        // 解碼
        $decoded = base64_decode(explode(',', $base64Data)[1]);
        $image = imagecreatefromstring($decoded);

        if ($image === false) {
            throw new \Exception('圖片解碼失敗');
        }

        // 取得原始尺寸
        $width = imagesx($image);
        $height = imagesy($image);

        // 計算新尺寸（保持寬高比，最大 400x400）
        $maxSize = 400;
        if ($width > $maxSize || $height > $maxSize) {
            $ratio = min($maxSize / $width, $maxSize / $height);
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);

            // 建立新圖片
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $newImage;
        }

        // 轉換為 JPEG（統一格式，更好的壓縮）
        ob_start();
        imagejpeg($image, null, 80); // 80% 品質
        $compressed = ob_get_clean();
        imagedestroy($image);

        // 轉回 Base64
        return base64_encode($compressed);
    }
}
```

### 6.3 測試方案

#### 6.3.1 E2E 測試（Playwright）

**測試案例**:
```typescript
// tests/e2e/avatar-upload.spec.ts
import { test, expect } from '@playwright/test';

test.describe('Avatar Upload', () => {
  test('should upload and display avatar successfully', async ({ page }) => {
    // 1. 登入
    await page.goto('/login');
    await page.fill('input[name="email"]', 'salesperson@example.com');
    await page.fill('input[name="password"]', 'password123');
    await page.click('button[type="submit"]');

    // 2. 前往個人資料頁
    await page.goto('/dashboard');

    // 3. 點擊編輯
    await page.click('button:has-text("編輯資料")');

    // 4. 上傳圖片
    const fileInput = page.locator('input[type="file"]');
    await fileInput.setInputFiles('./tests/fixtures/avatar.jpg');

    // 5. 等待預覽
    await expect(page.locator('[data-testid="avatar-preview"]')).toBeVisible();

    // 6. 儲存
    await page.click('button[type="submit"]:has-text("儲存變更")');

    // 7. 驗證成功訊息
    await expect(page.locator('text=個人資料已更新')).toBeVisible();

    // 8. 驗證 Avatar 已更新
    const avatar = page.locator('[data-testid="profile-avatar"]');
    await expect(avatar).toHaveAttribute('src', /^data:image/);
  });

  test('should show error for oversized file', async ({ page }) => {
    // ... 上傳超過 2MB 的檔案
    await fileInput.setInputFiles('./tests/fixtures/large-image.jpg'); // 5MB

    // 驗證錯誤訊息
    await expect(page.locator('text=檔案過大')).toBeVisible();
  });
});
```

#### 6.3.2 Visual Regression 測試

**工具**: Playwright + Percy

**測試案例**:
```typescript
// tests/visual/avatar.spec.ts
import { test } from '@playwright/test';
import percySnapshot from '@percy/playwright';

test.describe('Avatar Visual Tests', () => {
  test('avatar component variants', async ({ page }) => {
    await page.goto('/storybook/avatar');

    // 截圖不同尺寸
    await percySnapshot(page, 'Avatar - All Sizes');

    // 截圖 Fallback 狀態
    await page.click('[data-testid="toggle-fallback"]');
    await percySnapshot(page, 'Avatar - Fallback');

    // 截圖 Status Indicator
    await page.click('[data-testid="toggle-status"]');
    await percySnapshot(page, 'Avatar - Status Indicators');
  });
});
```

#### 6.3.3 Performance 測試

**工具**: Lighthouse CI

**配置**:
```json
// lighthouserc.json
{
  "ci": {
    "collect": {
      "url": [
        "http://localhost:3001/search",
        "http://localhost:3001/dashboard"
      ],
      "numberOfRuns": 3
    },
    "assert": {
      "assertions": {
        "categories:performance": ["error", {"minScore": 0.9}],
        "largest-contentful-paint": ["error", {"maxNumericValue": 2500}],
        "first-input-delay": ["error", {"maxNumericValue": 100}],
        "cumulative-layout-shift": ["error", {"maxNumericValue": 0.1}]
      }
    }
  }
}
```

---

## 7. 實作任務拆解

### Phase 1: 驗證現有功能（1 天）

**任務清單**:
- [ ] Task 1.1: 驗證 Search 列表頁 Avatar 顯示（1 小時）
  - 執行: Playwright 測試
  - 驗證: 20 個業務員 Avatar 正確顯示

- [ ] Task 1.2: 驗證 Salesperson Detail 頁 Avatar 顯示（30 分鐘）
  - 執行: Playwright 測試
  - 驗證: 單一業務員 Avatar 正確顯示

- [ ] Task 1.3: 驗證 Dashboard Profile 頁 Avatar 上傳流程（1 小時）
  - 執行: Playwright 測試
  - 驗證: 上傳 → 預覽 → 儲存 → 更新

- [ ] Task 1.4: 驗證 Fallback 機制（30 分鐘）
  - 執行: 單元測試 + Playwright
  - 驗證: 無 Avatar 時顯示姓名縮寫

- [ ] Task 1.5: 驗證跨頁面同步更新（1 小時）
  - 執行: Playwright 測試
  - 驗證: Dashboard 更新後，Search 頁面也同步更新

- [ ] Task 1.6: 撰寫驗證報告（1 小時）
  - 執行: 彙整測試結果
  - 輸出: `reports/avatar-verification-report.md`

### Phase 2: 安全性加強（2 天）

**Frontend 任務**:
- [ ] Task 2.1: 增強 `processImageUpload()` 驗證邏輯（2 小時）
  - 檔案類型白名單
  - MIME type 檢查
  - 檔案內容檢查（讀取 header）

- [ ] Task 2.2: 改善錯誤提示訊息（1 小時）
  - 具體化錯誤訊息
  - 提供解決方案提示

**Backend 任務**:
- [ ] Task 2.3: 建立 `UpdateSalespersonProfileRequest` 驗證規則（2 小時）
  - Base64 格式驗證
  - MIME type 白名單
  - 檔案大小限制
  - 圖片內容驗證（GD）

- [ ] Task 2.4: 建立 `ImageService` 服務（2 小時）
  - 圖片壓縮邏輯
  - 圖片驗證邏輯

- [ ] Task 2.5: 更新 `SalespersonController@updateProfile`（1 小時）
  - 整合 ImageService
  - 錯誤處理

**測試任務**:
- [ ] Task 2.6: 撰寫安全性測試（2 小時）
  - 惡意檔案上傳測試
  - XSS 攻擊測試
  - 檔案偽造測試

### Phase 3: 效能優化（2 天）

**Frontend 任務**:
- [ ] Task 3.1: 優化圖片壓縮策略（2 小時）
  - 智能壓縮（根據原始大小調整壓縮參數）
  - Web Worker 壓縮（不阻塞 UI）

- [ ] Task 3.2: 實作 Lazy Loading（2 小時）
  - 使用 Intersection Observer
  - Skeleton Loading 狀態

- [ ] Task 3.3: 優化 React Query Cache（1 小時）
  - 調整 staleTime 和 cacheTime
  - 實作預載入策略（hover 時預載入）

**Backend 任務**:
- [ ] Task 3.4: Backend 端圖片壓縮（2 小時）
  - 實作 `ImageService@compressImage()`
  - 整合到上傳流程

- [ ] Task 3.5: 考慮 CDN 快取策略（1 小時）
  - 研究 CloudFront/Cloudflare 整合
  - 制定遷移計畫（Out of Scope，僅規劃）

**測試任務**:
- [ ] Task 3.6: 效能測試（2 小時）
  - Lighthouse CI 測試
  - 載入時間測試
  - 大量 Avatar 渲染測試

### Phase 4: 使用者體驗優化（1 天）

**Frontend 任務**:
- [ ] Task 4.1: 優化上傳流程 UI（2 小時）
  - Loading 狀態優化
  - 壓縮進度顯示
  - 成功/錯誤訊息優化

- [ ] Task 4.2: 實作拖放上傳（1 小時）
  - Drag & Drop 支援
  - 拖放區域視覺化

- [ ] Task 4.3: 響應式設計優化（1 小時）
  - 不同裝置的 Avatar 尺寸
  - 觸控友善的相機按鈕

**測試任務**:
- [ ] Task 4.4: UX 測試（2 小時）
  - 多裝置測試（Desktop/Tablet/Mobile）
  - 觸控裝置測試
  - 無障礙性測試

### Phase 5: 測試與驗證（1 天）

- [ ] Task 5.1: E2E 測試完整覆蓋（3 小時）
  - 上傳流程測試
  - 錯誤處理測試
  - 跨頁面同步測試

- [ ] Task 5.2: Visual Regression 測試（2 小時）
  - Avatar 組件快照測試
  - 不同狀態和尺寸測試

- [ ] Task 5.3: Security 測試（1 小時）
  - 惡意檔案測試
  - XSS 攻擊測試

- [ ] Task 5.4: 撰寫最終驗證報告（2 小時）
  - 彙整所有測試結果
  - 列出已完成和未完成項目
  - 輸出: `reports/avatar-optimization-report.md`

---

## 8. 驗收標準

### 8.1 功能驗收

**Must Have**:
- [ ] 所有頁面（search、detail、dashboard）Avatar 正確顯示
- [ ] Fallback 機制正常運作（無 Avatar 時顯示姓名縮寫）
- [ ] 上傳流程完整（選擇 → 預覽 → 上傳 → 儲存 → 更新）
- [ ] 檔案驗證完整（類型、大小、內容）
- [ ] 錯誤處理完善（具體錯誤訊息）
- [ ] 跨頁面同步更新

**Should Have**:
- [ ] 圖片自動壓縮（> 2MB 或 > 400x400）
- [ ] Lazy loading 實作（列表頁面）
- [ ] Loading 狀態明確（上傳中、壓縮中）
- [ ] 響應式設計（Desktop/Tablet/Mobile）

### 8.2 效能驗收

- [ ] 單張 Avatar 載入時間 < 500ms
- [ ] 列表頁面（20 個 Avatar）載入時間 < 2s
- [ ] LCP < 2.5s
- [ ] FID < 100ms
- [ ] CLS < 0.1

### 8.3 安全性驗收

- [ ] 檔案類型白名單驗證通過
- [ ] MIME type 檢查通過
- [ ] 檔案大小限制通過（前端 + 後端）
- [ ] 圖片內容驗證通過（GD）
- [ ] XSS 攻擊防護測試通過

### 8.4 測試覆蓋驗收

- [ ] E2E 測試覆蓋率 ≥ 80%
- [ ] 單元測試覆蓋率 ≥ 90%
- [ ] Visual Regression 測試建立
- [ ] Performance 測試建立（Lighthouse CI）

---

## 9. 風險與依賴

### 9.1 潛在風險

#### 風險 1: 圖片壓縮品質不佳
- **描述**: 壓縮後圖片模糊或失真
- **機率**: 中
- **影響**: 中（影響使用者體驗）
- **緩解措施**:
  - 使用高品質壓縮演算法（browser-image-compression）
  - 設定適當的壓縮參數（80% 品質）
  - 提供壓縮前預覽，讓使用者確認
  - 允許使用者調整壓縮品質（未來功能）

#### 風險 2: 瀏覽器相容性問題
- **描述**: 部分舊版瀏覽器可能不支援 Intersection Observer 或 Web Worker
- **機率**: 低
- **影響**: 低（僅影響舊版瀏覽器）
- **緩解措施**:
  - 使用 Polyfill（core-js）
  - 提供降級方案（不使用 Lazy loading）
  - 限制支援的瀏覽器版本（文檔說明）

#### 風險 3: 效能未達預期
- **描述**: 大量 Avatar 載入時效能仍然不佳
- **機率**: 低
- **影響**: 中（影響使用者體驗）
- **緩解措施**:
  - 實作更激進的 Lazy loading（更大的 rootMargin）
  - 考慮虛擬化列表（react-window）
  - 考慮 CDN 快取（未來功能）

#### 風險 4: 安全性漏洞
- **描述**: 惡意檔案繞過驗證機制
- **機率**: 低
- **影響**: 高（安全風險）
- **緩解措施**:
  - 多層驗證（前端 + 後端）
  - 定期安全審計
  - 監控異常上傳行為
  - 限制上傳頻率（Rate limiting）

### 9.2 依賴項目

#### 外部依賴
- **browser-image-compression**: 前端圖片壓縮
  - **風險**: 套件維護停止或有安全漏洞
  - **緩解**: 定期更新，關注 npm audit

- **Next.js Image**: 圖片優化和 Lazy loading
  - **風險**: Next.js 升級可能導致 breaking changes
  - **緩解**: 遵循 Next.js 升級指南，謹慎升級

#### 內部依賴
- **React Query**: Cache 管理
  - **風險**: 版本升級可能影響現有功能
  - **緩解**: 充分測試後再升級

- **Laravel GD/Imagick**: Backend 圖片處理
  - **風險**: 伺服器環境未安裝 GD 或 Imagick
  - **緩解**: Docker 環境確保依賴已安裝

---

## 10. 時程規劃（參考）

### 總時程: 7 個工作天

| Phase | 任務 | 時間 | 負責人 |
|-------|------|------|--------|
| Phase 1 | 驗證現有功能 | 1 天 | QA Engineer |
| Phase 2 | 安全性加強 | 2 天 | Backend + Frontend |
| Phase 3 | 效能優化 | 2 天 | Frontend + DevOps |
| Phase 4 | 使用者體驗優化 | 1 天 | Frontend |
| Phase 5 | 測試與驗證 | 1 天 | QA Engineer |

### 里程碑

- **Day 3**: 完成驗證和安全性加強
- **Day 5**: 完成效能優化
- **Day 6**: 完成 UX 優化
- **Day 7**: 完成全面測試和驗證報告

---

## 11. 後續規劃

### 短期（1-3 個月）
- [ ] 監控 Avatar 載入效能（Real User Monitoring）
- [ ] 收集使用者回饋（上傳體驗、壓縮品質）
- [ ] 調整壓縮參數（根據使用者回饋）

### 中期（3-6 個月）
- [ ] 考慮引入 CDN（如 CloudFront/Cloudflare）
- [ ] 實作圖片裁切功能（crop & rotate）
- [ ] 實作多頭像歷史版本管理（如需要）

### 長期（6-12 個月）
- [ ] 考慮獨立的圖片服務（微服務架構）
- [ ] 實作 AI 背景移除/美顏（如預算允許）
- [ ] 實作管理員審核 Avatar 功能

---

## 12. 附錄

### 12.1 參考資料

**技術文檔**:
- [browser-image-compression](https://github.com/Donaldcwl/browser-image-compression)
- [Next.js Image Optimization](https://nextjs.org/docs/app/building-your-application/optimizing/images)
- [Intersection Observer API](https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API)
- [Laravel Image Intervention](https://image.intervention.io/)

**設計系統**:
- [frontend/docs/design-system.md](../../frontend/docs/design-system.md)
- [Avatar Component Spec](../../openspec/specs/frontend/ui-components.md#avatar)

**效能標準**:
- [.claude/knowledge/workflow/metrics-standards.md](../../.claude/knowledge/workflow/metrics-standards.md)

**競品分析**:
- LinkedIn Avatar 上傳流程
- GitHub Avatar 管理
- Twitter Profile Picture 編輯

### 12.2 專業術語

- **Avatar**: 使用者頭像，業務員的個人代表圖片
- **Fallback**: 後備機制，當 Avatar 不存在或載入失敗時的替代顯示
- **Data URL**: `data:image/jpeg;base64,...` 格式的圖片資料
- **Lazy Loading**: 延遲載入，只載入 viewport 內的圖片
- **Compression**: 圖片壓縮，減少檔案大小
- **MIME Type**: 檔案媒體類型，如 `image/jpeg`
- **Base64**: 二進制資料的文字編碼方式
- **XSS**: Cross-Site Scripting，跨站腳本攻擊
- **CSRF**: Cross-Site Request Forgery，跨站請求偽造
- **LCP**: Largest Contentful Paint，最大內容繪製時間
- **FID**: First Input Delay，首次輸入延遲
- **CLS**: Cumulative Layout Shift，累積版面配置位移

---

**建立日期**: 2026-01-21
**最後更新**: 2026-01-21
**版本**: 1.0
**狀態**: Proposal
**下一步**: 需求確認 → 規格撰寫
