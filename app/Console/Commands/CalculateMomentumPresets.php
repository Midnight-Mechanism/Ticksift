<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\SecurityController;

class CalculateMomentumPresets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'momentum:calculate-presets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates momentum for preset date ranges';

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
        $today = now()->toDateString();
        $date_ranges = [
            [now()->subWeeks(1)->toDateString(), $today],
            [now()->subMonths(1)->toDateString(), $today],
            [now()->firstOfYear()->toDateString(), $today],
            [now()->subYears(1)->toDateString(), $today],
            [now()->subYears(5)->toDateString(), $today],
            [\App\Models\Price::min('date'), $today],
        ];

        foreach ($date_ranges as [$start_date, $end_date]) {
            SecurityController::calculateMomentum($start_date, $end_date, $use_cached = FALSE);
            \Log::info('Momentum cached for ' . $start_date . ' to ' . $end_date);
        }
    }
}
