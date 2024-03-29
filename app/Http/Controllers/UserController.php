<?php

namespace App\Http\Controllers;

use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Validator;

class UserController extends Controller
{
    /**
     * Retrieve a user profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function profile(Request $request)
    {
        return view('auth.profile', []);
    }

    /**
     * Update a user's profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();

        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');

        $user->save();

        return back()
            ->with('status', 'success')
            ->with('message', __('auth.updated'));
    }

    /**
     * Update a user's password.
     *
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6|max:20|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();

        if ($request->input('password') != null) {
            $user->password = bcrypt($request->input('password'));
        }

        $user->save();

        return back()
            ->with('status', 'success')
            ->with('message', __('passwords.changed'));
    }

    /**
     * Redirect to the appropriate homepage.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return redirect()->route('securities.explorer');
    }

    /**
     * Store chart options in session
     */
    public function storeChartOptions(Request $request)
    {
        $request->session()->put([
            'chart_type' => $request->input('chart_type'),
            'chart_scale' => $request->input('chart_scale'),
            'chart_indicators' => $request->input('chart_indicators'),
        ]);
    }
}
