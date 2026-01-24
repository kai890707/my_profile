<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequestRequest;
use App\Http\Resources\ContactRequestResource;
use App\Models\User;
use App\Services\ContactRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

class ContactRequestController extends Controller
{
    public function __construct(
        private readonly ContactRequestService $contactRequestService
    ) {}

    /**
     * Store a new contact request.
     *
     * POST /api/contact-requests
     */
    #[OA\Post(
        path: '/api/contact-requests',
        summary: '提交聯繫請求',
        description: '客戶提交聯繫請求給業務員。需要登入，並受頻率限制（24h 內同業務員僅 1 次，每天最多 5 次）。',
        security: [['bearerAuth' => []]],
        tags: ['聯繫功能'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['salesperson_id', 'message'],
                properties: [
                    new OA\Property(property: 'salesperson_id', type: 'integer', example: 2),
                    new OA\Property(property: 'customer_phone', type: 'string', pattern: '^09\d{8}$', nullable: true, example: '0912345678'),
                    new OA\Property(property: 'message', type: 'string', minLength: 10, maxLength: 500, example: '您好，我想詢問保險相關服務，請問方便聯繫嗎？'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: '聯繫請求已送出',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 123),
                                new OA\Property(property: 'salesperson_id', type: 'integer', example: 2),
                                new OA\Property(property: 'customer_name', type: 'string', example: '王小明'),
                                new OA\Property(property: 'customer_email', type: 'string', example: 'user@example.com'),
                                new OA\Property(property: 'customer_phone', type: 'string', nullable: true, example: '0912-345-678'),
                                new OA\Property(property: 'message', type: 'string'),
                                new OA\Property(property: 'status', type: 'string', example: 'pending'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            ],
                            type: 'object'
                        ),
                        new OA\Property(property: 'message', type: 'string', example: '聯繫請求已送出，業務員將盡快回覆您'),
                    ],
                    type: 'object'
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
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
            new OA\Response(
                response: 429,
                description: '超過頻率限制',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function store(StoreContactRequestRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Authentication required'],
            ], 401);
        }

        try {
            // Create contact request using service
            $contactRequest = $this->contactRequestService->createRequest(
                user: $user,
                salespersonId: $request->input('salesperson_id'),
                data: [
                    'customer_phone' => $request->input('customer_phone'),
                    'message' => $request->input('message'),
                ],
                ipAddress: $request->ip(),
                userAgent: $request->userAgent()
            );

            Log::info('Contact request created', [
                'id' => $contactRequest->id,
                'user_id' => $user->id,
                'salesperson_id' => $contactRequest->salesperson_id,
            ]);

            return response()->json([
                'success' => true,
                'data' => new ContactRequestResource($contactRequest),
                'message' => '聯繫請求已送出，業務員將盡快回覆您',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create contact request', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RATE_LIMIT_EXCEEDED',
                    'message' => $e->getMessage(),
                ],
            ], 422);
        }
    }
}
