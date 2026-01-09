<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Industry;
use Illuminate\Http\JsonResponse;

class IndustryController extends Controller
{
    /**
     * Get all industries.
     */
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
