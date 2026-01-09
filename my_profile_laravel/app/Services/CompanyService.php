<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CompanyService
{
    /**
     * Get all approved companies.
     *
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<Company>
     */
    public function getAll(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Company::with(['industry', 'creator'])
            ->where('approval_status', 'approved');

        if (isset($filters['industry_id'])) {
            $query->where('industry_id', $filters['industry_id']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 15;

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get company by ID.
     */
    public function getById(int $id): ?Company
    {
        return Company::with(['industry', 'creator', 'approver', 'approvalLogs'])
            ->find($id);
    }

    /**
     * Get companies created by user.
     *
     * @return Collection<int, Company>
     */
    public function getByCreator(User $user): Collection
    {
        return Company::with(['industry'])
            ->where('created_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create new company.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): Company
    {
        return Company::create([
            'name' => $data['name'],
            'tax_id' => $data['tax_id'],
            'industry_id' => $data['industry_id'],
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'approval_status' => 'pending',
            'created_by' => $user->id,
        ]);
    }

    /**
     * Update company.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Company $company, array $data): Company
    {
        return DB::transaction(function () use ($company, $data): Company {
            $updateData = [];

            if (isset($data['name'])) {
                $updateData['name'] = $data['name'];
            }

            if (isset($data['tax_id'])) {
                $updateData['tax_id'] = $data['tax_id'];
            }

            if (isset($data['industry_id'])) {
                $updateData['industry_id'] = $data['industry_id'];
            }

            if (isset($data['address'])) {
                $updateData['address'] = $data['address'];
            }

            if (isset($data['phone'])) {
                $updateData['phone'] = $data['phone'];
            }

            // Reset approval status if data changed
            if (! empty($updateData)) {
                $updateData['approval_status'] = 'pending';
                $updateData['rejected_reason'] = null;
                $updateData['approved_by'] = null;
                $updateData['approved_at'] = null;
            }

            $company->update($updateData);

            return $company->fresh();
        });
    }

    /**
     * Delete company.
     */
    public function delete(Company $company): bool
    {
        return $company->delete();
    }

    /**
     * Get companies pending approval.
     *
     * @return Collection<int, Company>
     */
    public function getPendingApprovals(): Collection
    {
        return Company::with(['industry', 'creator'])
            ->where('approval_status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Approve company.
     */
    public function approve(Company $company, User $admin): Company
    {
        return DB::transaction(function () use ($company, $admin): Company {
            $company->update([
                'approval_status' => 'approved',
                'rejected_reason' => null,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            $company->approvalLogs()->create([
                'action' => 'approved',
                'admin_id' => $admin->id,
            ]);

            return $company->fresh();
        });
    }

    /**
     * Reject company.
     */
    public function reject(Company $company, User $admin, string $reason): Company
    {
        return DB::transaction(function () use ($company, $admin, $reason): Company {
            $company->update([
                'approval_status' => 'rejected',
                'rejected_reason' => $reason,
                'approved_by' => null,
                'approved_at' => null,
            ]);

            $company->approvalLogs()->create([
                'action' => 'rejected',
                'admin_id' => $admin->id,
                'reason' => $reason,
            ]);

            return $company->fresh();
        });
    }
}
