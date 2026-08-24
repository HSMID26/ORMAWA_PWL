<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Services\ActivityLogService;

class AuthController extends Controller
{
    /**
     * Endpoint API Login (Fitur 1)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with(['organization', 'roles'])->where('email', $request->email)->first();

        // Cek email dan password
        if (! $user || ! Hash::check($request->password, $user->password)) {
            if ($user) {
                ActivityLogService::log('login_failed', 'authentication', 'Login gagal: Kredensial tidak cocok', $user, null, clone $user);
            }
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak cocok.'],
            ]);
        }

        // Cek jika akun nonaktif (Fitur 9)
        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Akun Anda dinonaktifkan. Silakan hubungi admin.',
            ], 403);
        }

        // Generate Sanctum Token with Expiration
        $expirationMinutes = config('sanctum.expiration', 1440);
        $expiresAt = $expirationMinutes ? now()->addMinutes($expirationMinutes) : null;
        $token = $user->createToken('auth_token', ['*'], $expiresAt)->plainTextToken;

        ActivityLogService::log('login', 'authentication', 'User berhasil login', $user, null, clone $user);

        return response()->json([
            'message' => 'Login berhasil!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(), // Spatie Role
                'organization' => $user->organization,    // Data Organisasi terkait
            ]
        ]);
    }

    /**
     * Endpoint Cek User Login Saat Ini
     */
    public function me(Request $request)
    {
        $user = User::with(['organization', 'roles'])->find($request->user()->id);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(),
                'organization' => $user->organization,
            ]
        ]);
    }

    /**
     * Endpoint Logout
     */
    public function logout(Request $request)
    {
        // Log the logout activity BEFORE deleting the token so the authenticated user is known
        ActivityLogService::log('logout', 'authentication', 'User melakukan logout');

        // Hapus token yang sedang digunakan
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout'
        ]);
    }
}