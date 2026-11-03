<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // ponytail: throttle khusus percobaan GAGAL, keyed email+IP.
        // Pakai route-level 'throttle' bakal ikut ngehitung login sukses,
        // padahal panduan testing nyuruh ganti akun berkali-kali dalam semenit.
        $key = 'login:' . Str::lower($request->email) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => ['Terlalu banyak percobaan. Coba lagi dalam ' . RateLimiter::availableIn($key) . ' detik.'],
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'email' => ['Kredensial salah.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Akun dinonaktifkan.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        RateLimiter::clear($key);

        return response()->json([
            'token' => $token,
            'user' => $user->load('role.permissions'),
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->load('role.permissions'));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
