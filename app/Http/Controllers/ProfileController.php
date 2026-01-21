<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->back()->with('success', 'Profil berhasil diperbarui!');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login')->with('success', 'Berhasil logout!');
    }
    
    public function settings()
    {
        return view('settings.index');
    }
    
    public function updateColors(Request $request)
    {
        $colors = [
            'sidebar_bg' => $request->sidebar_bg ?? '#2C3E50',
            'sidebar_text' => $request->sidebar_text ?? '#ECF0F1',
            'topbar_bg' => $request->topbar_bg ?? '#E5E5E5',
            'menu_hover' => $request->menu_hover ?? '#D0D0D0',
            'menu_active' => $request->menu_active ?? '#FFFFFF',
        ];
        
        return redirect()->back()
            ->with('success', 'Pengaturan warna berhasil disimpan!')
            ->withCookie(cookie('theme_colors', json_encode($colors), 525600));
    }
}
