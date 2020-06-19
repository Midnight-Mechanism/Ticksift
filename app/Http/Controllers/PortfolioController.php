<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Portfolio;
use App\Models\Security;
use Auth;
use DB;

class PortfolioController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        return view('portfolios.index')->with(
            'portfolios',
            $user->portfolios()
                 ->with('securities:securities.id,ticker,name')
                 ->get()
                 ->map(function($item, $key) {
                     return [
                         'id' => $item->id,
                         'name' => $item->name,
                         'securities' => $item->securities->map(function($security) {
                             return $security->ticker ?? $security->name;
                         })->join(', '),
                         'updated_at' => $item->updated_at,
                     ];
                 })
        );
    }

    /**
     * Store a new resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $security_ids = explode(',', $request->input('security_ids'));
        $security_weights = explode(',', $request->input('security_weights'));

        $portfolio = Portfolio::create([
            'name' => $request->input('name'),
        ]);
        $portfolio->users()->attach($user->id);
        $portfolio->securities()->attach($security_ids);

        // Add Portfolio's Security Weights
        for ($i = 0; $i < count($security_ids); $i++) {
            DB::table('portfolio_security')
                    ->where('portfolio_id', $portfolio->id)
                    ->where('security_id', $security_ids[$i])
                    ->update(['weight' => $security_weights[$i]]);
        }

        return back()->with('success', 'Your portfolio has been saved.');
    }

    /**
     * Update an existing resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $portfolio = Portfolio::findOrFail($id);
        $security_ids = explode(',', $request->input('security_ids'));
        $security_weights = explode(',', $request->input('security_weights'));

        if ($user->can('update', $portfolio)) {
            $portfolio->securities()->sync($security_ids);
            // Update Portfolio Security Weights
            for ($i = 0; $i < count($security_ids); $i++) {
                DB::table('portfolio_security')
                    ->where('portfolio_id', $portfolio->id)
                    ->where('security_id', $security_ids[$i])
                    ->update(['weight' => $security_weights[$i]]);
            }
            return back()->with('success', 'Your portfolio has been updated.');
        }
        return back()->with('error', 'You do not have permission to update this portfolio.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $portfolio = Portfolio::findOrFail($id);

        if ($user->can('delete', $portfolio)) {
            $portfolio->delete();
            return back()->with('success', 'The portfolio was deleted.');
        }

        return back()->with('error', 'You do not have permission to delete this portfolio.');
    }

    /**
     * Fetch the security data for the specified resources.
     *
     * @return \Illuminate\Http\Response
     */
    public function securities(Request $request)
    {
        $user = Auth::user();
        $portfolio_ids = $request->input('portfolio_ids');

        $securities = [];
        foreach($portfolio_ids as $portfolio_id) {
            $portfolio = Portfolio::find($portfolio_id);
            if ($user->can('view', $portfolio)) {
                $securities = array_merge($securities, $portfolio
                    ->securities()
                    ->select(
                        'securities.id',
                        'securities.ticker',
                        'securities.name'
                    )
                    ->get()
                    ->toArray()
                );
            }

        }
        return response()->json($securities, 200, [], JSON_NUMERIC_CHECK);
    }

    /**
     * Fetch the security weights for the specified portfolio.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSecurityWeights(Request $request)
    {
        $portfolio_id = $request->input('portfolio_id');
        $security_ids = $request->input('security_ids');
        $portfolio = Portfolio::find($portfolio_id);
        $security_weights = collect([]);

        // Get details and weights for all securities in portfolio
        foreach ($security_ids as $security_id) {
            $security = Security::findOrFail($security_id);
            $security_weight = 1;
            if ($portfolio_id) {
                $security_weight = DB::table('portfolio_security')
                    ->where('portfolio_id', $portfolio_id)
                    ->where('security_id', $security_id)
                    ->first()->weight ?? 1;
            }
            $security_weights->push([
                'id' => $security->id,
                'ticker' => $security->ticker ?? $security->name,
                'name' => $security->name,
                'weight' => $security_weight
            ]);
        }

        return response()->json($security_weights->values(), 200, [], JSON_NUMERIC_CHECK);
    }
}
