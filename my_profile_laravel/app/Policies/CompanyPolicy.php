<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    /**
     * Determine if user can view any companies.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if user can view the company.
     */
    public function view(User $user, Company $company): bool
    {
        return true;
    }

    /**
     * Determine if user can create companies.
     */
    public function create(User $user): bool
    {
        return $user->isApprovedSalesperson();
    }

    /**
     * Determine if user can update the company.
     */
    public function update(User $user, Company $company): bool
    {
        return $user->isAdmin() || $company->created_by === $user->id;
    }

    /**
     * Determine if user can delete the company.
     */
    public function delete(User $user, Company $company): bool
    {
        return $user->isAdmin() || $company->created_by === $user->id;
    }
}
