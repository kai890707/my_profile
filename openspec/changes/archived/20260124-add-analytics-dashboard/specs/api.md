# API 規格文檔 - Analytics Dashboard

**Feature**: Analytics Dashboard
**Version**: 1.0
**Last Updated**: 2026-01-24

---

## 📋 概述

本文檔定義 Analytics Dashboard 功能的所有 Backend API 端點。包含：

- **業務員端點** (3 個) - 業務員查看自己的數據
- **管理員端點** (4 個) - 管理員查看平台整體數據

**認證機制**: JWT Bearer Token
**API 前綴**: `/api`
**回應格式**: JSON

---

## 🔐 認證與授權

所有端點都需要 JWT 認證：

```http
Authorization: Bearer {access_token}
```

**角色權限**:
- `salesperson` - 可存取業務員端點 (只能查看自己的數據)
- `admin` - 可存取管理員端點 (可查看所有數據)

**錯誤回應**:

```json
// 401 Unauthorized - Token 無效或過期
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Token is invalid or expired"
  }
}

// 403 Forbidden - 權限不足
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "You do not have permission to access this resource"
  }
}
```

---

## 📊 業務員端點 (Salesperson Endpoints)

### 1. GET /api/salesperson/analytics/stats

**描述**: 取得業務員統計數據（總瀏覽數、總聯繫數、增長率）

**認證**: Required (JWT)
**授權**: `salesperson` 或 `admin`
**權限**: 業務員只能查看自己的數據

#### Query Parameters

| 參數 | 型別 | 必填 | 預設值 | 說明 | 驗證規則 |
|-----|------|-----|-------|------|---------|
| range | string | 否 | 7days | 時間範圍 | in:today,7days,30days |

**時間範圍說明**:
- `today` - 今日 (00:00 ~ 現在)
- `7days` - 過去 7 天 (含今日)
- `30days` - 過去 30 天 (含今日)

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "profile_views": 342,
    "contact_requests": 28,
    "unique_visitors": 287,
    "conversion_rate": 8.19,
    "previous_period": {
      "profile_views": 298,
      "contact_requests": 22,
      "unique_visitors": 251,
      "conversion_rate": 7.38
    },
    "growth": {
      "profile_views_percent": 14.77,
      "contact_requests_percent": 27.27,
      "unique_visitors_percent": 14.34,
      "conversion_rate_percent": 10.98
    },
    "range": "7days"
  },
  "meta": {
    "timestamp": "2026-01-24T10:30:00Z"
  }
}
```

**欄位說明**:
- `profile_views` - 檔案瀏覽總次數
- `contact_requests` - 聯繫請求總數
- `unique_visitors` - 獨立訪客數 (基於 IP hash 去重)
- `conversion_rate` - 轉換率 (contact_requests / profile_views * 100)
- `previous_period` - 上個時段的數據 (用於比較)
- `growth` - 增長率 (百分比，正數表示成長，負數表示下降)

#### Error Responses

```json
// 400 Bad Request - 無效的 range 參數
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The selected range is invalid.",
    "details": {
      "range": ["The selected range is invalid."]
    }
  }
}

// 403 Forbidden - 嘗試查看他人數據
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "You can only view your own analytics"
  }
}
```

#### 業務規則

- **BR-001**: 業務員只能查看自己的數據
- **BR-002**: 混合查詢策略 (歷史用 daily_analytics, 今日用即時查詢)
- **BR-003**: 上個時段計算:
  - `today` → 昨天
  - `7days` → 前 7 天 (day -14 to day -8)
  - `30days` → 前 30 天 (day -60 to day -31)
- **BR-004**: 增長率計算: `((current - previous) / previous) * 100`
- **BR-005**: 若上個時段數據為 0，增長率顯示為 `null`

#### 效能要求

- **P50 回應時間**: < 100ms
- **P95 回應時間**: < 200ms
- **P99 回應時間**: < 500ms

#### 範例請求

```bash
# 查詢今日數據
curl -X GET "http://localhost:8080/api/salesperson/analytics/stats?range=today" \
  -H "Authorization: Bearer {token}"

