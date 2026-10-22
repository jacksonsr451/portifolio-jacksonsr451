<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPortfolioAdmin() ? true : false;
    }

    public function viewAny(User $user): bool
    {
        return $user->isPortfolioAdmin();
    }

    public function view(User $user, Project $project): bool
    {
        return $user->isPortfolioAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isPortfolioAdmin();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isPortfolioAdmin();
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isPortfolioAdmin();
    }
}
