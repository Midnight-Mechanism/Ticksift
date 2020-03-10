<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MathPHP\Statistics\Correlation;
use Carbon\CarbonPeriod;
use App\Models\Security;
use App\Models\Correlation as TicksiftCorrelation;

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
        // Find correlation between all securities
        foreach (Security::cursor() as $security) {
            foreach (Security::cursor() as $compared_security) {
                // Find all dates where both securities have correlations
                $prices = $security->prices->pluck('close', 'date');
                $compared_prices = $compared_security->prices->pluck('close', 'date');
                $overlap_dates = $prices->intersectByKeys($compared_prices)->keys();
                if ($overlap_dates->isEmpty()) {
                    continue;
                }

                // Filter security prices for only dates where both have data
                $prices = $prices->filter(function ($value, $key) use ($overlap_dates) {
                    return $overlap_dates->contains($key);
                });
                $compared_prices = $compared_prices->filter(function ($value, $key) use ($overlap_dates) {
                    return $overlap_dates->contains($key);
                });

                // Calculate correlation coefficient between the two securities' prices
                try{
                    $r = $this->correlationCoefficient($prices->all(), $compared_prices->all());
                } catch(Exception $e) {
                    // Set correlation coefficient to 0 if function returns error (e.g. standard deviation is 0 returning division by 0 error)
                    $r = 0;
                }

                \Log::info($security->name . ' to ' . $compared_security->name);
                \Log::info($r);

                // Insert correlation to database table securities_correlation
                $correlation = [
                    'security_id' => $security->id,
                    'compared_security_id' => $compared_security->id,
                    'correlation' => $r,
                ];
                TicksiftCorrelation::insert($correlation);
            }
        }
    }

    // Calculate Correlation Coefficients of 2 input arrays of equal length with matching keys
    public function correlationCoefficient($arr1, $arr2)
    {
        // Get length and averages of input arrays
        $length = count($arr1);
        if ($length > 0) {
            $mean1 = array_sum($arr1) / $length;
            $mean2 = array_sum($arr2) / $length;
        } else {
            return 0;
        }

        // Calculate all summations for correlation coefficient formula
        $a = $b = $axb = $a2 = $b2 = 0;
        foreach($arr1 as $i => $value) {
            $a = $arr1[$i] - $mean1;
            $b = $arr2[$i] - $mean2;
            $axb = $axb + ($a * $b);
            $a2 = $a2 + pow($a, 2);
            $b2 = $b2 + pow($b, 2);
        }

        // Calculate Correlation Coefficient and return value
        $corr = 0;
        if ($a2 * $b2 > 0) {
            $corr = $axb / sqrt($a2 * $b2);
        } 
        return $corr;
    }
}
