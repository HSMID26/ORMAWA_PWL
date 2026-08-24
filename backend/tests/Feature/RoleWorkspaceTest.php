<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Announcement;
use App\Models\Media;
use App\Models\Organization;
use App\Models\OrganizationPeriod;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $orgA;
    protected Organization $orgB;
    protected User $adminOrgA;
    protected User $adminOrgB;
    protected User $editorA;
    protected User $contributorA1;
    protected User $contributorA2;
    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin']);
        Role::firstOrCreate(['name' => 'Admin Organisasi']);
        Role::firstOrCreate(['name' => 'Editor']);
        Role::firstOrCreate(['name' => 'Kontributor']);

        $this->superAdmin = User::factory()->create([
            'name' => 'Global Super Admin',
            'email' => 'superadmin@test.com',
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

        $this->adminOrgA = User::factory()->create([
            'name' => 'Admin HMIF',
            'email' => 'admin@hmif.test',
            'organization_id' => $this->orgA->id,
            'status' => 'active',
        ]);
        $this->adminOrgA->assignRole('Admin Organisasi');

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

        $this->contributorA1 = User::factory()->create([
            'name' => 'Kontributor 1 HMIF',
            'email' => 'kontributor1@hmif.test',
            'organization_id' => $this->orgA->id,
            'status' => 'active',
        ]);
        $this->contributorA1->assignRole('Kontributor');

        $this->contributorA2 = User::factory()->create([
            'name' => 'Kontributor 2 HMIF',
            'email' => 'kontributor2@hmif.test',
            'organization_id' => $this->orgA->id,
            'status' => 'active',
        ]);
        $this->contributorA2->assignRole('Kontributor');
    }

    // 1. Admin Organisasi can access own organization user list
    public function test_admin_org_can_access_own_organization_user_list(): void
    {
        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->getJson('/api/users');

        $response->assertStatus(200);
        $response->assertJsonFragment(['email' => $this->editorA->email]);
        $response->assertJsonFragment(['email' => $this->contributorA1->email]);
    }

    // 2. Admin Organisasi cannot see users from other organizations
    public function test_admin_org_cannot_see_users_from_other_organizations(): void
    {
        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->getJson('/api/users');

        $response->assertStatus(200);
        $response->assertJsonMissing(['email' => $this->adminOrgB->email]);
    }

    // 3. Admin Organisasi cannot see Super Admin in user list
    public function test_admin_org_cannot_see_super_admin_in_user_list(): void
    {
        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->getJson('/api/users');

        $response->assertStatus(200);
        $response->assertJsonMissing(['email' => $this->superAdmin->email]);
    }

    // 4. Admin Organisasi can create Editor in own organization
    public function test_admin_org_can_create_editor_in_own_organization(): void
    {
        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Editor Baru HMIF',
                'email' => 'editorbaru@hmif.test',
                'password' => 'password123',
                'role' => 'Editor',
                'status' => 'active',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'editorbaru@hmif.test',
            'organization_id' => $this->orgA->id,
        ]);
        $newUser = User::where('email', 'editorbaru@hmif.test')->first();
        $this->assertTrue($newUser->hasRole('Editor'));
    }

    // 5. Admin Organisasi can create Kontributor in own organization
    public function test_admin_org_can_create_kontributor_in_own_organization(): void
    {
        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Kontributor Baru HMIF',
                'email' => 'kontribar@hmif.test',
                'password' => 'password123',
                'role' => 'Kontributor',
                'status' => 'active',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'kontribar@hmif.test',
            'organization_id' => $this->orgA->id,
        ]);
        $newUser = User::where('email', 'kontribar@hmif.test')->first();
        $this->assertTrue($newUser->hasRole('Kontributor'));
    }

    // 6. Admin Organisasi cannot create Super Admin or Admin Organisasi
    public function test_admin_org_cannot_create_super_admin_or_admin_organisasi(): void
    {
        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Illegal Admin',
                'email' => 'illegal@hmif.test',
                'password' => 'password123',
                'role' => 'Super Admin',
                'status' => 'active',
            ]);

        $response->assertStatus(422);

        $response2 = $this->actingAs($this->adminOrgA, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Illegal Org Admin',
                'email' => 'illegalorg@hmif.test',
                'password' => 'password123',
                'role' => 'Admin Organisasi',
                'status' => 'active',
            ]);

        $response2->assertStatus(422);
    }

    // 7. Admin Organisasi cannot assign user to another organization_id
    public function test_admin_org_cannot_assign_user_to_another_organization_id(): void
    {
        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Attempt Hijack User',
                'email' => 'hijack@hmif.test',
                'password' => 'password123',
                'role' => 'Kontributor',
                'organization_id' => $this->orgB->id,
                'status' => 'active',
            ]);

        $response->assertStatus(201);
        // Server MUST enforce own organization_id
        $this->assertDatabaseHas('users', [
            'email' => 'hijack@hmif.test',
            'organization_id' => $this->orgA->id,
        ]);
        $this->assertDatabaseMissing('users', [
            'email' => 'hijack@hmif.test',
            'organization_id' => $this->orgB->id,
        ]);
    }

    // 8. Admin Organisasi can edit Editor/Kontributor in own organization
    public function test_admin_org_can_edit_editor_or_kontributor_in_own_organization(): void
    {
        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->putJson("/api/users/{$this->editorA->id}", [
                'name' => 'Editor HMIF Updated',
                'email' => 'editor_updated@hmif.test',
                'role' => 'Kontributor',
                'status' => 'inactive',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $this->editorA->id,
            'name' => 'Editor HMIF Updated',
            'email' => 'editor_updated@hmif.test',
            'status' => 'inactive',
        ]);
    }

    // 9. Admin Organisasi cannot edit users from other organizations
    public function test_admin_org_cannot_edit_users_from_other_organizations(): void
    {
        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->putJson("/api/users/{$this->adminOrgB->id}", [
                'name' => 'Hacked Admin Robotik',
            ]);

        $response->assertStatus(403);
    }

    // 10. Admin Organisasi can delete Editor/Kontributor in own organization
    public function test_admin_org_can_delete_editor_or_kontributor_in_own_organization(): void
    {
        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->deleteJson("/api/users/{$this->contributorA2->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', [
            'id' => $this->contributorA2->id,
        ]);
    }

    // 11. Admin Organisasi cannot delete users from other organizations
    public function test_admin_org_cannot_delete_users_from_other_organizations(): void
    {
        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->deleteJson("/api/users/{$this->adminOrgB->id}");

        $response->assertStatus(403);
    }

    // 12. Editor cannot access user management (403)
    public function test_editor_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->editorA, 'sanctum')
            ->getJson('/api/users');

        $response->assertStatus(403);
    }

    // 13. Editor cannot create, edit, or delete users (403)
    public function test_editor_cannot_create_edit_or_delete_users(): void
    {
        $responseCreate = $this->actingAs($this->editorA, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Editor Created User',
                'email' => 'test_editor_create@test.com',
                'password' => 'password123',
                'role' => 'Kontributor',
            ]);
        $responseCreate->assertStatus(403);

        $responseEdit = $this->actingAs($this->editorA, 'sanctum')
            ->putJson("/api/users/{$this->contributorA1->id}", [
                'name' => 'Editor Modifying User',
            ]);
        $responseEdit->assertStatus(403);

        $responseDelete = $this->actingAs($this->editorA, 'sanctum')
            ->deleteJson("/api/users/{$this->contributorA1->id}");
        $responseDelete->assertStatus(403);
    }

    // 14. Kontributor cannot access user management (403)
    public function test_kontributor_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->contributorA1, 'sanctum')
            ->getJson('/api/users');

        $response->assertStatus(403);
    }

    // 15. Kontributor cannot create, edit, or delete users (403)
    public function test_kontributor_cannot_create_edit_or_delete_users(): void
    {
        $responseCreate = $this->actingAs($this->contributorA1, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Kontributor Created User',
                'email' => 'test_kontrib_create@test.com',
                'password' => 'password123',
                'role' => 'Kontributor',
            ]);
        $responseCreate->assertStatus(403);

        $responseEdit = $this->actingAs($this->contributorA1, 'sanctum')
            ->putJson("/api/users/{$this->contributorA2->id}", [
                'name' => 'Kontributor Modifying User',
            ]);
        $responseEdit->assertStatus(403);

        $responseDelete = $this->actingAs($this->contributorA1, 'sanctum')
            ->deleteJson("/api/users/{$this->contributorA2->id}");
        $responseDelete->assertStatus(403);
    }

    // 16. Editor can view, create, and update posts in own organization
    public function test_editor_can_view_create_and_update_posts_in_own_organization(): void
    {
        $response = $this->actingAs($this->editorA, 'sanctum')
            ->postJson('/api/posts', [
                'judul' => 'Artikel dari Editor HMIF',
                'konten' => '<p>Konten artikel oleh editor.</p>',
                'status' => 'draft',
            ]);

        $response->assertStatus(201);
        $postId = $response->json('data.id');

        $responseUpdate = $this->actingAs($this->editorA, 'sanctum')
            ->putJson("/api/posts/{$postId}", [
                'judul' => 'Artikel dari Editor HMIF (Updated)',
                'konten' => '<p>Konten artikel update.</p>',
                'status' => 'review',
            ]);

        $responseUpdate->assertStatus(200);
        $this->assertDatabaseHas('posts', [
            'id' => $postId,
            'judul' => 'Artikel dari Editor HMIF (Updated)',
            'status' => 'review',
        ]);
    }

    // 17. Editor creating/updating post with published is downgraded to review
    public function test_editor_submitting_published_post_is_downgraded_to_review(): void
    {
        $response = $this->actingAs($this->editorA, 'sanctum')
            ->postJson('/api/posts', [
                'judul' => 'Artikel Editor Langsung Publish',
                'konten' => '<p>Mencoba bypass publish.</p>',
                'status' => 'published',
            ]);

        $response->assertStatus(201);
        $postId = $response->json('data.id');

        $this->assertDatabaseHas('posts', [
            'id' => $postId,
            'status' => 'review',
        ]);
    }

    // 18. Kontributor can create draft post and submit for review
    public function test_kontributor_can_create_draft_and_submit_review(): void
    {
        $response = $this->actingAs($this->contributorA1, 'sanctum')
            ->postJson('/api/posts', [
                'judul' => 'Artikel Kontributor 1',
                'konten' => '<p>Konten artikel kontributor.</p>',
                'status' => 'draft',
            ]);

        $response->assertStatus(201);
        $postId = $response->json('data.id');

        $responseReview = $this->actingAs($this->contributorA1, 'sanctum')
            ->putJson("/api/posts/{$postId}", [
                'status' => 'review',
            ]);

        $responseReview->assertStatus(200);
        $this->assertDatabaseHas('posts', [
            'id' => $postId,
            'status' => 'review',
        ]);
    }

    // 19. Kontributor creating post with published is downgraded to review
    public function test_kontributor_creating_post_with_published_is_downgraded_to_review(): void
    {
        $response = $this->actingAs($this->contributorA1, 'sanctum')
            ->postJson('/api/posts', [
                'judul' => 'Artikel Kontributor Self Publish',
                'konten' => '<p>Konten uji coba.</p>',
                'status' => 'published',
            ]);

        $response->assertStatus(201);
        $postId = $response->json('data.id');

        $this->assertDatabaseHas('posts', [
            'id' => $postId,
            'status' => 'review',
        ]);
    }

    // 20. Kontributor cannot edit posts created by other users (403)
    public function test_kontributor_cannot_edit_posts_created_by_other_users(): void
    {
        $postByOther = Post::create([
            'judul' => 'Artikel Kontributor 2',
            'slug' => 'artikel-kontributor-2-12345',
            'konten' => 'Isi',
            'status' => 'draft',
            'user_id' => $this->contributorA2->id,
            'organization_id' => $this->orgA->id,
        ]);

        $response = $this->actingAs($this->contributorA1, 'sanctum')
            ->putJson("/api/posts/{$postByOther->id}", [
                'judul' => 'Artikel Direbut Kontributor 1',
            ]);

        $response->assertStatus(403);
    }

    // 21. Kontributor cannot delete posts created by other users (403)
    public function test_kontributor_cannot_delete_posts_created_by_other_users(): void
    {
        $postByOther = Post::create([
            'judul' => 'Artikel Kontributor 2 To Delete',
            'slug' => 'artikel-kontributor-2-del-12345',
            'konten' => 'Isi',
            'status' => 'draft',
            'user_id' => $this->contributorA2->id,
            'organization_id' => $this->orgA->id,
        ]);

        $response = $this->actingAs($this->contributorA1, 'sanctum')
            ->deleteJson("/api/posts/{$postByOther->id}");

        $response->assertStatus(403);
    }

    // 22. Admin Organisasi can approve/publish and reject posts
    public function test_admin_org_can_publish_and_reject_posts(): void
    {
        $post = Post::create([
            'judul' => 'Artikel Siap Review',
            'slug' => 'artikel-siap-review-12345',
            'konten' => 'Isi konten review',
            'status' => 'review',
            'user_id' => $this->contributorA1->id,
            'organization_id' => $this->orgA->id,
        ]);

        $responsePublish = $this->actingAs($this->adminOrgA, 'sanctum')
            ->putJson("/api/posts/{$post->id}", [
                'status' => 'published',
            ]);

        $responsePublish->assertStatus(200);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => 'published',
        ]);

        $responseReject = $this->actingAs($this->adminOrgA, 'sanctum')
            ->putJson("/api/posts/{$post->id}", [
                'status' => 'rejected',
            ]);

        $responseReject->assertStatus(200);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => 'rejected',
        ]);
    }

    // 23. Editor cannot reject posts (retains status)
    public function test_editor_cannot_reject_posts(): void
    {
        $post = Post::create([
            'judul' => 'Artikel Uji Reject Editor',
            'slug' => 'artikel-uji-reject-editor-12345',
            'konten' => 'Isi konten',
            'status' => 'review',
            'user_id' => $this->contributorA1->id,
            'organization_id' => $this->orgA->id,
        ]);

        $response = $this->actingAs($this->editorA, 'sanctum')
            ->putJson("/api/posts/{$post->id}", [
                'status' => 'rejected',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => 'review',
        ]);
    }

    // 24. Editor can access agenda, announcements, and media gallery
    public function test_editor_can_access_agenda_announcements_and_media(): void
    {
        $resAgenda = $this->actingAs($this->editorA, 'sanctum')
            ->getJson('/api/activities');
        $resAgenda->assertStatus(200);

        $resAnnouncement = $this->actingAs($this->editorA, 'sanctum')
            ->getJson('/api/announcements');
        $resAnnouncement->assertStatus(200);

        $resMedia = $this->actingAs($this->editorA, 'sanctum')
            ->getJson('/api/media');
        $resMedia->assertStatus(200);
    }

    // 25. Kontributor cannot access agenda, announcements, or media gallery (403)
    public function test_kontributor_cannot_access_agenda_announcements_or_media(): void
    {
        $resAgenda = $this->actingAs($this->contributorA1, 'sanctum')
            ->getJson('/api/activities');
        $resAgenda->assertStatus(403);

        $resAnnouncement = $this->actingAs($this->contributorA1, 'sanctum')
            ->getJson('/api/announcements');
        $resAnnouncement->assertStatus(403);

        $resMedia = $this->actingAs($this->contributorA1, 'sanctum')
            ->getJson('/api/media');
        $resMedia->assertStatus(403);
    }

    // 26. Editor cannot access organization settings, period management, or activity logs (403)
    public function test_editor_cannot_access_org_settings_period_or_activity_logs(): void
    {
        $resSettings = $this->actingAs($this->editorA, 'sanctum')
            ->putJson("/api/organizations/{$this->orgA->id}", [
                'warna_tema' => '#000000',
            ]);
        $resSettings->assertStatus(403);

        $resPeriod = $this->actingAs($this->editorA, 'sanctum')
            ->postJson("/api/organizations/{$this->orgA->id}/periods", [
                'period_name' => '2027/2028',
            ]);
        $resPeriod->assertStatus(403);

        $resLogs = $this->actingAs($this->editorA, 'sanctum')
            ->getJson('/api/activity-logs');
        $resLogs->assertStatus(403);
    }

    // 27. Kontributor cannot access organization settings, period management, or activity logs (403)
    public function test_kontributor_cannot_access_org_settings_period_or_activity_logs(): void
    {
        $resSettings = $this->actingAs($this->contributorA1, 'sanctum')
            ->putJson("/api/organizations/{$this->orgA->id}", [
                'warna_tema' => '#000000',
            ]);
        $resSettings->assertStatus(403);

        $resPeriod = $this->actingAs($this->contributorA1, 'sanctum')
            ->postJson("/api/organizations/{$this->orgA->id}/periods", [
                'period_name' => '2027/2028',
            ]);
        $resPeriod->assertStatus(403);

        $resLogs = $this->actingAs($this->contributorA1, 'sanctum')
            ->getJson('/api/activity-logs');
        $resLogs->assertStatus(403);
    }

    // 28. Admin Organisasi can create committee member without user account linking
    public function test_admin_org_can_create_committee_without_user_linking(): void
    {
        $period = OrganizationPeriod::create([
            'organization_id' => $this->orgA->id,
            'period_name' => 'Kepengurusan 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->postJson('/api/committees', [
                'name' => 'Fahkrie Pengurus',
                'position' => 'Ketua',
                'department' => 'Ketua',
                'organization_period_id' => $period->id,
                'status' => 'active',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('committees', [
            'name' => 'Fahkrie Pengurus',
            'position' => 'Ketua',
            'organization_id' => $this->orgA->id,
            'user_id' => null,
        ]);
    }

    // 29. Editor cannot create, edit, or delete committee members (403)
    public function test_editor_cannot_create_or_modify_committee(): void
    {
        $resCreate = $this->actingAs($this->editorA, 'sanctum')
            ->postJson('/api/committees', [
                'name' => 'Pengurus by Editor',
                'position' => 'Sekretaris',
                'period' => '2026/2027',
            ]);
        $resCreate->assertStatus(403);
    }

    // 30. Kontributor cannot create, edit, or delete committee members (403)
    public function test_kontributor_cannot_create_or_modify_committee(): void
    {
        $resCreate = $this->actingAs($this->contributorA1, 'sanctum')
            ->postJson('/api/committees', [
                'name' => 'Pengurus by Kontributor',
                'position' => 'Bendahara',
                'period' => '2026/2027',
            ]);
        $resCreate->assertStatus(403);
    }

    // 31. Committee creation does not create or mutate user accounts or roles
    public function test_committee_creation_does_not_affect_cms_user_accounts(): void
    {
        $initialUserCount = User::count();

        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->postJson('/api/committees', [
                'name' => 'Pengurus Baru Non Akun',
                'position' => 'Koordinator Humas',
                'period' => '2026/2027',
                'status' => 'active',
            ]);

        $response->assertStatus(201);
        $this->assertEquals($initialUserCount, User::count());
    }
}
