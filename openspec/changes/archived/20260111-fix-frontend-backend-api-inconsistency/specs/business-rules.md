# 業務規則定義

**專案**: 前後端 API 不一致修復
**版本**: 1.0
**最後更新**: 2026-01-11

---

## 概述

本文檔定義所有業務規則（Business Rules），包含驗證邏輯、授權檢查、資料一致性和錯誤處理。

**規則分類**:
- **BR-EXP**: Experiences 相關規則
- **BR-CERT**: Certifications 相關規則
- **BR-AUTH**: 認證與授權規則
- **BR-API**: API 回應格式規則
- **BR-DI**: 資料完整性規則
- **BR-APPROVAL**: 審核相關規則
- **BR-STATUS**: 狀態查詢規則

---

## 認證與授權規則

### BR-AUTH-001: JWT Token 認證

**描述**: 所有 API 端點（除非特別標註）都需要有效的 JWT Token

**實作位置**: Middleware `jwt.auth`

**檢查邏輯**:
```php
// routes/api.php
Route::middleware('jwt.auth')->group(function () {
    // 需要認證的路由
});

// app/Http/Middleware/JwtMiddleware.php
public function handle($request, Closure $next)
{
    try {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'User not found'
                ]
            ], 401);
        }

        auth()->setUser($user);

        return $next($request);
    } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'TOKEN_EXPIRED',
                'message' => 'Token has expired'
            ]
        ], 401);
    } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'TOKEN_INVALID',
                'message' => 'Token is invalid'
            ]
        ], 401);
    } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'UNAUTHORIZED',
                'message' => 'Token not provided'
            ]
        ], 401);
    }
}
```

**測試案例**:
- 未提供 Token → 401 UNAUTHORIZED
- Token 已過期 → 401 TOKEN_EXPIRED
- Token 無效 → 401 TOKEN_INVALID
- Token 有效 → 繼續執行

---

### BR-AUTH-002: 業務員角色檢查

**描述**: 特定 API 端點需要使用者 role 為 'salesperson'

**實作位置**: Controller 方法開頭

**檢查邏輯**:
```php
public function index(Request $request): JsonResponse
{
    $user = $request->user();

    if ($user->role !== 'salesperson') {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'FORBIDDEN',
                'message' => 'You must be a salesperson to access this resource'
            ]
        ], 403);
    }

    // 繼續執行業務邏輯
    // ...
}
```

**適用端點**:
- GET `/salesperson/profile`
- GET `/salesperson/experiences`
- POST `/salesperson/experiences`
- PUT `/salesperson/experiences/{id}`
- DELETE `/salesperson/experiences/{id}`
- GET `/salesperson/certifications`
- POST `/salesperson/certifications`
- DELETE `/salesperson/certifications/{id}`
- GET `/salesperson/approval-status`

**測試案例**:
- role = 'user' → 403 FORBIDDEN
- role = 'admin' → 403 FORBIDDEN (除非 admin 也可存取，需確認)
- role = 'salesperson' → 200 OK

**優化建議**:
可建立 Middleware `EnsureSalesperson`:
```php
// app/Http/Middleware/EnsureSalesperson.php
public function handle($request, Closure $next)
{
    if (auth()->user()->role !== 'salesperson') {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'FORBIDDEN',
                'message' => 'You must be a salesperson to access this resource'
            ]
        ], 403);
    }

    return $next($request);
}

// routes/api.php
Route::middleware(['jwt.auth', 'ensure.salesperson'])->prefix('salesperson')->group(function () {
    // 所有業務員路由
});
```

---

## Experiences 業務規則

### BR-EXP-001: 經驗資料所有權

**描述**: 業務員只能查詢、建立、更新、刪除自己的工作經驗

**實作位置**: ExperienceController（所有方法）

**檢查邏輯**:

**查詢（index）**:
```php
public function index(Request $request): JsonResponse
{
    // 自動篩選當前使用者的經驗
    $experiences = $request->user()
        ->experiences()
        ->ordered()
        ->get();

    return response()->json([
        'success' => true,
        'data' => ExperienceResource::collection($experiences),
        'message' => 'Experiences retrieved successfully'
    ]);
}
```

