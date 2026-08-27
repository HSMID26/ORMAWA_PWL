<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleAccessManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $editor;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Super Admin', 'Editor'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }
        foreach (['view-content', 'publish-content'] as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('Super Admin');
        $this->editor = User::factory()->create();
        $this->editor->assignRole('Editor');
    }

    public function test_only_super_admin_can_read_role_access(): void
    {
        $this->actingAs($this->editor, 'sanctum')
            ->getJson('/api/roles')
            ->assertForbidden();

        $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/roles')
            ->assertOk()
            ->assertJsonPath('data.roles.0.name', 'Super Admin');
    }

    public function test_super_admin_can_update_permissions_for_a_built_in_role(): void
    {
        $role = Role::findByName('Editor');

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->putJson('/api/roles/' . $role->id . '/permissions', [
                'permissions' => ['view-content', 'publish-content'],
            ])
            ->assertOk();

        $this->assertEqualsCanonicalizing(
            ['view-content', 'publish-content'],
            $response->json('data.permissions')
        );

        $this->assertTrue($role->fresh()->hasPermissionTo('publish-content'));
    }
}