# 查詢過去 7 天數據
curl -X GET "http://localhost:8080/api/salesperson/analytics/stats?range=7days" \
  -H "Authorization: Bearer {token}"
```

---

### 2. GET /api/salesperson/analytics/trends

**描述**: 取得業務員趨勢圖表數據（日期 + 數值陣列）

**認證**: Required (JWT)
**授權**: `salesperson` 或 `admin`
**權限**: 業務員只能查看自己的數據

#### Query Parameters

| 參數 | 型別 | 必填 | 預設值 | 說明 | 驗證規則 |
|-----|------|-----|-------|------|---------|
| range | string | 否 | 7days | 時間範圍 | in:today,7days,30days |

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "trends": [
      {
        "date": "2026-01-18",
        "profile_views": 45,
        "contact_requests": 3,
        "unique_visitors": 38
      },
      {
        "date": "2026-01-19",
        "profile_views": 52,
        "contact_requests": 5,
        "unique_visitors": 44
      },
      {
        "date": "2026-01-20",
        "profile_views": 48,
        "contact_requests": 4,
        "unique_visitors": 41
      },
      {
        "date": "2026-01-21",
        "profile_views": 61,
        "contact_requests": 6,
        "unique_visitors": 52
      },
      {
        "date": "2026-01-22",
        "profile_views": 55,
        "contact_requests": 4,
        "unique_visitors": 47
      },
      {
        "date": "2026-01-23",
        "profile_views": 49,
        "contact_requests": 3,
        "unique_visitors": 41
      },
      {
        "date": "2026-01-24",
        "profile_views": 32,
        "contact_requests": 3,
        "unique_visitors": 24
      }
    ],
    "range": "7days"
  },
  "meta": {
    "timestamp": "2026-01-24T10:30:00Z"
  }
}
```

**欄位說明**:
- `trends` - 趨勢數據陣列 (按日期升序排列)
- `date` - 日期 (YYYY-MM-DD 格式)
- `profile_views` - 當日檔案瀏覽次數
- `contact_requests` - 當日聯繫請求數
- `unique_visitors` - 當日獨立訪客數

**資料來源**:
- 歷史日期 (昨天及更早) → `daily_analytics` 表
- 今日 (當天) → `contact_events` 表即時查詢

#### Error Responses

```json
// 400 Bad Request - 無效的 range 參數
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The selected range is invalid.",
    "details": {
      "range": ["The selected range is invalid."]
    }
  }
}
```

#### 業務規則

- **BR-006**: 趨勢數據必須按日期升序排列
- **BR-007**: `today` 範圍只返回 1 筆數據 (當天)
- **BR-008**: `7days` 範圍返回 7 筆數據 (最近 7 天)
- **BR-009**: `30days` 範圍返回 30 筆數據 (最近 30 天)
- **BR-010**: 若某日無數據，該日數值為 0 (不跳過該日期)
- **BR-011**: 混合查詢策略 (歷史用 daily_analytics, 今日用即時查詢)

#### 效能要求

- **P50 回應時間**: < 150ms
- **P95 回應時間**: < 300ms
- **P99 回應時間**: < 500ms

#### 範例請求

```bash
# 查詢過去 7 天趨勢
curl -X GET "http://localhost:8080/api/salesperson/analytics/trends?range=7days" \
  -H "Authorization: Bearer {token}"

# 查詢過去 30 天趨勢
curl -X GET "http://localhost:8080/api/salesperson/analytics/trends?range=30days" \
  -H "Authorization: Bearer {token}"
```

---

### 3. GET /api/salesperson/analytics/recent-contacts

**描述**: 取得最近聯繫請求列表（最新 10 筆）

**認證**: Required (JWT)
**授權**: `salesperson` 或 `admin`
**權限**: 業務員只能查看自己的聯繫請求

#### Query Parameters

