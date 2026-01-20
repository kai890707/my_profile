<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectSalespersonRequest;
use App\Models\Certification;
use App\Models\Company;
use App\Models\Experience;
use App\Models\User;
use App\Services\CompanyService;
use App\Services\SalespersonProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class AdminController extends Controller
{
    public function __construct(
        private readonly CompanyService $companyService,
        private readonly SalespersonProfileService $profileService
    ) {}

    /**
     * Get admin dashboard statistics.
     */
    #[OA\Get(
        path: '/admin/statistics',
        summary: '取得管理員統計資訊',
        description: '取得系統統計資訊，包含業務員、公司、待審核項目的數量',
        security: [['bearerAuth' => []]],
        tags: ['管理員'],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功返回統計資訊',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'total_salespeople', type: 'integer', example: 150),
                                new OA\Property(property: 'active_salespeople', type: 'integer', example: 120),
                                new OA\Property(property: 'pending_salespeople', type: 'integer', example: 30),
                                new OA\Property(property: 'total_companies', type: 'integer', example: 80),
                                new OA\Property(property: 'pending_approvals', type: 'integer', example: 15),
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
    public function statistics(): JsonResponse
    {
        $statistics = [
            'total_salespeople' => User::where('role', User::ROLE_SALESPERSON)->count(),
            'active_salespeople' => User::where('role', User::ROLE_SALESPERSON)
                ->where('salesperson_status', User::STATUS_APPROVED)
                ->count(),
            'pending_salespeople' => User::where('role', User::ROLE_SALESPERSON)
                ->where('salesperson_status', User::STATUS_PENDING)
                ->count(),
            'total_companies' => Company::count(),
            'pending_approvals' => $this->companyService->getPendingApprovals()->count() +
                $this->profileService->getPendingApprovals()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
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
     * Get pending salesperson applications.
     *
     * GET /api/admin/salesperson-applications
     */
    #[OA\Get(
        path: '/admin/salesperson-applications',
        summary: '取得待審核業務員申請',
        description: '取得所有待審核的業務員申請列表',
        security: [['bearerAuth' => []]],
        tags: ['管理員'],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功返回待審核列表',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function salespersonApplications(): JsonResponse
    {
        $applications = User::where('role', User::ROLE_SALESPERSON)
            ->where('salesperson_status', User::STATUS_PENDING)
            ->with('salespersonProfile')
            ->orderBy('salesperson_applied_at', 'asc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $applications,
        ]);
    }

    /**
     * Approve salesperson application.
     *
     * POST /api/admin/salesperson-applications/{id}/approve
     */
    #[OA\Post(
        path: '/admin/salesperson-applications/{id}/approve',
        summary: '批准業務員申請',
        description: '將業務員狀態設為已審核通過',
        security: [['bearerAuth' => []]],
        tags: ['管理員'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: '用戶 ID',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '業務員申請已批准',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: '已批准業務員申請'),
                    ]
                )
            ),
        ]
    )]
    public function approveSalesperson(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->salesperson_status !== User::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'error' => '此申請無法審核',
            ], 400);
        }

        $user->approveSalesperson();

        return response()->json([
            'success' => true,
            'user' => $user->load('salespersonProfile'),
            'message' => '已批准業務員申請',
        ]);
    }

    /**
     * Reject salesperson application.
     *
     * POST /api/admin/salesperson-applications/{id}/reject
     */
    #[OA\Post(
        path: '/admin/salesperson-applications/{id}/reject',
        summary: '拒絕業務員申請',
        description: '將業務員狀態設為已拒絕，需提供拒絕原因',
        security: [['bearerAuth' => []]],
        tags: ['管理員'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: '用戶 ID',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/RejectSalespersonRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: '業務員申請已拒絕',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: '已拒絕業務員申請'),
                    ]
                )
            ),
        ]
    )]
    public function rejectSalesperson(RejectSalespersonRequest $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->salesperson_status !== User::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'error' => '此申請無法審核',
            ], 400);
        }

        $user->rejectSalesperson(
            $request->input('rejection_reason'),
            $request->getReapplyDays()
        );

        return response()->json([
            'success' => true,
            'user' => $user,
            'message' => '已拒絕業務員申請',
        ]);
    }

    /**
     * Approve company.
     *
     * POST /api/admin/approve-company/{id}
     */
    #[OA\Post(
        path: '/admin/approve-company/{id}',
        summary: '批准公司',
        description: '將公司審核狀態設為已通過',
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
                description: '公司已批准',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: '公司已批准'),
                        new OA\Property(property: 'company', type: 'object'),
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
    public function approveCompany(int $id): JsonResponse
    {
        $company = Company::findOrFail($id);

        if ($company->approval_status !== 'pending') {
            return response()->json([
                'success' => false,
                'error' => '此公司無法審核',
            ], 400);
        }

        $company->approval_status = 'approved';
        $company->approved_by = auth()->id();
        $company->approved_at = now();
        $company->save();

        return response()->json([
            'success' => true,
            'company' => $company,
            'message' => '公司已批准',
        ]);
    }

    /**
     * Get all regions for settings.
     *
     * GET /api/admin/settings/regions
     */
    #[OA\Get(
        path: '/admin/settings/regions',
        summary: '取得所有地區',
        description: '取得系統中所有可用的地區列表',
        security: [['bearerAuth' => []]],
        tags: ['管理員'],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功返回地區列表',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: '台北市'),
                                    new OA\Property(property: 'parent_id', type: 'integer', nullable: true, example: null),
                                ],
                                type: 'object'
                            )
                        ),
                    ]
                )
            ),
        ]
    )]
    public function getRegions(): JsonResponse
    {
        $regions = DB::table('regions')
            ->select('id', 'name', 'parent_id')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $regions,
        ]);
    }

    /**
     * Get all industries for settings.
     *
     * GET /api/admin/settings/industries
     */
    #[OA\Get(
        path: '/admin/settings/industries',
        summary: '取得所有產業',
        description: '取得系統中所有可用的產業列表',
        security: [['bearerAuth' => []]],
        tags: ['管理員'],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功返回產業列表',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: '科技業'),
                                ],
                                type: 'object'
                            )
                        ),
                    ]
                )
            ),
        ]
    )]
    public function getIndustries(): JsonResponse
    {
        $industries = DB::table('industries')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $industries,
        ]);
    }

    /**
     * Approve experience.
     *
     * POST /api/admin/approve-experience/{id}
     */
    #[OA\Post(
        path: '/admin/approve-experience/{id}',
        summary: '批准工作經驗',
        description: '將工作經驗審核狀態設為已通過',
        security: [['bearerAuth' => []]],
        tags: ['管理員'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: '工作經驗 ID',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '工作經驗已批准',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: '工作經驗已批准'),
                        new OA\Property(property: 'experience', type: 'object'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: '工作經驗不存在',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function approveExperience(int $id): JsonResponse
    {
        $experience = Experience::findOrFail($id);

        if ($experience->approval_status !== 'pending') {
            return response()->json([
                'success' => false,
                'error' => '此工作經驗無法審核',
            ], 400);
        }

        $experience->approval_status = 'approved';
        $experience->approved_by = auth()->id();
        $experience->approved_at = now();
        $experience->save();

        return response()->json([
            'success' => true,
            'experience' => $experience,
            'message' => '工作經驗已批准',
        ]);
    }

    /**
     * Approve certification.
     *
     * POST /api/admin/approve-certification/{id}
     */
    #[OA\Post(
        path: '/admin/approve-certification/{id}',
        summary: '批准證照',
        description: '將證照審核狀態設為已通過',
        security: [['bearerAuth' => []]],
        tags: ['管理員'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: '證照 ID',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '證照已批准',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: '證照已批准'),
                        new OA\Property(property: 'certification', type: 'object'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: '證照不存在',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function approveCertification(int $id): JsonResponse
    {
        $certification = Certification::findOrFail($id);

        if ($certification->approval_status !== 'pending') {
            return response()->json([
                'success' => false,
                'error' => '此證照無法審核',
            ], 400);
        }

        $certification->approval_status = 'approved';
        $certification->approved_by = auth()->id();
        $certification->approved_at = now();
        $certification->save();

        return response()->json([
            'success' => true,
            'certification' => $certification,
            'message' => '證照已批准',
        ]);
    }
}
