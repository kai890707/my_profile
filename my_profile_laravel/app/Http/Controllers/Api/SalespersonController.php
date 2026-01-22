<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveCompanyRequest;
use App\Http\Requests\UpdateSalespersonProfileRequest;
use App\Http\Requests\UpgradeSalespersonRequest;
use App\Models\User;
use App\Services\AvatarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

class SalespersonController extends Controller
{
    /**
     * Upgrade current user to salesperson.
     *
     * POST /api/salesperson/upgrade
     */
    #[OA\Post(
        path: '/api/salesperson/upgrade',
        summary: '升級為業務員',
        description: '將當前用戶升級為業務員，提交審核。審核通過後可使用完整業務員功能。',
        security: [['bearerAuth' => []]],
        tags: ['業務員功能 - 帳號管理'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['full_name', 'phone'],
                properties: [
                    new OA\Property(property: 'full_name', type: 'string', maxLength: 255, example: '王小明'),
                    new OA\Property(property: 'phone', type: 'string', pattern: '^09\d{8}$', example: '0912345678'),
                    new OA\Property(property: 'bio', type: 'string', maxLength: 1000, nullable: true, example: '擁有10年保險業務經驗，專注於壽險與醫療險'),
                    new OA\Property(property: 'specialties', type: 'string', maxLength: 500, nullable: true, example: '壽險, 醫療險, 投資型保單'),
                    new OA\Property(
                        property: 'service_regions',
                        type: 'array',
                        items: new OA\Items(type: 'string', maxLength: 100),
                        nullable: true,
                        example: ['台北市', '新北市', '桃園市']
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: '升級成功，進入審核中',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                        new OA\Property(property: 'message', type: 'string', example: '升級成功！您的業務員資料正在審核中，預計 1-3 個工作天完成。'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: '已經是業務員',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 401,
                description: '未認證',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: '驗證失敗',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
            new OA\Response(
                response: 429,
                description: '申請遭拒，需等待重新申請期限',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string', example: '請於 2026-02-01 後重新申請'),
                        new OA\Property(property: 'can_reapply_at', type: 'string', format: 'date-time', example: '2026-02-01T00:00:00Z'),
                    ]
                )
            ),
        ]
    )]
    public function upgrade(UpgradeSalespersonRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Authentication required'],
            ], 401);
        }

        // Check if already salesperson
        if ($user->isSalesperson()) {
            return response()->json([
                'success' => false,
                'error' => '您已經是業務員',
            ], 400);
        }

        // Check if rejected and can reapply
        if ($user->salesperson_status === User::STATUS_REJECTED && ! $user->canReapply()) {
            return response()->json([
                'success' => false,
                'error' => '請於 '.$user->can_reapply_at?->format('Y-m-d').' 後重新申請',
                'can_reapply_at' => $user->can_reapply_at,
            ], 429);
        }

        DB::beginTransaction();

        try {
            $user->upgradeToSalesperson([
                'full_name' => $request->input('full_name'),
                'phone' => $request->input('phone'),
                'bio' => $request->input('bio'),
                'specialties' => $request->input('specialties'),
                'service_regions' => $request->input('service_regions'),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'user' => $user->fresh()->load('salespersonProfile'),
                'message' => '升級成功！您的業務員資料正在審核中，預計 1-3 個工作天完成。',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => '升級失敗：'.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get salesperson status.
     *
     * GET /api/salesperson/status
     */
    #[OA\Get(
        path: '/api/salesperson/status',
        summary: '取得業務員狀態',
        description: '取得當前用戶的業務員申請與審核狀態。公開端點，未登入返回 null。',
        tags: ['業務員功能 - 帳號管理'],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功取得狀態',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'role', type: 'string', enum: ['user', 'salesperson', 'admin'], example: 'salesperson'),
                                new OA\Property(property: 'salesperson_status', type: 'string', enum: ['pending', 'approved', 'rejected'], nullable: true, example: 'approved'),
                                new OA\Property(property: 'salesperson_applied_at', type: 'string', format: 'date-time', nullable: true, example: '2026-01-15T10:00:00Z'),
                                new OA\Property(property: 'salesperson_approved_at', type: 'string', format: 'date-time', nullable: true, example: '2026-01-16T14:30:00Z'),
                                new OA\Property(property: 'rejection_reason', type: 'string', nullable: true, example: '資料不完整'),
                                new OA\Property(property: 'can_reapply', type: 'boolean', example: false),
                                new OA\Property(property: 'can_reapply_at', type: 'string', format: 'date-time', nullable: true, example: '2026-02-01T00:00:00Z'),
                                new OA\Property(property: 'days_until_reapply', type: 'integer', nullable: true, example: 16),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function status(): JsonResponse
    {
        /** @var User|null $user */
        $user = auth()->user();

        // If user is not logged in or has never applied to be a salesperson
        if (! $user || $user->salesperson_status === null) {
            return response()->json([
                'success' => true,
                'data' => [
                    'role' => $user?->role ?? 'user',
                    'salesperson_status' => null,
                    'salesperson_applied_at' => null,
                    'salesperson_approved_at' => null,
                    'rejection_reason' => null,
                    'can_reapply' => false,
                    'can_reapply_at' => null,
                    'days_until_reapply' => null,
                ],
            ]);
        }

        // Calculate days until reapply
        $daysUntilReapply = null;
        if ($user->can_reapply_at) {
            $diff = now()->diffInDays($user->can_reapply_at, false);
            // Use ceil to round up partial days, or 0 if past date
            $daysUntilReapply = $diff < 0 ? 0 : (int) ceil($diff);
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
        ]);
    }

    /**
     * Get approval status for all resources.
     *
     * GET /api/salesperson/approval-status
     */
    #[OA\Get(
        path: '/api/salesperson/approval-status',
        summary: '取得所有資源審核狀態',
        description: '取得業務員的個人資料、公司、證照、經驗等所有資源的審核狀態。僅業務員可存取。',
        security: [['bearerAuth' => []]],
        tags: ['業務員功能 - 帳號管理'],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功取得審核狀態',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/ApprovalStatus'
                        ),
                        new OA\Property(property: 'message', type: 'string', example: 'Approval status retrieved successfully'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: '未認證',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 403,
                description: '權限不足 (非業務員)',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function approvalStatus(\Illuminate\Http\Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Authentication required'],
            ], 401);
        }

        // Check if user is a salesperson
        if (! $user->isSalesperson()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Only salespeople can access approval status',
                ],
            ], 403);
        }

        // Eager load relationships to avoid N+1 queries
        $user->load([
            'salespersonProfile',
            'salespersonProfile.company',
            'certifications',
            'experiences',
        ]);

        // Get profile status
        $profileStatus = $user->salespersonProfile?->approval_status ?? 'pending';

        // Get company status
        $companyStatus = $user->salespersonProfile?->company?->approval_status ?? null;

        // Get certifications with approval status
        $certifications = $user->certifications->map(function ($cert) {
            return [
                'id' => $cert->id,
                'name' => $cert->name,
                'approval_status' => $cert->approval_status,
                'rejected_reason' => $cert->rejected_reason,
            ];
        });

        // Get experiences with approval status
        $experiences = $user->experiences->map(function ($exp) {
            return [
                'id' => $exp->id,
                'company' => $exp->company,
                'position' => $exp->position,
                'approval_status' => $exp->approval_status,
                'rejected_reason' => $exp->rejected_reason,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'profile_status' => $profileStatus,
                'company_status' => $companyStatus,
                'certifications' => $certifications,
                'experiences' => $experiences,
            ],
            'message' => 'Approval status retrieved successfully',
        ]);
    }

    /**
     * Update salesperson profile.
     *
     * PUT /api/salesperson/profile
     */
    #[OA\Put(
        path: '/api/salesperson/profile',
        summary: '更新業務員個人資料',
        description: '更新業務員的個人資料，包含基本資料、專長、服務區域、頭像等。僅業務員可使用。',
        security: [['bearerAuth' => []]],
        tags: ['業務員功能 - 個人資料'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'full_name', type: 'string', maxLength: 255, example: '王小明'),
                    new OA\Property(property: 'phone', type: 'string', maxLength: 20, example: '0912345678'),
                    new OA\Property(property: 'bio', type: 'string', maxLength: 1000, nullable: true, example: '資深保險業務，專注於壽險與醫療險'),
                    new OA\Property(property: 'specialties', type: 'string', maxLength: 500, nullable: true, example: '壽險, 醫療險, 投資型保單'),
                    new OA\Property(
                        property: 'service_regions',
                        type: 'array',
                        items: new OA\Items(type: 'string', maxLength: 100),
                        nullable: true,
                        example: ['台北市', '新北市']
                    ),
                    new OA\Property(property: 'company_id', type: 'integer', nullable: true, example: 1),
                    new OA\Property(
                        property: 'avatar',
                        type: 'string',
                        nullable: true,
                        description: 'Base64 編碼的圖片資料 (Data URL 格式: data:image/jpeg;base64,...)',
                        example: 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAA...'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: '成功更新個人資料',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'profile', ref: '#/components/schemas/SalespersonProfile'),
                        new OA\Property(property: 'message', type: 'string', example: '個人資料已更新'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: '未認證',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 403,
                description: '權限不足 (非業務員)',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: '驗證失敗或頭像處理失敗',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
    public function updateProfile(
        UpdateSalespersonProfileRequest $request,
        AvatarService $avatarService
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Authentication required'],
            ], 401);
        }

        if (! $user->isSalesperson()) {
            return response()->json([
                'success' => false,
                'error' => '僅業務員可更新個人資料',
            ], 403);
        }

        $data = $request->validated();

        // Process avatar if present
        if ($request->filled('avatar')) {
            try {
                $processed = $avatarService->processAvatar($data['avatar']);

                // Replace avatar data URL with processed binary data
                $data['avatar_data'] = $processed['data'];
                $data['avatar_mime'] = $processed['mime'];
                $data['avatar_size'] = $processed['size'];
                unset($data['avatar']);

            } catch (\InvalidArgumentException $e) {
                Log::warning('Avatar processing failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'AVATAR_PROCESSING_FAILED',
                        'message' => $e->getMessage(),
                    ],
                ], 422);
            }
        }

        $user->salespersonProfile()->update($data);

        // Reload profile to get updated data
        $profile = $user->salespersonProfile()->first();

        // Build response with avatar data URL (using helper to properly encode binary data)
        $responseData = $profile->toArray();

        // Remove binary fields that can't be JSON encoded
        unset($responseData['avatar_data'], $responseData['avatar_mime'], $responseData['avatar_size']);

        // Add avatar as data URL instead
        $responseData['avatar'] = $avatarService->getAvatarUrl($profile);

        return response()->json([
            'success' => true,
            'profile' => $responseData,
            'message' => '個人資料已更新',
        ]);
    }

    /**
     * Search approved salespeople.
     *
     * GET /api/salespeople
     */
    #[OA\Get(
        path: '/api/salespeople',
        summary: '搜尋業務員',
        description: '搜尋已審核通過的業務員列表。公開端點，支援分頁。',
        tags: ['業務員搜尋'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                description: '頁碼',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1, example: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                description: '每頁筆數',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 20, example: 20)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功取得業務員列表',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/SalespersonSearchResult')
                        ),
                    ]
                )
            ),
        ]
    )]
    public function index(AvatarService $avatarService): JsonResponse
    {
        $salespeople = User::where('role', User::ROLE_SALESPERSON)
            ->where('salesperson_status', User::STATUS_APPROVED)
            ->whereHas('salespersonProfile')
            ->with(['salespersonProfile.company'])
            ->paginate(20);

        // Transform to flat structure matching SalespersonSearchResult
        $transformed = $salespeople->through(function (User $user) use ($avatarService) {
            $profile = $user->salespersonProfile;
            $company = $profile?->company;

            // Build avatar data URL using helper (properly encodes binary data)
            $avatarUrl = $profile ? $avatarService->getAvatarUrl($profile) : null;

            return [
                'id' => $profile?->id,
                'full_name' => $profile?->full_name,
                'phone' => $profile?->phone ?? '',
                'bio' => $profile?->bio,
                'specialties' => $profile?->specialties,
                'avatar' => $avatarUrl,
                'company_name' => $company?->name,
                'industry_name' => null, // Industry relation removed from Company model
                'service_regions' => $profile?->service_regions ?? [],
                'created_at' => $user->created_at?->toIso8601String() ?? '',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transformed,
        ]);
    }

    /**
     * Save company information for salesperson.
     *
     * POST /api/salesperson/company
     */
    #[OA\Post(
        path: '/api/salesperson/company',
        summary: '儲存公司資訊',
        description: '設定業務員的所屬公司或自營業者狀態。僅業務員可使用。',
        security: [['bearerAuth' => []]],
        tags: ['業務員功能 - 個人資料'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'company_id',
                        type: 'integer',
                        nullable: true,
                        description: '公司ID (與 is_self_employed 互斥)',
                        example: 1
                    ),
                    new OA\Property(
                        property: 'is_self_employed',
                        type: 'boolean',
                        nullable: true,
                        description: '是否為自營業者 (與 company_id 互斥)',
                        example: false
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: '成功儲存公司資訊',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'profile',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'company_id', type: 'integer', nullable: true, example: 1),
                                        new OA\Property(
                                            property: 'company',
                                            properties: [
                                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                                new OA\Property(property: 'name', type: 'string', example: 'ABC保險公司'),
                                            ],
                                            type: 'object',
                                            nullable: true
                                        ),
                                    ],
                                    type: 'object'
                                ),
                            ],
                            type: 'object'
                        ),
                        new OA\Property(property: 'message', type: 'string', example: '公司資訊已更新'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: '未認證',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 403,
                description: '權限不足 (非業務員)',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: '業務員個人資料不存在',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: '驗證失敗 (company_id 與 is_self_employed 互斥)',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
    public function saveCompany(SaveCompanyRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Authentication required'],
            ], 401);
        }

        // Check if user is a salesperson
        if (! $user->isSalesperson()) {
            return response()->json([
                'success' => false,
                'error' => '僅業務員可儲存公司資訊',
            ], 403);
        }

        // Get or create salesperson profile
        $profile = $user->salespersonProfile;

        if (! $profile) {
            return response()->json([
                'success' => false,
                'error' => '業務員個人資料不存在',
            ], 404);
        }

        // Update company information
        if ($request->filled('company_id')) {
            // Set company_id
            $profile->update(['company_id' => $request->input('company_id')]);
            $message = '公司資訊已更新';
        } elseif ($request->boolean('is_self_employed')) {
            // Set to self-employed (company_id = null)
            $profile->update(['company_id' => null]);
            $message = '已設定為自營業者';
        }

        // Reload profile with company relationship
        $profile->load('company');

        return response()->json([
            'success' => true,
            'data' => [
                'profile' => [
                    'id' => $profile->id,
                    'company_id' => $profile->company_id,
                    'company' => $profile->company ? [
                        'id' => $profile->company->id,
                        'name' => $profile->company->name,
                    ] : null,
                ],
            ],
            'message' => $message,
        ]);
    }

    /**
     * Upload avatar for current salesperson.
     *
     * POST /api/salesperson/avatar
     * Rate limit: 10 requests/minute
     */
    #[OA\Post(
        path: '/api/salesperson/avatar',
        summary: '上傳頭像',
        description: '上傳或更新業務員頭像。支援 JPEG、PNG、WebP、GIF 格式。僅業務員可使用。限流: 10 次/分鐘。',
        security: [['bearerAuth' => []]],
        tags: ['業務員功能 - 個人資料'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['avatar'],
                properties: [
                    new OA\Property(
                        property: 'avatar',
                        type: 'string',
                        description: 'Base64 編碼的圖片資料 (Data URL 格式: data:image/jpeg;base64,...)',
                        example: 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAA...'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: '成功上傳頭像',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'avatar', type: 'string', example: 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAA...'),
                            ],
                            type: 'object'
                        ),
                        new OA\Property(property: 'message', type: 'string', example: '頭像上傳成功'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: '頭像上傳失敗',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 401,
                description: '未認證',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 403,
                description: '權限不足 (非業務員)',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: '業務員個人資料不存在',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: '驗證失敗',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
            new OA\Response(
                response: 429,
                description: '超過限流限制 (10次/分鐘)',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function uploadAvatar(
        UpdateSalespersonProfileRequest $request,
        AvatarService $avatarService
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Authentication required'],
            ], 401);
        }

        if (! $user->isSalesperson()) {
            return response()->json([
                'success' => false,
                'error' => '僅業務員可上傳頭像',
            ], 403);
        }

        $profile = $user->salespersonProfile;

        if (! $profile) {
            return response()->json([
                'success' => false,
                'error' => '業務員個人資料不存在',
            ], 404);
        }

        // Validate avatar is present
        if (! $request->filled('avatar')) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Avatar is required'],
            ], 422);
        }

        try {
            $processed = $avatarService->processAvatar($request->input('avatar'));

            // Update profile with processed avatar
            $profile->avatar_data = $processed['data'];
            $profile->avatar_mime = $processed['mime'];
            $profile->save();

            Log::info('Avatar uploaded successfully', [
                'user_id' => $user->id,
                'profile_id' => $profile->id,
                'mime' => $processed['mime'],
                'size' => $processed['size'],
            ]);

            // Return avatar as data URL
            $avatarUrl = $avatarService->getAvatarUrl($profile);

            return response()->json([
                'success' => true,
                'data' => [
                    'avatar' => $avatarUrl,
                ],
                'message' => '頭像上傳成功',
            ]);
        } catch (\Exception $e) {
            Log::error('Avatar upload failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'AVATAR_UPLOAD_FAILED',
                    'message' => $e->getMessage(),
                ],
            ], 400);
        }
    }

    /**
     * Delete avatar for current salesperson.
     *
     * DELETE /api/salesperson/avatar
     * Rate limit: 10 requests/minute
     */
    #[OA\Delete(
        path: '/api/salesperson/avatar',
        summary: '刪除頭像',
        description: '刪除業務員頭像，恢復為無頭像狀態。僅業務員可使用。限流: 10 次/分鐘。',
        security: [['bearerAuth' => []]],
        tags: ['業務員功能 - 個人資料'],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功刪除頭像',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'avatar', type: 'string', nullable: true, example: null),
                            ],
                            type: 'object'
                        ),
                        new OA\Property(property: 'message', type: 'string', example: '頭像已刪除'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: '未認證',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 403,
                description: '權限不足 (非業務員)',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: '業務員個人資料不存在',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 429,
                description: '超過限流限制 (10次/分鐘)',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function deleteAvatar(): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Authentication required'],
            ], 401);
        }

        if (! $user->isSalesperson()) {
            return response()->json([
                'success' => false,
                'error' => '僅業務員可刪除頭像',
            ], 403);
        }

        $profile = $user->salespersonProfile;

        if (! $profile) {
            return response()->json([
                'success' => false,
                'error' => '業務員個人資料不存在',
            ], 404);
        }

        // Clear avatar
        $profile->avatar_data = null;
        $profile->avatar_mime = null;
        $profile->save();

        Log::info('Avatar deleted successfully', [
            'user_id' => $user->id,
            'profile_id' => $profile->id,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'avatar' => null,
            ],
            'message' => '頭像已刪除',
        ]);
    }
}
