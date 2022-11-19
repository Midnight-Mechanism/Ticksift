<?php

namespace App\Console\Commands;

use App\Http\Controllers\SecurityController;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

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
        $max_date = \App\Models\Price::max('date');
        $min_date = \App\Models\Price::sourceTableFilter('SEP')->min('date');
        Cache::put('min-sep-date', $min_date);
        $date_ranges = [
            [Carbon::parse($max_date)->subWeeks(1)->toDateString(), $max_date],
            [Carbon::parse($max_date)->subMonths(1)->toDateString(), $max_date],
            [Carbon::parse($max_date)->firstOfYear()->toDateString(), $max_date],
            [Carbon::parse($max_date)->subYears(1)->toDateString(), $max_date],
            [$min_date, $max_date],
        ];

        foreach ($date_ranges as [$start_date, $end_date]) {
            SecurityController::calculateMomentum($start_date, $end_date, $use_cached = false);
            \Log::info('Momentum cached for '.$start_date.' to '.$end_date);
        }
    }
}
