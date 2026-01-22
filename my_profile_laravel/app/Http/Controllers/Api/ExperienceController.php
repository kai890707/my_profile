<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExperienceRequest;
use App\Http\Requests\UpdateExperienceRequest;
use App\Http\Resources\ExperienceResource;
use App\Models\Experience;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ExperienceController extends Controller
{
    /**
     * Get all experiences for the authenticated user
     */
    #[OA\Get(
        path: '/api/salesperson/experiences',
        summary: '取得工作經驗列表',
        description: '取得當前業務員的所有工作經驗，支援排序',
        security: [['bearerAuth' => []]],
        tags: ['業務員功能 - 工作經驗'],
        parameters: [
            new OA\Parameter(
                name: 'sort_by',
                in: 'query',
                description: '排序欄位 (start_date, company, position, sort_order)',
                required: false,
                schema: new OA\Schema(type: 'string', default: 'start_date', enum: ['start_date', 'company', 'position', 'sort_order'])
            ),
            new OA\Parameter(
                name: 'order',
                in: 'query',
                description: '排序方向',
                required: false,
                schema: new OA\Schema(type: 'string', default: 'desc', enum: ['asc', 'desc'])
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功取得經驗列表',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Experience')
                        ),
                        new OA\Property(property: 'message', type: 'string', example: 'Experiences retrieved successfully'),
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
    public function index(Request $request): JsonResponse
    {
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
                    'message' => 'Only salespeople can access experiences',
                ],
            ], 403);
        }

        // Get query parameters
        $sortBy = $request->query('sort_by', 'start_date');
        $order = $request->query('order', 'desc');

        // Validate sort_by parameter
        $allowedSortFields = ['start_date', 'company', 'position', 'sort_order'];
        if (! in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'start_date';
        }

        // Validate order parameter
        $order = in_array(strtolower($order), ['asc', 'desc']) ? strtolower($order) : 'desc';

        // Query experiences
        $experiences = $user->experiences()
            ->orderBy('sort_order', 'asc')
            ->orderBy($sortBy, $order)
            ->get();

        return response()->json([
            'success' => true,
            'data' => ExperienceResource::collection($experiences),
            'message' => 'Experiences retrieved successfully',
        ], 200);
    }

    /**
     * Store a new experience
     */
    #[OA\Post(
        path: '/api/salesperson/experiences',
        summary: '新增工作經驗',
        description: '新增一筆工作經驗記錄，自動審核通過',
        security: [['bearerAuth' => []]],
        tags: ['業務員功能 - 工作經驗'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['company', 'position', 'start_date'],
                properties: [
                    new OA\Property(property: 'company', type: 'string', maxLength: 255, example: '台積電'),
                    new OA\Property(property: 'position', type: 'string', maxLength: 255, example: '業務經理'),
                    new OA\Property(property: 'start_date', type: 'string', format: 'date', example: '2020-01-01'),
                    new OA\Property(property: 'end_date', type: 'string', format: 'date', nullable: true, example: '2023-12-31'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: '負責企業客戶開發'),
                    new OA\Property(property: 'sort_order', type: 'integer', nullable: true, example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: '成功建立經驗',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Experience'),
                        new OA\Property(property: 'message', type: 'string', example: 'Experience created successfully'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: '未認證', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 403, description: '權限不足', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: '驗證失敗', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function store(StoreExperienceRequest $request): JsonResponse
    {
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
                    'message' => 'Only salespeople can create experiences',
                ],
            ], 403);
        }

        // Create experience with validated data
        $data = $request->validated();
        $data['user_id'] = $user->id;
        $data['approval_status'] = 'approved'; // Auto-approved as per requirements

        $experience = Experience::create($data);

        return response()->json([
            'success' => true,
            'data' => new ExperienceResource($experience),
            'message' => 'Experience created successfully',
        ], 201);
    }

    /**
     * Update an existing experience
     */
    #[OA\Put(
        path: '/api/salesperson/experiences/{id}',
        summary: '更新工作經驗',
        description: '更新指定的工作經驗記錄 (僅擁有者可更新)',
        security: [['bearerAuth' => []]],
        tags: ['業務員功能 - 工作經驗'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: '經驗 ID',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['company', 'position', 'start_date'],
                properties: [
                    new OA\Property(property: 'company', type: 'string', maxLength: 255, example: '台積電'),
                    new OA\Property(property: 'position', type: 'string', maxLength: 255, example: '資深業務經理'),
                    new OA\Property(property: 'start_date', type: 'string', format: 'date', example: '2020-01-01'),
                    new OA\Property(property: 'end_date', type: 'string', format: 'date', nullable: true, example: '2023-12-31'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: '負責企業客戶開發與維護'),
                    new OA\Property(property: 'sort_order', type: 'integer', nullable: true, example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: '成功更新',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Experience'),
                        new OA\Property(property: 'message', type: 'string', example: 'Experience updated successfully'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: '未認證', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 403, description: '無權限 (非擁有者)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 404, description: '經驗不存在', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: '驗證失敗', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function update(UpdateExperienceRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Authentication required'],
            ], 401);
        }

        // Find experience
        $experience = Experience::find($id);

        if (! $experience) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Experience not found',
                ],
            ], 404);
        }

        // Check ownership (BR-EXP-001)
        if ($experience->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'You can only update your own experiences',
                ],
            ], 403);
        }

        // Update experience
        $experience->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => new ExperienceResource($experience),
            'message' => 'Experience updated successfully',
        ], 200);
    }

    /**
     * Delete an experience
     */
    #[OA\Delete(
        path: '/api/salesperson/experiences/{id}',
        summary: '刪除工作經驗',
        description: '刪除指定的工作經驗記錄 (僅擁有者可刪除)',
        security: [['bearerAuth' => []]],
        tags: ['業務員功能 - 工作經驗'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: '經驗 ID',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功刪除',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Experience deleted successfully'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: '未認證', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 403, description: '無權限 (非擁有者)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 404, description: '經驗不存在', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Authentication required'],
            ], 401);
        }

        // Find experience
        $experience = Experience::find($id);

        if (! $experience) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Experience not found',
                ],
            ], 404);
        }

        // Check ownership (BR-EXP-001)
        if ($experience->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'You can only delete your own experiences',
                ],
            ], 403);
        }

        // Delete experience
        $experience->delete();

        return response()->json([
            'success' => true,
            'message' => 'Experience deleted successfully',
        ], 200);
    }
}
