<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function before(User $user): ?bool
    {
        return $user->isPortfolioAdmin() ? true : false;
    }

    public function viewAny(User $user): bool
    {
        return $user->isPortfolioAdmin();
    }

    public function view(User $user, Article $article): bool
    {
        return $user->isPortfolioAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isPortfolioAdmin();
    }

    public function update(User $user, Article $article): bool
    {
        return $user->isPortfolioAdmin();
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->isPortfolioAdmin();
    }
}