無

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "contacts": [
      {
        "id": 245,
        "customer_name": "王小明",
        "customer_email": "wang@example.com",
        "customer_phone": "0912345678",
        "message": "您好，我對您的產品很有興趣，想了解更多詳情...",
        "status": "pending",
        "created_at": "2026-01-24T09:15:00Z"
      },
      {
        "id": 243,
        "customer_name": "李大華",
        "customer_email": "lee@example.com",
        "customer_phone": null,
        "message": "請問貴公司有提供企業方案嗎？我們需要...",
        "status": "contacted",
        "created_at": "2026-01-23T16:22:00Z"
      },
      {
        "id": 241,
        "customer_name": "陳美玲",
        "customer_email": "chen@example.com",
        "customer_phone": "0987654321",
        "message": "想詢問價格和交貨時間",
        "status": "closed",
        "created_at": "2026-01-23T11:05:00Z"
      }
    ],
    "total": 10
  },
  "meta": {
    "timestamp": "2026-01-24T10:30:00Z"
  }
}
```

**欄位說明**:
- `contacts` - 聯繫請求陣列 (按建立時間倒序排列)
- `id` - 聯繫請求 ID
- `customer_name` - 客戶姓名
- `customer_email` - 客戶 Email (解密後的明文)
- `customer_phone` - 客戶電話 (解密後的明文，可為 null)
- `message` - 聯繫訊息 (完整內容)
- `status` - 狀態 (`pending`, `contacted`, `closed`)
- `created_at` - 建立時間 (ISO 8601 格式)
- `total` - 返回筆數 (最多 10 筆)

#### Error Responses

```json
// 403 Forbidden - 嘗試查看他人聯繫請求
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "You can only view your own contact requests"
  }
}
```

#### 業務規則

- **BR-012**: 只返回最新 10 筆聯繫請求
- **BR-013**: 按 `created_at` 倒序排列 (最新的在前)
- **BR-014**: 業務員只能查看 `salesperson_id` 等於自己的記錄
- **BR-015**: Email 和 Phone 自動解密 (Model 使用 encrypted cast)
- **BR-016**: 若無聯繫請求，返回空陣列 `[]`

#### 效能要求

- **P50 回應時間**: < 50ms
- **P95 回應時間**: < 100ms
- **P99 回應時間**: < 200ms

#### 範例請求

```bash
# 查詢最近聯繫請求
curl -X GET "http://localhost:8080/api/salesperson/analytics/recent-contacts" \
  -H "Authorization: Bearer {token}"
```

---

## 🔧 管理員端點 (Admin Endpoints)

### 4. GET /api/admin/analytics/overview

**描述**: 取得平台整體概覽數據（總業務員數、總瀏覽數、總聯繫數、平台轉換率）

**認證**: Required (JWT)
**授權**: `admin`

#### Query Parameters

| 參數 | 型別 | 必填 | 預設值 | 說明 | 驗證規則 |
|-----|------|-----|-------|------|---------|
| range | string | 否 | 30days | 時間範圍 | in:7days,30days |

**注意**: 管理員端點不提供 `today` 範圍（平台數據需要較長時間範圍才有意義）

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "total_salespersons": 148,
    "total_profile_views": 12547,
    "total_contact_requests": 892,
    "total_unique_visitors": 10234,
    "platform_conversion_rate": 7.11,
    "active_salespersons": 132,
    "inactive_salespersons": 16,
    "activity_rate": 89.19,
    "previous_period": {
      "total_profile_views": 11203,
      "total_contact_requests": 798,
      "platform_conversion_rate": 7.12,
      "active_salespersons": 128
    },
    "growth": {
      "profile_views_percent": 11.99,
      "contact_requests_percent": 11.78,
      "conversion_rate_percent": -0.14,
      "active_salespersons_percent": 3.13
    },
    "range": "30days"
  },
  "meta": {
    "timestamp": "2026-01-24T10:30:00Z"
  }
}
```

**欄位說明**:
- `total_salespersons` - 平台總業務員數 (role = 'salesperson', status = 'approved')
- `total_profile_views` - 所有業務員檔案的總瀏覽數
- `total_contact_requests` - 平台產生的總聯繫請求數
- `total_unique_visitors` - 總獨立訪客數
- `platform_conversion_rate` - 平台整體轉換率 (total_contact_requests / total_profile_views * 100)
- `active_salespersons` - 活躍業務員數 (在指定時間範圍內有至少 1 次 profile_view 事件)
- `inactive_salespersons` - 低活躍業務員數 (在指定時間範圍內 0 次 profile_view)
- `activity_rate` - 活躍率 (active_salespersons / total_salespersons * 100)
- `previous_period` - 上個時段的數據
- `growth` - 增長率

