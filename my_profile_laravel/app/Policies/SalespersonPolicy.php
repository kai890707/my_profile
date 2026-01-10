<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class SalespersonPolicy
{
    /**
     * Determine if user can view salesperson dashboard.
     */
    public function viewDashboard(User $user): bool
    {
        return $user->isSalesperson() || $user->isAdmin();
    }

    /**
     * Determine if user can create companies.
     */
    public function createCompany(User $user): bool
    {
        return $user->isApprovedSalesperson();
    }

    /**
     * Determine if user can create ratings.
     */
    public function createRating(User $user): bool
    {
        return $user->isApprovedSalesperson();
    }

    /**
     * Determine if user can be searched.
     */
    public function canBeSearched(User $user): bool
    {
        return $user->isApprovedSalesperson();
    }
}