**建立（store）**:
```php
public function store(StoreExperienceRequest $request): JsonResponse
{
    // 自動設定 user_id 為當前使用者
    $data = $request->validated();
    $data['user_id'] = $request->user()->id;
    $data['approval_status'] = 'approved';
    $data['sort_order'] = 0;

    $experience = Experience::create($data);

    return response()->json([
        'success' => true,
        'data' => new ExperienceResource($experience),
        'message' => 'Experience created successfully'
    ], 201);
}
```

**更新（update）**:
```php
public function update(UpdateExperienceRequest $request, int $id): JsonResponse
{
    $experience = Experience::find($id);

    if (!$experience) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'NOT_FOUND',
                'message' => 'Experience not found'
            ]
        ], 404);
    }

    // 檢查所有權
    if ($experience->user_id !== $request->user()->id) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'FORBIDDEN',
                'message' => 'You can only update your own experiences'
            ]
        ], 403);
    }

    $experience->update($request->validated());

    return response()->json([
        'success' => true,
        'data' => new ExperienceResource($experience->fresh()),
        'message' => 'Experience updated successfully'
    ]);
}
```

**刪除（destroy）**:
```php
public function destroy(Request $request, int $id): JsonResponse
{
    $experience = Experience::find($id);

    if (!$experience) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'NOT_FOUND',
                'message' => 'Experience not found'
            ]
        ], 404);
    }

    // 檢查所有權
    if ($experience->user_id !== $request->user()->id) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'FORBIDDEN',
                'message' => 'You can only delete your own experiences'
            ]
        ], 403);
    }

    $experience->delete();

    return response()->json([
        'success' => true,
        'data' => null,
        'message' => 'Experience deleted successfully'
    ]);
}
```

**測試案例**:
- 業務員 A 嘗試更新業務員 B 的經驗 → 403 FORBIDDEN
- 業務員 A 嘗試刪除業務員 B 的經驗 → 403 FORBIDDEN
- 業務員 A 查詢經驗 → 只回傳 A 的經驗
- 業務員 A 建立經驗 → user_id 自動設為 A

---

### BR-EXP-002: 經驗排序規則

**描述**: 經驗列表預設按 sort_order ASC, start_date DESC 排序

**實作位置**: Experience Model Scope

**檢查邏輯**:
```php
// app/Models/Experience.php
public function scopeOrdered($query)
{
    return $query->orderBy('sort_order', 'asc')
        ->orderBy('start_date', 'desc');
}

// Controller 使用
$experiences = $request->user()->experiences()->ordered()->get();
```

**排序邏輯**:
1. 優先按 `sort_order` 升序排序（使用者自訂順序）
2. 相同 `sort_order` 時，按 `start_date` 降序排序（最新的在前）

**測試案例**:
- 3 筆經驗，sort_order 都是 0 → 按 start_date 降序
- 3 筆經驗，sort_order 分別是 1, 0, 2 → 順序為 0, 1, 2
- sort_order 相同時，start_date 較新的在前

---

### BR-EXP-003: 經驗日期驗證

**描述**: end_date 必須大於或等於 start_date

**實作位置**: Form Request 驗證

**檢查邏輯**:
```php
// app/Http/Requests/StoreExperienceRequest.php
public function rules(): array
{
    return [
        'company' => ['required', 'string', 'max:200'],
        'position' => ['required', 'string', 'max:200'],
        'start_date' => ['required', 'date', 'date_format:Y-m-d'],
        'end_date' => [
            'nullable',
            'date',
            'date_format:Y-m-d',
            'after_or_equal:start_date', // 核心驗證規則
        ],
        'description' => ['nullable', 'string'],
    ];
}

public function messages(): array
{
    return [
        'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
    ];
}
```

**測試案例**:
- start_date = '2020-01-01', end_date = '2019-12-31' → 422 VALIDATION_ERROR
- start_date = '2020-01-01', end_date = '2020-01-01' → 201 OK
- start_date = '2020-01-01', end_date = '2022-12-31' → 201 OK
- start_date = '2020-01-01', end_date = null → 201 OK

---

### BR-EXP-004: 經驗自動審核

**描述**: 新建的經驗 approval_status 自動設為 'approved'（不需審核）

**實作位置**: ExperienceController::store

