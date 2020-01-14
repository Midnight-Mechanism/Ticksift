<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Portfolio;
use Auth;

class PortfolioController extends Controller
{
    /**
     * Search for the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        $results = Portfolio::where('name', 'ILIKE', '%' . $query . '%')->get();
        return response()->json($results);
    }

    /**
     * Fetch the security data for the specified resources.
     *
     * @return \Illuminate\Http\Response
     */
    public function securities(Request $request)
    {
        $portfolio = Portfolio::findOrFail($request->input('id'));
        $securities = $portfolio
            ->securities()
            ->select(
                'securities.id',
                'securities.ticker',
                'securities.name'
            )
            ->get();
        return response()->json($securities, 200, [], JSON_NUMERIC_CHECK);
    }
}
