<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Security;
use App\Models\Price;

class SecurityController extends Controller
{
    /**
     * Calculate price elasticity for the specified resource.
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
     * Calculate price elasticity for the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function prices(Request $request)
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

        $security_ids = $request->input('ids');
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
