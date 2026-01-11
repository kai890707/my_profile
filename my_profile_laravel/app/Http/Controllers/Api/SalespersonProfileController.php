<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SalespersonProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class SalespersonProfileController extends Controller
{
    public function __construct(
        private readonly SalespersonProfileService $profileService
    ) {}

    /**
     * Get all approved salesperson profiles (public).
     */
    #[OA\Get(
        path: '/profiles',
        summary: '取得所有業務員檔案',
        description: '取得所有已審核通過的業務員檔案列表，支援公司、地區和關鍵字篩選',
        tags: ['業務員檔案'],
        parameters: [
            new OA\Parameter(
                name: 'company_id',
                in: 'query',
                description: '公司 ID',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'service_region',
                in: 'query',
                description: '服務地區 ID',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'search',
                in: 'query',
                description: '搜尋關鍵字（姓名）',
                required: false,
                schema: new OA\Schema(type: 'string', example: '王')
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                description: '每頁筆數',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 15, example: 15)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功返回業務員列表',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'profiles',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/SalespersonProfile')),
                                        new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                        new OA\Property(property: 'total', type: 'integer', example: 50),
                                    ]
                                ),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'company_id' => $request->query('company_id'),
            'service_region' => $request->query('service_region'),
            'search' => $request->query('search'),
            'per_page' => $request->query('per_page', 15),
        ];

        $profiles = $this->profileService->getAll($filters);

        return response()->json([
            'success' => true,
            'data' => [
                'profiles' => $profiles,
            ],
        ]);
    }

    /**
     * Get a single salesperson profile by ID (public).
     */
    #[OA\Get(
        path: '/profiles/{id}',
        summary: '取得單一業務員檔案',
        description: '根據 ID 取得業務員的完整檔案資訊',
        tags: ['業務員檔案'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: '業務員檔案 ID',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功返回業務員檔案',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'profile', ref: '#/components/schemas/SalespersonProfile'),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: '業務員檔案不存在',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $profile = $this->profileService->getById($id);

        if ($profile === null) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'profile' => $profile,
            ],
        ]);
    }

    /**
     * Get authenticated user's salesperson profile.
     */
    #[OA\Get(
        path: '/profile',
        summary: '取得我的業務員檔案',
        description: '取得當前已認證用戶的業務員檔案',
        security: [['bearerAuth' => []]],
        tags: ['業務員檔案'],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功返回業務員檔案',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'profile', ref: '#/components/schemas/SalespersonProfile'),
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
            new OA\Response(
                response: 404,
                description: '業務員檔案不存在',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function me(Request $request): JsonResponse
    {
        // Get authenticated user using Laravel standard method
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $profile = $this->profileService->getByUserId($user->id);

        if ($profile === null) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'profile' => $profile,
            ],
        ]);
    }

    /**
     * Create salesperson profile for authenticated user.
     */
    #[OA\Post(
        path: '/profile',
        summary: '建立業務員檔案',
        description: '為當前用戶建立業務員檔案',
        security: [['bearerAuth' => []]],
        tags: ['業務員檔案'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['full_name', 'phone'],
                properties: [
                    new OA\Property(property: 'company_id', type: 'integer', nullable: true, example: 1),
                    new OA\Property(property: 'full_name', type: 'string', maxLength: 200, example: '王小明'),
                    new OA\Property(property: 'phone', type: 'string', maxLength: 20, example: '0912-345-678'),
                    new OA\Property(property: 'bio', type: 'string', nullable: true, example: '資深業務員，專注於科技產業'),
                    new OA\Property(property: 'specialties', type: 'string', nullable: true, example: 'SaaS, Cloud Solutions'),
                    new OA\Property(property: 'service_regions', type: 'array', nullable: true, items: new OA\Items(type: 'integer'), example: [1, 2, 3]),
                    new OA\Property(property: 'avatar', type: 'string', nullable: true, description: 'Base64 encoded image data'),
                    new OA\Property(property: 'avatar_mime', type: 'string', maxLength: 50, nullable: true, example: 'image/jpeg'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: '業務員檔案建立成功',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Profile created successfully'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'profile', ref: '#/components/schemas/SalespersonProfile'),
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
            new OA\Response(
                response: 409,
                description: '檔案已存在',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: '驗證失敗',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        // Get authenticated user using Laravel standard method
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Check if user already has a profile
        $existingProfile = $this->profileService->getByUserId($user->id);
        if ($existingProfile !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Profile already exists',
            ], 409);
        }

        $validator = Validator::make($request->all(), [
            'company_id' => 'nullable|integer|exists:companies,id',
            'full_name' => 'required|string|max:200',
            'phone' => 'required|string|max:20|regex:/^[0-9\-\s\(\)]+$/',
            'bio' => 'nullable|string',
            'specialties' => 'nullable|string',
            'service_regions' => 'nullable|array',
            'avatar' => 'nullable|string',
            'avatar_mime' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $profile = $this->profileService->create($user, $validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profile created successfully',
            'data' => [
                'profile' => $profile,
            ],
        ], 201);
    }

    /**
     * Update authenticated user's salesperson profile.
     */
    #[OA\Put(
        path: '/profile',
        summary: '更新業務員檔案',
        description: '更新當前用戶的業務員檔案',
        security: [['bearerAuth' => []]],
        tags: ['業務員檔案'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'company_id', type: 'integer', nullable: true, example: 1),
                    new OA\Property(property: 'full_name', type: 'string', maxLength: 200, example: '王小明'),
                    new OA\Property(property: 'phone', type: 'string', maxLength: 20, example: '0912-345-678'),
                    new OA\Property(property: 'bio', type: 'string', nullable: true, example: '資深業務員，專注於科技產業'),
                    new OA\Property(property: 'specialties', type: 'string', nullable: true, example: 'SaaS, Cloud Solutions'),
                    new OA\Property(property: 'service_regions', type: 'array', nullable: true, items: new OA\Items(type: 'integer'), example: [1, 2, 3]),
                    new OA\Property(property: 'avatar', type: 'string', nullable: true, description: 'Base64 encoded image data'),
                    new OA\Property(property: 'avatar_mime', type: 'string', maxLength: 50, nullable: true, example: 'image/jpeg'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: '業務員檔案更新成功',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Profile updated successfully'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'profile', ref: '#/components/schemas/SalespersonProfile'),
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
            new OA\Response(
                response: 404,
                description: '業務員檔案不存在',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: '驗證失敗',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
    public function update(Request $request): JsonResponse
    {
        // Get authenticated user using Laravel standard method
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $profile = $this->profileService->getByUserId($user->id);

        if ($profile === null) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'company_id' => 'nullable|integer|exists:companies,id',
            'full_name' => 'sometimes|required|string|max:200',
            'phone' => 'sometimes|required|string|max:20|regex:/^[0-9\-\s\(\)]+$/',
            'bio' => 'nullable|string',
            'specialties' => 'nullable|string',
            'service_regions' => 'nullable|array',
            'avatar' => 'nullable|string',
            'avatar_mime' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $profile = $this->profileService->update($profile, $validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'profile' => $profile,
            ],
        ]);
    }

    /**
     * Delete authenticated user's salesperson profile.
     */
    #[OA\Delete(
        path: '/profile',
        summary: '刪除業務員檔案',
        description: '刪除當前用戶的業務員檔案',
        security: [['bearerAuth' => []]],
        tags: ['業務員檔案'],
        responses: [
            new OA\Response(
                response: 200,
                description: '業務員檔案刪除成功',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Profile deleted successfully'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: '未認證',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: '業務員檔案不存在',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function destroy(Request $request): JsonResponse
    {
        // Get authenticated user using Laravel standard method
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $profile = $this->profileService->getByUserId($user->id);

        if ($profile === null) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
            ], 404);
        }

        $this->profileService->delete($profile);

        return response()->json([
            'success' => true,
            'message' => 'Profile deleted successfully',
        ]);
    }
}
