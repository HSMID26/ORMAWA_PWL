<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Services\ActivityLogService;

class UserController extends Controller
{
    /**
     * Tampilkan semua daftar user beserta organisasinya
     */
    public function index()
    {
        $users = User::with(['organization', 'roles'])->latest()->get()->map(function ($user) {
            $user->role = $user->roles->first()?->name ?? '-';
            return $user;
        });

        return response()->json([
            'status' => 'success',
            'data'   => $users
        ]);
    }

    /**
     * Buat User/Admin Baru & Assign ke Organisasi (Khusus Super Admin PKA)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|string|email|max:255|unique:users,email',
            'password'        => 'required|string|min:8',
            'role'            => 'required|string|in:Super Admin,Admin Organisasi,Editor,Kontributor',
            'organization_id' => 'required|exists:organizations,id',
            'status'          => 'nullable|in:active,inactive',
        ]);

        $user = User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'organization_id' => $request->organization_id,
            'status'          => $request->status ?? 'active',
        ]);

        // Assign Spatie Role
        $user->assignRole($request->role);

        $user->load(['organization', 'roles']);
        $user->role = $user->roles->first()?->name;

        ActivityLogService::log('create', 'users', 'Membuat user baru: ' . $user->name, $user);

        return response()->json([
            'status'  => 'success',
            'message' => 'User/Admin ORMAWA berhasil dibuat!',
            'data'    => $user
        ], 201);
    }

    /**
     * Detail User
     */
    public function show($id)
    {
        $user = User::with(['organization', 'roles'])->findOrFail($id);
        $user->role = $user->roles->first()?->name ?? '-';

        return response()->json([
            'status' => 'success',
            'data'   => $user
        ]);
    }

    /**
     * Update data User/Admin
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'            => 'sometimes|required|string|max:255',
            'email'           => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password'        => 'nullable|string|min:8',
            'role'            => 'sometimes|required|string|in:Super Admin,Admin Organisasi,Editor,Kontributor',
            'organization_id' => 'sometimes|nullable|exists:organizations,id',
            'status'          => 'sometimes|required|in:active,inactive',
        ]);

        $userData = $request->only(['name', 'email', 'organization_id', 'status']);

        // Update password hanya jika diisi
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $oldStatus = $user->status;

        $user->update($userData);

        if ($request->has('role')) {
            $user->syncRoles([$request->role]);
        }

        if (isset($userData['status']) && $userData['status'] !== $oldStatus) {
            $statusTitle = $userData['status'] === 'active' ? 'Akun Diaktifkan' : 'Akun Dinonaktifkan';
            $statusMessage = $userData['status'] === 'active' ? 'Akun Anda telah diaktifkan kembali.' : 'Akun Anda telah dinonaktifkan.';
            
            \App\Services\NotificationService::send(
                $user,
                'user_status_changed',
                $statusTitle,
                $statusMessage,
                null
            );
        }

        $user->load(['organization', 'roles']);
        $user->role = $user->roles->first()?->name ?? '-';

        ActivityLogService::log('update', 'users', 'Memperbarui data user: ' . $user->name, $user);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data user berhasil diperbarui!',
            'data'    => $user
        ]);
    }

    /**
     * Hapus User
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        ActivityLogService::log('delete', 'users', 'Menghapus user: ' . $user->name, clone $user);

        return response()->json([
            'status'  => 'success',
            'message' => 'User berhasil dihapus!'
        ]);
    }
}