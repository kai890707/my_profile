<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class CompanyController extends Controller
{
    public function __construct(
        private readonly CompanyService $companyService
    ) {
    }

    /**
     * Get all approved companies (public).
     */
    #[OA\Get(
        path: '/companies',
        summary: '取得所有公司列表',
        description: '取得所有已審核通過的公司列表，支援產業篩選和關鍵字搜尋',
        tags: ['公司管理'],
        parameters: [
            new OA\Parameter(
                name: 'industry_id',
                in: 'query',
                description: '產業類別 ID',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'search',
                in: 'query',
                description: '搜尋關鍵字（公司名稱）',
                required: false,
                schema: new OA\Schema(type: 'string', example: '科技')
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
                description: '成功返回公司列表',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'companies',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Company')),
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
            'industry_id' => $request->query('industry_id'),
            'search' => $request->query('search'),
            'per_page' => $request->query('per_page', 15),
        ];

        $companies = $this->companyService->getAll($filters);

        return response()->json([
            'success' => true,
            'data' => [
                'companies' => $companies,
            ],
        ]);
    }

    /**
     * Get single company by ID (public).
     */
    #[OA\Get(
        path: '/companies/{id}',
        summary: '取得單一公司詳情',
        description: '根據 ID 取得公司的完整資訊',
        tags: ['公司管理'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: '公司 ID',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功返回公司資訊',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'company', ref: '#/components/schemas/Company'),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: '公司不存在',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $company = $this->companyService->getById($id);

        if ($company === null) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'company' => $company,
            ],
        ]);
    }

    /**
     * Get companies created by authenticated user.
     */
    #[OA\Get(
        path: '/companies/my',
        summary: '取得我的公司列表',
        description: '取得當前用戶建立的所有公司',
        security: [['bearerAuth' => []]],
        tags: ['公司管理'],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功返回公司列表',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'companies', type: 'array', items: new OA\Items(ref: '#/components/schemas/Company')),
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
    public function myCompanies(Request $request): JsonResponse
    {
        $user = $request->get('auth_user');

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $companies = $this->companyService->getByCreator($user);

        return response()->json([
            'success' => true,
            'data' => [
                'companies' => $companies,
            ],
        ]);
    }

    /**
     * Create new company.
     */
    #[OA\Post(
        path: '/companies',
        summary: '建立新公司',
        description: '建立新的公司資料，狀態為 pending 需等待管理員審核',
        security: [['bearerAuth' => []]],
        tags: ['公司管理'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'tax_id', 'industry_id'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 200, example: 'ABC科技股份有限公司'),
                    new OA\Property(property: 'tax_id', type: 'string', maxLength: 20, example: '12345678'),
                    new OA\Property(property: 'industry_id', type: 'integer', example: 1),
                    new OA\Property(property: 'address', type: 'string', nullable: true, example: '台北市信義區信義路五段7號'),
                    new OA\Property(property: 'phone', type: 'string', maxLength: 20, nullable: true, example: '02-12345678'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: '公司建立成功',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Company created successfully'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'company', ref: '#/components/schemas/Company'),
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
                response: 422,
                description: '驗證失敗',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $user = $request->get('auth_user');

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:200',
            'tax_id' => 'required|string|max:20|unique:companies,tax_id',
            'industry_id' => 'required|integer|exists:industries,id',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $company = $this->companyService->create($user, $validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Company created successfully',
            'data' => [
                'company' => $company,
            ],
        ], 201);
    }

    /**
     * Update company.
     */
    #[OA\Put(
        path: '/companies/{id}',
        summary: '更新公司資料',
        description: '更新公司資料，只能更新自己建立的公司',
        security: [['bearerAuth' => []]],
        tags: ['公司管理'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: '公司 ID',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 200, example: 'ABC科技股份有限公司'),
                    new OA\Property(property: 'tax_id', type: 'string', maxLength: 20, example: '12345678'),
                    new OA\Property(property: 'industry_id', type: 'integer', example: 1),
                    new OA\Property(property: 'address', type: 'string', nullable: true, example: '台北市信義區信義路五段7號'),
                    new OA\Property(property: 'phone', type: 'string', maxLength: 20, nullable: true, example: '02-12345678'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: '公司更新成功',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Company updated successfully'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'company', ref: '#/components/schemas/Company'),
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
                response: 403,
                description: '權限不足',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: '公司不存在',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: '驗證失敗',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->get('auth_user');

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $company = $this->companyService->getById($id);

        if ($company === null) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found',
            ], 404);
        }

        // Check if user is the creator
        if ($company->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden - You can only update your own companies',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:200',
            'tax_id' => 'sometimes|required|string|max:20|unique:companies,tax_id,' . $id,
            'industry_id' => 'sometimes|required|integer|exists:industries,id',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $company = $this->companyService->update($company, $validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Company updated successfully',
            'data' => [
                'company' => $company,
            ],
        ]);
    }

    /**
     * Delete company.
     */
    #[OA\Delete(
        path: '/companies/{id}',
        summary: '刪除公司',
        description: '刪除公司資料，只能刪除自己建立的公司',
        security: [['bearerAuth' => []]],
        tags: ['公司管理'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: '公司 ID',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '公司刪除成功',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Company deleted successfully'),
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
                description: '權限不足',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: '公司不存在',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->get('auth_user');

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $company = $this->companyService->getById($id);

        if ($company === null) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found',
            ], 404);
        }

        // Check if user is the creator
        if ($company->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden - You can only delete your own companies',
            ], 403);
        }

        $this->companyService->delete($company);

        return response()->json([
            'success' => true,
            'message' => 'Company deleted successfully',
        ]);
    }
}
