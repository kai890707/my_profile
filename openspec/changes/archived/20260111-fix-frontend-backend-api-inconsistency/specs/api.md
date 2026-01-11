# API 規格文檔

**專案**: 前後端 API 不一致修復
**版本**: 1.0
**最後更新**: 2026-01-11

---

## 概述

本文檔定義所有需要新增或修改的 API 端點規格，確保前後端完全一致。

**修復範圍**:
- 新增 8 個缺失的 API 端點
- 修正 2 個 API 回應格式
- 統一 API 回應結構

---

## 認證機制

所有 API 端點（除非特別標註）都需要 JWT 認證：

**Request Header**:
```
Authorization: Bearer {access_token}
```

**認證失敗回應 (401)**:
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Unauthenticated"
  }
}
```

**授權失敗回應 (403)**:
```json
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "You are not authorized to perform this action"
  }
}
```

---

## API 端點

### Phase 1 - Critical APIs 🔴

---

#### 1. GET /salesperson/profile

**描述**: 取得當前登入業務員的個人檔案（路由別名）

**Authentication**: Required (JWT)

**Authorization**: 業務員角色 (`role = 'salesperson'`)

**Request Headers**:
```
Authorization: Bearer {access_token}
```

**Query Parameters**: 無

**Response (200 OK)**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 10,
    "company_id": 5,
    "full_name": "張三",
    "phone": "0912345678",
    "bio": "資深業務員，專注於科技產業",
    "specialties": "軟體銷售、系統整合",
    "service_regions": ["台北市", "新北市"],
    "years_of_experience": 5,
    "rating": 4.5,
    "approval_status": "approved",
    "rejected_reason": null,
    "avatar_url": "https://example.com/avatar/1.jpg",
    "created_at": "2026-01-10T12:00:00Z",
    "updated_at": "2026-01-10T12:00:00Z",
    "user": {
      "id": 10,
      "email": "zhang@example.com",
      "role": "salesperson"
    },
    "company": {
      "id": 5,
      "name": "ABC 科技股份有限公司",
      "approval_status": "approved"
    }
  },
  "message": "Profile retrieved successfully"
}
```

**Error Responses**:
- **401 Unauthorized**: Missing or invalid token
- **403 Forbidden**: User is not a salesperson
- **404 Not Found**: Profile not found

**Business Rules**:
- BR-AUTH-001: 需要有效的 JWT Token
- BR-AUTH-002: 使用者 role 必須是 'salesperson'
- BR-API-001: 回應格式必須包含 `success: boolean`

**Implementation Notes**:
- 此端點是 `GET /profile` 的路由別名
- 指向同一個 Controller 方法: `SalespersonProfileController::me`
- 為了保持前端 API 調用一致性而新增

**Related Endpoints**:
- PUT `/salesperson/profile` - 更新個人檔案（已存在）

---

#### 2. GET /salesperson/experiences

**描述**: 取得當前登入業務員的所有工作經驗列表

**Authentication**: Required (JWT)

**Authorization**: 業務員角色

**Request Headers**:
```
Authorization: Bearer {access_token}
```

**Query Parameters**:
| 參數 | 型別 | 必填 | 說明 | 預設值 |
|-----|------|-----|------|-------|
| sort_by | string | 否 | 排序欄位 (start_date, company, position) | start_date |
| order | string | 否 | 排序方向 (asc, desc) | desc |

**Request Example**:
```
GET /api/salesperson/experiences?sort_by=start_date&order=desc
```

