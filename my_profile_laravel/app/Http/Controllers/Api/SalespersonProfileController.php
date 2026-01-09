<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SalespersonProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SalespersonProfileController extends Controller
{
    public function __construct(
        private readonly SalespersonProfileService $profileService
    ) {
    }

    /**
     * Get all approved salesperson profiles (public).
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'company_id' => $request->query('company_id'),
            'service_region' => $request->query('service_region'),
            'search' => $request->query('search'),
            'per_page' => $request->query('per_page', 15),
        ];

        $profiles = $this->profileService->getAll($filters);

        return response()->json([
            'success' => true,
            'data' => [
                'profiles' => $profiles,
            ],
        ]);
    }

    /**
     * Get a single salesperson profile by ID (public).
     */
    public function show(int $id): JsonResponse
    {
        $profile = $this->profileService->getById($id);

        if ($profile === null) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'profile' => $profile,
            ],
        ]);
    }

    /**
     * Get authenticated user's salesperson profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->get('auth_user');

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $profile = $this->profileService->getByUserId($user->id);

        if ($profile === null) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'profile' => $profile,
            ],
        ]);
    }

    /**
     * Create salesperson profile for authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->get('auth_user');

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Check if user already has a profile
        $existingProfile = $this->profileService->getByUserId($user->id);
        if ($existingProfile !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Profile already exists',
            ], 409);
        }

        $validator = Validator::make($request->all(), [
            'company_id' => 'nullable|integer|exists:companies,id',
            'full_name' => 'required|string|max:200',
            'phone' => 'required|string|max:20|regex:/^[0-9\-\s\(\)]+$/',
            'bio' => 'nullable|string',
            'specialties' => 'nullable|string',
            'service_regions' => 'nullable|array',
            'avatar' => 'nullable|string',
            'avatar_mime' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $profile = $this->profileService->create($user, $validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profile created successfully',
            'data' => [
                'profile' => $profile,
            ],
        ], 201);
    }

    /**
     * Update authenticated user's salesperson profile.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->get('auth_user');

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $profile = $this->profileService->getByUserId($user->id);

        if ($profile === null) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'company_id' => 'nullable|integer|exists:companies,id',
            'full_name' => 'sometimes|required|string|max:200',
            'phone' => 'sometimes|required|string|max:20|regex:/^[0-9\-\s\(\)]+$/',
            'bio' => 'nullable|string',
            'specialties' => 'nullable|string',
            'service_regions' => 'nullable|array',
            'avatar' => 'nullable|string',
            'avatar_mime' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $profile = $this->profileService->update($profile, $validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'profile' => $profile,
            ],
        ]);
    }

    /**
     * Delete authenticated user's salesperson profile.
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->get('auth_user');

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $profile = $this->profileService->getByUserId($user->id);

        if ($profile === null) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
            ], 404);
        }

        $this->profileService->delete($profile);

        return response()->json([
            'success' => true,
            'message' => 'Profile deleted successfully',
        ]);
    }
}
