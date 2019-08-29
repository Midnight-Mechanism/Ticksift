<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Simulation;
use Carbon\Carbon;

class DeleteUnsavedSimulations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulation:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete unsaved simulations older than a week';

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
        Simulation::where('saved', false)->where('updated_at', '<=', Carbon::now()->subWeeks(1))->delete();
    }
}
