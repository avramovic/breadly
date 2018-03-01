<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function verify($token, Request $request)
    {

        $user = User::where('verification_token', $token)->first();

        if (!$user || $user->is_verified) {
            abort(404);
        }

        $user->is_verified        = 1;
        $user->verification_token = null;
        $user->save();

        return view('pages.message', [
            'title' => 'Nice.',
            'message' => 'Your account is now verified!',
        ]);
    }
}