**Response (200 OK)**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 10,
      "company": "ABC Company",
      "position": "Senior Sales Manager",
      "start_date": "2020-01-01",
      "end_date": "2022-12-31",
      "description": "Managed a team of 10 sales representatives, achieved 120% of annual target",
      "approval_status": "approved",
      "rejected_reason": null,
      "approved_by": null,
      "approved_at": null,
      "sort_order": 0,
      "created_at": "2026-01-10T12:00:00Z",
      "updated_at": "2026-01-10T12:00:00Z"
    },
    {
      "id": 2,
      "user_id": 10,
      "company": "XYZ Corporation",
      "position": "Sales Representative",
      "start_date": "2018-06-01",
      "end_date": "2019-12-31",
      "description": "B2B sales, technology products",
      "approval_status": "approved",
      "rejected_reason": null,
      "approved_by": null,
      "approved_at": null,
      "sort_order": 1,
      "created_at": "2026-01-10T12:00:00Z",
      "updated_at": "2026-01-10T12:00:00Z"
    }
  ],
  "message": "Experiences retrieved successfully"
}
```

**Error Responses**:
- **401 Unauthorized**: Missing or invalid token
- **403 Forbidden**: User is not a salesperson

**Business Rules**:
- BR-EXP-001: 業務員只能查詢自己的經驗
- BR-EXP-002: 預設按 start_date DESC 排序
- BR-EXP-002b: 如果 start_date 相同，按 sort_order ASC 排序

**Performance Requirements**:
- Response Time P95 < 100ms
- 支援 100 QPS

---

#### 3. POST /salesperson/experiences

**描述**: 新增工作經驗

**Authentication**: Required (JWT)

**Authorization**: 業務員角色

**Request Headers**:
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "company": "ABC Company",
  "position": "Senior Sales Manager",
  "start_date": "2020-01-01",
  "end_date": "2022-12-31",
  "description": "Managed a team of 10 sales representatives"
}
```

**Request Parameters**:
| 參數 | 型別 | 必填 | 驗證規則 | 說明 |
|-----|------|-----|---------|------|
| company | string | 是 | required, max:200 | 公司名稱 |
| position | string | 是 | required, max:200 | 職位 |
| start_date | string | 是 | required, date, date_format:Y-m-d | 開始日期 (YYYY-MM-DD) |
| end_date | string | 否 | nullable, date, date_format:Y-m-d, after_or_equal:start_date | 結束日期 (YYYY-MM-DD) |
| description | string | 否 | nullable, string | 工作描述 |

**Validation Rules** (Laravel):
```php
[
    'company' => ['required', 'string', 'max:200'],
    'position' => ['required', 'string', 'max:200'],
    'start_date' => ['required', 'date', 'date_format:Y-m-d'],
    'end_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
    'description' => ['nullable', 'string'],
]
```

**Success Response (201 Created)**:
```json
{
  "success": true,
  "data": {
    "id": 3,
    "user_id": 10,
    "company": "ABC Company",
    "position": "Senior Sales Manager",
    "start_date": "2020-01-01",
    "end_date": "2022-12-31",
    "description": "Managed a team of 10 sales representatives",
    "approval_status": "approved",
    "rejected_reason": null,
    "approved_by": null,
    "approved_at": null,
    "sort_order": 0,
    "created_at": "2026-01-11T10:00:00Z",
    "updated_at": "2026-01-11T10:00:00Z"
  },
  "message": "Experience created successfully"
}
```

**Error Responses**:

**401 Unauthorized**:
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Unauthenticated"
  }
}
```

**403 Forbidden**:
```json
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "You must be a salesperson to create experiences"
  }
}
```

**422 Validation Error**:
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid",
    "details": {
      "company": ["The company field is required."],
      "end_date": ["The end date must be a date after or equal to start date."]
    }
  }
}
```

**Business Rules**:
- BR-EXP-001: 只能建立自己的經驗
- BR-EXP-003: end_date 必須 >= start_date
- BR-EXP-004: 新建的經驗 approval_status 自動設為 'approved'（不需審核）
- BR-EXP-005: sort_order 自動設為 0（前端可透過更新調整順序）

**Implementation Notes**:
```php
// Controller 自動填充欄位
$data['user_id'] = auth()->id();
$data['approval_status'] = 'approved';
$data['sort_order'] = 0;
```

---

#### 4. PUT /salesperson/experiences/{id}

**描述**: 更新工作經驗

**Authentication**: Required (JWT)

**Authorization**: 業務員角色 + 擁有者

**Request Headers**:
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**URL Parameters**:
| 參數 | 型別 | 說明 |
|-----|------|------|
| id | integer | 經驗 ID |

**Request Body**:
```json
{
  "company": "ABC Company Ltd.",
  "position": "Senior Sales Director",
  "start_date": "2020-01-01",
  "end_date": "2023-12-31",
  "description": "Updated description"
}
```

