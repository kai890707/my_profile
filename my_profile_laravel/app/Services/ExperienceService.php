<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Experience;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ExperienceService
{
    /**
     * Get all experiences for a user.
     *
     * @return Collection<int, Experience>
     */
    public function getByUserId(int $userId): Collection
    {
        return Experience::where('user_id', $userId)
            ->orderBy('sort_order', 'asc')
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Get experience by ID.
     */
    public function getById(int $id): ?Experience
    {
        return Experience::with(['user', 'approver'])->find($id);
    }

    /**
     * Create new experience.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): Experience
    {
        return Experience::create([
            'user_id' => $user->id,
            'company' => $data['company'],
            'position' => $data['position'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'description' => $data['description'] ?? null,
            'approval_status' => 'pending',
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    /**
     * Update experience.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Experience $experience, array $data): Experience
    {
        $updateData = [];

        if (isset($data['company'])) {
            $updateData['company'] = $data['company'];
        }

        if (isset($data['position'])) {
            $updateData['position'] = $data['position'];
        }

        if (isset($data['start_date'])) {
            $updateData['start_date'] = $data['start_date'];
        }

        if (isset($data['end_date'])) {
            $updateData['end_date'] = $data['end_date'];
        }

        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }

        if (isset($data['sort_order'])) {
            $updateData['sort_order'] = $data['sort_order'];
        }

        // Reset approval if changed
        if (! empty($updateData) && ! isset($data['sort_order'])) {
            $updateData['approval_status'] = 'pending';
            $updateData['rejected_reason'] = null;
            $updateData['approved_by'] = null;
            $updateData['approved_at'] = null;
        }

        $experience->update($updateData);
        $experience->refresh();

        return $experience;
    }

    /**
     * Delete experience.
     */
    public function delete(Experience $experience): bool
    {
        return (bool) $experience->delete();
    }

    /**
     * Approve experience.
     */
    public function approve(Experience $experience, User $admin): Experience
    {
        $experience->update([
            'approval_status' => 'approved',
            'rejected_reason' => null,
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        $experience->refresh();

        return $experience;
    }

    /**
     * Reject experience.
     */
    public function reject(Experience $experience, User $admin, string $reason): Experience
    {
        $experience->update([
            'approval_status' => 'rejected',
            'rejected_reason' => $reason,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        $experience->refresh();

        return $experience;
    }
}
