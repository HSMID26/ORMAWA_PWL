<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const DEFAULTS = [
        'Super Admin' => ['work_programs.view', 'work_programs.create', 'work_programs.update', 'work_programs.delete'],
        'Admin Organisasi' => ['work_programs.view', 'work_programs.create', 'work_programs.update', 'work_programs.delete'],
        'Editor' => ['work_programs.view', 'work_programs.create', 'work_programs.update'],
    ];

    public function up(): void
    {
        foreach (array_unique(array_merge(...array_values(self::DEFAULTS))) as $permissionName) {
            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);

            foreach (self::DEFAULTS as $roleName => $permissions) {
                if (!in_array($permissionName, $permissions, true)) {
                    continue;
                }

                $role = Role::query()
                    ->where('name', $roleName)
                    ->where('guard_name', 'web')
                    ->first();

                if ($role) {
                    $role->givePermissionTo($permission);
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::query()
            ->whereIn('name', array_unique(array_merge(...array_values(self::DEFAULTS))))
            ->where('guard_name', 'web')
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
