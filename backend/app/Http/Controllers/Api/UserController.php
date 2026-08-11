<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Tampilkan semua daftar user beserta organisasinya
     */
    public function index()
    {
        $users = User::with('organization')->latest()->get();

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
            'role'            => 'required|string|in:Admin Organisasi,Editor,Kontributor',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        $user = User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'role'            => $request->role,
            'organization_id' => $request->organization_id,
        ]);

        // Load relasi organisasinya untuk response JSON
        $user->load('organization');

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
        $user = User::with('organization')->findOrFail($id);

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
            'name'            => 'required|string|max:255',
            'email'           => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password'        => 'nullable|string|min:8',
            'role'            => 'required|string|in:Super Admin,Admin Organisasi,Editor,Kontributor',
            'organization_id' => 'nullable|exists:organizations,id',
        ]);

        $userData = [
            'name'            => $request->name,
            'email'           => $request->email,
            'role'            => $request->role,
            'organization_id' => $request->organization_id,
        ];

        // Update password hanya jika diisi
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);
        $user->load('organization');

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

        return response()->json([
            'status'  => 'success',
            'message' => 'User berhasil dihapus!'
        ]);
    }
}