**檢查邏輯**:
```php
public function store(StoreExperienceRequest $request): JsonResponse
{
    $data = $request->validated();
    $data['user_id'] = $request->user()->id;
    $data['approval_status'] = 'approved'; // 自動設為 approved
    $data['sort_order'] = 0;

    $experience = Experience::create($data);

    return response()->json([
        'success' => true,
        'data' => new ExperienceResource($experience),
        'message' => 'Experience created successfully'
    ], 201);
}
```

**測試案例**:
- 建立經驗時不提供 approval_status → 自動設為 'approved'
- 建立經驗時提供 approval_status = 'pending' → 被忽略，仍為 'approved'

**注意事項**:
- Migration 預設值也是 'approved'，確保資料一致性
- 與 Certifications 不同（Certifications 預設為 'pending'）

---

### BR-EXP-005: 經驗 sort_order 預設值

**描述**: 新建的經驗 sort_order 預設為 0

**實作位置**: ExperienceController::store

**檢查邏輯**:
```php
$data['sort_order'] = 0;
```

**測試案例**:
- 建立經驗時不提供 sort_order → 自動設為 0
- 前端可透過 PUT 更新調整 sort_order

---

## Certifications 業務規則

### BR-CERT-001: 證照資料所有權

**描述**: 業務員只能查詢、建立、刪除自己的證照

**實作位置**: CertificationController（所有方法）

**檢查邏輯**: 同 BR-EXP-001（Experiences 所有權）

**測試案例**:
- 業務員 A 嘗試刪除業務員 B 的證照 → 403 FORBIDDEN
- 業務員 A 查詢證照 → 只回傳 A 的證照
- 業務員 A 建立證照 → user_id 自動設為 A

---

### BR-CERT-002: 證照檔案大小限制

**描述**: 上傳的證照檔案解碼後大小不得超過 16MB（MEDIUMBLOB 限制）

**實作位置**: Form Request 自定義驗證

**檢查邏輯**:
```php
// app/Http/Requests/StoreCertificationRequest.php
public function rules(): array
{
    return [
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
    ];
}

public function withValidator($validator): void
{
    $validator->after(function ($validator) {
        if ($this->has('file_data') && !empty($this->file_data)) {
            $fileSize = $this->getDecodedFileSize($this->file_data);

            if ($fileSize > 16 * 1024 * 1024) { // 16MB
                $validator->errors()->add(
                    'file_data',
                    'The file size must not exceed 16MB'
                );
            }
        }
    });
}

protected function getDecodedFileSize(string $base64): int
{
    // 解析 Base64 資料
    if (preg_match('/^data:[a-z\/]+;base64,(.+)$/', $base64, $matches)) {
        $base64Data = $matches[1];
        $decoded = base64_decode($base64Data);
        return strlen($decoded);
    }

    return 0;
}
```

**測試案例**:
- 上傳 10MB 檔案 → 201 OK
- 上傳 16MB 檔案 → 201 OK
- 上傳 17MB 檔案 → 422 VALIDATION_ERROR

**錯誤回應**:
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

---

### BR-CERT-003: 證照檔案類型限制

**描述**: 上傳的證照檔案類型必須是 image/jpeg, image/png, image/jpg, application/pdf

**實作位置**: Form Request 驗證

**檢查邏輯**:
```php
public function rules(): array
{
    return [
        // ...
        'file_data' => [
            'nullable',
            'string',
            'regex:/^data:(image\/(jpeg|png|jpg)|application\/pdf);base64,/',
        ],
    ];
}

public function messages(): array
{
    return [
        'file_data.regex' => 'The file must be an image (jpeg, png, jpg) or PDF',
    ];
}
```

**測試案例**:
- MIME type = 'image/jpeg' → 201 OK
- MIME type = 'image/png' → 201 OK
- MIME type = 'image/jpg' → 201 OK
- MIME type = 'application/pdf' → 201 OK
- MIME type = 'image/gif' → 422 VALIDATION_ERROR
- MIME type = 'application/msword' → 422 VALIDATION_ERROR

---

### BR-CERT-004: 證照 Base64 解碼與儲存

**描述**: 前端上傳的 Base64 編碼檔案需解碼後儲存到 file_data MEDIUMBLOB 欄位

**實作位置**: CertificationController::store

