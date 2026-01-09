<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class RegionController extends Controller
{
    /**
     * Get all regions (hierarchical structure).
     */
    #[OA\Get(
        path: '/regions',
        summary: '取得所有地區（階層式）',
        description: '取得所有頂層地區（縣市）及其子地區（鄉鎮市區）的階層式結構',
        tags: ['參考數據'],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功返回地區列表',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'regions',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'name', type: 'string', example: '台北市'),
                                            new OA\Property(property: 'parent_id', type: 'integer', nullable: true, example: null),
                                            new OA\Property(
                                                property: 'children',
                                                type: 'array',
                                                items: new OA\Items(ref: '#/components/schemas/Region')
                                            ),
                                        ],
                                        type: 'object'
                                    )
                                ),
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
        // Get top-level regions (counties/cities)
        $regions = Region::with('children')
            ->whereNull('parent_id')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'regions' => $regions,
            ],
        ]);
    }

    /**
     * Get all regions as flat list.
     */
    #[OA\Get(
        path: '/regions/flat',
        summary: '取得所有地區（平面式）',
        description: '取得所有地區的平面列表，不含階層結構',
        tags: ['參考數據'],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功返回地區列表',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'regions', type: 'array', items: new OA\Items(ref: '#/components/schemas/Region')),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function flat(): JsonResponse
    {
        $regions = Region::orderBy('parent_id', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'regions' => $regions,
            ],
        ]);
    }

    /**
     * Get a single region by ID with children.
     */
    #[OA\Get(
        path: '/regions/{id}',
        summary: '取得單一地區',
        description: '根據 ID 取得地區詳情及其子地區',
        tags: ['參考數據'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: '地區 ID',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功返回地區資訊',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'region',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'name', type: 'string', example: '台北市'),
                                        new OA\Property(property: 'parent_id', type: 'integer', nullable: true, example: null),
                                        new OA\Property(property: 'children', type: 'array', items: new OA\Items(ref: '#/components/schemas/Region')),
                                    ],
                                    type: 'object'
                                ),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: '地區不存在',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $region = Region::with('children')->find($id);

        if ($region === null) {
            return response()->json([
                'success' => false,
                'message' => 'Region not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'region' => $region,
            ],
        ]);
    }

    /**
     * Get children of a specific region.
     */
    #[OA\Get(
        path: '/regions/{id}/children',
        summary: '取得地區的子地區',
        description: '取得指定地區的所有子地區（例如：取得台北市的所有區）',
        tags: ['參考數據'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: '父地區 ID',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '成功返回子地區列表',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'parent', ref: '#/components/schemas/Region'),
                                new OA\Property(property: 'children', type: 'array', items: new OA\Items(ref: '#/components/schemas/Region')),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: '父地區不存在',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function children(int $id): JsonResponse
    {
        $region = Region::find($id);

        if ($region === null) {
            return response()->json([
                'success' => false,
                'message' => 'Region not found',
            ], 404);
        }

        $children = Region::where('parent_id', $id)
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'parent' => $region,
                'children' => $children,
            ],
        ]);
    }
}
