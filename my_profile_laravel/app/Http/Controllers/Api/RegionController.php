<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\JsonResponse;

class RegionController extends Controller
{
    /**
     * Get all regions (hierarchical structure).
     */
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
