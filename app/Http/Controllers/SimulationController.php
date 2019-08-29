<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Models\User;
use App\Models\Simulation;
use App\Models\Security;

class SimulationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        return view('simulations.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        $simulation = Simulation::create([
            'user_id' => $user->id,
        ]);

        return redirect()->route('simulations.show', [
            'simulation' => $simulation,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $user = Auth::user();
        $simulation = Simulation::findOrFail($id);
        $securities = Security::orderBy('ticker')->get();

        if ($user->can('view', $simulation)) {
            return view('simulations.show', [
                'simulation' => $simulation,
                'securities' => $securities,
            ]);
        }
        return back()->with('error', 'You do not have permission to access this project.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $simulation = Simulation::findOrFail($id);

        if ($user->can('update', $simulation)) {
            // Check if another simulation with same name already exists
            $duplicate = Simulation::where([
                ['name', '=', $request->input('name')],
                ['saved', '=', 'true'],
                ['user_id', '=', $user->id], // per user
                ['id', '<>', $id], // not the current simulation
            ])->exists();

            if ($duplicate) {
                return back()->with('error', 'A simulation with name "'.$request->input('name').'" already exists for this project. Please enter a unique name.');
            } else {
                $simulation->update([
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                    'saved' => true,
                ]);
                return redirect()->route('simulations.index', $simulation->project_id)->with('success', 'The simulation was successfully saved.');
            }
        }

        return back()->with('error', 'You do not have permission to save this simulation.');
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
        $simulation = Simulation::findOrFail($id);

        if ($user->can('delete', $simulation)) {
            $simulation->delete();
            return back()->with('success', 'The simulation was deleted.');
        }
        return back()->with('error', 'You do not have permission to delete this simulation.');
    }
}
