<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Security;
use App\Models\Price;

class SecurityController extends Controller
{

    /**
     * Provide the security explorer.
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
                ->select(
                    'id',
                    'ticker',
                    'name',
                )
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
     * Calculate the security momentum.
     *
     * @return \Illuminate\Http\Response
     */
    public function calculateMomentum(Request $request)
    {
        $dates = explode(' ', $request->input('dates'));

        $start_date = $dates[0];
        if (count($dates) > 1) {
            // date range, e.g. "1995-01-01 to 1995-02-01"
            $end_date = $dates[2];
        } else {
            // single date, e.g. "1995-01-01"
            $end_date = $dates[0];
        }

        $volume_threshold = $request->input('volume_threshold');
        $request->session()->put('security_dates', [
            $start_date,
            $end_date
        ]);
        $request->session()->put('security_volume_threshold', $volume_threshold);

        $min_query = \DB::table('prices')
            ->where('volume', '>=', $volume_threshold ?: 0)
            ->whereBetween('date', [
                $start_date,
                $end_date,
            ])
            ->join('securities', 'prices.security_id', 'securities.id')
            ->select(\DB::raw('DISTINCT ON (ticker) ticker, date, close'))
            ->orderBy('ticker');

        $max_query = clone $min_query;

        $earliest_prices = $min_query
            ->orderBy('date')
            ->get();

        $latest_prices = $max_query
            ->orderBy('date', 'desc')
            ->get();

        $winners = collect([]);
        $losers = collect([]);

        foreach($earliest_prices as $earliest_price) {
            $ticker = $earliest_price->ticker;
            $latest_price = $latest_prices->firstWhere('ticker', $ticker);
            if (!$latest_price) continue;
            $coeff = $latest_price->close / $earliest_price->close;
            if ($coeff >= 1) {
                $winners->push([
                    'ticker' => $ticker,
                    'earliest_close' => $earliest_price->close,
                    'latest_close' => $latest_price->close,
                    'increase' => $coeff - 1
                ]);
            } else {
                $losers->push([
                    'ticker' => $ticker,
                    'earliest_close' => $earliest_price->close,
                    'latest_close' => $latest_price->close,
                    'decrease' => 1 - $coeff
                ]);
            }
        }

        $results['winners'] = $winners->sortByDesc('increase')->values()->take(10);
        $results['losers'] = $losers->sortByDesc('decrease')->values()->take(10);
        return response()->json($results, 200, [], JSON_NUMERIC_CHECK);
    }


    /**
     * Search for the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        $results = Security::where('ticker', 'ILIKE', '%' . $query . '%')
            ->orWhere('name', 'ILIKE', '%' . $query . '%')
            ->select(
                'id',
                'ticker',
                'name',
            )
            ->get();
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

        $prices = [];
        foreach ($security_ids as $security_id) {
            $security = Security::findOrFail($security_id);
            $prices[$security->ticker] = $security
                             ->prices()
                             ->whereBetween('date', [
                                 $start_date,
                                 $end_date,
                             ])
                             ->select(
                                 'date',
                                 'open',
                                 'high',
                                 'low',
                                 'close',
                             )->get();
        }
        return response()->json($prices, 200, [], JSON_NUMERIC_CHECK);
    }
}
