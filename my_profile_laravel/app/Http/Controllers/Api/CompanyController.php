<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    public function __construct(
        private readonly CompanyService $companyService
    ) {
    }

    /**
     * Get all approved companies (public).
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'industry_id' => $request->query('industry_id'),
            'search' => $request->query('search'),
            'per_page' => $request->query('per_page', 15),
        ];

        $companies = $this->companyService->getAll($filters);

        return response()->json([
            'success' => true,
            'data' => [
                'companies' => $companies->items(),
                'pagination' => [
                    'current_page' => $companies->currentPage(),
                    'per_page' => $companies->perPage(),
                    'total' => $companies->total(),
                    'last_page' => $companies->lastPage(),
                ],
            ],
        ]);
    }

    /**
     * Get single company by ID (public).
     */
    public function show(int $id): JsonResponse
    {
        $company = $this->companyService->getById($id);

        if ($company === null) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'company' => $company,
            ],
        ]);
    }

    /**
     * Get companies created by authenticated user.
     */
    public function myCompanies(Request $request): JsonResponse
    {
        $user = $request->get('auth_user');

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $companies = $this->companyService->getByCreator($user);

        return response()->json([
            'success' => true,
            'data' => [
                'companies' => $companies,
            ],
        ]);
    }

    /**
     * Create new company.
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

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:200',
            'tax_id' => 'required|string|max:20|unique:companies,tax_id',
            'industry_id' => 'required|integer|exists:industries,id',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $company = $this->companyService->create($user, $validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Company created successfully',
            'data' => [
                'company' => $company,
            ],
        ], 201);
    }

    /**
     * Update company.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->get('auth_user');

        if (! $user instanceof User) {
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

        // Check if user is the creator
        if ($company->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden - You can only update your own companies',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:200',
            'tax_id' => 'sometimes|required|string|max:20|unique:companies,tax_id,' . $id,
            'industry_id' => 'sometimes|required|integer|exists:industries,id',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $company = $this->companyService->update($company, $validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Company updated successfully',
            'data' => [
                'company' => $company,
            ],
        ]);
    }

    /**
     * Delete company.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->get('auth_user');

        if (! $user instanceof User) {
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

        // Check if user is the creator
        if ($company->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden - You can only delete your own companies',
            ], 403);
        }

        $this->companyService->delete($company);

        return response()->json([
            'success' => true,
            'message' => 'Company deleted successfully',
        ]);
    }
}
