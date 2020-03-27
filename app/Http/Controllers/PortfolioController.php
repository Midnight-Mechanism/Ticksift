<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Portfolio;
use Auth;

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
                         'created_at' => $item->created_at,
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

        $portfolio = Portfolio::create([
            'name' => $request->input('name'),
        ]);
        $portfolio->users()->attach($user->id);
        $portfolio->securities()->attach($security_ids);

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

        if ($user->can('update', $portfolio)) {
            $portfolio->securities()->sync($security_ids);
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
     * Search for the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        $query = $request->input('q');

        if (isset($user)) {
            $portfolios = $user->portfolios();
        } else {
            $portfolios = Portfolio::doesntHave('users');
        }

        return response()->json(
            $portfolios
                ->where('name', 'ILIKE', '%' . $query . '%')
                ->get()
        );
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
            if (
                (!isset($user) && $portfolio->users->isEmpty()) ||
                (isset($user) && $user->can('view', $portfolio))
            ) {
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
}
