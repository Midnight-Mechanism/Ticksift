<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MathPHP\Statistics\Correlation;
use Carbon\CarbonPeriod;
use App\Models\Security;

class CalculateCorrelations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'correlations:calculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate correlations';

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
        $securities = Security::all();
        $correlations = collect([]);
        foreach ($securities as $security) {
            foreach ($securities as $compared_security) {
                $prices = $security->prices->pluck('close', 'date');
                $compared_prices = $compared_security->prices->pluck('close', 'date');
                $overlap_dates = $prices->intersectByKeys($compared_prices)->keys();

                if ($overlap_dates->isEmpty()) {
                    continue;
                }

                $prices = $prices->filter(function ($value, $key) use ($overlap_dates) {
                    return $overlap_dates->contains($key);
                });
                $compared_prices = $compared_prices->filter(function ($value, $key) use ($overlap_dates) {
                    return $overlap_dates->contains($key);
                });

                $r = Correlation::r(
                    $prices->all(),
                    $compared_prices->all()
                );

                \Log::info($security->name . ' to ' . $compared_security->name);
                \Log::info($r);
            }
        }
    }
}
