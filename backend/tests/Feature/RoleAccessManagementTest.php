<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\RoleController;
use App\Models\Activity;
use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Media;
use App\Models\Organization;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RoleAccessManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $adminOrg;
    protected User $editor;
    protected User $contributor;
    protected Organization $org;
    protected Organization $orgB;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        // Initialize all permissions and baseline defaults
        RoleController::ensureDefaultPermissions();

        $this->org = Organization::create([
            'nama' => 'UKM Robotik ITI',
            'jenis' => 'UKM',
            'subdomain' => 'robotik',
            'status' => 'active',
        ]);

        $this->orgB = Organization::create([
            'nama' => 'BEM FTI ITI',
            'jenis' => 'BEM',
            'subdomain' => 'bemfti',
            'status' => 'active',
        ]);

        $this->superAdmin = User::create([
            'name' => 'Super Admin PKA',
            'email' => 'pka@iti.ac.id',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $this->superAdmin->assignRole('Super Admin');

        $this->adminOrg = User::create([
            'name' => 'Admin Robotik',
            'email' => 'admin@robotik.iti.ac.id',
            'password' => bcrypt('password123'),
            'organization_id' => $this->org->id,
            'status' => 'active',
        ]);
        $this->adminOrg->assignRole('Admin Organisasi');

        $this->editor = User::create([
            'name' => 'Editor Robotik',
            'email' => 'editor@robotik.iti.ac.id',
            'password' => bcrypt('password123'),
            'organization_id' => $this->org->id,
            'status' => 'active',
        ]);
        $this->editor->assignRole('Editor');

        $this->contributor = User::create([
            'name' => 'Kontributor Robotik',
            'email' => 'kontributor@robotik.iti.ac.id',
            'password' => bcrypt('password123'),
            'organization_id' => $this->org->id,
            'status' => 'active',
        ]);
        $this->contributor->assignRole('Kontributor');
    }

    public function test_only_super_admin_can_read_role_access(): void
    {
        // 1. Editor gets 403
        $this->actingAs($this->editor, 'sanctum')
            ->getJson('/api/roles')
            ->assertForbidden();

        // 2. Admin Organisasi gets 403
        $this->actingAs($this->adminOrg, 'sanctum')
            ->getJson('/api/roles')
            ->assertForbidden();

        // 3. Contributor gets 403
        $this->actingAs($this->contributor, 'sanctum')
            ->getJson('/api/roles')
            ->assertForbidden();

        // 4. Guest gets 401
        app('auth')->forgetGuards();
        $this->getJson('/api/roles')
            ->assertUnauthorized();

        // 5. Super Admin gets 200 OK with accurate populated permissions (not 0)
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/roles')
            ->assertOk();

        $response->assertJsonStructure([
            'status',
            'data' => [
                'roles',
                'permissions',
                'groups',
            ]
        ]);

        $rolesData = $response->json('data.roles');
        $adminOrgRole = collect($rolesData)->firstWhere('name', 'Admin Organisasi');
        $this->assertNotNull($adminOrgRole);
        $this->assertNotEmpty($adminOrgRole['permissions']);
        $this->assertContains('gallery.create', $adminOrgRole['permissions']);
    }

    public function test_super_admin_can_update_permissions_for_a_built_in_role(): void
    {
        $role = Role::findByName('Editor');

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->putJson('/api/roles/' . $role->id . '/permissions', [
                'permissions' => ['posts.view', 'posts.publish'],
            ])
            ->assertOk();

        $this->assertEqualsCanonicalizing(
            ['posts.view', 'posts.publish'],
            $response->json('data.permissions')
        );

        $this->assertTrue($role->fresh()->hasPermissionTo('posts.publish'));
        $this->assertTrue($role->fresh()->hasPermissionTo('posts.view'));
        $this->assertFalse($role->fresh()->hasPermissionTo('posts.delete'));
    }

    public function test_gallery_create_permission_strictly_enforces_live_backend_upload(): void
    {
        // 1. Initially, Admin Organisasi has gallery.create -> Upload succeeds (200)
        $image = UploadedFile::fake()->image('test_doc.jpg', 600, 400);

        $res1 = $this->actingAs($this->adminOrg, 'sanctum')
            ->postJson('/api/upload-image', [
                'image' => $image,
                'title' => 'Dokumentasi Robotik Lomba 1',
            ]);
        $res1->assertOk();

        // 2. Super Admin revokes gallery.create from Admin Organisasi
        $adminRole = Role::findByName('Admin Organisasi', 'web');
        $newPermissions = collect($adminRole->permissions->pluck('name'))
            ->reject(fn ($p) => $p === 'gallery.create')
            ->values()
            ->all();

        $this->actingAs($this->superAdmin, 'sanctum')
            ->putJson('/api/roles/' . $adminRole->id . '/permissions', [
                'permissions' => $newPermissions,
            ])
            ->assertOk();

        // 3. Now Admin Organisasi tries to upload image again -> 403 Forbidden!
        $image2 = UploadedFile::fake()->image('test_doc2.jpg', 600, 400);

        $res2 = $this->actingAs($this->adminOrg, 'sanctum')
            ->postJson('/api/upload-image', [
                'image' => $image2,
                'title' => 'Dokumentasi Robotik Lomba 2',
            ]);
        $res2->assertForbidden();

        // 4. Super Admin restores gallery.create to Admin Organisasi
        $newPermissions[] = 'gallery.create';
        $this->actingAs($this->superAdmin, 'sanctum')
            ->putJson('/api/roles/' . $adminRole->id . '/permissions', [
                'permissions' => $newPermissions,
            ])
            ->assertOk();

        // 5. Admin Organisasi tries again -> 200 OK!
        $image3 = UploadedFile::fake()->image('test_doc3.jpg', 600, 400);

        $res3 = $this->actingAs($this->adminOrg, 'sanctum')
            ->postJson('/api/upload-image', [
                'image' => $image3,
                'title' => 'Dokumentasi Robotik Lomba 3',
            ]);
        $res3->assertOk();
    }

    public function test_gallery_delete_permission_strictly_enforces_live_deletion(): void
    {
        $media = Media::create([
            'organization_id' => $this->org->id,
            'user_id' => $this->adminOrg->id,
            'name' => 'Foto Robotik',
            'filename' => 'foto.webp',
            'path' => 'gallery-images/foto.webp',
            'mime_type' => 'image/webp',
            'size' => 1024,
        ]);

        // Revoke gallery.delete from Admin Organisasi
        $adminRole = Role::findByName('Admin Organisasi', 'web');
        $adminRole->revokePermissionTo('gallery.delete');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Attempt delete -> 403 Forbidden
        $this->actingAs($this->adminOrg, 'sanctum')
            ->deleteJson('/api/media/' . $media->id)
            ->assertForbidden();

        // Grant gallery.delete back
        $adminRole->givePermissionTo('gallery.delete');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Attempt delete -> 200 OK
        $this->actingAs($this->adminOrg, 'sanctum')
            ->deleteJson('/api/media/' . $media->id)
            ->assertOk();
    }

    public function test_document_permissions_strictly_enforced(): void
    {
        $docFile = UploadedFile::fake()->create('sk_organisasi.pdf', 100, 'application/pdf');

        // Revoke documents.create from Editor
        $editorRole = Role::findByName('Editor', 'web');
        $editorRole->revokePermissionTo('documents.create');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($this->editor, 'sanctum')
            ->postJson('/api/documents', [
                'name' => 'SK Kepengurusan',
                'file' => $docFile,
                'category' => 'SK',
            ])
            ->assertForbidden();

        // Grant documents.create
        $editorRole->givePermissionTo('documents.create');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($this->editor, 'sanctum')
            ->postJson('/api/documents', [
                'name' => 'SK Kepengurusan',
                'file' => $docFile,
                'category' => 'SK',
            ])
            ->assertCreated();
    }

    public function test_posts_publish_and_contributor_ownership_rules(): void
    {
        // Contributor without posts.publish submitting status = 'published' -> automatically fallback to review
        $response = $this->actingAs($this->contributor, 'sanctum')
            ->postJson('/api/posts', [
                'judul' => 'Inovasi Robotik Terbaru',
                'konten' => 'Konten lengkap artikel...',
                'status' => 'published',
            ])
            ->assertCreated();

        $this->assertEquals('review', $response->json('data.status'));
        $postId = $response->json('data.id');

        // Contributor editing another user's post -> 403 Forbidden
        $otherPost = Post::create([
            'organization_id' => $this->org->id,
            'user_id' => $this->adminOrg->id,
            'judul' => 'Berita Admin',
            'slug' => 'berita-admin-123',
            'konten' => 'Isi berita admin',
            'status' => 'published',
        ]);

        $this->actingAs($this->contributor, 'sanctum')
            ->putJson('/api/posts/' . $otherPost->id, [
                'judul' => 'Hacked title',
            ])
            ->assertForbidden();

        // Admin Organisasi with posts.publish can publish
        $this->actingAs($this->adminOrg, 'sanctum')
            ->putJson('/api/posts/' . $postId, [
                'status' => 'published',
            ])
            ->assertOk();

        $this->assertEquals('published', Post::find($postId)->status);
    }

    public function test_structure_manage_permission_strictly_enforced(): void
    {
        // Contributor without structure.manage attempting to add committee -> 403
        $this->actingAs($this->contributor, 'sanctum')
            ->postJson('/api/committees', [
                'name' => 'Ketua Himpunan',
                'position' => 'Ketua Umum',
                'period' => '2026/2027',
            ])
            ->assertForbidden();

        // Admin Organisasi with structure.manage -> 201 Created
        $this->actingAs($this->adminOrg, 'sanctum')
            ->postJson('/api/committees', [
                'name' => 'Ketua Himpunan',
                'position' => 'Ketua Umum',
                'period' => '2026/2027',
            ])
            ->assertCreated();
    }

    public function test_user_management_tenant_isolation(): void
    {
        $userInOrgB = User::create([
            'name' => 'Anggota BEM',
            'email' => 'anggota@bemfti.iti.ac.id',
            'password' => bcrypt('password123'),
            'organization_id' => $this->orgB->id,
            'status' => 'active',
        ]);
        $userInOrgB->assignRole('Kontributor');

        // Admin of Org A tries to edit user in Org B -> 403 Forbidden
        $this->actingAs($this->adminOrg, 'sanctum')
            ->putJson('/api/users/' . $userInOrgB->id, [
                'name' => 'Hacked Name',
            ])
            ->assertForbidden();

        // Super Admin can edit user in Org B -> 200 OK
        $this->actingAs($this->superAdmin, 'sanctum')
            ->putJson('/api/users/' . $userInOrgB->id, [
                'name' => 'Updated by Super Admin',
            ])
            ->assertOk();
    }

    public function test_super_admin_critical_governance_permissions_cannot_be_stripped(): void
    {
        $role = Role::findByName('Super Admin');

        // Attempt to strip all permissions
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->putJson('/api/roles/' . $role->id . '/permissions', [
                'permissions' => ['posts.view'],
            ]);

        $response->assertOk();

        // Critical permissions must still be present
        $freshRole = $role->fresh();
        $this->assertTrue($freshRole->hasPermissionTo('users.manage'));
        $this->assertTrue($freshRole->hasPermissionTo('organizations.manage'));
        $this->assertTrue($freshRole->hasPermissionTo('roles.manage'));
        $this->assertTrue($freshRole->hasPermissionTo('activity_logs.view'));
        $this->assertTrue($freshRole->hasPermissionTo('platform_settings.manage'));
    }

    public function test_activity_log_is_generated_on_permission_update(): void
    {
        $role = Role::findByName('Kontributor');

        $this->actingAs($this->superAdmin, 'sanctum')
            ->putJson('/api/roles/' . $role->id . '/permissions', [
                'permissions' => ['posts.view', 'posts.create'],
            ])
            ->assertOk();

        $log = ActivityLog::where('action', 'update')
            ->where('module', 'users')
            ->latest()
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString('Super Admin memperbarui hak akses role Kontributor', $log->description);
    }

    public function test_gallery_view_and_update_reproducibility(): void
    {
        $adminRole = Role::findByName('Admin Organisasi');

        $media = Media::create([
            'organization_id' => $this->org->id,
            'user_id' => $this->adminOrg->id,
            'name' => 'Foto Robotik',
            'filename' => 'foto.webp',
            'path' => 'gallery-images/foto.webp',
            'mime_type' => 'image/webp',
            'size' => 1024,
        ]);

        // 1. Initial: gallery.view is granted -> 200 OK
        $this->actingAs($this->adminOrg, 'sanctum')
            ->getJson('/api/media')
            ->assertOk();

        // 2. Revoke gallery.view from Admin Organisasi
        $adminRole->revokePermissionTo('gallery.view');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($this->adminOrg, 'sanctum')
            ->getJson('/api/media')
            ->assertForbidden();

        // 3. Re-grant gallery.view & test gallery.update
        $adminRole->givePermissionTo('gallery.view');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($this->adminOrg, 'sanctum')
            ->getJson('/api/media')
            ->assertOk();

        // Update photo metadata with gallery.update
        $this->actingAs($this->adminOrg, 'sanctum')
            ->putJson('/api/media/' . $media->id, [
                'caption' => 'Updated caption',
            ])
            ->assertOk();

        $this->assertEquals('Updated caption', $media->fresh()->caption);

        // Revoke gallery.update -> 403 Forbidden
        $adminRole->revokePermissionTo('gallery.update');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($this->adminOrg, 'sanctum')
            ->putJson('/api/media/' . $media->id, [
                'caption' => 'Should fail',
            ])
            ->assertForbidden();
    }

    public function test_agenda_and_announcements_reproducibility(): void
    {
        $adminRole = Role::findByName('Admin Organisasi');

        $activity = Activity::create([
            'organization_id' => $this->org->id,
            'user_id' => $this->adminOrg->id,
            'judul' => 'Workshop Robotika',
            'status' => 'published',
        ]);

        // 1. Initial: agenda.view is granted -> 200 OK
        $this->actingAs($this->adminOrg, 'sanctum')
            ->getJson('/api/activities')
            ->assertOk();

        // 2. Revoke agenda.view -> 403 Forbidden
        $adminRole->revokePermissionTo('agenda.view');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($this->adminOrg, 'sanctum')
            ->getJson('/api/activities')
            ->assertForbidden();

        // 3. Re-grant agenda.view and test agenda.delete
        $adminRole->givePermissionTo('agenda.view');
        $adminRole->revokePermissionTo('agenda.delete');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($this->adminOrg, 'sanctum')
            ->deleteJson('/api/activities/' . $activity->id)
            ->assertForbidden();

        $adminRole->givePermissionTo('agenda.delete');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($this->adminOrg, 'sanctum')
            ->deleteJson('/api/activities/' . $activity->id)
            ->assertOk();
    }

    public function test_structure_view_reproducibility(): void
    {
        $adminRole = Role::findByName('Admin Organisasi');

        // Initial: structure.view granted -> 200 OK
        $this->actingAs($this->adminOrg, 'sanctum')
            ->getJson('/api/committees')
            ->assertOk();

        // Revoke structure.view -> 403 Forbidden
        $adminRole->revokePermissionTo('structure.view');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($this->adminOrg, 'sanctum')
            ->getJson('/api/committees')
            ->assertForbidden();
    }

    public function test_users_view_reproducibility(): void
    {
        $adminRole = Role::findByName('Admin Organisasi');

        // Initial: users.view granted -> 200 OK
        $this->actingAs($this->adminOrg, 'sanctum')
            ->getJson('/api/users')
            ->assertOk();

        // Revoke users.view -> 403 Forbidden
        $adminRole->revokePermissionTo('users.view');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($this->adminOrg, 'sanctum')
            ->getJson('/api/users')
            ->assertForbidden();
    }

    public function test_activity_logs_view_reproducibility(): void
    {
        $adminRole = Role::findByName('Admin Organisasi');

        // Initial: activity_logs.view granted -> 200 OK
        $this->actingAs($this->adminOrg, 'sanctum')
            ->getJson('/api/activity-logs')
            ->assertOk();

        // Revoke activity_logs.view -> 403 Forbidden
        $adminRole->revokePermissionTo('activity_logs.view');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($this->adminOrg, 'sanctum')
            ->getJson('/api/activity-logs')
            ->assertForbidden();
    }

    public function test_cross_tenant_content_isolation_reproducibility(): void
    {
        $postInOrgB = Post::create([
            'organization_id' => $this->orgB->id,
            'user_id' => $this->superAdmin->id,
            'judul' => 'Berita BEM FTI',
            'slug' => 'berita-bem-fti-1',
            'konten' => 'Isi berita organisasi B',
            'status' => 'published',
        ]);

        $mediaInOrgB = Media::create([
            'organization_id' => $this->orgB->id,
            'user_id' => $this->superAdmin->id,
            'name' => 'Foto BEM FTI',
            'filename' => 'foto-bem.webp',
            'path' => 'gallery-images/foto-bem.webp',
            'mime_type' => 'image/webp',
            'size' => 2048,
        ]);

        // Admin of Org A has posts.update & gallery.delete = true, but trying to access Org B resources must return 403
        $this->actingAs($this->adminOrg, 'sanctum')
            ->putJson('/api/posts/' . $postInOrgB->id, [
                'judul' => 'Cross tenant update attempt',
            ])
            ->assertForbidden();

        $this->actingAs($this->adminOrg, 'sanctum')
            ->deleteJson('/api/media/' . $mediaInOrgB->id)
            ->assertForbidden();
    }
}