#### Error Responses

```json
// 403 Forbidden - 非管理員嘗試存取
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "This endpoint is only accessible to administrators"
  }
}

// 400 Bad Request - 無效的 range 參數
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The selected range is invalid.",
    "details": {
      "range": ["The selected range is invalid."]
    }
  }
}
```

#### 業務規則

- **BR-017**: 只有 admin 角色可以存取
- **BR-018**: 總業務員數統計 approved 狀態的業務員
- **BR-019**: 活躍業務員定義: 在時間範圍內有至少 1 次 profile_view 事件
- **BR-020**: 混合查詢策略 (歷史用 daily_analytics, 今日用即時查詢)
- **BR-021**: 上個時段計算:
  - `7days` → 前 7 天 (day -14 to day -8)
  - `30days` → 前 30 天 (day -60 to day -31)

#### 效能要求

- **P50 回應時間**: < 200ms
- **P95 回應時間**: < 500ms
- **P99 回應時間**: < 1000ms

#### 範例請求

```bash
# 查詢過去 30 天平台概覽
curl -X GET "http://localhost:8080/api/admin/analytics/overview?range=30days" \
  -H "Authorization: Bearer {admin_token}"
```

---

### 5. GET /api/admin/analytics/top-salespersons

**描述**: 取得瀏覽數最高的前 10 名業務員

**認證**: Required (JWT)
**授權**: `admin`

#### Query Parameters

| 參數 | 型別 | 必填 | 預設值 | 說明 | 驗證規則 |
|-----|------|-----|-------|------|---------|
| range | string | 否 | 30days | 時間範圍 | in:7days,30days |

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "top_salespersons": [
      {
        "salesperson_id": 45,
        "name": "張三豐",
        "email": "zhang@example.com",
        "profile_views": 523,
        "contact_requests": 48,
        "unique_visitors": 445,
        "conversion_rate": 9.18
      },
      {
        "salesperson_id": 72,
        "name": "李四海",
        "email": "lee@example.com",
        "profile_views": 487,
        "contact_requests": 41,
        "unique_visitors": 412,
        "conversion_rate": 8.42
      },
      {
        "salesperson_id": 103,
        "name": "王五郎",
        "email": "wang@example.com",
        "profile_views": 456,
        "contact_requests": 35,
        "unique_visitors": 389,
        "conversion_rate": 7.68
      }
    ],
    "range": "30days"
  },
  "meta": {
    "timestamp": "2026-01-24T10:30:00Z",
    "total_count": 10
  }
}
```

**欄位說明**:
- `top_salespersons` - 前 10 名業務員陣列 (按 profile_views 降序排列)
- `salesperson_id` - 業務員 User ID
- `name` - 業務員姓名
- `email` - 業務員 Email
- `profile_views` - 檔案瀏覽次數
- `contact_requests` - 聯繫請求數
- `unique_visitors` - 獨立訪客數
- `conversion_rate` - 轉換率

#### Error Responses

```json
// 403 Forbidden - 非管理員嘗試存取
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "This endpoint is only accessible to administrators"
  }
}
```

#### 業務規則

- **BR-022**: 只返回前 10 名業務員
- **BR-023**: 按 `profile_views` 降序排列
- **BR-024**: 若業務員數 < 10，返回實際數量
- **BR-025**: 只統計 approved 狀態的業務員
- **BR-026**: 混合查詢策略 (歷史用 daily_analytics, 今日用即時查詢)

#### 效能要求

- **P50 回應時間**: < 200ms
- **P95 回應時間**: < 400ms
- **P99 回應時間**: < 800ms

#### 範例請求

```bash
# 查詢過去 30 天 Top 10 業務員
curl -X GET "http://localhost:8080/api/admin/analytics/top-salespersons?range=30days" \
  -H "Authorization: Bearer {admin_token}"