**Request Parameters**: 同 POST /salesperson/experiences

**Validation Rules**: 同 POST /salesperson/experiences

**Success Response (200 OK)**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 10,
    "company": "ABC Company Ltd.",
    "position": "Senior Sales Director",
    "start_date": "2020-01-01",
    "end_date": "2023-12-31",
    "description": "Updated description",
    "approval_status": "approved",
    "rejected_reason": null,
    "approved_by": null,
    "approved_at": null,
    "sort_order": 0,
    "created_at": "2026-01-10T12:00:00Z",
    "updated_at": "2026-01-11T10:30:00Z"
  },
  "message": "Experience updated successfully"
}
```

**Error Responses**:
- **401 Unauthorized**: Missing or invalid token
- **403 Forbidden**: Not the owner of this experience
- **404 Not Found**: Experience not found
- **422 Validation Error**: Invalid data

**403 Forbidden Example**:
```json
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "You can only update your own experiences"
  }
}
```

**404 Not Found Example**:
```json
{
  "success": false,
  "error": {
    "code": "NOT_FOUND",
    "message": "Experience not found"
  }
}
```

**Business Rules**:
- BR-EXP-001: 只能更新自己的經驗
- BR-EXP-003: end_date 必須 >= start_date

**Authorization Check**:
```php
if ($experience->user_id !== auth()->id()) {
    return response()->json([
        'success' => false,
        'error' => [
            'code' => 'FORBIDDEN',
            'message' => 'You can only update your own experiences'
        ]
    ], 403);
}
```

---

#### 5. DELETE /salesperson/experiences/{id}

**描述**: 刪除工作經驗

**Authentication**: Required (JWT)

**Authorization**: 業務員角色 + 擁有者

**Request Headers**:
```
Authorization: Bearer {access_token}
```

**URL Parameters**:
| 參數 | 型別 | 說明 |
|-----|------|------|
| id | integer | 經驗 ID |

**Success Response (200 OK)**:
```json
{
  "success": true,
  "data": null,
  "message": "Experience deleted successfully"
}
```

**Error Responses**:
- **401 Unauthorized**: Missing or invalid token
- **403 Forbidden**: Not the owner
- **404 Not Found**: Experience not found

**Business Rules**:
- BR-EXP-001: 只能刪除自己的經驗
- BR-DI-001: 執行硬刪除（直接從資料庫移除）

**Implementation Notes**:
- 使用硬刪除 (`$experience->delete()`)
- 不使用軟刪除（Soft Delete）

---

#### 6. GET /salesperson/certifications

**描述**: 取得當前登入業務員的所有證照列表

**Authentication**: Required (JWT)

**Authorization**: 業務員角色

**Request Headers**:
```
Authorization: Bearer {access_token}
```

**Query Parameters**:
| 參數 | 型別 | 必填 | 說明 | 預設值 |
|-----|------|-----|------|-------|
| approval_status | string | 否 | 篩選審核狀態 (pending, approved, rejected) | all |

**Request Example**:
```
GET /api/salesperson/certifications?approval_status=approved
```

**Response (200 OK)**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 10,
      "name": "PMP 專案管理證照",
      "issuer": "PMI",
      "issue_date": "2021-06-15",
      "expiry_date": "2024-06-15",
      "description": "Project Management Professional",
      "file_data": null,
      "file_mime": "application/pdf",
      "file_size": 524288,
      "approval_status": "approved",
      "rejected_reason": null,
      "approved_by": 5,
      "approved_at": "2021-06-20T10:00:00Z",
      "created_at": "2021-06-15T12:00:00Z",
      "updated_at": "2021-06-20T10:00:00Z"
    },
    {
      "id": 2,
      "user_id": 10,
      "name": "Google Analytics 認證",
      "issuer": "Google",
      "issue_date": "2022-03-10",
      "expiry_date": null,
      "description": null,
      "file_data": null,
      "file_mime": "image/jpeg",
      "file_size": 204800,
      "approval_status": "pending",
      "rejected_reason": null,
      "approved_by": null,
      "approved_at": null,
      "created_at": "2022-03-10T09:00:00Z",
      "updated_at": "2022-03-10T09:00:00Z"
    }
  ],
  "message": "Certifications retrieved successfully"
}
```

