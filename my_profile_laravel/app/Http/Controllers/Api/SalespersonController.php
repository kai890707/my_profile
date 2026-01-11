<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSalespersonProfileRequest;
use App\Http\Requests\UpgradeSalespersonRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SalespersonController extends Controller
{
    /**
     * Upgrade current user to salesperson.
     *
     * POST /api/salesperson/upgrade
     */
    public function upgrade(UpgradeSalespersonRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Check if already salesperson
        if ($user->isSalesperson()) {
            return response()->json([
                'success' => false,
                'error' => '您已經是業務員',
            ], 400);
        }

        // Check if rejected and can reapply
        if ($user->salesperson_status === User::STATUS_REJECTED && ! $user->canReapply()) {
            return response()->json([
                'success' => false,
                'error' => '請於 '.$user->can_reapply_at?->format('Y-m-d').' 後重新申請',
                'can_reapply_at' => $user->can_reapply_at,
            ], 429);
        }

        DB::beginTransaction();

        try {
            $user->upgradeToSalesperson([
                'full_name' => $request->input('full_name'),
                'phone' => $request->input('phone'),
                'bio' => $request->input('bio'),
                'specialties' => $request->input('specialties'),
                'service_regions' => $request->input('service_regions'),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'user' => $user->fresh()->load('salespersonProfile'),
                'message' => '升級成功！您的業務員資料正在審核中，預計 1-3 個工作天完成。',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => '升級失敗：'.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get salesperson status.
     *
     * GET /api/salesperson/status
     */
    public function status(): JsonResponse
    {
        /** @var User|null $user */
        $user = auth()->user();

        // If user is not logged in or has never applied to be a salesperson
        if (! $user || $user->salesperson_status === null) {
            return response()->json([
                'success' => true,
                'data' => [
                    'role' => $user?->role ?? 'user',
                    'salesperson_status' => null,
                    'salesperson_applied_at' => null,
                    'salesperson_approved_at' => null,
                    'rejection_reason' => null,
                    'can_reapply' => false,
                    'can_reapply_at' => null,
                    'days_until_reapply' => null,
                ],
            ]);
        }

        // Calculate days until reapply
        $daysUntilReapply = null;
        if ($user->can_reapply_at) {
            $diff = now()->diffInDays($user->can_reapply_at, false);
            // Use ceil to round up partial days, or 0 if past date
            $daysUntilReapply = $diff < 0 ? 0 : (int) ceil($diff);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'role' => $user->role,
                'salesperson_status' => $user->salesperson_status,
                'salesperson_applied_at' => $user->salesperson_applied_at,
                'salesperson_approved_at' => $user->salesperson_approved_at,
                'rejection_reason' => $user->rejection_reason,
                'can_reapply' => $user->canReapply(),
                'can_reapply_at' => $user->can_reapply_at,
                'days_until_reapply' => $daysUntilReapply,
            ],
        ]);
    }

    /**
     * Get approval status for all resources.
     *
     * GET /api/salesperson/approval-status
     */
    public function approvalStatus(\Illuminate\Http\Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Check if user is a salesperson
        if (! $user->isSalesperson()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Only salespeople can access approval status',
                ],
            ], 403);
        }

        // Eager load relationships to avoid N+1 queries
        $user->load([
            'salespersonProfile',
            'salespersonProfile.company',
            'certifications',
            'experiences',
        ]);

        // Get profile status
        $profileStatus = $user->salespersonProfile?->approval_status ?? 'pending';

        // Get company status
        $companyStatus = $user->salespersonProfile?->company?->approval_status ?? null;

        // Get certifications with approval status
        $certifications = $user->certifications->map(function ($cert) {
            return [
                'id' => $cert->id,
                'name' => $cert->name,
                'approval_status' => $cert->approval_status,
                'rejected_reason' => $cert->rejected_reason,
            ];
        });

        // Get experiences with approval status
        $experiences = $user->experiences->map(function ($exp) {
            return [
                'id' => $exp->id,
                'company' => $exp->company,
                'position' => $exp->position,
                'approval_status' => $exp->approval_status,
                'rejected_reason' => $exp->rejected_reason,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'profile_status' => $profileStatus,
                'company_status' => $companyStatus,
                'certifications' => $certifications,
                'experiences' => $experiences,
            ],
            'message' => 'Approval status retrieved successfully',
        ]);
    }

    /**
     * Update salesperson profile.
     *
     * PUT /api/salesperson/profile
     */
    public function updateProfile(UpdateSalespersonProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->isSalesperson()) {
            return response()->json([
                'success' => false,
                'error' => '僅業務員可更新個人資料',
            ], 403);
        }

        $user->salespersonProfile()->update($request->validated());

        return response()->json([
            'success' => true,
            'profile' => $user->salespersonProfile,
            'message' => '個人資料已更新',
        ]);
    }

    /**
     * Search approved salespeople.
     *
     * GET /api/salespeople
     */
    public function index(): JsonResponse
    {
        $salespeople = User::where('role', User::ROLE_SALESPERSON)
            ->where('salesperson_status', User::STATUS_APPROVED)
            ->with('salespersonProfile.company')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $salespeople,
        ]);
    }
}
