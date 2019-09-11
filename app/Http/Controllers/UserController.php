<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use Auth;
use Invite;
use Validator;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Retrieve a user profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function profile(Request $request) {
        return view('auth.profile', []);
    }

    /**
     * Update a user's profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateProfile(Request $request) {
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

        return back()->with('success', 'Your information was successfully updated.');
    }


    /**
     * Update a user's password.
     *
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request) {
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

        return back()->with('success', 'Your password was successfully changed.');
    }

    /**
     * Redirect to the appropriate homepage.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return redirect()->route('securities.show');
    }
}
