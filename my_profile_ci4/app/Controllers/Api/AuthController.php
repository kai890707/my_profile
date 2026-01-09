<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\SalespersonProfileModel;
use App\Libraries\JWTLib;
use OpenApi\Attributes as OA;

#[OA\Info(title: "業務推廣系統 API", version: "1.0.0", description: "業務員管理與搜尋平台的 RESTful API")]
#[OA\Server(url: "http://localhost:8080", description: "開發環境")]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "輸入 JWT Token（不需要加 'Bearer ' 前綴）"
)]
#[OA\Tag(name: "認證", description: "使用者認證與授權相關 API")]
class AuthController extends BaseController
{
    protected $userModel;
    protected $salespersonProfileModel;
    protected $jwtLib;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->salespersonProfileModel = new SalespersonProfileModel();
        $this->jwtLib = new JWTLib();
    }

    #[OA\Post(
        path: "/api/auth/register",
        tags: ["認證"],
        summary: "業務員註冊",
        description: "建立新的業務員帳號並自動建立個人檔案",
        requestBody: new OA\RequestBody(
            required: true,
            description: "註冊資料",
            content: new OA\JsonContent(
                required: ["username", "email", "password", "full_name"],
                properties: [
                    new OA\Property(property: "username", type: "string", minLength: 3, maxLength: 50, example: "john_doe", description: "使用者名稱"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "john@example.com", description: "電子郵件"),
                    new OA\Property(property: "password", type: "string", format: "password", minLength: 8, example: "SecurePass123", description: "密碼"),
                    new OA\Property(property: "full_name", type: "string", minLength: 2, maxLength: 100, example: "王小明", description: "真實姓名"),
                    new OA\Property(property: "phone", type: "string", pattern: "^09\\d{8}$", example: "0912345678", description: "手機號碼（選填）")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "註冊成功",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "註冊成功"),
                        new OA\Property(property: "data", type: "object")
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "驗證失敗",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "驗證失敗")
                    ]
                )
            )
        ]
    )]
    public function register()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'full_name' => 'required|min_length[2]|max_length[100]',
            'phone' => 'permit_empty|regex_match[/^09\d{8}$/]',
        ];

        if (!$this->validate($rules)) {
            return $this->respondValidationError($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);

        // 開始交易
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 建立 User 帳號
            $userData = [
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'], // Model 會自動雜湊
                'role' => 'salesperson',
                'status' => 'pending', // 待審核
            ];

            $userId = $this->userModel->insert($userData);

            if (!$userId) {
                throw new \Exception('建立使用者失敗');
            }

            // 建立業務員 Profile
            $profileData = [
                'user_id' => $userId,
                'full_name' => $data['full_name'],
                'phone' => $data['phone'] ?? null,
                'bio' => $data['bio'] ?? null,
                'approval_status' => 'pending', // 待審核
            ];

            $profileId = $this->salespersonProfileModel->insert($profileData);

            if (!$profileId) {
                throw new \Exception('建立業務員資料失敗');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('資料庫交易失敗');
            }

            return $this->respondSuccess([
                'user_id' => $userId,
                'username' => $data['username'],
                'email' => $data['email'],
                'status' => 'pending',
            ], '註冊成功，請等待管理員審核', 201);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Register failed: ' . $e->getMessage());
            return $this->respondError('註冊失敗：' . $e->getMessage(), 500);
        }
    }

    #[OA\Post(
        path: "/api/auth/login",
        tags: ["認證"],
        summary: "使用者登入",
        description: "使用 email 和密碼登入，返回 JWT Token",
        requestBody: new OA\RequestBody(
            required: true,
            description: "登入憑證",
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "john@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "SecurePass123")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "登入成功"),
            new OA\Response(response: 401, description: "登入失敗"),
            new OA\Response(response: 422, description: "驗證失敗")
        ]
    )]
    public function login()
    {
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->respondValidationError($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);
        $email = $data['email'];
        $password = $data['password'];

        // 查詢使用者
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            return $this->respondError('Email 或密碼錯誤', 401);
        }

        // 驗證密碼
        if (!$this->userModel->verifyPassword($password, $user['password_hash'])) {
            return $this->respondError('Email 或密碼錯誤', 401);
        }

        // 檢查帳號狀態
        if ($user['status'] === 'pending') {
            return $this->respondError('您的帳號尚未通過審核，請等待管理員審核', 403);
        }

        if ($user['status'] === 'inactive') {
            return $this->respondError('您的帳號已被停用', 403);
        }

        // 生成 JWT Token
        $accessToken = $this->jwtLib->generateAccessToken(
            $user['id'],
            $user['email'],
            $user['role']
        );

        $refreshToken = $this->jwtLib->generateRefreshToken($user['id']);

        return $this->respondSuccess([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => getenv('JWT_ACCESS_EXPIRY') ?: 3600,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'status' => $user['status'],
            ],
        ], '登入成功');
    }

    #[OA\Post(
        path: "/api/auth/refresh",
        tags: ["認證"],
        summary: "刷新 Access Token",
        description: "使用 Refresh Token 取得新的 Access Token",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["refresh_token"],
                properties: [
                    new OA\Property(property: "refresh_token", type: "string", example: "eyJ0eXAiOiJKV1Qi...")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "刷新成功"),
            new OA\Response(response: 401, description: "Token 無效或過期")
        ]
    )]
    public function refresh()
    {
        $rules = [
            'refresh_token' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->respondValidationError($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);
        $refreshToken = $data['refresh_token'];

        // 驗證 Refresh Token
        $payload = $this->jwtLib->verifyToken($refreshToken);

        if (!$payload) {
            return $this->respondError('Refresh Token 無效或已過期', 401);
        }

        // 檢查 Token 類型
        if (!$this->jwtLib->validateTokenType($payload, 'refresh')) {
            return $this->respondError('Token 類型不正確', 401);
        }

        // 取得使用者資訊
        $userId = $payload->sub;
        $user = $this->userModel->find($userId);

        if (!$user) {
            return $this->respondError('使用者不存在', 404);
        }

        // 生成新的 Access Token
        $newAccessToken = $this->jwtLib->generateAccessToken(
            $user['id'],
            $user['email'],
            $user['role']
        );

        return $this->respondSuccess([
            'access_token' => $newAccessToken,
            'token_type' => 'Bearer',
            'expires_in' => getenv('JWT_ACCESS_EXPIRY') ?: 3600,
        ], 'Token 刷新成功');
    }

    #[OA\Post(
        path: "/api/auth/logout",
        tags: ["認證"],
        summary: "使用者登出",
        description: "登出（前端需清除儲存的 Token）",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "登出成功"),
            new OA\Response(response: 401, description: "未認證")
        ]
    )]
    public function logout()
    {
        // JWT 是無狀態的，實際登出操作由前端清除 Token
        // 這裡只是提供一個端點讓前端呼叫
        return $this->respondSuccess(null, '登出成功');
    }

    #[OA\Get(
        path: "/api/auth/me",
        tags: ["認證"],
        summary: "取得當前使用者資訊",
        description: "取得當前已登入使用者的詳細資訊",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "成功取得使用者資訊"),
            new OA\Response(response: 401, description: "未認證")
        ]
    )]
    public function me()
    {
        $userId = $this->getCurrentUserId();

        if (!$userId) {
            return $this->respondUnauthorized();
        }

        $user = $this->userModel->find($userId);

        if (!$user) {
            return $this->respondNotFound('使用者不存在');
        }

        // 移除敏感資訊
        unset($user['password_hash']);
        unset($user['deleted_at']);

        // 如果是業務員，加入 Profile 資訊
        if ($user['role'] === 'salesperson') {
            $profile = $this->salespersonProfileModel->getByUserId($userId);
            if ($profile) {
                // 移除檔案資料 (太大)
                unset($profile['avatar_data']);
                $user['profile'] = $profile;
            }
        }

        return $this->respondSuccess($user, '取得使用者資訊成功');
    }
}
