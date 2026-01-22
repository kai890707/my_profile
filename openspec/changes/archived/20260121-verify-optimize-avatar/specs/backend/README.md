# Backend 技術規格總覽：Avatar 驗證與優化

**功能**: Avatar 功能完整驗證與安全性優化
**日期**: 2026-01-21
**版本**: 1.0
**狀態**: Specification

---

## 📋 規格文件索引

| 文件 | 大小 | 說明 |
|-----|------|------|
| [api.md](./api.md) | 17KB | API 端點規格、請求/回應格式、錯誤處理 |
| [data-model.md](./data-model.md) | 17KB | 資料庫結構、索引策略、備份恢復 |
| [business-rules.md](./business-rules.md) | 20KB | 驗證規則、業務邏輯、邊界情況 |
| [architecture.md](./architecture.md) | 30KB | 服務層設計、流程圖、測試架構 |

**總計**: 84KB 完整技術規格

---

## 🎯 規格摘要

### 核心目標

1. **安全性增強**
   - 檔案類型白名單驗證（JPEG, PNG, WebP, GIF）
   - MIME type 檢查（防止偽造副檔名）
   - 檔案內容驗證（使用 GD/Imagick）
   - 防止 XSS 攻擊（禁止 SVG）

2. **效能優化**
   - 圖片自動壓縮（400x400, 80% 品質）
   - 檔案大小限制（2MB）
   - 回應時間 < 500ms (P95)

3. **使用者體驗**
   - 清晰的錯誤訊息
   - 保持寬高比（不變形）
   - 支援透明背景（PNG/GIF）

---

## 📊 技術架構

### 分層設計

```
┌─────────────────────────────┐
│ Controller Layer            │  SalespersonController
│ - HTTP 請求處理             │  - 權限檢查
│ - 回應格式化                │  - 錯誤處理
└─────────────┬───────────────┘
              │
┌─────────────▼───────────────┐
│ Service Layer               │  AvatarService
│ - 業務邏輯                   │  - 驗證邏輯
│ - 圖片處理協調               │  ImageProcessingService
└─────────────┬───────────────┘  - 圖片壓縮
              │                  - 尺寸調整
┌─────────────▼───────────────┐
│ Data Layer                  │  SalespersonProfile Model
│ - 資料存取                   │  - Eloquent ORM
│ - 資料一致性                 │  - BLOB 儲存
└─────────────────────────────┘
```

### 關鍵組件

**新增服務**:
- `AvatarService` - Avatar 驗證與處理協調
- `ImageProcessingService` - 圖片壓縮與調整

**現有組件增強**:
- `SalespersonController::updateProfile()` - 整合 AvatarService
- `UpdateSalespersonProfileRequest` - 增強驗證規則

---

## 🔧 技術要求

### 環境要求

```
PHP: >= 8.4
Laravel: 11.x
MySQL: 8.0
PHP Extensions:
  - GD (圖片處理)
  - EXIF (可選，方向校正)
```

### 依賴套件

```json
{
  "require": {
    "php": "^8.4",
    "laravel/framework": "^11.0"
  }
}
```

---

## 📝 API 端點摘要

### PUT /api/salesperson/profile

**用途**: 更新業務員個人資料（含 Avatar）

**認證**: Required (JWT Bearer Token)

**請求範例**:
```json
{
  "full_name": "王小明",
  "phone": "0912345678",
  "avatar": "data:image/jpeg;base64,/9j/4AAQSkZJRg..."
}
```

**成功回應 (200)**:
```json
{
  "success": true,
  "profile": {
    "id": 1,
    "full_name": "王小明",
    "avatar": "data:image/jpeg;base64,...",
    "avatar_size": 245678,
    "avatar_mime": "image/jpeg"
  },
  "message": "個人資料已更新"
}
```

**錯誤回應**:
- `401` Unauthorized - 未認證
- `403` Forbidden - 非業務員
- `413` Payload Too Large - 檔案過大（> 2MB）
- `415` Unsupported Media Type - 不支援的格式
- `422` Unprocessable Entity - 驗證失敗
- `429` Too Many Requests - Rate Limit
- `500` Internal Server Error - 處理失敗

---

## 🗄️ 資料模型摘要

### salesperson_profiles 表

**Avatar 相關欄位**:

| 欄位 | 型別 | 說明 |
|-----|------|------|
| avatar_data | MEDIUMBLOB | Base64 解碼後的二進制資料 (< 2MB) |
| avatar_mime | VARCHAR(50) | MIME type (image/jpeg, image/png, etc.) |
| avatar_size | INT UNSIGNED | 解碼後的檔案大小 (bytes) |

**資料一致性**:
- 三個欄位必須同時為 NULL 或同時有值
- Laravel Model 層強制執行

**儲存策略**:
- Base64 解碼後儲存（節省 25% 空間）
- MEDIUMBLOB 可容納 16MB（實際限制 2MB）

---

## ✅ 驗證規則摘要

### VR-001: Data URL 格式驗證
```regex
/^data:image\/(jpeg|png|webp|gif);base64,[A-Za-z0-9+\/]+=*$/
```

### VR-002: MIME Type 白名單
```php
['image/jpeg', 'image/png', 'image/webp', 'image/gif']
```
❌ 禁止 SVG（防止 XSS）

### VR-003: Base64 編碼驗證
- 必須可正確解碼（strict mode）

### VR-004: 檔案大小限制
- 解碼後 <= 2MB (2,097,152 bytes)

### VR-005: 圖片內容驗證
- 使用 GD `imagecreatefromstring()` 驗證
- 防止檔案偽造

---

## 🎨 圖片處理策略

### 壓縮規則

**觸發條件**:
- 圖片尺寸 > 400x400
- 檔案大小 > 500KB

