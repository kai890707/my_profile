<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class EventTrackingController extends Controller
{
    /**
     * Track a contact event.
     *
     * POST /api/events/track
     */
    #[OA\Post(
        path: '/api/events/track',
        summary: '追蹤聯繫事件',
        description: '追蹤用戶與業務員的互動事件（瀏覽檔案、提交表單）。可匿名追蹤。',
        tags: ['事件追蹤'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['event_type', 'salesperson_id'],
                properties: [
                    new OA\Property(property: 'event_type', type: 'string', enum: ['profile_view', 'contact_form_submission'], example: 'profile_view'),
                    new OA\Property(property: 'salesperson_id', type: 'integer', example: 2),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: '事件已記錄',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: '事件已記錄'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 422,
                description: '驗證失敗',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
        ]
    )]
    public function track(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_type' => ['required', 'string', 'in:profile_view,contact_form_submission'],
            'salesperson_id' => ['required', 'integer', 'exists:users,id'],
        ], [
            'event_type.required' => '請提供事件類型',
            'event_type.in' => '事件類型不正確',
            'salesperson_id.required' => '請提供業務員 ID',
            'salesperson_id.exists' => '業務員不存在',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => '驗證失敗',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        // Track event (supports anonymous users)
        ContactEvent::track(
            eventType: $request->input('event_type'),
            salespersonId: $request->input('salesperson_id'),
            userId: auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => '事件已記錄',
        ], 201);
    }
}
