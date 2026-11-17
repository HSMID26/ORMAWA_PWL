<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleMutationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $orgA;
    protected Organization $orgB;
    protected User $adminOrgA1;
    protected User $adminOrgA2;
    protected User $adminOrgB;
    protected User $editorA;
    protected User $contributorA;
    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin']);
        Role::firstOrCreate(['name' => 'Admin Organisasi']);
        Role::firstOrCreate(['name' => 'Editor']);
        Role::firstOrCreate(['name' => 'Kontributor']);

        $this->superAdmin = User::factory()->create([
            'name' => 'Super Admin System',
            'email' => 'superadmin@system.test',
            'organization_id' => null,
            'status' => 'active',
        ]);
        $this->superAdmin->assignRole('Super Admin');

        $this->orgA = Organization::create([
            'nama' => 'Himpunan Mahasiswa Informatika',
            'jenis' => 'HMPS',
            'subdomain' => 'hmif',
            'status' => 'active',
        ]);

        $this->orgB = Organization::create([
            'nama' => 'Unit Kegiatan Mahasiswa Robotika',
            'jenis' => 'UKM',
            'subdomain' => 'robotik',
            'status' => 'active',
        ]);

        $this->adminOrgA1 = User::factory()->create([
            'name' => 'Fahkrie Al',
            'email' => 'fahkrie@hmif.test',
            'organization_id' => $this->orgA->id,
            'status' => 'active',
        ]);
        $this->adminOrgA1->assignRole('Admin Organisasi');

        $this->adminOrgA2 = User::factory()->create([
            'name' => 'Second Admin HMIF',
            'email' => 'admin2@hmif.test',
            'organization_id' => $this->orgA->id,
            'status' => 'active',
        ]);
        $this->adminOrgA2->assignRole('Admin Organisasi');

        $this->adminOrgB = User::factory()->create([
            'name' => 'Admin Robotik',
            'email' => 'admin@robotik.test',
            'organization_id' => $this->orgB->id,
            'status' => 'active',
        ]);
        $this->adminOrgB->assignRole('Admin Organisasi');

        $this->editorA = User::factory()->create([
            'name' => 'Editor HMIF',
            'email' => 'editor@hmif.test',
            'organization_id' => $this->orgA->id,
            'status' => 'active',
        ]);
        $this->editorA->assignRole('Editor');

        $this->contributorA = User::factory()->create([
            'name' => 'Kontributor HMIF',
            'email' => 'kontributor@hmif.test',
            'organization_id' => $this->orgA->id,
            'status' => 'active',
        ]);
        $this->contributorA->assignRole('Kontributor');
    }

    // 1. Admin Organisasi edit own name -> role tetap Admin Organisasi
    public function test_admin_org_edit_own_name_preserves_admin_org_role(): void
    {
        $response = $this->actingAs($this->adminOrgA1, 'sanctum')
            ->putJson("/api/users/{$this->adminOrgA1->id}", [
                'name' => 'Admin Futsal',
            ]);

        $response->assertStatus(200);
        $this->adminOrgA1->refresh();
        $this->assertEquals('Admin Futsal', $this->adminOrgA1->name);
        $this->assertTrue($this->adminOrgA1->hasRole('Admin Organisasi'));
        $this->assertFalse($this->adminOrgA1->hasRole('Editor'));
    }

    // 2. Admin Organisasi edit own email -> role tetap Admin Organisasi
    public function test_admin_org_edit_own_email_preserves_admin_org_role(): void
    {
        $response = $this->actingAs($this->adminOrgA1, 'sanctum')
            ->putJson("/api/users/{$this->adminOrgA1->id}", [
                'email' => 'new_admin_email@hmif.test',
            ]);

        $response->assertStatus(200);
        $this->adminOrgA1->refresh();
        $this->assertEquals('new_admin_email@hmif.test', $this->adminOrgA1->email);
        $this->assertTrue($this->adminOrgA1->hasRole('Admin Organisasi'));
        $this->assertFalse($this->adminOrgA1->hasRole('Editor'));
    }

    // 3. Admin Organisasi edit own profile tanpa role -> role tetap Admin Organisasi
    public function test_admin_org_edit_own_profile_without_role_field_preserves_role(): void
    {
        $response = $this->actingAs($this->adminOrgA1, 'sanctum')
            ->putJson("/api/users/{$this->adminOrgA1->id}", [
                'name' => 'Admin Profile Updated',
                'password' => 'newpassword123',
            ]);

        $response->assertStatus(200);
        $this->adminOrgA1->refresh();
        $this->assertTrue($this->adminOrgA1->hasRole('Admin Organisasi'));
        $this->assertFalse($this->adminOrgA1->hasRole('Editor'));
    }

    // 4. Request dengan role=Editor terhadap akun Admin Organisasi -> 403 dan role tetap Admin Organisasi
    public function test_request_with_role_editor_on_admin_organisasi_is_rejected(): void
    {
        $response = $this->actingAs($this->adminOrgA1, 'sanctum')
            ->putJson("/api/users/{$this->adminOrgA1->id}", [
                'name' => 'Attempt Downgrade Self',
                'role' => 'Editor',
            ]);

        $response->assertStatus(403);
        $this->adminOrgA1->refresh();
        $this->assertTrue($this->adminOrgA1->hasRole('Admin Organisasi'));
        $this->assertFalse($this->adminOrgA1->hasRole('Editor'));
    }

    // 5. Admin Organisasi tidak dapat mengubah role Admin Organisasi lain
    public function test_admin_org_cannot_edit_or_mutate_role_of_another_admin_org(): void
    {
        $response = $this->actingAs($this->adminOrgA1, 'sanctum')
            ->putJson("/api/users/{$this->adminOrgA2->id}", [
                'name' => 'Attempt Modify Other Admin',
                'role' => 'Kontributor',
            ]);

        $response->assertStatus(403);
        $this->adminOrgA2->refresh();
        $this->assertTrue($this->adminOrgA2->hasRole('Admin Organisasi'));
    }

    // 6. Admin Organisasi tidak dapat mengedit Super Admin
    public function test_admin_org_cannot_edit_super_admin(): void
    {
        $response = $this->actingAs($this->adminOrgA1, 'sanctum')
            ->putJson("/api/users/{$this->superAdmin->id}", [
                'name' => 'Hacked Super Admin',
            ]);

        $response->assertStatus(403);
        $this->superAdmin->refresh();
        $this->assertEquals('Super Admin System', $this->superAdmin->name);
    }

    // 7. Admin Organisasi dapat edit profile Editor tanpa mengubah role secara tidak sengaja
    public function test_admin_org_can_edit_editor_profile_without_mutating_role(): void
    {
        $response = $this->actingAs($this->adminOrgA1, 'sanctum')
            ->putJson("/api/users/{$this->editorA->id}", [
                'name' => 'Editor HMIF Renamed',
                'email' => 'renamed_editor@hmif.test',
            ]);

        $response->assertStatus(200);
        $this->editorA->refresh();
        $this->assertEquals('Editor HMIF Renamed', $this->editorA->name);
        $this->assertEquals('renamed_editor@hmif.test', $this->editorA->email);
        $this->assertTrue($this->editorA->hasRole('Editor'));
    }

    // 8. Admin Organisasi dapat edit profile Kontributor tanpa mengubah role secara tidak sengaja
    public function test_admin_org_can_edit_kontributor_profile_without_mutating_role(): void
    {
        $response = $this->actingAs($this->adminOrgA1, 'sanctum')
            ->putJson("/api/users/{$this->contributorA->id}", [
                'name' => 'Kontributor HMIF Renamed',
                'email' => 'renamed_kontrib@hmif.test',
            ]);

        $response->assertStatus(200);
        $this->contributorA->refresh();
        $this->assertEquals('Kontributor HMIF Renamed', $this->contributorA->name);
        $this->assertEquals('renamed_kontrib@hmif.test', $this->contributorA->email);
        $this->assertTrue($this->contributorA->hasRole('Kontributor'));
        $this->assertFalse($this->contributorA->hasRole('Editor'));
    }

    // 9. Editor tidak dapat mengakses user management
    public function test_editor_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->editorA, 'sanctum')
            ->getJson('/api/users');

        $response->assertStatus(403);

        $responseUpdate = $this->actingAs($this->editorA, 'sanctum')
            ->putJson("/api/users/{$this->contributorA->id}", [
                'name' => 'Editor Hacking User',
            ]);

        $responseUpdate->assertStatus(403);
    }

    // 10. Kontributor tidak dapat mengakses user management
    public function test_kontributor_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->contributorA, 'sanctum')
            ->getJson('/api/users');

        $response->assertStatus(403);

        $responseUpdate = $this->actingAs($this->contributorA, 'sanctum')
            ->putJson("/api/users/{$this->editorA->id}", [
                'name' => 'Kontributor Hacking User',
            ]);

        $responseUpdate->assertStatus(403);
    }

    // 11. Cross-tenant user update tetap ditolak
    public function test_cross_tenant_user_update_is_rejected(): void
    {
        $response = $this->actingAs($this->adminOrgA1, 'sanctum')
            ->putJson("/api/users/{$this->adminOrgB->id}", [
                'name' => 'Org A Hacking Org B User',
            ]);

        $response->assertStatus(403);
    }

    // 12. organization_id user tidak berubah ketika profile diedit
    public function test_organization_id_does_not_change_when_profile_edited(): void
    {
        $response = $this->actingAs($this->adminOrgA1, 'sanctum')
            ->putJson("/api/users/{$this->editorA->id}", [
                'name' => 'Editor Hijack Attempt',
                'organization_id' => $this->orgB->id,
            ]);

        $response->assertStatus(200);
        $this->editorA->refresh();
        $this->assertEquals((int)$this->orgA->id, (int)$this->editorA->organization_id);
    }
}
