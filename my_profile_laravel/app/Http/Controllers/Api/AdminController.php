<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectSalespersonRequest;
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
}
