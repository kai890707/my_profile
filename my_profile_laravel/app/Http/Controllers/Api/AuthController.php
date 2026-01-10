<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterSalespersonRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    /**
     * Register a new user.
     */
    #[OA\Post(
        path: '/auth/register',
        summary: '業務員註冊',
        description: '建立新的業務員帳號並自動建立個人檔案',
        tags: ['認證'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/RegisterRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: '註冊成功',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthResponse')
            ),
            new OA\Response(
                response: 422,
                description: '驗證失敗',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:100|unique:users,username',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'nullable|in:user,salesperson',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->authService->register($validator->validated());

        /** @var \App\Models\User $user */
        $user = $result['user'];

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                ],
                'access_token' => $result['access_token'],
                'refresh_token' => $result['refresh_token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
            ],
        ], 201);
    }

    /**
     * Authenticate user and return token.
     */
    #[OA\Post(
        path: '/auth/login',
        summary: '用戶登入',
        description: '使用 email 和密碼進行認證，返回 JWT access token 和 refresh token',
        tags: ['認證'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: '登入成功',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthResponse')
            ),
            new OA\Response(
                response: 401,
                description: '認證失敗',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: '驗證失敗',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->authService->login($validator->validated());

        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = $result['user'];

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                ],
                'access_token' => $result['access_token'],
                'refresh_token' => $result['refresh_token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
            ],
        ]);
    }

    /**
     * Refresh the authentication token.
     */
    #[OA\Post(
        path: '/auth/refresh',
        summary: '刷新 Token',
        description: '使用 refresh token 獲取新的 access token',
        tags: ['認證'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['refresh_token'],
                properties: [
                    new OA\Property(property: 'refresh_token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGc...'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Token 刷新成功',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Token refreshed successfully'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'access_token', type: 'string'),
                                new OA\Property(property: 'refresh_token', type: 'string'),
                                new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                                new OA\Property(property: 'expires_in', type: 'integer', example: 3600),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Refresh token 無效',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function refresh(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $refreshToken = $request->input('refresh_token');

            if (! is_string($refreshToken)) {
                throw new \Exception('Invalid refresh token');
            }

            $result = $this->authService->refreshWithToken($refreshToken);

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid refresh token',
            ], 401);
        }
    }

    /**
     * Log out the user (invalidate token).
     */
    #[OA\Post(
        path: '/auth/logout',
        summary: '登出',
        description: '將當前 access token 加入黑名單，使其失效',
        security: [['bearerAuth' => []]],
        tags: ['認證'],
        responses: [
            new OA\Response(
                response: 200,
                description: '登出成功',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Successfully logged out'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: '未認證',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function logout(): JsonResponse
    {
        try {
            $this->authService->logout();

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
            ], 500);
        }
    }

    /**
     * Get the authenticated user.
     */
    #[OA\Get(
        path: '/auth/me',
        summary: '取得當前用戶資訊',
        description: '返回當前已認證用戶的完整資訊',
        security: [['bearerAuth' => []]],
        tags: ['認證'],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功返回用戶資訊',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: '未認證',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function me(): JsonResponse
    {
        $user = $this->authService->user();

        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                    'created_at' => $user->created_at?->toIso8601String(),
                    'updated_at' => $user->updated_at?->toIso8601String(),
                ],
            ],
        ]);
    }

    /**
     * Register directly as salesperson.
     *
     * POST /api/auth/register-salesperson
     */
    #[OA\Post(
        path: '/auth/register-salesperson',
        summary: '業務員直接註冊',
        description: '建立新的業務員帳號並自動建立業務員檔案（狀態為 pending）',
        tags: ['認證'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/RegisterSalespersonRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: '註冊成功',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', properties: [
                            new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                            new OA\Property(property: 'access_token', type: 'string'),
                            new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                        ], type: 'object'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: '驗證失敗',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
    public function registerSalesperson(RegisterSalespersonRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Generate username from email (part before @) + random suffix
            $emailPrefix = explode('@', $request->input('email'))[0];
            $username = $emailPrefix . '_' . \Illuminate\Support\Str::random(4);

            // Ensure username is unique (should be very unlikely to collide)
            while (User::where('username', $username)->exists()) {
                $username = $emailPrefix . '_' . \Illuminate\Support\Str::random(4);
            }

            // Create user
            $user = User::create([
                'username' => $username,
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password_hash' => Hash::make($request->input('password')),
                'role' => User::ROLE_SALESPERSON,
                'salesperson_status' => User::STATUS_PENDING,
                'salesperson_applied_at' => now(),
                'status' => 'active', // User must be active for JWT auth
            ]);

            // Create salesperson profile
            $user->salespersonProfile()->create([
                'full_name' => $request->input('full_name'),
                'phone' => $request->input('phone'),
                'bio' => $request->input('bio'),
                'specialties' => $request->input('specialties'),
                'service_regions' => $request->input('service_regions'),
            ]);

            DB::commit();

            // Generate token using AuthService
            $loginResult = $this->authService->login([
                'email' => $request->input('email'),
                'password' => $request->input('password'),
            ]);

            if ($loginResult === null) {
                throw new \Exception('Failed to generate authentication token');
            }

            // Reload user with profile
            $user->load('salespersonProfile');

            return response()->json([
                'success' => true,
                'message' => '註冊成功！您的業務員資料正在審核中，預計 1-3 個工作天完成。',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'salesperson_status' => $user->salesperson_status,
                        'salesperson_applied_at' => $user->salesperson_applied_at?->toIso8601String(),
                    ],
                    'profile' => $user->salespersonProfile,
                    'access_token' => $loginResult['access_token'],
                    'refresh_token' => $loginResult['refresh_token'],
                    'token_type' => $loginResult['token_type'],
                    'expires_in' => $loginResult['expires_in'],
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => '註冊失敗：' . $e->getMessage(),
            ], 500);
        }
    }
}