```

---

### 6. GET /api/admin/analytics/activity

**描述**: 取得業務員活躍度分析（活躍數、低活躍數、活躍率）

**認證**: Required (JWT)
**授權**: `admin`

#### Query Parameters

無 (固定統計過去 7 天)

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "total_salespersons": 148,
    "active_salespersons": 132,
    "inactive_salespersons": 16,
    "activity_rate": 89.19,
    "activity_breakdown": [
      {
        "range": "0 views",
        "count": 16,
        "percentage": 10.81
      },
      {
        "range": "1-10 views",
        "count": 28,
        "percentage": 18.92
      },
      {
        "range": "11-50 views",
        "count": 45,
        "percentage": 30.41
      },
      {
        "range": "51-100 views",
        "count": 32,
        "percentage": 21.62
      },
      {
        "range": "100+ views",
        "count": 27,
        "percentage": 18.24
      }
    ]
  },
  "meta": {
    "timestamp": "2026-01-24T10:30:00Z",
    "period": "Last 7 days"
  }
}
```

**欄位說明**:
- `total_salespersons` - 平台總業務員數
- `active_salespersons` - 活躍業務員數 (過去 7 天有至少 1 次 profile_view)
- `inactive_salespersons` - 低活躍業務員數 (過去 7 天 0 次 profile_view)
- `activity_rate` - 活躍率 (%)
- `activity_breakdown` - 活躍度分布 (按瀏覽數範圍統計)

#### Error Responses

```json
// 403 Forbidden - 非管理員嘗試存取
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "This endpoint is only accessible to administrators"
  }
}
```

#### 業務規則

- **BR-027**: 固定統計過去 7 天 (不可自訂時間範圍)
- **BR-028**: 活躍業務員定義: 過去 7 天有至少 1 次 profile_view 事件
- **BR-029**: 只統計 approved 狀態的業務員
- **BR-030**: 活躍度分布按瀏覽數範圍分組

#### 效能要求

- **P50 回應時間**: < 150ms
- **P95 回應時間**: < 300ms
- **P99 回應時間**: < 600ms

#### 範例請求

```bash
# 查詢業務員活躍度
curl -X GET "http://localhost:8080/api/admin/analytics/activity" \
  -H "Authorization: Bearer {admin_token}"
```

---

### 7. GET /api/admin/analytics/growth

**描述**: 取得平台成長趨勢（日/週/月 瀏覽數和聯繫數趨勢）

**認證**: Required (JWT)
**授權**: `admin`

#### Query Parameters

| 參數 | 型別 | 必填 | 預設值 | 說明 | 驗證規則 |
|-----|------|-----|-------|------|---------|
| range | string | 否 | 30days | 時間範圍 | in:7days,30days |

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "trends": [
      {
        "date": "2025-12-26",
        "profile_views": 385,
        "contact_requests": 28,
        "unique_visitors": 312,
        "active_salespersons": 125
      },
      {
        "date": "2025-12-27",
        "profile_views": 412,
        "contact_requests": 31,
        "unique_visitors": 345,
        "active_salespersons": 128
      },
      {
        "date": "2025-12-28",
        "profile_views": 398,
        "contact_requests": 29,
        "unique_visitors": 329,
        "active_salespersons": 126
      }
    ],
    "range": "30days"
  },
  "meta": {
    "timestamp": "2026-01-24T10:30:00Z"
  }
}
```

**欄位說明**:
- `trends` - 趨勢數據陣列 (按日期升序排列)
- `date` - 日期 (YYYY-MM-DD 格式)
- `profile_views` - 當日平台總瀏覽數
- `contact_requests` - 當日平台總聯繫數
- `unique_visitors` - 當日平台總獨立訪客數
- `active_salespersons` - 當日活躍業務員數

#### Error Responses

```json
// 403 Forbidden - 非管理員嘗試存取
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "This endpoint is only accessible to administrators"
  }
}
```

#### 業務規則

- **BR-031**: 趨勢數據必須按日期升序排列
- **BR-032**: `7days` 範圍返回 7 筆數據
- **BR-033**: `30days` 範圍返回 30 筆數據
- **BR-034**: 若某日無數據，該日數值為 0
- **BR-035**: 混合查詢策略 (歷史用 daily_analytics, 今日用即時查詢)
- **BR-036**: 當日活躍業務員數 = 當日有至少 1 次 profile_view 的業務員數

#### 效能要求

- **P50 回應時間**: < 250ms
- **P95 回應時間**: < 500ms
- **P99 回應時間**: < 1000ms

#### 範例請求

```bash
# 查詢過去 30 天平台成長趨勢
curl -X GET "http://localhost:8080/api/admin/analytics/growth?range=30days" \
  -H "Authorization: Bearer {admin_token}"
