<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class IndicatorController extends Controller
{

    /**
     * Fetch recession data.
     *
     * @return \Illuminate\Http\Response
     */
    public function recessions(Request $request)
    {
        $subquery = DB::table('recessions')
            ->selectRaw('date AS start_date, LEAD(date, 1) OVER (ORDER BY date) AS end_date, is_recession');

        $recessions = DB::query()
            ->fromSub($subquery, 'a')
            ->where('is_recession', true)
            ->select('start_date', 'end_date')
            ->orderBy('start_date')
            ->get();

        return response()->json($recessions->values(), 200, [], JSON_NUMERIC_CHECK);
    }
}
