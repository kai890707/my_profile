<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SalespersonProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SalespersonProfileService
{
    /**
     * Get all salesperson profiles with pagination.
     *
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator<SalespersonProfile>
     */
    public function getAll(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = SalespersonProfile::with(['user', 'company'])
            ->where('approval_status', 'approved');

        if (isset($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        if (isset($filters['service_region'])) {
            $query->whereJsonContains('service_regions', $filters['service_region']);
        }

        if (isset($filters['search']) && is_string($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search): void {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('specialties', 'like', "%{$search}%")
                    ->orWhere('bio', 'like', "%{$search}%");
            });
        }

        /** @var int $perPage */
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 15;

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get a single salesperson profile by ID.
     */
    public function getById(int $id): ?SalespersonProfile
    {
        return SalespersonProfile::with(['user', 'company', 'approvalLogs'])
            ->find($id);
    }

    /**
     * Get salesperson profile by user ID.
     */
    public function getByUserId(int $userId): ?SalespersonProfile
    {
        return SalespersonProfile::with(['user', 'company'])
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Create a new salesperson profile.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): SalespersonProfile
    {
        return DB::transaction(function () use ($user, $data): SalespersonProfile {
            $profileData = [
                'user_id' => $user->id,
                'company_id' => $data['company_id'] ?? null,
                'full_name' => $data['full_name'],
                'phone' => $data['phone'],
                'bio' => $data['bio'] ?? null,
                'specialties' => $data['specialties'] ?? null,
                'service_regions' => $data['service_regions'] ?? null,
                'approval_status' => 'pending',
            ];

            if (isset($data['avatar']) && is_string($data['avatar'])) {
                $avatarData = base64_decode($data['avatar'], true);
                if ($avatarData !== false) {
                    $profileData['avatar_data'] = $avatarData;
                    $profileData['avatar_mime'] = $data['avatar_mime'] ?? 'image/jpeg';
                    $profileData['avatar_size'] = strlen($avatarData);
                }
            }

            return SalespersonProfile::create($profileData);
        });
    }

    /**
     * Update salesperson profile.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(SalespersonProfile $profile, array $data): SalespersonProfile
    {
        return DB::transaction(function () use ($profile, $data): SalespersonProfile {
            $updateData = [];

            if (isset($data['company_id'])) {
                $updateData['company_id'] = $data['company_id'];
            }

            if (isset($data['full_name'])) {
                $updateData['full_name'] = $data['full_name'];
            }

            if (isset($data['phone'])) {
                $updateData['phone'] = $data['phone'];
            }

            if (isset($data['bio'])) {
                $updateData['bio'] = $data['bio'];
            }

            if (isset($data['specialties'])) {
                $updateData['specialties'] = $data['specialties'];
            }

            if (isset($data['service_regions'])) {
                $updateData['service_regions'] = $data['service_regions'];
            }

            if (isset($data['avatar']) && is_string($data['avatar'])) {
                $avatarData = base64_decode($data['avatar'], true);
                if ($avatarData !== false) {
                    $updateData['avatar_data'] = $avatarData;
                    $updateData['avatar_mime'] = $data['avatar_mime'] ?? 'image/jpeg';
                    $updateData['avatar_size'] = strlen($avatarData);
                }
            }

            // Reset approval status if profile data changed
            if (! empty($updateData)) {
                $updateData['approval_status'] = 'pending';
                $updateData['rejected_reason'] = null;
                $updateData['approved_by'] = null;
                $updateData['approved_at'] = null;
            }

            $profile->update($updateData);
            $profile->refresh();

            return $profile;
        });
    }

    /**
     * Delete salesperson profile.
     */
    public function delete(SalespersonProfile $profile): bool
    {
        return (bool) $profile->delete();
    }

    /**
     * Get profiles pending approval.
     *
     * @return Collection<int, SalespersonProfile>
     */
    public function getPendingApprovals(): Collection
    {
        return SalespersonProfile::with(['user', 'company'])
            ->where('approval_status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Approve salesperson profile.
     */
    public function approve(SalespersonProfile $profile, User $admin): SalespersonProfile
    {
        return DB::transaction(function () use ($profile, $admin): SalespersonProfile {
            $profile->update([
                'approval_status' => 'approved',
                'rejected_reason' => null,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            $profile->approvalLogs()->create([
                'action' => 'approved',
                'admin_id' => $admin->id,
            ]);

            $profile->refresh();

            return $profile;
        });
    }

    /**
     * Reject salesperson profile.
     */
    public function reject(SalespersonProfile $profile, User $admin, string $reason): SalespersonProfile
    {
        return DB::transaction(function () use ($profile, $admin, $reason): SalespersonProfile {
            $profile->update([
                'approval_status' => 'rejected',
                'rejected_reason' => $reason,
                'approved_by' => null,
                'approved_at' => null,
            ]);

            $profile->approvalLogs()->create([
                'action' => 'rejected',
                'admin_id' => $admin->id,
                'reason' => $reason,
            ]);

            $profile->refresh();

            return $profile;
        });
    }
}