**檢查邏輯**:
```php
public function store(StoreCertificationRequest $request): JsonResponse
{
    $data = $request->validated();
    $data['user_id'] = $request->user()->id;
    $data['approval_status'] = 'pending';

    // 處理 Base64 檔案
    if (!empty($data['file_data'])) {
        // 解析 MIME type 和 Base64 資料
        // 格式: data:application/pdf;base64,JVBERi0xLjQK...
        if (preg_match('/^data:([a-z\/]+);base64,(.+)$/', $data['file_data'], $matches)) {
            $mime = $matches[1];
            $base64Data = $matches[2];

            // 解碼
            $fileContent = base64_decode($base64Data);

            // 儲存到資料庫
            $data['file_data'] = $fileContent;
            $data['file_mime'] = $mime;
            $data['file_size'] = strlen($fileContent);
        } else {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_FILE_FORMAT',
                    'message' => 'Invalid file format'
                ]
            ], 422);
        }
    } else {
        $data['file_data'] = null;
        $data['file_mime'] = null;
        $data['file_size'] = null;
    }

    $certification = Certification::create($data);

    return response()->json([
        'success' => true,
        'data' => new CertificationResource($certification),
        'message' => 'Certification created successfully and pending approval'
    ], 201);
}
```

**測試案例**:
- 上傳格式: `data:application/pdf;base64,JVBERi0...` → 正確解碼並儲存
- 不上傳檔案（file_data = null） → file_data, file_mime, file_size 都為 null
- 檔案內容正確儲存到 MEDIUMBLOB

---

### BR-CERT-005: 證照預設待審核

**描述**: 新建的證照 approval_status 預設為 'pending'（需要審核）

**實作位置**: CertificationController::store

**檢查邏輯**:
```php
$data['approval_status'] = 'pending';
```

**測試案例**:
- 建立證照時不提供 approval_status → 自動設為 'pending'
- 建立證照時提供 approval_status = 'approved' → 被忽略，仍為 'pending'

**注意事項**:
- Migration 預設值也是 'pending'
- 與 Experiences 不同（Experiences 預設為 'approved'）

---

### BR-CERT-006: 證照排序規則

**描述**: 證照列表預設按 created_at DESC 排序（最新的在前）

**實作位置**: CertificationController::index

**檢查邏輯**:
```php
public function index(Request $request): JsonResponse
{
    $query = $request->user()->certifications();

    // 篩選審核狀態
    if ($request->has('approval_status') && $request->approval_status !== 'all') {
        $query->where('approval_status', $request->approval_status);
    }

    $certifications = $query->orderBy('created_at', 'desc')->get();

    return response()->json([
        'success' => true,
        'data' => CertificationResource::collection($certifications),
        'message' => 'Certifications retrieved successfully'
    ]);
}
```

**測試案例**:
- 3 筆證照，created_at 不同 → 按 created_at 降序

---

## API 回應格式規則

### BR-API-001: 統一回應格式（success: boolean）

**描述**: 所有 API 端點必須回傳統一的 JSON 格式，包含 `success: boolean`

**實作位置**: 所有 Controller

**成功回應格式**:
```json
{
  "success": true,
  "data": <T>,
  "message": "Operation successful"
}
```

**錯誤回應格式**:
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

**檢查邏輯**:
```php
// 成功回應
return response()->json([
    'success' => true,
    'data' => $data,
    'message' => $message
], $statusCode);

// 錯誤回應
return response()->json([
    'success' => false,
    'error' => [
        'code' => $errorCode,
        'message' => $errorMessage,
        'details' => $details ?? null
    ]
], $statusCode);
```

**測試案例**:
- 所有成功回應都有 `success: true`
- 所有錯誤回應都有 `success: false`
- 錯誤回應包含 `error.code` 和 `error.message`

---

### BR-API-002: GET 不回傳 file_data

**描述**: GET `/salesperson/certifications` 不回傳 file_data 欄位內容（永遠回傳 null）

**實作位置**: CertificationResource

**檢查邏輯**:
```php
// app/Http/Resources/CertificationResource.php
public function toArray(Request $request): array
{
    return [
        // ...
        'file_data' => null, // 永遠回傳 null
        'file_mime' => $this->file_mime,
        'file_size' => $this->file_size,
        // ...
    ];
}
```

**理由**: file_data 是 MEDIUMBLOB，可能高達 16MB，回傳會嚴重影響效能

**測試案例**:
- GET `/salesperson/certifications` → 所有 file_data 都是 null
- file_mime 和 file_size 正確回傳

