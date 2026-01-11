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

class ExperienceController extends Controller
{
    /**
     * Get all experiences for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

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
    public function store(StoreExperienceRequest $request): JsonResponse
    {
        $user = $request->user();

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
    public function update(UpdateExperienceRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

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
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

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
