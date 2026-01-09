<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CertificationService;
use App\Services\CompanyService;
use App\Services\SalespersonProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class AdminController extends Controller
{
    public function __construct(
        private readonly CompanyService $companyService,
        private readonly SalespersonProfileService $profileService
    ) {
    }

    /**
     * Get all pending approvals.
     */
    #[OA\Get(
        path: '/admin/pending-approvals',
        summary: '取得所有待審核項目',
        description: '取得所有待審核的公司和業務員檔案列表',
        security: [['bearerAuth' => []]],
        tags: ['管理員'],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功返回待審核列表',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'companies', type: 'array', items: new OA\Items(ref: '#/components/schemas/Company')),
                                new OA\Property(property: 'profiles', type: 'array', items: new OA\Items(ref: '#/components/schemas/SalespersonProfile')),
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
                description: '權限不足（需要管理員權限）',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function pendingApprovals(Request $request): JsonResponse
    {
        $companies = $this->companyService->getPendingApprovals();
        $profiles = $this->profileService->getPendingApprovals();

        return response()->json([
            'success' => true,
            'data' => [
                'companies' => $companies,
                'profiles' => $profiles,
            ],
        ]);
    }

    /**
     * Approve company.
     */
    #[OA\Post(
        path: '/admin/companies/{id}/approve',
        summary: '審核通過公司',
        description: '將公司狀態設為已審核通過',
        security: [['bearerAuth' => []]],
        tags: ['管理員'],
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
                description: '公司審核通過',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Company approved successfully'),
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
        ]
    )]
    public function approveCompany(Request $request, int $id): JsonResponse
    {
        $admin = $request->get('auth_user');

        if (! $admin instanceof User) {
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

        $company = $this->companyService->approve($company, $admin);

        return response()->json([
            'success' => true,
            'message' => 'Company approved successfully',
            'data' => [
                'company' => $company,
            ],
        ]);
    }

    /**
     * Reject company.
     */
    #[OA\Post(
        path: '/admin/companies/{id}/reject',
        summary: '拒絕公司審核',
        description: '將公司狀態設為已拒絕，需提供拒絕原因',
        security: [['bearerAuth' => []]],
        tags: ['管理員'],
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
                required: ['reason'],
                properties: [
                    new OA\Property(property: 'reason', type: 'string', example: '資料不完整，請重新提交'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: '公司審核已拒絕',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Company rejected successfully'),
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
    public function rejectCompany(Request $request, int $id): JsonResponse
    {
        $admin = $request->get('auth_user');

        if (! $admin instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $company = $this->companyService->getById($id);

        if ($company === null) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found',
            ], 404);
        }

        $company = $this->companyService->reject($company, $admin, $validator->validated()['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Company rejected successfully',
            'data' => [
                'company' => $company,
            ],
        ]);
    }

    /**
     * Approve salesperson profile.
     */
    #[OA\Post(
        path: '/admin/profiles/{id}/approve',
        summary: '審核通過業務員檔案',
        description: '將業務員檔案狀態設為已審核通過',
        security: [['bearerAuth' => []]],
        tags: ['管理員'],
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
                description: '業務員檔案審核通過',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Profile approved successfully'),
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
                response: 403,
                description: '權限不足',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: '業務員檔案不存在',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function approveProfile(Request $request, int $id): JsonResponse
    {
        $admin = $request->get('auth_user');

        if (! $admin instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $profile = $this->profileService->getById($id);

        if ($profile === null) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
            ], 404);
        }

        $profile = $this->profileService->approve($profile, $admin);

        return response()->json([
            'success' => true,
            'message' => 'Profile approved successfully',
            'data' => [
                'profile' => $profile,
            ],
        ]);
    }

    /**
     * Reject salesperson profile.
     */
    #[OA\Post(
        path: '/admin/profiles/{id}/reject',
        summary: '拒絕業務員檔案審核',
        description: '將業務員檔案狀態設為已拒絕，需提供拒絕原因',
        security: [['bearerAuth' => []]],
        tags: ['管理員'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: '業務員檔案 ID',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['reason'],
                properties: [
                    new OA\Property(property: 'reason', type: 'string', example: '照片不清晰，請重新上傳'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: '業務員檔案審核已拒絕',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Profile rejected successfully'),
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
                response: 403,
                description: '權限不足',
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
    public function rejectProfile(Request $request, int $id): JsonResponse
    {
        $admin = $request->get('auth_user');

        if (! $admin instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $profile = $this->profileService->getById($id);

        if ($profile === null) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
            ], 404);
        }

        $profile = $this->profileService->reject($profile, $admin, $validator->validated()['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Profile rejected successfully',
            'data' => [
                'profile' => $profile,
            ],
        ]);
    }
}