---

## 資料完整性規則

### BR-DI-001: 硬刪除策略

**描述**: Experiences 和 Certifications 執行硬刪除（直接從資料庫移除）

**實作位置**: Controller destroy 方法

**檢查邏輯**:
```php
$experience->delete(); // 硬刪除
$certification->delete(); // 硬刪除
```

**測試案例**:
- 刪除經驗後，資料庫中該筆記錄不存在
- 刪除證照後，資料庫中該筆記錄不存在

**注意事項**:
- 不使用軟刪除（SoftDeletes trait）
- 如需審計日誌，考慮在刪除前記錄到 audit log（未來考慮）

---

### BR-DI-002: 級聯刪除

**描述**: 刪除使用者時，自動刪除其所有 Experiences 和 Certifications

**實作位置**: Migration 外鍵定義

**檢查邏輯**:
```sql
FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE
```

**測試案例**:
- 刪除使用者 A → A 的所有 experiences 和 certifications 都被刪除
- 使用 `User::find($id)->delete()` 應觸發級聯刪除

---

## 審核相關規則

### BR-APPROVAL-001: Profile Status 來源

**描述**: `/salesperson/approval-status` 的 profile_status 從 salesperson_profiles 表取得

**實作位置**: SalespersonController::approvalStatus

**檢查邏輯**:
```php
$profile = $user->salespersonProfile;
$profileStatus = $profile?->approval_status ?? 'pending';
```

**測試案例**:
- 使用者有 profile → 回傳 profile.approval_status
- 使用者無 profile → 回傳 'pending'

---

### BR-APPROVAL-002: Company Status 來源

**描述**: `/salesperson/approval-status` 的 company_status 從關聯的 company 取得（可能為 null）

**實作位置**: SalespersonController::approvalStatus

**檢查邏輯**:
```php
$companyStatus = $profile?->company?->approval_status ?? null;
```

**測試案例**:
- Profile 有關聯 company → 回傳 company.approval_status
- Profile 無關聯 company → 回傳 null

---

### BR-APPROVAL-003: Certifications 列表包含所有狀態

**描述**: `/salesperson/approval-status` 回傳的 certifications 包含所有審核狀態（不篩選）

**實作位置**: SalespersonController::approvalStatus

**檢查邏輯**:
```php
'certifications' => $user->certifications->map(fn($cert) => [
    'id' => $cert->id,
    'name' => $cert->name,
    'approval_status' => $cert->approval_status,
    'rejected_reason' => $cert->rejected_reason,
]),
```

**測試案例**:
- 使用者有 pending, approved, rejected 證照 → 全部回傳

---

### BR-APPROVAL-004: Experiences 列表包含所有狀態

**描述**: `/salesperson/approval-status` 回傳的 experiences 包含所有審核狀態（不篩選）

**實作位置**: SalespersonController::approvalStatus

**檢查邏輯**: 同 BR-APPROVAL-003

---

## 狀態查詢規則

### BR-STATUS-001: 所有使用者可查詢自己的狀態

**描述**: `/salesperson/status` 對所有登入使用者開放（不限 salesperson）

**實作位置**: SalespersonController::status

**檢查邏輯**:
```php
public function status(Request $request): JsonResponse
{
    // 不檢查 role，所有登入使用者都可調用
    $user = $request->user();

    return response()->json([
        'success' => true,
        'data' => [
            'role' => $user->role,
            'salesperson_status' => $user->salesperson_status,
            // ...
        ]
    ]);
}
```

**測試案例**:
- role = 'user' → 200 OK，回傳狀態
- role = 'salesperson' → 200 OK，回傳狀態
- role = 'admin' → 200 OK，回傳狀態

---

### BR-STATUS-002: days_until_reapply 計算方式

**描述**: days_until_reapply 計算為 can_reapply_at - now() 的天數（可為負數）

**實作位置**: SalespersonController::status

**檢查邏輯**:
```php
$daysUntilReapply = null;
if ($user->can_reapply_at) {
    $daysUntilReapply = now()->diffInDays($user->can_reapply_at, false);
    // false 表示如果已過期會回傳負數
}
```

**測試案例**:
- can_reapply_at = null → days_until_reapply = null
- can_reapply_at = 未來 10 天 → days_until_reapply = 10
- can_reapply_at = 未來 0 天（今天）→ days_until_reapply = 0
- can_reapply_at = 過去 5 天 → days_until_reapply = -5

