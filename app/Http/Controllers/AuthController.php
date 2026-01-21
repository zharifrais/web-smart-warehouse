<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // ================================
    //  LOGIN PAGE
    // ================================
    public function index()
    {
        return view('auth.login');
    }

    // ================================
    //  OVERRIDE IDENTIFIER → username
    // ================================
    public function username()
    {
        return 'username';
    }

    // ================================
    //  LOGIN PROCESS
    // ================================
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if (Auth::attempt([
            'username' => $request->username,
            'password' => $request->password
        ])) {
            return redirect('/dashboard');
        }

        return back()->with('error', 'Username atau Password salah!');
    }

    // ================================
    //  LOGOUT
    // ================================
    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
