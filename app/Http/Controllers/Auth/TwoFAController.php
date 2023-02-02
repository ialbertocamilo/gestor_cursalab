<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Session;
use App\Http\Controllers\Controller;

class TwoFAController extends Controller
{
    public function showAuth2faForm(Request $request) {
        if(!Session::has('init_2fa')) {
            return redirect('/login');
        }
        return view('auth.auth2fa', ['maxlength' => env('AUTH2FA_CODE_DIGITS') ]);
    }
}