---

### BR-STATUS-003: can_reapply 由 Model 方法決定

**描述**: can_reapply 欄位由 User Model 的 canReapply() 方法決定

**實作位置**: User Model

**檢查邏輯**:
```php
// app/Models/User.php
public function canReapply(): bool
{
    if ($this->salesperson_status !== 'rejected') {
        return false;
    }

    if (!$this->can_reapply_at) {
        return false;
    }

    return now()->greaterThanOrEqualTo($this->can_reapply_at);
}

// Controller
'can_reapply' => $user->canReapply(),
```

**測試案例**:
- status = 'rejected', can_reapply_at = 過去 → true
- status = 'rejected', can_reapply_at = 未來 → false
- status = 'approved' → false
- status = 'rejected', can_reapply_at = null → false

---

## 錯誤處理規則

### BR-ERROR-001: 驗證錯誤統一格式

**描述**: 所有 Laravel Validation 錯誤都轉換為統一格式

**實作位置**: Exception Handler 或 Form Request

**檢查邏輯**:
```php
// app/Exceptions/Handler.php
protected function invalidJson($request, ValidationException $exception)
{
    return response()->json([
        'success' => false,
        'error' => [
            'code' => 'VALIDATION_ERROR',
            'message' => 'The given data was invalid',
            'details' => $exception->errors()
        ]
    ], 422);
}
```

**錯誤回應範例**:
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid",
    "details": {
      "email": ["The email has already been taken."],
      "password": ["The password must be at least 8 characters."]
    }
  }
}
```

---

### BR-ERROR-002: 404 錯誤統一格式

**描述**: 資源不存在時回傳統一的 404 錯誤

**實作位置**: Controller

**檢查邏輯**:
```php
$experience = Experience::find($id);

if (!$experience) {
    return response()->json([
        'success' => false,
        'error' => [
            'code' => 'NOT_FOUND',
            'message' => 'Experience not found'
        ]
    ], 404);
}
```

---

### BR-ERROR-003: 500 錯誤統一格式

**描述**: 伺服器內部錯誤回傳統一格式（不洩漏敏感資訊）

**實作位置**: Exception Handler

**檢查邏輯**:
```php
// app/Exceptions/Handler.php
public function render($request, Throwable $exception)
{
    if ($request->expectsJson()) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'INTERNAL_ERROR',
                'message' => 'An error occurred while processing your request'
            ]
        ], 500);
    }

    return parent::render($request, $exception);
}
```

---

## 效能相關規則

### BR-PERF-001: Eager Loading 避免 N+1

**描述**: 查詢關聯資料時使用 Eager Loading

**實作位置**: Controller 查詢

**檢查邏輯**:
```php
// ❌ N+1 查詢
$experiences = Experience::where('user_id', $userId)->get();
foreach ($experiences as $exp) {
    echo $exp->approver->name; // 每次都查詢
}

// ✅ Eager Loading
$experiences = Experience::with('approver')->where('user_id', $userId)->get();
foreach ($experiences as $exp) {
    echo $exp->approver->name; // 只查詢一次
}
```

**適用場景**:
- GET `/salesperson/approval-status` - 需要 eager load profile, company, certifications, experiences

---

## 安全相關規則

### BR-SEC-001: 防止 SQL Injection

**描述**: 所有資料庫查詢使用 Eloquent ORM 或參數化查詢

**檢查邏輯**:
```php
// ✅ 使用 Eloquent（自動防護）
Experience::where('user_id', $userId)->get();

// ✅ 使用參數化查詢
DB::select('SELECT * FROM experiences WHERE user_id = ?', [$userId]);

// ❌ 不安全（永遠不要這樣做）
DB::select("SELECT * FROM experiences WHERE user_id = $userId");
```

---

### BR-SEC-002: 密碼不明文儲存

**描述**: 密碼使用 Hash::make() 加密

**注意**: 本專案不涉及密碼修改，僅作為一般規則記錄

---

## 變更記錄

| 日期 | 版本 | 變更內容 |
|------|------|---------|
| 2026-01-11 | 1.0 | 初始版本，定義所有業務規則 |

---

**文檔狀態**: ✅ Complete
**審核狀態**: Pending Review
