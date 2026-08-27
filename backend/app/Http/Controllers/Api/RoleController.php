<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private const PERMISSION_GROUPS = [
        'Pengguna & Organisasi' => [
            'manage-users' => 'Mengelola pengguna',
            'manage-organizations' => 'Mengelola organisasi',
            'manage-periods' => 'Mengelola periode organisasi',
        ],
        'Konten' => [
            'view-content' => 'Melihat konten',
            'create-content' => 'Membuat konten',
            'edit-content' => 'Mengubah konten',
            'delete-content' => 'Menghapus konten',
            'publish-content' => 'Menerbitkan konten',
        ],
        'Sistem' => [
            'view-activity-logs' => 'Melihat activity log',
            'manage-platform-settings' => 'Mengelola pengaturan platform',
        ],
    ];

    private const BUILT_IN_ROLES = ['Super Admin', 'Admin Organisasi', 'Editor', 'Kontributor'];

    public function index(Request $request)
    {
        $this->ensureSuperAdmin($request);

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name', 'guard_name']);

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', self::BUILT_IN_ROLES)
            ->with('permissions:id,name,guard_name')
            ->orderByRaw("CASE name WHEN 'Super Admin' THEN 1 WHEN 'Admin Organisasi' THEN 2 WHEN 'Editor' THEN 3 WHEN 'Kontributor' THEN 4 ELSE 5 END")
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->values(),
            ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'roles' => $roles,
                'permissions' => $permissions,
                'groups' => self::PERMISSION_GROUPS,
            ],
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $this->ensureSuperAdmin($request);

        abort_unless($role->guard_name === 'web' && in_array($role->name, self::BUILT_IN_ROLES, true), 404);

        $allowedPermissions = array_merge(...array_values(array_map('array_keys', self::PERMISSION_GROUPS)));
        $validated = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', Rule::in($allowedPermissions)],
        ]);

        $role->syncPermissions($validated['permissions']);

        return response()->json([
            'status' => 'success',
            'message' => 'Hak akses role berhasil diperbarui.',
            'data' => [
                'name' => $role->name,
                'permissions' => $role->permissions()->pluck('name')->values(),
            ],
        ]);
    }

    private function ensureSuperAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('Super Admin'), 403, 'Hanya Super Admin yang dapat mengelola hak akses.');
    }
}