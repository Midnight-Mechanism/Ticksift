<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Security;
use App\Models\Price;

class SecurityController extends Controller
{

    /**
     * Show the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
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

        return view('securities.show')
            ->with('old_dates', $old_dates)
            ->with('old_securities', $old_securities);
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
