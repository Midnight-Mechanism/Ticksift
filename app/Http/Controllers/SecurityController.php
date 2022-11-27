<?php

namespace App\Http\Controllers;

use App\Models\Price;
use App\Models\Security;
use App\Models\SourceTable;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class SecurityController extends Controller
{
    /**
     * Build the winners and losers list from prices.
     *
     * @param  \Illuminate\Support\Collection  $earliest_prices
     * @param  \Illuminate\Support\Collection  $latest_prices
     * @return  array
     */
    protected static function buildWinnersLosers(Collection $earliestPrices, Collection $latestPrices)
    {
        [$winners, $losers] = [collect(), collect()];

        $latest_prices = $latestPrices->keyBy->short_name;

        foreach ($earliestPrices as $earliest_price) {
            $short_name = $earliest_price->short_name;

            $latest_price = $latest_prices->get($short_name);

            if (! $latest_price || $earliest_price->close == $latest_price->close) {
                continue;
            }

            $coeff = $latest_price->close / $earliest_price->close;
            $volume = round(($latest_price->volume + $earliest_price->volume) / 2);

            unset($earliest_price->volume);
            $base = (array) $earliest_price + [
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
    public static function calculateMomentum($start_date, $end_date, $use_cached = true, $security_ids = null)
    {
        $cache_key = 'momentum';

        if ($security_ids) {
            $cache_key .= '-'.implode('-', $security_ids);
        }

        $cache_key .= '-start-'.$start_date;
        $cache_key .= '-end-'.$end_date;

        if ($use_cached) {
            $cached_results = Cache::get($cache_key);
            if ($cached_results) {
                return $cached_results;
            }
        }

        $query = DB::table('prices')
            ->whereBetween('date', [$start_date, $end_date])
            ->join('securities', 'prices.security_id', 'securities.id')
            ->leftJoin('industries', 'securities.industry_id', 'industries.id')
            ->leftJoin('sectors', 'industries.sector_id', 'sectors.id')
            ->leftJoin('currencies', 'securities.currency_id', 'currencies.id');

        if ($security_ids) {
            $query->whereIn('security_id', $security_ids);
        } else {
            $query->where('scale_marketcap', '>=', 5);
        }

        $query->select(
            'ticker',
            'securities.name',
            DB::raw('COALESCE(ticker, securities.name) AS short_name'),
            'industries.name AS industry',
            'sectors.name AS sector',
            'sectors.color AS sector_color',
            'scale_marketcap',
            'currencies.code AS currency_code',
            'date',
            'close',
            'volume'
        )->distinct('short_name')
         ->orderBy('short_name');

        $earliest_prices = (clone $query)->oldest('date')->get();
        $latest_prices = $query->latest('date')->get();

        [$winners, $losers] = self::buildWinnersLosers(
            $earliest_prices, $latest_prices
        );

        $results = [
            'winners' => $winners->sortByDesc('increase')->values(),
            'losers' => $losers->sortByDesc('decrease')->values(),
        ];

        Cache::put($cache_key, $results, now()->addWeek());

        return $results;
    }

    /**
     * Calculate the security momentum.
     *
     * @return \Illuminate\Http\Response
     */
    public function momentumResults(Request $request)
    {
        $dates = $request->input('dates');

        if (count($dates) > 1) {
            // date range
            $start_date = $dates[0];
            $end_date = $dates[1];

            $request->session()->put([
                'security_dates' => [$start_date, $end_date],
            ]);
        } else {
            // single date
            // we want the start date to be the prior trading day
            $earlier_price = Price::select('date')
                ->where('date', '<=', $dates[0])
                ->distinct()
                ->orderBy('date', 'desc')
                ->skip(1)
                ->first();
            $start_date = $earlier_price ? $earlier_price->date : $dates[0];
            $end_date = $dates[0];

            $request->session()->put([
                'security_dates' => [$end_date, $end_date],
            ]);
        }

        $results = $this->calculateMomentum($start_date, $end_date, true, $request->input('security_ids'));

        return response()->json($results, 200, [], JSON_NUMERIC_CHECK);
    }

    /**
     * Provide the security explorer view.
     *
     * @return \Illuminate\Http\Response
     */
    public function explorer(Request $request)
    {
        $user = Auth::user();

        return Inertia::render('Securities/Explorer', [
            'portfolios' => $user ? $user
                ->portfolios()
                ->with('securities')
                ->get() : null,
        ]);
    }

    /**
     * Provide the security momentum view.
     *
     * @return \Illuminate\Http\Response
     */
    public function momentum(Request $request)
    {
        $stored_dates = $request->session()->get('security_dates');

        return Inertia::render('Securities/Momentum');
    }

    /**
     * Search for the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        $source_tables = SourceTable::all();
        $like_operator = (DB::getDriverName() === 'pgsql') ? 'ILIKE' : 'LIKE';
        $results = Security::where('ticker', $like_operator, '%'.$query.'%')
            ->orWhere('name', $like_operator, '%'.$query.'%')
            ->select(
                'id AS value',
                'source_table_id',
                'name',
                'ticker',
            )
            ->orderBy('ticker')->orderBy('name')
            ->get()
        ->map(function ($security) {
            $security->label = $security->ticker_name;
            unset($security->ticker_name);

            return $security;
        })->groupBy(function ($security) use ($source_tables) {
            $source_table_id = $security->source_table_id;
            unset($security->source_table_id);

            return $source_tables->firstWhere('id', $source_table_id)->group ?? 'Misc.';
        })->sortBy(function ($security, $type) {
            switch($type) {
                case 'Securities':
                    return 1;
                case 'Misc.':
                    return 0;
                default:
                    return 2;
            }
        })->map(function ($securities, $type) {
            return [
                'label' => $type,
                'options' => $securities,
            ];
        })->values();

        return response()->json($results);
    }

    /**
     * Fetch the price data for the specified resources.
     *
     * @return \Illuminate\Http\Response
     */
    public function prices(Request $request)
    {
        $dates = $request->input('dates');
        $security_ids = $request->input('security_ids');

        $start_date = $dates[0];
        if (count($dates) > 1) {
            // date range
            $end_date = $dates[1];
        } else {
            // single date
            $end_date = $dates[0];
        }

        $request->session()->put('security_dates', [
            $start_date,
            $end_date,
        ]);

        if ($request->has('is_ratio')) {
            $request->session()->put('ratio_security_id', empty($security_ids) ? null : $security_ids[0]);
        } else {
            $request->session()->put('security_ids', $security_ids);
        }

        if (empty($security_ids)) {
            return response()->json([], 200, [], JSON_NUMERIC_CHECK);
        }

        $security_prices = collect([]);

        foreach ($security_ids as $security_id) {
            $security = Security::findOrFail($security_id);
            $security_prices->push([
                'short_name' => $security->ticker ?? $security->name,
                'currency_code' => $security->currency ? $security->currency->code : null,
                'prices' => $security
                    ->prices()
                    ->whereBetween('date', [$start_date, $end_date])
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
}
