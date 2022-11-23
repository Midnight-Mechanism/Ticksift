<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

        return Inertia::render('Portfolios', [
            'portfolios' => $user
                ->portfolios()
                ->with('securities')
                ->get(),
        ]);
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

        $portfolio = Portfolio::create([
            'name' => $request->input('name'),
        ]);
        $portfolio->users()->attach($user->id);
        $portfolio->securities()->attach($request->input('security_ids'));

        return back()->with('message', __('portfolios.created'));
    }

    /**
     * Update an existing resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param    $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $portfolio = Portfolio::findOrFail($id);

        if ($user->can('update', $portfolio)) {
            $portfolio->securities()->sync($request->input('security_ids'));

            return back()->with('message', __('portfolios.updated'));
        }

        return back()->with('message', __('portfolios.no_permission'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $portfolio = Portfolio::findOrFail($id);

        if ($user->can('delete', $portfolio)) {
            $portfolio->delete();

            return back()->with('message', __('portfolios.deleted'));
        }

        return back()->with('message', __('portfolios.no_permission'));
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
        foreach ($portfolio_ids as $portfolio_id) {
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
}