**處理策略**:
```
1. 尺寸調整: 壓縮到 400x400 (保持寬高比)
2. 品質壓縮: JPEG 80%, PNG level 8
3. 格式保留: 保留原始格式（不強制轉 JPEG）
4. 透明支援: PNG/GIF 透明背景保留
```

**預期效果**:
```
原始: 2MB, 2048x1536 → 壓縮後: 300KB, 400x300 (壓縮 85%)
原始: 1.5MB, 1200x900 → 壓縮後: 250KB, 400x300 (壓縮 83%)
```

---

## 🔐 安全性設計

### 多層防護

1. **格式驗證** - 白名單策略
2. **MIME type 檢查** - 防止偽造
3. **內容驗證** - GD 解析圖片
4. **大小限制** - 2MB 上限
5. **Rate Limiting** - 10 次/分鐘
6. **權限檢查** - 只能更新自己的 Avatar

### 防護範圍

- ✅ XSS 攻擊（禁止 SVG）
- ✅ 檔案偽造（內容驗證）
- ✅ 資源耗盡（大小限制）
- ✅ DoS 攻擊（Rate Limiting）

---

## 📈 效能標準

### 回應時間（參考 metrics-standards.md）

| 操作 | P50 | P95 | P99 |
|------|-----|-----|-----|
| 更新（無 Avatar） | < 100ms | < 200ms | < 400ms |
| 更新（含 Avatar） | < 300ms | < 500ms | < 1000ms |

### 處理時間分解

| 步驟 | 目標時間 |
|------|---------|
| Base64 解碼 | < 50ms |
| 圖片驗證 (GD) | < 100ms |
| 圖片壓縮 | < 200ms |
| 資料庫寫入 | < 50ms |
| **總計** | **< 400ms** |

### 並發能力

| 指標 | 目標值 |
|------|--------|
| 並發請求數 | >= 20 req/s |
| 錯誤率 | < 0.5% |

---

## 🧪 測試策略

### 測試覆蓋率目標

| 類型 | 目標 |
|------|-----|
| Feature Tests | >= 95% |
| Unit Tests | >= 90% |
| Line Coverage | >= 85% |

### 關鍵測試案例

**Happy Path**:
- [ ] 上傳有效的 JPEG
- [ ] 上傳有效的 PNG
- [ ] 上傳有效的 WebP
- [ ] 上傳有效的 GIF

**Validation**:
- [ ] 拒絕 SVG 檔案
- [ ] 拒絕超過 2MB 的檔案
- [ ] 拒絕無效的 Base64
- [ ] 拒絕損壞的圖片
- [ ] 拒絕偽造的圖片

**Processing**:
- [ ] 大圖片自動壓縮到 400x400
- [ ] 壓縮後檔案大小 < 原始大小
- [ ] 保持寬高比

**Security**:
- [ ] 防止 XSS（拒絕 SVG）
- [ ] 防止檔案偽造
- [ ] Rate Limiting 運作

**Performance**:
- [ ] 處理時間符合標準
- [ ] 並發處理正常

---

## 📦 實作任務拆解

### Phase 1: Service Layer 實作（2 天）

- [ ] 建立 `AvatarService` (4 小時)
- [ ] 建立 `ImageProcessingService` (4 小時)
- [ ] 單元測試 (4 小時)

### Phase 2: Controller 整合（1 天）

- [ ] 修改 `SalespersonController::updateProfile()` (2 小時)
- [ ] 修改 `UpdateSalespersonProfileRequest` (1 小時)
- [ ] Feature 測試 (3 小時)

### Phase 3: 錯誤處理與日誌（1 天）

- [ ] 完善錯誤處理 (2 小時)
- [ ] 新增日誌記錄 (2 小時)
- [ ] 監控指標實作 (2 小時)

### Phase 4: 測試與驗證（1 天）

- [ ] 完整測試套件 (4 小時)
- [ ] 效能測試 (2 小時)
- [ ] 安全測試 (2 小時)

**總計**: 5 個工作天

---

## ✅ 驗收標準

### 功能驗收

- [ ] 所有驗證規則實作並測試通過
- [ ] 圖片壓縮功能正常
- [ ] 權限控制正確
- [ ] Rate Limiting 正常運作
- [ ] 資料一致性保證

### 安全驗收

- [ ] 拒絕 SVG 上傳
- [ ] 防止檔案偽造
- [ ] 防止資源耗盡
- [ ] Rate Limiting 防止濫用

### 效能驗收

- [ ] 圖片處理時間 < 400ms (P95)
- [ ] 並發處理 >= 20 req/s
- [ ] 錯誤率 < 0.5%

### 測試驗收

- [ ] Feature Tests 覆蓋率 >= 95%
- [ ] Unit Tests 覆蓋率 >= 90%
- [ ] PHPStan Level 9 通過

---

## 📚 參考資料

### 內部文檔
- [Proposal](../../proposal.md) - 功能提案與需求
- [量化指標標準](../../../../../.claude/knowledge/workflow/metrics-standards.md)
- [Backend 開發規範](../../../../../my_profile_laravel/CLAUDE.md)

### 外部資源
- [PHP GD Manual](https://www.php.net/manual/en/book.image.php)
- [Laravel Validation](https://laravel.com/docs/11.x/validation)
- [Laravel Service Container](https://laravel.com/docs/11.x/container)

---

## 📞 問題聯絡

如有規格疑問，請聯絡：
- **技術架構**: Senior Software Architect
- **Backend 實作**: Backend Team Lead
- **測試**: QA Engineer

---

**撰寫日期**: 2026-01-21
**作者**: Senior Software Architect
**版本**: 1.0
**狀態**: Ready for Implementation
