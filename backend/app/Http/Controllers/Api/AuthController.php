<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50',
            'password' => 'required|string|max:128',
            'role' => 'required|string|in:owner,admin,kasir',
        ]);

        // Rate limiting: max 5 attempts per minute per IP to prevent brute force
        $throttleKey = 'login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'username' => ["Terlalu banyak percobaan. Silakan coba lagi dalam {$seconds} detik."],
            ]);
        }

        RateLimiter::hit($throttleKey, 60);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Username atau password salah.'],
            ]);
        }

        if ($user->role !== $request->role) {
            throw ValidationException::withMessages([
                'role' => ['Role yang Anda pilih tidak sesuai dengan akun ini.'],
            ]);
        }

        // Clear rate limit on successful login
        RateLimiter::clear($throttleKey);

        $user->update(['last_login' => now()]);

        // Token expires in 12 hours instead of never expiring
        $token = $user->createToken('auth_token', [], now()->addHours(12))->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->only('id', 'username', 'name', 'role'),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->only('id', 'username', 'name', 'role'));
    }
}