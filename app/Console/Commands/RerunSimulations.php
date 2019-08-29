<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Simulation;

class RerunSimulations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulation:rerun';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reruns simulations with results';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $simulations = Simulation::where('saved', true)->get();
        foreach ($simulations as $simulation) {
            if($simulation->products->pluck('preference_share')->filter()->isNotEmpty()) {
                \App\Http\Controllers\SimulationController::runSimulation($simulation);
            }
        }
    }
}
