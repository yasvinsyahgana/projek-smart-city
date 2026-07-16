<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (session('admin_logged_in')) {
            return redirect('/smart-lamp');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $adminUsername = env('ADMIN_USERNAME', 'admin');
        $adminPassword = env('ADMIN_PASSWORD', 'admin123');

        if ($credentials['username'] === $adminUsername && $credentials['password'] === $adminPassword) {
            session(['admin_logged_in' => true, 'admin_username' => $credentials['username']]);
            return redirect('/smart-lamp')->with('success', 'Login berhasil!');
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->withInput();
    }

    public function logout()
    {
        session()->forget(['admin_logged_in', 'admin_username']);
        return redirect('/login')->with('success', 'Anda telah logout.');
    }
}