```

---

## 📊 統一錯誤處理

### 5xx Server Errors

```json
// 500 Internal Server Error - 伺服器錯誤
{
  "success": false,
  "error": {
    "code": "INTERNAL_SERVER_ERROR",
    "message": "An unexpected error occurred. Please try again later."
  }
}
```

### Rate Limiting

```json
// 429 Too Many Requests - 超過速率限制
{
  "success": false,
  "error": {
    "code": "RATE_LIMIT_EXCEEDED",
    "message": "Too many requests. Please try again later.",
    "retry_after": 60
  }
}
```

**速率限制**:
- 業務員端點: 60 requests / minute / user
- 管理員端點: 120 requests / minute / user

---

## 🔒 安全考量

### Rate Limiting (防止資料探測)

- 業務員端點: 60 requests/minute/user
- 管理員端點: 120 requests/minute/user
- 使用 Laravel Rate Limiting Middleware

### 資料權限控制

- 業務員只能查看自己的數據 (透過 JWT user_id 驗證)
- 管理員可以查看所有數據
- 使用 Policy 和 Middleware 強制執行

### 敏感資料保護

- IP 地址使用 SHA256 雜湊儲存 (不儲存原始 IP)
- User Agent 儲存但不暴露給前端
- 聯繫請求的 Email/Phone 使用 Laravel Encrypted Cast

### API 安全

- 所有端點都需要 JWT 認證
- JWT Token 過期時間: 60 分鐘 (Access Token)
- 使用 HTTPS (生產環境)
- CORS 設定限制來源

---

## 📈 效能優化策略

### 混合彙總策略

- **歷史數據** (昨天及更早) → 查詢 `daily_analytics` 表 (預先彙總)
- **今日數據** (當天) → 查詢 `contact_events` 表 (即時查詢)
- **合併邏輯** → 在 API 層合併兩者

### 資料庫優化

- 使用複合索引加速查詢
- 使用 `SUM()` 在資料庫層彙總
- 避免 N+1 查詢問題

### 快取策略

- 歷史數據可快取 24 小時 (不會再變動)
- 今日數據不快取 (即時數據)
- 使用 Laravel Cache Tags

---

## 📝 測試要求

每個 API 端點必須包含以下測試：

### Feature Tests

1. **成功情境測試**
   - 正確的參數返回 200 OK
   - 回應格式正確
   - 資料內容正確

2. **驗證失敗測試**
   - 無效的 range 參數返回 400
   - 錯誤訊息正確

3. **認證失敗測試**
   - 無 Token 返回 401
   - 過期 Token 返回 401

4. **授權失敗測試**
   - 業務員嘗試查看他人數據返回 403
   - 非管理員嘗試存取管理員端點返回 403

5. **邊界情況測試**
   - 無數據時返回空陣列或 0 值
   - 上個時段數據為 0 時增長率為 null

### 效能測試

- 測試 P95 回應時間符合要求
- 測試併發請求處理能力

### 測試覆蓋率目標

- Feature Tests: >= 95%
- 所有 API 端點都有完整測試

---

## 📚 參考資料

- **Proposal**: `../proposal.md`
- **資料模型規格**: `./data-model.md`
- **業務規則規格**: `./business-rules.md`
- **系統架構規格**: `./architecture.md`
- **Laravel 文檔**: https://laravel.com/docs/11.x
- **JWT 文檔**: https://jwt-auth.readthedocs.io/

---

**文檔版本**: 1.0
**最後更新**: 2026-01-24
**維護者**: Backend Team
