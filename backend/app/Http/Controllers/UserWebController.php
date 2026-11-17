<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Services\ActivityLogService;
use App\Services\NotificationService;

class UserWebController extends Controller
{
    /**
     * Tampilkan daftar akun pengguna/pengurus
     */
    public function index(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->hasRole('Super Admin');

        $query = User::with(['organization', 'roles']);

        // Jika bukan Super Admin, batasi hanya melihat user di organisasinya sendiri
        if (!$isSuperAdmin) {
            $query->where('organization_id', $currentUser->organization_id);
        }

        // Filter Pencarian Nama / Email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter Role
        if ($request->filled('role')) {
            $role = $request->role;
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        // Filter Status (active / inactive)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->get();
        $organizations = $isSuperAdmin ? Organization::where('status', 'active')->get() : collect();
        $roles = $isSuperAdmin 
            ? Role::all() 
            : Role::whereIn('name', ['Admin Organisasi', 'Editor', 'Kontributor'])->get();

        return view('users.index', compact('users', 'organizations', 'roles', 'isSuperAdmin'));
    }

    /**
     * Tambah Akun Pengurus Baru
     */
    public function store(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->hasRole('Super Admin');

        $allowedRoles = $isSuperAdmin 
            ? ['Super Admin', 'Admin Organisasi', 'Editor', 'Kontributor']
            : ['Admin Organisasi', 'Editor', 'Kontributor'];

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|string|email|max:255|unique:users,email',
            'password'        => 'required|string|min:8',
            'role'            => ['required', 'string', Rule::in($allowedRoles)],
            'organization_id' => $isSuperAdmin ? 'nullable|exists:organizations,id' : 'nullable',
            'status'          => 'required|in:active,inactive',
        ]);

        $orgId = $isSuperAdmin 
            ? ($request->role === 'Super Admin' ? null : $request->organization_id) 
            : $currentUser->organization_id;

        $user = User::create([
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'password'        => Hash::make($validated['password']),
            'organization_id' => $orgId,
            'status'          => $validated['status'],
        ]);

        $user->assignRole($validated['role']);

        ActivityLogService::log('create', 'users', 'Membuat akun pengurus baru: ' . $user->name . ' (' . $user->email . ')', $user);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Akun pengurus berhasil dibuat!',
                'data'    => $user->load(['organization', 'roles'])
            ]);
        }

        return redirect()->route('users.index')->with('success', 'Akun pengurus berhasil dibuat!');
    }

    /**
     * Update Data Akun Pengurus
     */
    public function update(Request $request, User $user)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->hasRole('Super Admin');

        // Mencegah Admin Org mengubah akun milik organisasi lain
        if (!$isSuperAdmin && $user->organization_id !== $currentUser->organization_id) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah akun ini.');
        }

        $allowedRoles = $isSuperAdmin 
            ? ['Super Admin', 'Admin Organisasi', 'Editor', 'Kontributor']
            : ['Admin Organisasi', 'Editor', 'Kontributor'];

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password'        => 'nullable|string|min:8',
            'role'            => ['required', 'string', Rule::in($allowedRoles)],
            'organization_id' => $isSuperAdmin ? 'nullable|exists:organizations,id' : 'nullable',
            'status'          => 'required|in:active,inactive',
        ]);

        // Mencegah user menonaktifkan akun sendiri
        if ($user->id === $currentUser->id && $validated['status'] === 'inactive') {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Anda tidak dapat menonaktifkan akun Anda sendiri!'], 400);
            }
            return redirect()->back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri!');
        }

        $oldStatus = $user->status;

        $updateData = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
            'status' => $validated['status'],
        ];

        if ($isSuperAdmin) {
            $updateData['organization_id'] = $validated['role'] === 'Super Admin' ? null : $request->organization_id;
        }

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);
        $user->syncRoles([$validated['role']]);

        // Notifikasi jika status berubah
        if ($oldStatus !== $validated['status']) {
            $statusTitle = $validated['status'] === 'active' ? 'Akun Anda Diaktifkan Kembali' : 'Akun Anda Dinonaktifkan';
            $statusMessage = $validated['status'] === 'active' 
                ? 'Akun pengurus Anda di CMS Ormawa telah diaktifkan.' 
                : 'Akun pengurus Anda di CMS Ormawa telah dinonaktifkan oleh administrator.';

            NotificationService::send($user, 'user_status_changed', $statusTitle, $statusMessage, null);
        }

        ActivityLogService::log('update', 'users', 'Memperbarui akun pengurus: ' . $user->name, $user);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data akun pengurus berhasil diperbarui!',
                'data'    => $user->load(['organization', 'roles'])
            ]);
        }

        return redirect()->route('users.index')->with('success', 'Data akun pengurus berhasil diperbarui!');
    }

    /**
     * Toggle Status Aktif / Nonaktif Akun
     */
    public function toggleStatus(User $user)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if ($user->id === $currentUser->id) {
            if (request()->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Anda tidak dapat menonaktifkan akun Anda sendiri!'], 400);
            }
            return redirect()->back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri!');
        }

        // Mencegah Admin Org mengubah akun organisasi lain
        if (!$currentUser->hasRole('Super Admin') && $user->organization_id !== $currentUser->organization_id) {
            abort(403, 'Akses ditolak.');
        }

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        $statusTitle = $newStatus === 'active' ? 'Akun Diaktifkan' : 'Akun Dinonaktifkan';
        $statusMessage = $newStatus === 'active' 
            ? 'Akun Anda telah diaktifkan kembali.' 
            : 'Akun Anda telah dinonaktifkan oleh administrator.';

        NotificationService::send($user, 'user_status_changed', $statusTitle, $statusMessage, null);
        ActivityLogService::log('update', 'users', "Mengubah status akun {$user->name} menjadi {$newStatus}", $user);

        if (request()->wantsJson()) {
            return response()->json([
                'status'     => 'success',
                'message'    => "Akun {$user->name} berhasil " . ($newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan') . '!',
                'new_status' => $newStatus
            ]);
        }

        return redirect()->route('users.index')->with('success', "Status akun {$user->name} berhasil diperbarui!");
    }

    /**
     * Hapus Akun Pengurus
     */
    public function destroy(User $user)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if ($user->id === $currentUser->id) {
            if (request()->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Anda tidak dapat menghapus akun Anda sendiri!'], 400);
            }
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }

        if (!$currentUser->hasRole('Super Admin') && $user->organization_id !== $currentUser->organization_id) {
            abort(403, 'Akses ditolak.');
        }

        $userName = $user->name;
        $userCopy = clone $user;
        $user->delete();

        ActivityLogService::log('delete', 'users', "Menghapus akun pengurus: {$userName}", $userCopy);

        if (request()->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => "Akun {$userName} berhasil dihapus!"
            ]);
        }

        return redirect()->route('users.index')->with('success', "Akun {$userName} berhasil dihapus!");
    }
}
