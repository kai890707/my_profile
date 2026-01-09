<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Industry;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class IndustryController extends Controller
{
    /**
     * Get all industries.
     */
    #[OA\Get(
        path: '/industries',
        summary: '取得所有產業類別',
        description: '取得系統中所有的產業類別列表',
        tags: ['參考數據'],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功返回產業列表',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'industries', type: 'array', items: new OA\Items(ref: '#/components/schemas/Industry')),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function index(): JsonResponse
    {
        $industries = Industry::orderBy('name', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'industries' => $industries,
            ],
        ]);
    }

    /**
     * Get a single industry by ID.
     */
    #[OA\Get(
        path: '/industries/{id}',
        summary: '取得單一產業類別',
        description: '根據 ID 取得產業類別詳情',
        tags: ['參考數據'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: '產業類別 ID',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功返回產業資訊',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'industry', ref: '#/components/schemas/Industry'),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: '產業類別不存在',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $industry = Industry::find($id);

        if ($industry === null) {
            return response()->json([
                'success' => false,
                'message' => 'Industry not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'industry' => $industry,
            ],
        ]);
    }
}
