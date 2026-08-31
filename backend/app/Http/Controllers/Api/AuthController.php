<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
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
                'avatar' => $user->avatar,
                'role' => $user->getRoleNames()->first(), // Spatie Role
                'permissions' => $user->getAllPermissions()->pluck('name')->values(),
                'organization' => $user->organization,    // Data Organisasi terkait
            ]
        ]);
    }

    /**
     * Endpoint Cek User Login Saat Ini / Profil
     */
    public function me(Request $request)
    {
        $user = User::with(['organization', 'roles', 'permissions'])->find($request->user()->id);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'role' => $user->getRoleNames()->first(),
                'permissions' => $user->getAllPermissions()->pluck('name')->values(),
                'organization' => $user->organization,
                'status' => $user->status,
                'created_at' => $user->created_at?->toIso8601String(),
            ]
        ]);
    }

    /**
     * Endpoint Update Profil Pengguna Sendiri
     */
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|min:2|max:100',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.min' => 'Nama minimal terdiri dari 2 karakter.',
            'name.max' => 'Nama maksimal 100 karakter.',
        ]);

        $name = trim($request->name);
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => ['Nama lengkap tidak boleh hanya berupa spasi.'],
            ]);
        }

        $user->name = $name;

        // Handle Avatar Upload if a file is provided
        if ($request->hasFile('avatar')) {
            $request->validate([
                'avatar' => 'file|mimes:jpeg,jpg,png,webp|max:5120',
            ], [
                'avatar.mimes' => 'Format foto profil harus berupa JPG, JPEG, PNG, atau WEBP.',
                'avatar.max' => 'Ukuran foto profil maksimal 5 MB.',
            ]);

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = '/storage/' . $path;
        } elseif ($request->has('avatar') && is_string($request->avatar)) {
            // If empty string is passed explicitly, clear avatar
            if (empty(trim($request->avatar))) {
                $user->avatar = null;
            }
        }

        $user->save();

        ActivityLogService::log('update', 'users', 'User memperbarui profil: ' . $user->name, $user);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'role' => $user->getRoleNames()->first(),
                'organization' => $user->organization,
                'status' => $user->status,
                'created_at' => $user->created_at?->toIso8601String(),
            ]
        ]);
    }

    /**
     * Endpoint Update Password Pengguna Sendiri
     */
    public function updatePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Kata sandi saat ini tidak sesuai.'],
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        ActivityLogService::log('password_changed', 'authentication', 'User mengubah kata sandi', $user);

        return response()->json([
            'message' => 'Kata sandi berhasil diperbarui.'
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

    /**
     * Endpoint API Forgot Password (Minta Tautan Reset Kata Sandi)
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Masukkan alamat email yang valid.',
        ]);

        // Kirim reset link menggunakan Laravel Password broker
        $status = \Illuminate\Support\Facades\Password::sendResetLink(
            $request->only('email')
        );

        $user = User::where('email', $request->email)->first();
        if ($user) {
            ActivityLogService::log('password_reset_requested', 'authentication', 'Permintaan reset password diajukan', $user, null, clone $user);
        }

        // Generic response to prevent user enumeration
        return response()->json([
            'status' => 'success',
            'message' => 'Jika email Anda terdaftar dalam sistem, tautan untuk mengatur ulang kata sandi telah dikirimkan ke email Anda.'
        ], 200);
    }

    /**
     * Endpoint API Reset Password (Simpan Kata Sandi Baru dengan Token)
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'token.required' => 'Token reset password tidak valid atau sudah kedaluwarsa.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => \Illuminate\Support\Str::random(60),
                ])->save();

                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        if ($status === \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                ActivityLogService::log('password_reset_completed', 'authentication', 'Kata sandi akun berhasil diatur ulang', $user, null, clone $user);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Kata sandi Anda berhasil diperbarui. Silakan login kembali dengan kata sandi baru Anda.'
            ], 200);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}