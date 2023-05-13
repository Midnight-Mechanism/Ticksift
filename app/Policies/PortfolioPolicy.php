<?php

namespace App\Policies;

use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PortfolioPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any portfolios.
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the portfolio.
     *
     * @return mixed
     */
    public function view(User $user, Portfolio $portfolio)
    {
        return $portfolio->users()->where('users.id', $user->id)->exists() || $portfolio->users->isEmpty();
    }

    /**
     * Determine whether the user can create portfolios.
     *
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the portfolio.
     *
     * @return mixed
     */
    public function update(User $user, Portfolio $portfolio)
    {
        return $portfolio->users()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine whether the user can delete the portfolio.
     *
     * @return mixed
     */
    public function delete(User $user, Portfolio $portfolio)
    {
        return $portfolio->users()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine whether the user can restore the portfolio.
     *
     * @return mixed
     */
    public function restore(User $user, Portfolio $portfolio)
    {
        return $portfolio->users()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine whether the user can permanently delete the portfolio.
     *
     * @return mixed
     */
    public function forceDelete(User $user, Portfolio $portfolio)
    {
        return $portfolio->users()->where('users.id', $user->id)->exists();
    }
}
