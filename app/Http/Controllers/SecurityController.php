<?php

namespace App\Http\Controllers;

use App\Models\Price;
use App\Models\Security;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SecurityController extends Controller
{

    /**
     * Build the winners and losers list from prices.
     *
     * @param   \Illuminate\Support\Collection  $earliest_prices
     * @param   \Illuminate\Support\Collection  $latest_prices
     * @return  array
     */
    protected static function buildWinnersLosers(Collection $earliestPrices, Collection $latestPrices)
    {
        [$winners, $losers] = [collect(), collect()];

        $latest_prices = $latestPrices->keyBy->ticker;

        foreach ($earliestPrices as $earliest_price) {
            $ticker = $earliest_price->ticker;

            $latest_price = $latest_prices->get($ticker);

            if (! $latest_price || $earliest_price->close == $latest_price->close) continue;

            $coeff = $latest_price->close / $earliest_price->close;
            $volume = round(($latest_price->volume + $earliest_price->volume) / 2);

            unset($earliest_price->volume);
            $base = (array)$earliest_price + [
                'earliest_close' => $earliest_price->close,
                'latest_close' => $latest_price->close,
                'volume' => $volume,
            ];
            unset($base['close']);
            unset($base['date']);

            if ($coeff >= 1) {
                $winners->push($base + ['increase' => $coeff - 1]);
            } else {
                $losers->push($base + ['decrease' => 1 - $coeff]);
            }
        }

        return [$winners, $losers];
    }

    /**
     * Calculate momentum results
     *
     * @param start_date
     * @param end_date
     * @param use_cached
     * @return array
     */
    public static function calculateMomentum($start_date, $end_date, $use_cached = TRUE) {
        $cache_key = 'momentum';

        $cache_key .= '-start-' . $start_date;
        $cache_key .= '-end-' . $end_date;

        if ($use_cached) {
            $cached_results = Cache::get($cache_key);
            if ($cached_results) {
                return $cached_results;
            }
        }

        $query =  DB::table('prices')
            ->whereBetween('date', [$start_date, $end_date])
            ->join('securities', 'prices.security_id', 'securities.id')
            ->join('industries', 'securities.industry_id', 'industries.id')
            ->join('sectors', 'industries.sector_id', 'sectors.id')
            ->join('currencies', 'securities.currency_id', 'currencies.id')
            ->select(
                'ticker',
                'securities.name',
                'industries.name AS industry',
                'sectors.name AS sector',
                'sectors.color AS sector_color',
                'scale_marketcap',
                'currencies.code AS currency_code',
                'date',
                'close',
                'volume'
            )
            ->distinct('ticker')
            ->orderBy('ticker');

        $earliest_prices = (clone $query)->oldest('date')->get();
        $latest_prices = $query->latest('date')->get();

        [$winners, $losers] = self::buildWinnersLosers(
            $earliest_prices, $latest_prices
        );

        $results = [
            'winners' => $winners->sortByDesc('increase')->values(),
            'losers' => $losers->sortByDesc('decrease')->values(),
        ];

        Cache::put($cache_key, $results, now()->addDays(1));
        return $results;
    }

    /**
     * Calculate the security momentum.
     *
     * @return \Illuminate\Http\Response
     */
    public function getMomentum(Request $request)
    {
        $dates = explode(' ', $request->input('dates'));

        if (count($dates) > 1) {
            // date range, e.g. "1995-01-01 to 1995-02-01"
            $start_date = $dates[0];
            $end_date = $dates[2];

            $request->session()->put([
                'security_dates' => [$start_date, $end_date],
            ]);
        } else {
            // single date, e.g. "1995-01-01"
            // we want the start date to be the prior trading day
            $start_date = Price::select('date')
                ->where('date', '<=', $dates[0])
                ->distinct()
                ->orderBy('date', 'desc')
                ->skip(1)
                ->first()
                ->date;
            $end_date = $dates[0];

            $request->session()->put([
                'security_dates' => [$end_date, $end_date],
            ]);
        }

        $results = $this->calculateMomentum($start_date, $end_date);

        return response()->json($results, 200, [], JSON_NUMERIC_CHECK);
    }

    /**
     * Provide the security explorer view.
     *
     * @return \Illuminate\Http\Response
     */
    public function explorer(Request $request)
    {
        $old_dates = $request->session()->get('security_dates');
        $old_security_ids = $request->session()->get('security_ids');

        $old_securities = [];

        if ($old_security_ids) {
            $old_securities = Security::whereIn('id', $old_security_ids)
                ->select('id', 'ticker', 'name')
                ->get();
        }

        return view('securities.explorer')
            ->with('old_dates', $old_dates)
            ->with('old_securities', $old_securities);
    }

    /**
     * Provide the security momentum view.
     *
     * @return \Illuminate\Http\Response
     */
    public function momentum(Request $request)
    {
        $old_dates = $request->session()->get('security_dates');

        return view('securities.momentum')
            ->with('old_dates', $old_dates);
    }

    /**
     * Find the specified resource by ticker.
     *
     * @return \Illuminate\Http\Response
     */
    public function find(Request $request)
    {
        $ticker = $request->input('ticker');
        $security = Security::where('ticker', $ticker)->first();

        return response()->json($security);
    }

    /**
     * Search for the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        $equities_and_funds = Security::where('ticker', 'ILIKE', '%' . $query . '%')
            ->orWhere('name', 'ILIKE', '%' . $query . '%')
            ->select(
                'id',
                'source_table_id',
                DB::raw("CONCAT(ticker, ' - ', name) AS text")
            )
            ->get()
            ->partition(function($security) {
                $source_table_id = $security->source_table_id;
                unset($security->source_table_id);
                return $source_table_id != 3;
            });

        $results = [
            [
                'text' => 'Equities',
                'children' => $equities_and_funds[0]->values(),
            ],
            [
                'text' => 'Funds',
                'children' => $equities_and_funds[1]->values(),
            ]
        ];

        return response()->json($results);
    }

    /**
     * Fetch the price data for the specified resources.
     *
     * @return \Illuminate\Http\Response
     */
    public function prices(Request $request)
    {
        $dates = explode(' ', $request->input('dates'));
        $security_ids = $request->input('ids');

        $start_date = $dates[0];
        if (count($dates) > 1) {
            // date range, e.g. "1995-01-01 to 1995-02-01"
            $end_date = $dates[2];
        } else {
            // single date, e.g. "1995-01-01"
            $end_date = $dates[0];
        }

        $request->session()->put('security_dates', [
            $start_date,
            $end_date,
        ]);
        $request->session()->put('security_ids', $security_ids);

        if (empty($security_ids)) {
            return response()->json([], 200, [], JSON_NUMERIC_CHECK);
        }

        $security_prices = collect([]);

        foreach ($security_ids as $security_id) {
            $security = Security::findOrFail($security_id);
            $security_prices->push([
                'ticker' => $security->ticker,
                'currency_code' => $security->currency->code,
                'prices' => $security
                    ->prices()
                    ->whereBetween('date', [$start_date, $end_date,])
                    ->select(
                        'date',
                        'open',
                        'high',
                        'low',
                        'close',
                        'volume'
                    )->get(),
            ]);
        }

        $security_prices = $security_prices->sortByDesc(function ($security_data, $security) {
            if (count($security_data['prices']) > 0) {
                return $security_data['prices']->last()->close;
            } else {
                return 0;
            }
        });

        return response()->json($security_prices->values(), 200, [], JSON_NUMERIC_CHECK);
    }

    /**
     * Store chart options in session
     *
     */
    public function storeChartOptions(Request $request) {
        $request->session()->put([
            'chart_type' => $request->input('chart_type'),
            'chart_scale' => $request->input('chart_scale'),
        ]);
    }

}
