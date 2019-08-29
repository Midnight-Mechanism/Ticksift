<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\Attribute;
use App\Models\Simulation;
use App\Policies\ProjectPolicy;
use App\Policies\AttributePolicy;
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
        Project::class => ProjectPolicy::class,
        Attribute::class => AttributePolicy::class,
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
