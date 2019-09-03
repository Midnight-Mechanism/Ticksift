<?php

namespace App\Providers;

use App\Models\Portfolio;
use App\Models\Simulation;
use App\Policies\PortfolioPolicy;
use App\Policies\SimulationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Portfolio::class => PortfolioPolicy::class,
        Simulation::class => SimulationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
