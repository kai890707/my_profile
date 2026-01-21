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
        description: '取得所有待審核的公司、業務員檔案、工作經驗和證照列表',
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
                                new OA\Property(property: 'experiences', type: 'array', items: new OA\Items(ref: '#/components/schemas/Experience')),
                                new OA\Property(property: 'certifications', type: 'array', items: new OA\Items(ref: '#/components/schemas/Certification')),
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

        // Get pending experiences and certifications
        $experiences = Experience::where('approval_status', 'pending')
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $certifications = Certification::where('approval_status', 'pending')
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'companies' => $companies,
                'profiles' => $profiles,
                'experiences' => $experiences,
                'certifications' => $certifications,
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

        $validated = $request->validated();
        $user->rejectSalesperson(
            (string) $validated['rejection_reason'],
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
        $company->approved_by = (int) auth()->id();
        $company->approved_at = now()->toDateTimeString();
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
            $statusMessage = match ($experience->approval_status) {
                'approved' => '此工作經驗已經被批准',
                'rejected' => '此工作經驗已被拒絕',
                default => '此工作經驗無法審核',
            };

            return response()->json([
                'success' => false,
                'error' => $statusMessage,
                'current_status' => $experience->approval_status,
                'approved_by' => $experience->approved_by,
                'approved_at' => $experience->approved_at,
            ], 400);
        }

        $experience->approval_status = 'approved';
        $experience->approved_by = (int) auth()->id();
        $experience->approved_at = now()->toDateTimeString();
        $experience->save();

        return response()->json([
            'success' => true,
            'experience' => $experience,
            'message' => '工作經驗已批准',
        ]);
    }

    /**
     * Reject experience.
     *
     * POST /api/admin/reject-experience/{id}
     */
    #[OA\Post(
        path: '/admin/reject-experience/{id}',
        summary: 'Reject experience',
        security: [['bearerAuth' => []]],
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'reason', type: 'string', example: '資訊不完整'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Experience rejected successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'experience', ref: '#/components/schemas/Experience'),
                        new OA\Property(property: 'message', type: 'string', example: '工作經驗已拒絕'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid status'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Experience not found'),
        ]
    )]
    public function rejectExperience(int $id, Request $request): JsonResponse
    {
        $experience = Experience::findOrFail($id);

        if ($experience->approval_status !== 'pending') {
            $statusMessage = match ($experience->approval_status) {
                'approved' => '此工作經驗已經被批准，無法拒絕',
                'rejected' => '此工作經驗已被拒絕',
                default => '此工作經驗無法拒絕',
            };

            return response()->json([
                'success' => false,
                'error' => $statusMessage,
                'current_status' => $experience->approval_status,
            ], 400);
        }

        $experience->approval_status = 'rejected';
        $experience->rejected_reason = (string) $request->input('reason');
        $experience->approved_by = (int) auth()->id();
        $experience->approved_at = now()->toDateTimeString();
        $experience->save();

        return response()->json([
            'success' => true,
            'experience' => $experience,
            'message' => '工作經驗已拒絕',
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
            $statusMessage = match ($certification->approval_status) {
                'approved' => '此證照已經被批准',
                'rejected' => '此證照已被拒絕',
                default => '此證照無法審核',
            };

            return response()->json([
                'success' => false,
                'error' => $statusMessage,
                'current_status' => $certification->approval_status,
                'approved_by' => $certification->approved_by,
                'approved_at' => $certification->approved_at,
            ], 400);
        }

        $certification->approval_status = 'approved';
        $certification->approved_by = (int) auth()->id();
        $certification->approved_at = now()->toDateTimeString();
        $certification->save();

        return response()->json([
            'success' => true,
            'certification' => $certification,
            'message' => '證照已批准',
        ]);
    }

    /**
     * Reject certification.
     *
     * POST /api/admin/reject-certification/{id}
     */
    #[OA\Post(
        path: '/admin/reject-certification/{id}',
        summary: 'Reject certification',
        security: [['bearerAuth' => []]],
        tags: ['Admin'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'reason', type: 'string', example: '證照過期'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Certification rejected successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'certification', ref: '#/components/schemas/Certification'),
                        new OA\Property(property: 'message', type: 'string', example: '證照已拒絕'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid status'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Certification not found'),
        ]
    )]
    public function rejectCertification(int $id, Request $request): JsonResponse
    {
        $certification = Certification::findOrFail($id);

        if ($certification->approval_status !== 'pending') {
            $statusMessage = match ($certification->approval_status) {
                'approved' => '此證照已經被批准，無法拒絕',
                'rejected' => '此證照已被拒絕',
                default => '此證照無法拒絕',
            };

            return response()->json([
                'success' => false,
                'error' => $statusMessage,
                'current_status' => $certification->approval_status,
            ], 400);
        }

        $certification->approval_status = 'rejected';
        $certification->rejected_reason = (string) $request->input('reason');
        $certification->approved_by = (int) auth()->id();
        $certification->approved_at = now()->toDateTimeString();
        $certification->save();

        return response()->json([
            'success' => true,
            'certification' => $certification,
            'message' => '證照已拒絕',
        ]);
    }

    /**
     * Get all users with filters
     */
    #[OA\Get(
        path: '/admin/users',
        summary: '取得使用者列表',
        description: '取得所有使用者列表，支援角色和狀態篩選',
        security: [['bearerAuth' => []]],
        tags: ['管理員'],
        parameters: [
            new OA\Parameter(
                name: 'role',
                in: 'query',
                description: '角色篩選',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['admin', 'salesperson', 'user'])
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                description: '狀態篩選',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['active', 'inactive', 'pending'])
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                description: '頁碼',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                description: '每頁筆數',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 15, maximum: 100)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功返回使用者列表',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/User')
                        ),
                        new OA\Property(
                            property: 'meta',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer'),
                                new OA\Property(property: 'per_page', type: 'integer'),
                                new OA\Property(property: 'total', type: 'integer'),
                                new OA\Property(property: 'last_page', type: 'integer'),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function getUsers(Request $request): JsonResponse
    {
        $query = User::query();

        // 角色篩選
        if ($request->has('role')) {
            $query->where('role', $request->input('role'));
        }

        // 狀態篩選
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // 排序：管理員優先，再依建立時間
        $query->orderByRaw("FIELD(role, 'admin', 'salesperson', 'user')")
            ->orderBy('created_at', 'desc');

        // 分頁（預設 15 筆，最大 100 筆）
        $perPage = min((int) $request->input('per_page', 15), 100);
        $users = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ],
        ]);
    }

    /**
     * Update user status
     */
    #[OA\Put(
        path: '/admin/users/{id}/status',
        summary: '更新使用者狀態',
        description: '啟用或停用使用者帳號',
        security: [['bearerAuth' => []]],
        tags: ['管理員'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: '使用者 ID',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['active', 'inactive'],
                        description: '目標狀態'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: '成功更新使用者狀態',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/User'),
                        new OA\Property(property: 'message', type: 'string', example: '使用者狀態已更新'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: '禁止的操作',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string'),
                        new OA\Property(property: 'error_code', type: 'string'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: '使用者不存在',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string', example: '使用者不存在'),
                    ]
                )
            ),
        ]
    )]
    public function updateUserStatus(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => '使用者不存在',
                'error_code' => 'USER_NOT_FOUND',
            ], 404);
        }

        // 驗證
        $validated = $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        // 業務規則檢查：不能停用管理員
        if ($user->role === User::ROLE_ADMIN) {
            return response()->json([
                'success' => false,
                'error' => '無法停用管理員帳號',
                'error_code' => 'FORBIDDEN_ACTION',
            ], 400);
        }

        // 業務規則檢查：不能停用自己
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'error' => '無法停用自己的帳號',
                'error_code' => 'CANNOT_DISABLE_SELF',
            ], 400);
        }

        // 更新狀態
        $user->status = $validated['status'];
        $user->save();

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => '使用者狀態已更新',
        ]);
    }

    /**
     * Delete user
     */
    #[OA\Delete(
        path: '/admin/users/{id}',
        summary: '刪除使用者',
        description: '永久刪除使用者帳號',
        security: [['bearerAuth' => []]],
        tags: ['管理員'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: '使用者 ID',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功刪除使用者',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: '使用者已刪除'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: '禁止的操作',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string'),
                        new OA\Property(property: 'error_code', type: 'string'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: '使用者不存在',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string', example: '使用者不存在'),
                    ]
                )
            ),
        ]
    )]
    public function deleteUser(int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => '使用者不存在',
                'error_code' => 'USER_NOT_FOUND',
            ], 404);
        }

        // 業務規則檢查：不能刪除管理員
        if ($user->role === User::ROLE_ADMIN) {
            return response()->json([
                'success' => false,
                'error' => '無法刪除管理員帳號',
                'error_code' => 'FORBIDDEN_ACTION',
            ], 400);
        }

        // 業務規則檢查：不能刪除自己
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'error' => '無法刪除自己的帳號',
                'error_code' => 'CANNOT_DELETE_SELF',
            ], 400);
        }

        // 刪除使用者（連帶刪除關聯資料）
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => '使用者已刪除',
        ]);
    }
}
