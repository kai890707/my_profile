<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCertificationRequest;
use App\Http\Resources\CertificationResource;
use App\Models\Certification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CertificationController extends Controller
{
    /**
     * Get all certifications for the authenticated user
     */
    #[OA\Get(
        path: '/api/salesperson/certifications',
        summary: '取得證照列表',
        description: '取得當前業務員的所有證照，按建立時間倒序排列',
        security: [['bearerAuth' => []]],
        tags: ['業務員功能 - 證照管理'],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功取得證照列表',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Certification')
                        ),
                        new OA\Property(property: 'message', type: 'string', example: 'Certifications retrieved successfully'),
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
                    'message' => 'Only salespeople can access certifications',
                ],
            ], 403);
        }

        // Query certifications
        $certifications = $user->certifications()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => CertificationResource::collection($certifications),
            'message' => 'Certifications retrieved successfully',
        ], 200);
    }

    /**
     * Store a new certification with Base64 file
     */
    #[OA\Post(
        path: '/api/salesperson/certifications',
        summary: '新增證照',
        description: '新增一筆證照記錄，支援 Base64 檔案上傳 (最大 16MB)，需等待審核',
        security: [['bearerAuth' => []]],
        tags: ['業務員功能 - 證照管理'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'issuer'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'PMP 專案管理師'),
                    new OA\Property(property: 'issuer', type: 'string', maxLength: 255, example: 'PMI'),
                    new OA\Property(property: 'issue_date', type: 'string', format: 'date', nullable: true, example: '2023-01-15'),
                    new OA\Property(property: 'expiry_date', type: 'string', format: 'date', nullable: true, example: '2026-01-15'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: '國際專案管理專業證照'),
                    new OA\Property(
                        property: 'file',
                        type: 'string',
                        format: 'byte',
                        nullable: true,
                        description: 'Base64 編碼的檔案內容 (可含 data URI prefix)，最大 16MB',
                        example: 'data:application/pdf;base64,JVBERi0xLjQKJeLjz9MKMy...'
                    ),
                    new OA\Property(
                        property: 'file_mime',
                        type: 'string',
                        nullable: true,
                        description: '檔案 MIME 類型',
                        example: 'application/pdf'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: '成功建立證照',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Certification'),
                        new OA\Property(property: 'message', type: 'string', example: 'Certification created successfully. Pending approval.'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: '未認證', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 403, description: '權限不足', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(
                response: 422,
                description: '驗證失敗 (含檔案過大或 Base64 格式錯誤)',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
            ),
        ]
    )]
    public function store(StoreCertificationRequest $request): JsonResponse
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
                    'message' => 'Only salespeople can create certifications',
                ],
            ], 403);
        }

        // Get validated data
        $data = $request->validated();

        // Decode Base64 file
        $fileData = null;
        $fileSize = null;

        if (isset($data['file']) && ! empty($data['file'])) {
            // Remove data URL prefix if present (e.g., "data:image/png;base64,")
            $base64String = $data['file'];
            if (preg_match('/^data:([^;]+);base64,(.+)$/', $base64String, $matches)) {
                $base64String = $matches[2];
            }

            // Decode Base64
            $fileData = base64_decode($base64String, true);

            if ($fileData === false) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_FILE',
                        'message' => 'Invalid Base64 file data',
                    ],
                ], 422);
            }

            $fileSize = strlen($fileData);

            // Check file size (16MB = 16 * 1024 * 1024 bytes)
            $maxSize = 16 * 1024 * 1024;
            if ($fileSize > $maxSize) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'FILE_TOO_LARGE',
                        'message' => 'File size exceeds 16MB limit',
                    ],
                ], 422);
            }
        }

        // Create certification
        $certification = Certification::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'issuer' => $data['issuer'],
            'issue_date' => $data['issue_date'] ?? null,
            'expiry_date' => $data['expiry_date'] ?? null,
            'description' => $data['description'] ?? null,
            'file_mime' => $data['file_mime'] ?? null,
            'file_size' => $fileSize,
            'approval_status' => 'pending', // Requires approval
        ]);

        // Store file_data separately using raw query (because it's not in $fillable)
        if ($fileData !== null) {
            \DB::table('certifications')
                ->where('id', $certification->id)
                ->update(['file_data' => $fileData]);
        }

        // Reload certification to get updated data
        $certification->refresh();

        return response()->json([
            'success' => true,
            'data' => new CertificationResource($certification),
            'message' => 'Certification created successfully. Pending approval.',
        ], 201);
    }

    /**
     * Delete a certification
     */
    #[OA\Delete(
        path: '/api/salesperson/certifications/{id}',
        summary: '刪除證照',
        description: '刪除指定的證照記錄 (僅擁有者可刪除)',
        security: [['bearerAuth' => []]],
        tags: ['業務員功能 - 證照管理'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: '證照 ID',
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
                        new OA\Property(property: 'message', type: 'string', example: 'Certification deleted successfully'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: '未認證', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 403, description: '無權限 (非擁有者)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 404, description: '證照不存在', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
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

        // Find certification
        $certification = Certification::find($id);

        if (! $certification) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Certification not found',
                ],
            ], 404);
        }

        // Check ownership (BR-CERT-001)
        if ($certification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'You can only delete your own certifications',
                ],
            ], 403);
        }

        // Delete certification
        $certification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Certification deleted successfully',
        ], 200);
    }
}