**Error Responses**:
- **401 Unauthorized**: Missing or invalid token
- **403 Forbidden**: User is not a salesperson

**Business Rules**:
- BR-CERT-001: 業務員只能查詢自己的證照
- BR-API-002: `file_data` 欄位在 GET 時不回傳內容（僅回傳 null），避免傳輸過大
- BR-CERT-006: 預設按 created_at DESC 排序

**Performance Requirements**:
- Response Time P95 < 100ms
- file_data 欄位不包含在回應中（效能考量）

**Implementation Notes**:
```php
// Resource 中不包含 file_data
public function toArray($request)
{
    return [
        'id' => $this->id,
        // ... other fields
        'file_data' => null, // 永遠回傳 null
        'file_mime' => $this->file_mime,
        'file_size' => $this->file_size,
        // ...
    ];
}
```

---

#### 7. POST /salesperson/certifications

**描述**: 上傳證照（支援檔案 Base64 編碼）

**Authentication**: Required (JWT)

**Authorization**: 業務員角色

**Request Headers**:
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "name": "PMP 專案管理證照",
  "issuer": "PMI",
  "issue_date": "2021-06-15",
  "expiry_date": "2024-06-15",
  "description": "Project Management Professional",
  "file_data": "data:application/pdf;base64,JVBERi0xLjQKJeLjz9MKMyAwIG9iago8PC..."
}
```

**Request Parameters**:
| 參數 | 型別 | 必填 | 驗證規則 | 說明 |
|-----|------|-----|---------|------|
| name | string | 是 | required, max:200 | 證照名稱 |
| issuer | string | 是 | required, max:200 | 發證單位 |
| issue_date | string | 否 | nullable, date, date_format:Y-m-d | 發證日期 (YYYY-MM-DD) |
| expiry_date | string | 否 | nullable, date, date_format:Y-m-d, after:issue_date | 到期日期 (YYYY-MM-DD) |
| description | string | 否 | nullable, string | 證照說明 |
| file_data | string | 否 | nullable, string, regex:/^data:(image\/(jpeg\|png\|jpg)\|application\/pdf);base64,/ | Base64 編碼的檔案 |

**Validation Rules** (Laravel):
```php
[
    'name' => ['required', 'string', 'max:200'],
    'issuer' => ['required', 'string', 'max:200'],
    'issue_date' => ['nullable', 'date', 'date_format:Y-m-d'],
    'expiry_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after:issue_date'],
    'description' => ['nullable', 'string'],
    'file_data' => [
        'nullable',
        'string',
        'regex:/^data:(image\/(jpeg|png|jpg)|application\/pdf);base64,/',
    ],
]
```

**Custom Validation** (檔案大小):
```php
// 自定義驗證邏輯
protected function validateFileSize(string $base64): bool
{
    // 解碼後的大小不得超過 16MB (MEDIUMBLOB 限制)
    $decoded = base64_decode(explode(',', $base64)[1]);
    return strlen($decoded) <= 16 * 1024 * 1024; // 16MB
}
```

**Success Response (201 Created)**:
```json
{
  "success": true,
  "data": {
    "id": 3,
    "user_id": 10,
    "name": "PMP 專案管理證照",
    "issuer": "PMI",
    "issue_date": "2021-06-15",
    "expiry_date": "2024-06-15",
    "description": "Project Management Professional",
    "file_data": null,
    "file_mime": "application/pdf",
    "file_size": 524288,
    "approval_status": "pending",
    "rejected_reason": null,
    "approved_by": null,
    "approved_at": null,
    "created_at": "2026-01-11T11:00:00Z",
    "updated_at": "2026-01-11T11:00:00Z"
  },
  "message": "Certification created successfully and pending approval"
}
```

**Error Responses**:

**422 Validation Error** (檔案過大):
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid",
    "details": {
      "file_data": ["The file size must not exceed 16MB"]
    }
  }
}
```

