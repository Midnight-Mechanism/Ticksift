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
     * Activate an invited user.
     *
     * @return \Illuminate\Http\Response
     */
    public function activate(Request $request, $code) {
        if(Invite::isValid($code)) {
            $invitation = Invite::get($code);
            Auth::loginUsingId($invitation->user_id, true);
            $user = Auth::user();
            $user->activated = true;
            $user->save();
            Invite::consume($code);
            return redirect()->route('profile');
        }
    }

    /**
     * Retrieve a user profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function profile(Request $request) {
        return view('auth.profile', []);
    }

    /**
     * Update a user's password.
     *
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request) {
        $validator = Validator::make($request->all(), [
            'password'              => 'required|min:6|max:20|confirmed',
            'password_confirmation' => 'required|same:password',
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
