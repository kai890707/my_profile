<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CertificationService;
use App\Services\CompanyService;
use App\Services\SalespersonProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function __construct(
        private readonly CompanyService $companyService,
        private readonly SalespersonProfileService $profileService
    ) {
    }

    /**
     * Get all pending approvals.
     */
    public function pendingApprovals(Request $request): JsonResponse
    {
        $companies = $this->companyService->getPendingApprovals();
        $profiles = $this->profileService->getPendingApprovals();

        return response()->json([
            'success' => true,
            'data' => [
                'companies' => $companies,
                'profiles' => $profiles,
            ],
        ]);
    }

    /**
     * Approve company.
     */
    public function approveCompany(Request $request, int $id): JsonResponse
    {
        $admin = $request->get('auth_user');

        if (! $admin instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $company = $this->companyService->getById($id);

        if ($company === null) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found',
            ], 404);
        }

        $company = $this->companyService->approve($company, $admin);

        return response()->json([
            'success' => true,
            'message' => 'Company approved successfully',
            'data' => [
                'company' => $company,
            ],
        ]);
    }

    /**
     * Reject company.
     */
    public function rejectCompany(Request $request, int $id): JsonResponse
    {
        $admin = $request->get('auth_user');

        if (! $admin instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $company = $this->companyService->getById($id);

        if ($company === null) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found',
            ], 404);
        }

        $company = $this->companyService->reject($company, $admin, $validator->validated()['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Company rejected successfully',
            'data' => [
                'company' => $company,
            ],
        ]);
    }

    /**
     * Approve salesperson profile.
     */
    public function approveProfile(Request $request, int $id): JsonResponse
    {
        $admin = $request->get('auth_user');

        if (! $admin instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $profile = $this->profileService->getById($id);

        if ($profile === null) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
            ], 404);
        }

        $profile = $this->profileService->approve($profile, $admin);

        return response()->json([
            'success' => true,
            'message' => 'Profile approved successfully',
            'data' => [
                'profile' => $profile,
            ],
        ]);
    }

    /**
     * Reject salesperson profile.
     */
    public function rejectProfile(Request $request, int $id): JsonResponse
    {
        $admin = $request->get('auth_user');

        if (! $admin instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $profile = $this->profileService->getById($id);

        if ($profile === null) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
            ], 404);
        }

        $profile = $this->profileService->reject($profile, $admin, $validator->validated()['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Profile rejected successfully',
            'data' => [
                'profile' => $profile,
            ],
        ]);
    }
}
