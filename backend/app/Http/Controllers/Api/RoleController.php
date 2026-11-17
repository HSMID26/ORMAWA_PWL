<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public const PERMISSION_GROUPS = [
        'Pengguna & Organisasi' => [
            'users.view'           => 'Melihat daftar akun pengguna organisasi',
            'users.manage'         => 'Menambah, mengedit, dan menghapus akun pengguna',
            'organizations.view'   => 'Melihat profil dan pengaturan organisasi',
            'organizations.manage' => 'Mengubah konfigurasi modul dan profil organisasi',
            'periods.manage'       => 'Mengajukan dan mengelola periode kepengurusan',
        ],
        'Berita & Artikel' => [
            'posts.view'           => 'Melihat daftar berita dan artikel internal',
            'posts.create'         => 'Membuat draf berita dan artikel baru',
            'posts.update'         => 'Mengubah isi konten berita dan artikel',
            'posts.delete'         => 'Menghapus berita dan artikel',
            'posts.publish'        => 'Mempublikasikan artikel langsung ke portal publik',
        ],
        'Agenda & Pengumuman' => [
            'agenda.view'          => 'Melihat daftar agenda kegiatan internal',
            'agenda.create'        => 'Membuat agenda kegiatan baru',
            'agenda.update'        => 'Mengubah data agenda kegiatan',
            'agenda.delete'        => 'Menghapus agenda kegiatan',
            'agenda.publish'       => 'Mempublikasikan agenda kegiatan ke portal publik',
            'announcements.view'   => 'Melihat daftar pengumuman internal',
            'announcements.create' => 'Membuat pengumuman baru',
            'announcements.update' => 'Mengubah data pengumuman',
            'announcements.delete' => 'Menghapus pengumuman',
            'announcements.publish'=> 'Mempublikasikan pengumuman ke portal publik',
        ],
        'Galeri & Dokumen' => [
            'gallery.view'         => 'Melihat galeri foto dokumentasi',
            'gallery.create'       => 'Mengunggah foto dokumentasi kegiatan baru',
            'gallery.update'       => 'Mengubah metadata foto dokumentasi kegiatan',
            'gallery.delete'       => 'Menghapus foto dokumentasi kegiatan',
            'documents.view'       => 'Melihat arsip berkas dokumen organisasi',
            'documents.create'     => 'Mengunggah berkas dokumen organisasi baru',
            'documents.update'     => 'Mengubah data berkas dokumen organisasi',
            'documents.delete'     => 'Menghapus berkas dokumen organisasi',
        ],
        'Struktur Pengurus' => [
            'structure.view'       => 'Melihat bagan struktur dan pengurus organisasi',
            'structure.manage'     => 'Menambah, mengedit, dan menghapus pengurus organisasi',
        ],
        'Audit & Tata Kelola' => [
            'activity_logs.view'   => 'Melihat catatan log aktivitas (Audit Trail)',
            'roles.manage'         => 'Mengelola konfigurasi hak akses role (Super Admin)',
            'platform_settings.manage' => 'Mengelola pengaturan platform global',
        ],
    ];

    public const ROLE_DEFAULTS = [
        'Super Admin' => [
            'users.view', 'users.manage',
            'organizations.view', 'organizations.manage',
            'periods.manage',
            'posts.view', 'posts.create', 'posts.update', 'posts.delete', 'posts.publish',
            'agenda.view', 'agenda.create', 'agenda.update', 'agenda.delete', 'agenda.publish',
            'announcements.view', 'announcements.create', 'announcements.update', 'announcements.delete', 'announcements.publish',
            'gallery.view', 'gallery.create', 'gallery.update', 'gallery.delete',
            'documents.view', 'documents.create', 'documents.update', 'documents.delete',
            'structure.view', 'structure.manage',
            'activity_logs.view',
            'roles.manage',
            'platform_settings.manage',
        ],
        'Admin Organisasi' => [
            'users.view', 'users.manage',
            'organizations.view', 'organizations.manage',
            'periods.manage',
            'posts.view', 'posts.create', 'posts.update', 'posts.delete', 'posts.publish',
            'agenda.view', 'agenda.create', 'agenda.update', 'agenda.delete', 'agenda.publish',
            'announcements.view', 'announcements.create', 'announcements.update', 'announcements.delete', 'announcements.publish',
            'gallery.view', 'gallery.create', 'gallery.update', 'gallery.delete',
            'documents.view', 'documents.create', 'documents.update', 'documents.delete',
            'structure.view', 'structure.manage',
            'activity_logs.view',
        ],
        'Editor' => [
            'posts.view', 'posts.create', 'posts.update', 'posts.delete',
            'agenda.view', 'agenda.create', 'agenda.update', 'agenda.delete',
            'announcements.view', 'announcements.create', 'announcements.update', 'announcements.delete',
            'gallery.view', 'gallery.create', 'gallery.update', 'gallery.delete',
            'documents.view', 'documents.create', 'documents.update', 'documents.delete',
            'structure.view',
        ],
        'Kontributor' => [
            'posts.view', 'posts.create', 'posts.update',
        ],
    ];

    public const BUILT_IN_ROLES = ['Super Admin', 'Admin Organisasi', 'Editor', 'Kontributor'];

    /**
     * Memastikan seluruh permission dan baseline role tersinkronisasi di database
     */
    public static function ensureDefaultPermissions(): void
    {
        // 1. Ensure all permissions exist
        foreach (self::PERMISSION_GROUPS as $perms) {
            foreach (array_keys($perms) as $permName) {
                Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            }
        }

        // 2. Ensure all built-in roles exist
        foreach (self::BUILT_IN_ROLES as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            if ($role->permissions()->count() === 0 && isset(self::ROLE_DEFAULTS[$roleName])) {
                $role->syncPermissions(self::ROLE_DEFAULTS[$roleName]);
            }
        }
    }

    /**
     * Tampilkan daftar role beserta permissions aktif dan grupnya
     */
    public function index(Request $request)
    {
        $this->ensureSuperAdmin($request);

        self::ensureDefaultPermissions();

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

    /**
     * Perbarui hak akses / permissions untuk suatu role
     */
    public function update(Request $request, Role $role)
    {
        $this->ensureSuperAdmin($request);

        abort_unless($role->guard_name === 'web' && in_array($role->name, self::BUILT_IN_ROLES, true), 404, 'Role tidak ditemukan.');

        $allowedPermissions = array_merge(...array_values(array_map('array_keys', self::PERMISSION_GROUPS)));
        
        $validated = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', Rule::in($allowedPermissions)],
        ], [
            'permissions.required' => 'Daftar izin hak akses wajib diisi.',
            'permissions.array' => 'Format daftar izin tidak valid.',
        ]);

        $permissionsToSync = $validated['permissions'];

        // Security: Prevent Super Admin self-lockout by ensuring critical governance permissions cannot be stripped
        if ($role->name === 'Super Admin') {
            $criticalPermissions = ['manage-users', 'manage-organizations', 'view-activity-logs', 'manage-platform-settings', 'roles.manage', 'users.manage', 'organizations.manage', 'activity_logs.view', 'platform_settings.manage'];
            foreach ($criticalPermissions as $critical) {
                if (in_array($critical, $allowedPermissions, true) && !in_array($critical, $permissionsToSync, true)) {
                    $permissionsToSync[] = $critical;
                }
            }
        }

        $role->syncPermissions($permissionsToSync);

        // Invalidate Spatie Permission Cache immediately so backend authorization reflects changes live
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        ActivityLogService::log(
            'update',
            'users',
            "Super Admin memperbarui hak akses role {$role->name}",
            null,
            ['role' => $role->name, 'permissions' => $permissionsToSync]
        );

        return response()->json([
            'status' => 'success',
            'message' => "Hak akses role {$role->name} berhasil diperbarui.",
            'data' => [
                'id' => $role->id,
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