**422 Validation Error** (不支援的檔案類型):
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid",
    "details": {
      "file_data": ["The file must be an image (jpeg, png, jpg) or PDF"]
    }
  }
}
```

**Business Rules**:
- BR-CERT-001: 只能建立自己的證照
- BR-CERT-002: 檔案大小限制 16MB（MEDIUMBLOB）
- BR-CERT-003: 檔案類型限制: image/jpeg, image/png, image/jpg, application/pdf
- BR-CERT-004: Base64 解碼後儲存到 file_data MEDIUMBLOB 欄位
- BR-CERT-005: 新建的證照 approval_status 預設為 'pending'（需要審核）

**Implementation Notes**:
```php
// Controller 處理 Base64
if (!empty($data['file_data'])) {
    // 解析 MIME type 和 Base64 資料
    preg_match('/^data:([a-z\/]+);base64,(.+)$/', $data['file_data'], $matches);
    $mime = $matches[1]; // e.g., 'application/pdf'
    $base64Data = $matches[2];

    // 解碼
    $fileContent = base64_decode($base64Data);

    // 儲存到資料庫
    $data['file_data'] = $fileContent;
    $data['file_mime'] = $mime;
    $data['file_size'] = strlen($fileContent);
} else {
    $data['file_data'] = null;
    $data['file_mime'] = null;
    $data['file_size'] = null;
}

