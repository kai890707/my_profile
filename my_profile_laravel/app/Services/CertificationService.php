<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Certification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CertificationService
{
    /**
     * Get all certifications for a user.
     *
     * @return Collection<int, Certification>
     */
    public function getByUserId(int $userId): Collection
    {
        return Certification::where('user_id', $userId)
            ->orderBy('issue_date', 'desc')
            ->get();
    }

    /**
     * Get certification by ID.
     */
    public function getById(int $id): ?Certification
    {
        return Certification::with(['user', 'approver', 'approvalLogs'])->find($id);
    }

    /**
     * Create new certification.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): Certification
    {
        $certData = [
            'user_id' => $user->id,
            'name' => $data['name'],
            'issuer' => $data['issuer'],
            'issue_date' => $data['issue_date'],
            'expiry_date' => $data['expiry_date'] ?? null,
            'description' => $data['description'] ?? null,
            'approval_status' => 'pending',
        ];

        if (isset($data['file']) && is_string($data['file'])) {
            $fileData = base64_decode($data['file'], true);
            if ($fileData !== false) {
                $certData['file_data'] = $fileData;
                $certData['file_mime'] = $data['file_mime'] ?? 'application/pdf';
                $certData['file_size'] = strlen($fileData);
            }
        }

        return Certification::create($certData);
    }

    /**
     * Update certification.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Certification $certification, array $data): Certification
    {
        return DB::transaction(function () use ($certification, $data): Certification {
            $updateData = [];

            if (isset($data['name'])) {
                $updateData['name'] = $data['name'];
            }

            if (isset($data['issuer'])) {
                $updateData['issuer'] = $data['issuer'];
            }

            if (isset($data['issue_date'])) {
                $updateData['issue_date'] = $data['issue_date'];
            }

            if (isset($data['expiry_date'])) {
                $updateData['expiry_date'] = $data['expiry_date'];
            }

            if (isset($data['description'])) {
                $updateData['description'] = $data['description'];
            }

            if (isset($data['file']) && is_string($data['file'])) {
                $fileData = base64_decode($data['file'], true);
                if ($fileData !== false) {
                    $updateData['file_data'] = $fileData;
                    $updateData['file_mime'] = $data['file_mime'] ?? 'application/pdf';
                    $updateData['file_size'] = strlen($fileData);
                }
            }

            // Reset approval if changed
            if (! empty($updateData)) {
                $updateData['approval_status'] = 'pending';
                $updateData['rejected_reason'] = null;
                $updateData['approved_by'] = null;
                $updateData['approved_at'] = null;
            }

            $certification->update($updateData);
            $certification->refresh();

            return $certification;
        });
    }

    /**
     * Delete certification.
     */
    public function delete(Certification $certification): bool
    {
        return (bool) $certification->delete();
    }

    /**
     * Approve certification.
     */
    public function approve(Certification $certification, User $admin): Certification
    {
        return DB::transaction(function () use ($certification, $admin): Certification {
            $certification->update([
                'approval_status' => 'approved',
                'rejected_reason' => null,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            $certification->approvalLogs()->create([
                'action' => 'approved',
                'admin_id' => $admin->id,
            ]);

            $certification->refresh();

            return $certification;
        });
    }

    /**
     * Reject certification.
     */
    public function reject(Certification $certification, User $admin, string $reason): Certification
    {
        return DB::transaction(function () use ($certification, $admin, $reason): Certification {
            $certification->update([
                'approval_status' => 'rejected',
                'rejected_reason' => $reason,
                'approved_by' => null,
                'approved_at' => null,
            ]);

            $certification->approvalLogs()->create([
                'action' => 'rejected',
                'admin_id' => $admin->id,
                'reason' => $reason,
            ]);

            $certification->refresh();

            return $certification;
        });
    }
}
