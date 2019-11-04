<?php

namespace App\Http\Controllers;

use App\Models\Price;
use App\Models\Security;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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
     * Calculate the security momentum.
     *
     * @return \Illuminate\Http\Response
     */
    public function calculateMomentum(Request $request)
    {
        $start_date = ($dates = explode(' ', $request->input('dates')))[0];

        $end_date = (count($dates) > 1) ?
            $dates[2] : // date range, e.g. "1995-01-01 to 1995-02-01"
            $dates[0]; // single date, e.g. "1995-01-01"

        $volume_threshold = $request->input('volume_threshold');

        $request->session()->put([
            'security_dates' => [$start_date, $end_date],
            'security_volume_threshold' => $volume_threshold,
        ]);

        $query =  DB::table('prices')
            ->where('volume', '>=', $volume_threshold ?: 0)
            ->whereBetween('date', [$start_date, $end_date])
            ->join('securities', 'prices.security_id', 'securities.id')
            ->select('ticker', 'date', 'close')
            ->distinct('ticker')
            ->orderBy('ticker');

        $earliest_prices = (clone $query)->oldest('date')->get();
        $latest_prices = $query->latest('date')->get();

        [$winners, $losers] = $this->buildWinnersLosers(
            $earliest_prices, $latest_prices
        );

        return response()->json([
            'winners' => $winners->sortByDesc('increase')->values(),
            'losers' => $losers->sortByDesc('decrease')->values(),
        ], 200, [], JSON_NUMERIC_CHECK);
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
            ->select('id', 'ticker', 'name')
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
                ->whereBetween('date', [$start_date, $end_date,])
                ->select(
                    'date',
                    'open',
                    'high',
                    'low',
                    'close'
                )->get();
        }

        return response()->json($prices, 200, [], JSON_NUMERIC_CHECK);
    }

    /**
     * Build the winners and losers list from prices.
     *
     * @param   \Illuminate\Support\Collection  $earliest_prices
     * @param   \Illuminate\Support\Collection  $latest_prices
     * @return  array
     */
    protected function buildWinnersLosers(Collection $earliestPrices, Collection $latestPrices)
    {
        [$winners, $losers] = [collect(), collect()];

        $latest_prices = $latestPrices->keyBy->ticker;

        foreach ($earliestPrices as $earliest_price) {
            $ticker = $earliest_price->ticker;

            $latest_price = $latest_prices->get($ticker);

            if (! $latest_price) continue;

            $coeff = $latest_price->close / $earliest_price->close;

            $base = [
                'ticker' => $ticker,
                'earliest_close' => $earliest_price->close,
                'latest_close' => $latest_price->close,
            ];

            if ($coeff >= 1) {
                $winners->push($base + ['increase' => $coeff - 1]);
            } else {
                $losers->push($base + ['decrease' => 1 - $coeff]);
            }
        }

        return [$winners, $losers];
    }
}