// 自動填充
$data['user_id'] = auth()->id();
$data['approval_status'] = 'pending';
```

**File Format**:
- 前端上傳格式: `data:{mime};base64,{content}`
- 範例: `data:application/pdf;base64,JVBERi0xLjQKJeL...`

---

#### 8. DELETE /salesperson/certifications/{id}

**描述**: 刪除證照

**Authentication**: Required (JWT)

**Authorization**: 業務員角色 + 擁有者

**Request Headers**:
```
Authorization: Bearer {access_token}
```

**URL Parameters**:
| 參數 | 型別 | 說明 |
|-----|------|------|
| id | integer | 證照 ID |

**Success Response (200 OK)**:
```json
{
  "success": true,
  "data": null,
  "message": "Certification deleted successfully"
}
```

**Error Responses**:
- **401 Unauthorized**: Missing or invalid token
- **403 Forbidden**: Not the owner
- **404 Not Found**: Certification not found

**Business Rules**:
- BR-CERT-001: 只能刪除自己的證照
- BR-DI-001: 執行硬刪除（直接從資料庫移除）

**Authorization Check**:
```php
if ($certification->user_id !== auth()->id()) {
    return response()->json([
        'success' => false,
        'error' => [
            'code' => 'FORBIDDEN',
            'message' => 'You can only delete your own certifications'
        ]
    ], 403);
}
```

---

### Phase 2 - High Priority APIs 🟡

---

#### 9. GET /salesperson/approval-status

**描述**: 聚合查詢所有審核狀態（Profile, Company, Certifications, Experiences）

**Authentication**: Required (JWT)

**Authorization**: 業務員角色

**Request Headers**:
```
Authorization: Bearer {access_token}
```

**Query Parameters**: 無

**Response (200 OK)**:
```json
{
  "success": true,
  "data": {
    "profile_status": "approved",
    "company_status": "approved",
    "certifications": [
      {
        "id": 1,
        "name": "PMP 專案管理證照",
        "approval_status": "approved",
        "rejected_reason": null
      },
      {
        "id": 2,
        "name": "Google Analytics 認證",
        "approval_status": "pending",
        "rejected_reason": null
      }
    ],
    "experiences": [
      {
        "id": 1,
        "company": "ABC Company",
        "position": "Senior Sales Manager",
        "approval_status": "approved",
        "rejected_reason": null
      }
    ]
  },
  "message": "Approval status retrieved successfully"
}
```

**Response Schema**:
```typescript
interface ApprovalStatusData {
  profile_status: 'pending' | 'approved' | 'rejected';
  company_status: 'pending' | 'approved' | 'rejected' | null;
  certifications: Array<{
    id: number;
    name: string;
    approval_status: 'pending' | 'approved' | 'rejected';
    rejected_reason: string | null;
  }>;
  experiences: Array<{
    id: number;
    company: string;
    position: string;
    approval_status: 'pending' | 'approved' | 'rejected';
    rejected_reason: string | null;
  }>;
}
```

**Error Responses**:
- **401 Unauthorized**: Missing or invalid token
- **403 Forbidden**: User is not a salesperson

**Business Rules**:
- BR-AUTH-002: 使用者 role 必須是 'salesperson'
- BR-APPROVAL-001: profile_status 從 salesperson_profiles 表取得
- BR-APPROVAL-002: company_status 從關聯的 company 取得（可能為 null）
- BR-APPROVAL-003: certifications 包含所有證照（不限審核狀態）
- BR-APPROVAL-004: experiences 包含所有經驗（不限審核狀態）

**Implementation Notes**:
```php
public function approvalStatus(Request $request): JsonResponse
{
    $user = $request->user();

    // Eager loading 避免 N+1 查詢
    $profile = $user->salespersonProfile()
        ->with(['company', 'certifications', 'experiences'])
        ->first();

    return response()->json([
        'success' => true,
        'data' => [
            'profile_status' => $profile?->approval_status ?? 'pending',
            'company_status' => $profile?->company?->approval_status ?? null,
            'certifications' => $user->certifications->map(fn($cert) => [
                'id' => $cert->id,
                'name' => $cert->name,
                'approval_status' => $cert->approval_status,
                'rejected_reason' => $cert->rejected_reason,
            ]),
            'experiences' => $user->experiences->map(fn($exp) => [
                'id' => $exp->id,
                'company' => $exp->company,
                'position' => $exp->position,
                'approval_status' => $exp->approval_status,
                'rejected_reason' => $exp->rejected_reason,
            ]),
        ],
        'message' => 'Approval status retrieved successfully'
    ]);
}
```

**Performance Considerations**:
- 使用 Eager Loading (`with()`) 避免 N+1 查詢問題
- 預期查詢數: 2-3 queries（User, Profile+Company, Certifications+Experiences）

---

#### 10. GET /salesperson/status (修正回應格式)

**描述**: 取得業務員狀態（包含審核狀態和重新申請資訊）

**Authentication**: Required (JWT)

**Authorization**: 無（所有登入使用者都可調用）

**Request Headers**:
```
Authorization: Bearer {access_token}
```

**Query Parameters**: 無

**Response (200 OK)** - 業務員使用者:
```json
{
  "success": true,
  "data": {
    "role": "salesperson",
    "salesperson_status": "approved",
    "salesperson_applied_at": "2026-01-01T10:00:00Z",
    "salesperson_approved_at": "2026-01-05T15:30:00Z",
    "rejection_reason": null,
    "can_reapply": false,
    "can_reapply_at": null,
    "days_until_reapply": null
  },
  "message": "Status retrieved successfully"
}
```

**Response (200 OK)** - 一般使用者:
```json
{
  "success": true,
  "data": {
    "role": "user",
    "salesperson_status": null,
    "salesperson_applied_at": null,
    "salesperson_approved_at": null,
    "rejection_reason": null,
    "can_reapply": false,
    "can_reapply_at": null,
    "days_until_reapply": null
  },
  "message": "Status retrieved successfully"
}
```

**Response (200 OK)** - 被拒絕的業務員（可重新申請）:
```json
{
  "success": true,
  "data": {
    "role": "user",
    "salesperson_status": "rejected",
    "salesperson_applied_at": "2026-01-01T10:00:00Z",
    "salesperson_approved_at": null,
    "rejection_reason": "資料不完整，請補充工作經驗",
    "can_reapply": true,
    "can_reapply_at": "2026-01-31T10:00:00Z",
    "days_until_reapply": 0
  },
  "message": "Status retrieved successfully"
}
```

**Response Schema**:
```typescript
interface SalespersonStatusResponse {
  role: 'user' | 'salesperson' | 'admin';
  salesperson_status: 'pending' | 'approved' | 'rejected' | null;
  salesperson_applied_at: string | null;
  salesperson_approved_at: string | null;
  rejection_reason: string | null;
  can_reapply: boolean;
  can_reapply_at: string | null;
  days_until_reapply: number | null;
}
```

**Error Responses**:
- **401 Unauthorized**: Missing or invalid token

**Business Rules**:
- BR-STATUS-001: 所有登入使用者都可查詢自己的狀態
- BR-STATUS-002: `days_until_reapply` 計算方式: `can_reapply_at - now()` 的天數（可為負數）
- BR-STATUS-003: `can_reapply` 由 User Model 的 `canReapply()` 方法決定

**修正內容**（與原本回應的差異）:
| 欄位 | 原本 | 修正後 |
|-----|------|-------|
| role | ❌ 缺失 | ✅ 新增 |
| status | ✅ 存在 | ✅ 改名為 `salesperson_status` |
| days_until_reapply | ❌ 缺失 | ✅ 新增 |
| 整體結構 | `success + data at root` | ✅ `success + data wrapper` |

**Implementation Notes**:
```php
public function status(Request $request): JsonResponse
{
    $user = $request->user();

    // 計算距離重新申請的天數
    $daysUntilReapply = null;
    if ($user->can_reapply_at) {
        $daysUntilReapply = now()->diffInDays($user->can_reapply_at, false);
        // false 表示如果已過期會回傳負數
    }

    return response()->json([
        'success' => true,
        'data' => [
            'role' => $user->role,
            'salesperson_status' => $user->salesperson_status,
            'salesperson_applied_at' => $user->salesperson_applied_at,
            'salesperson_approved_at' => $user->salesperson_approved_at,
            'rejection_reason' => $user->rejection_reason,
            'can_reapply' => $user->canReapply(),
            'can_reapply_at' => $user->can_reapply_at,
            'days_until_reapply' => $daysUntilReapply,
        ],
        'message' => 'Status retrieved successfully'
    ]);
}
```

---

## 統一回應格式

所有 API 端點都必須遵循以下回應格式:

### 成功回應

```json
{
  "success": true,
  "data": <T>,
  "message": "Operation successful"
}
```

### 錯誤回應

```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Error message",
    "details": {}
  }
}
```

### 錯誤碼定義

| 錯誤碼 | HTTP Status | 說明 |
|-------|-------------|------|
| UNAUTHORIZED | 401 | 未認證（缺少或無效的 Token） |
| FORBIDDEN | 403 | 無權限（認證成功但角色不符或非擁有者） |
| NOT_FOUND | 404 | 資源不存在 |
| VALIDATION_ERROR | 422 | 驗證失敗 |
| INTERNAL_ERROR | 500 | 伺服器內部錯誤 |

---

## 效能要求

| 指標 | 目標值 |
|------|-------|
| API 回應時間 (P95) | < 200ms |
| API 回應時間 (P99) | < 500ms |
| 資料庫查詢數 | < 10 queries per request |
| 並發支援 | 100 QPS |

**優化策略**:
- 使用 Eager Loading (`with()`) 避免 N+1 查詢
- 為常查詢欄位建立索引
- GET 端點不回傳 file_data 欄位（BLOB）

---

## 安全考量

1. **認證**: 所有端點都需要 JWT Token
2. **授權**: 檢查使用者角色和資源擁有權
3. **輸入驗證**: 使用 Form Request 驗證所有輸入
4. **檔案上傳**:
   - 限制檔案類型（image/*, application/pdf）
   - 限制檔案大小（16MB）
   - Base64 解碼驗證
5. **SQL Injection**: 使用 Eloquent ORM（自動防護）
6. **XSS**: 前端使用 React 自動轉義

---

## 測試要求

每個 API 端點至少需要以下測試案例:

1. **成功案例**: 正常流程測試
2. **認證失敗**: 無 Token 或 Token 無效
3. **授權失敗**: Token 有效但角色不符或非擁有者
4. **驗證失敗**: 必填欄位缺失、格式錯誤
5. **404 案例**: 資源不存在

**測試覆蓋目標**: 每個端點 5+ 測試案例

---

## 變更記錄

| 日期 | 版本 | 變更內容 |
|------|------|---------|
| 2026-01-11 | 1.0 | 初始版本，定義所有 API 端點規格 |

---

**文檔狀態**: ✅ Complete
**審核狀態**: Pending Review
