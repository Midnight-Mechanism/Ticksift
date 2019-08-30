<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Security;

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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function prices($id)
    {
        $prices = Security::findOrFail($id)
            ->prices()
            ->select(
                'date',
                'open',
                'high',
                'low',
                'close',
            )->get();
        return response()->json($prices);
    }
}
