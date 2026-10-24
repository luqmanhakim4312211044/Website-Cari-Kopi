<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Email atau password salah.');
        }

        $request->session()->regenerate();

        return redirect()->intended(
            Auth::user()->canManageShop() ? route('dashboard') : route('home')
        );
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // Rapikan dulu: huruf kecil & tanpa spasi, agar "Nama@Gmail.com " tidak dianggap email lain.
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $invalidEmail = 'Gunakan alamat email aktif yang valid, misalnya nama@gmail.com.';

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            // `dns`: domainnya harus benar-benar ada dan menerima email (a@a.a ditolak).
            // `regex`: akhiran domain minimal 2 huruf.
            'email' => ['required', 'string', 'max:255', 'email:rfc,dns', 'regex:/^[^@\s]+@[^@\s]+\.[A-Za-z]{2,}$/', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.email' => $invalidEmail,
            'email.regex' => $invalidEmail,
            'email.unique' => 'Email ini sudah terdaftar.',
        ]);

        $data['password'] = Hash::make($data['password']);
        User::create($data);

        return redirect()->route('login')->with('success', 'Akun berhasil dibuat, silakan masuk.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Kamu sudah keluar.');
    }
}
