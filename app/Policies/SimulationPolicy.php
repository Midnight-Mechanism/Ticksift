<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Simulation;
use Illuminate\Auth\Access\HandlesAuthorization;

class SimulationPolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the simulation.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Simulation  $simulation
     * @return mixed
     */
    public function view(User $user, Simulation $simulation)
    {
        return $simulation->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the simulation.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Simulation  $simulation
     * @return mixed
     */
    public function update(User $user, Simulation $simulation)
    {
        return $simulation->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the simulation.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Simulation  $simulation
     * @return mixed
     */
    public function delete(User $user, Simulation $simulation)
    {
        return $simulation->user_id === $user->id;
    }
}
