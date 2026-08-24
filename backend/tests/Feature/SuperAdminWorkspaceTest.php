<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Media;
use App\Models\Organization;
use App\Models\OrganizationPeriod;
use App\Models\OrganizationRegistration;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuperAdminWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $adminOrgA;
    protected User $editorA;
    protected User $contributorA;
    protected User $adminOrgB;
    protected Organization $orgA;
    protected Organization $orgB;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Ensure Roles exist
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin Organisasi', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Kontributor', 'guard_name' => 'web']);

        // 2. Setup Organizations
        $this->orgA = Organization::create([
            'nama' => 'UKM Robotik',
            'jenis' => 'UKM',
            'subdomain' => 'robotik',
            'status' => 'active',
            'warna_tema' => '#1d4ed8',
        ]);

        $this->orgB = Organization::create([
            'nama' => 'UKM Musik',
            'jenis' => 'UKM',
            'subdomain' => 'musik',
            'status' => 'active',
            'warna_tema' => '#dc2626',
        ]);

        // 3. Setup Super Admin
        $this->superAdmin = User::create([
            'name' => 'Platform Super Admin',
            'email' => 'superadmin@platform.test',
            'password' => bcrypt('password123'),
            'organization_id' => null,
            'status' => 'active',
        ]);
        $this->superAdmin->assignRole('Super Admin');

        // 4. Setup Org A Users
        $this->adminOrgA = User::create([
            'name' => 'Admin Robotik',
            'email' => 'admin@robotik.test',
            'password' => bcrypt('password123'),
            'organization_id' => $this->orgA->id,
            'status' => 'active',
        ]);
        $this->adminOrgA->assignRole('Admin Organisasi');

        $this->editorA = User::create([
            'name' => 'Editor Robotik',
            'email' => 'editor@robotik.test',
            'password' => bcrypt('password123'),
            'organization_id' => $this->orgA->id,
            'status' => 'active',
        ]);
        $this->editorA->assignRole('Editor');

        $this->contributorA = User::create([
            'name' => 'Kontributor Robotik',
            'email' => 'kontributor@robotik.test',
            'password' => bcrypt('password123'),
            'organization_id' => $this->orgA->id,
            'status' => 'active',
        ]);
        $this->contributorA->assignRole('Kontributor');

        // 5. Setup Org B Admin
        $this->adminOrgB = User::create([
            'name' => 'Admin Musik',
            'email' => 'admin@musik.test',
            'password' => bcrypt('password123'),
            'organization_id' => $this->orgB->id,
            'status' => 'active',
        ]);
        $this->adminOrgB->assignRole('Admin Organisasi');
    }

    // ─── 1. DASHBOARD ACCESS & METRICS ──────────────────────────────────────

    public function test_super_admin_can_access_global_dashboard(): void
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/super-admin/dashboard');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'organizations' => ['total', 'active', 'inactive', 'pending_registrations'],
                'periods' => ['active', 'pending', 'expired', 'expiring_soon'],
                'users' => ['total', 'active', 'inactive', 'org_admins', 'editors', 'contributors'],
                'content' => [
                    'posts' => ['total', 'published', 'review', 'draft', 'rejected'],
                    'agendas' => ['total', 'upcoming', 'today', 'past'],
                    'announcements' => ['total', 'published', 'draft', 'archived'],
                    'media' => ['total', 'images', 'documents'],
                ],
                'governance_alerts',
                'recent_activities',
            ],
        ]);

        $this->assertEquals(2, $response->json('data.organizations.total'));
    }

    public function test_admin_organization_cannot_access_super_admin_dashboard(): void
    {
        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->getJson('/api/super-admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_editor_cannot_access_super_admin_dashboard(): void
    {
        $response = $this->actingAs($this->editorA, 'sanctum')
            ->getJson('/api/super-admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_contributor_cannot_access_super_admin_dashboard(): void
    {
        $response = $this->actingAs($this->contributorA, 'sanctum')
            ->getJson('/api/super-admin/dashboard');

        $response->assertStatus(403);
    }

    // ─── 2. ORGANIZATION MANAGEMENT ─────────────────────────────────────────

    public function test_super_admin_can_list_all_organizations_with_enrichment(): void
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/organizations');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        $this->assertNotNull($response->json('data.0.admin_user'));
        $this->assertArrayHasKey('users_count', $response->json('data.0'));
    }

    public function test_super_admin_can_view_organization_detail(): void
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson("/api/organizations/{$this->orgA->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'id' => $this->orgA->id,
                'nama' => 'UKM Robotik',
            ],
        ]);
        $this->assertArrayHasKey('editors_count', $response->json('data'));
        $this->assertArrayHasKey('contributors_count', $response->json('data'));
    }

    public function test_super_admin_can_activate_and_deactivate_organization(): void
    {
        // 1. Deactivate
        $resDeact = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson("/api/organizations/{$this->orgA->id}/deactivate");

        $resDeact->assertStatus(200);
        $this->assertDatabaseHas('organizations', [
            'id' => $this->orgA->id,
            'status' => 'inactive',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'deactivate',
            'module' => 'organizations',
        ]);

        // 2. Activate
        $resAct = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson("/api/organizations/{$this->orgA->id}/activate");

        $resAct->assertStatus(200);
        $this->assertDatabaseHas('organizations', [
            'id' => $this->orgA->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_org_cannot_deactivate_organization(): void
    {
        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->postJson("/api/organizations/{$this->orgA->id}/deactivate");

        $response->assertStatus(403);
    }

    // ─── 3. ORGANIZATION ADMIN MANAGEMENT ───────────────────────────────────

    public function test_super_admin_can_list_organization_admins(): void
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/users?role=Admin Organisasi');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_super_admin_can_create_organization_admin(): void
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'New Admin Robotik 2',
                'email' => 'newadmin@robotik.test',
                'password' => 'password123',
                'role' => 'Admin Organisasi',
                'organization_id' => $this->orgA->id,
                'status' => 'active',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'newadmin@robotik.test',
            'organization_id' => $this->orgA->id,
            'status' => 'active',
        ]);

        $newUser = User::where('email', 'newadmin@robotik.test')->first();
        $this->assertTrue($newUser->hasRole('Admin Organisasi'));

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'create',
            'module' => 'users',
        ]);
    }

    public function test_non_super_admin_cannot_create_organization_admin(): void
    {
        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Attempted Org Admin',
                'email' => 'attempted@robotik.test',
                'password' => 'password123',
                'role' => 'Admin Organisasi',
                'organization_id' => $this->orgA->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_super_admin_can_activate_deactivate_and_reset_password_for_user(): void
    {
        // 1. Deactivate
        $resDeact = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson("/api/users/{$this->adminOrgA->id}/deactivate");
        $resDeact->assertStatus(200);
        $this->assertEquals('inactive', $this->adminOrgA->fresh()->status);

        // 2. Activate
        $resAct = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson("/api/users/{$this->adminOrgA->id}/activate");
        $resAct->assertStatus(200);
        $this->assertEquals('active', $this->adminOrgA->fresh()->status);

        // 3. Reset Password
        $resReset = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson("/api/users/{$this->adminOrgA->id}/reset-password", [
                'password' => 'newsecretpassword123',
            ]);
        $resReset->assertStatus(200);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'reset_password',
            'module' => 'users',
        ]);
    }

    // ─── 4. GLOBAL USER MONITORING ──────────────────────────────────────────

    public function test_super_admin_can_list_and_filter_global_users(): void
    {
        $resOrgA = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson("/api/users?organization_id={$this->orgA->id}");
        $resOrgA->assertStatus(200);
        $this->assertCount(3, $resOrgA->json('data'));

        $resEditor = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/users?role=Editor');
        $resEditor->assertStatus(200);
        $this->assertCount(1, $resEditor->json('data'));
    }

    // ─── 5. PERIOD & RENEWAL GOVERNANCE ─────────────────────────────────────

    public function test_super_admin_can_list_global_periods(): void
    {
        OrganizationPeriod::create([
            'organization_id' => $this->orgA->id,
            'period_name' => 'Periode 2026 Robotik',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'pending',
        ]);

        OrganizationPeriod::create([
            'organization_id' => $this->orgB->id,
            'period_name' => 'Periode 2026 Musik',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/organization-periods');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        $resPending = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/organization-periods?status=pending');
        $resPending->assertStatus(200);
        $this->assertCount(1, $resPending->json('data'));
    }

    public function test_super_admin_can_approve_and_reject_period(): void
    {
        $period = OrganizationPeriod::create([
            'organization_id' => $this->orgA->id,
            'period_name' => 'Renewal 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'pending',
        ]);

        // 1. Super Admin Approves
        $resApprove = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson("/api/organization-periods/{$period->id}/approve");

        $resApprove->assertStatus(200);
        $this->assertEquals('active', $period->fresh()->status);
        $this->assertEquals($this->superAdmin->id, $period->fresh()->approved_by);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'approve',
            'module' => 'organization_periods',
        ]);

        // 2. Reject another pending period with reason
        $period2 = OrganizationPeriod::create([
            'organization_id' => $this->orgB->id,
            'period_name' => 'Renewal 2027',
            'start_date' => '2027-01-01',
            'end_date' => '2027-12-31',
            'status' => 'pending',
        ]);

        $resReject = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson("/api/organization-periods/{$period2->id}/reject", [
                'reason' => 'Dokumen LPJ belum lengkap.',
            ]);

        $resReject->assertStatus(200);
        $this->assertEquals('rejected', $period2->fresh()->status);
        $this->assertEquals('Dokumen LPJ belum lengkap.', $period2->fresh()->rejection_reason);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'reject',
            'module' => 'organization_periods',
        ]);
    }

    public function test_non_super_admin_cannot_approve_period(): void
    {
        $period = OrganizationPeriod::create([
            'organization_id' => $this->orgA->id,
            'period_name' => 'Renewal 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->postJson("/api/organization-periods/{$period->id}/approve");

        $response->assertStatus(403);
    }

    // ─── 6. GLOBAL CONTENT MONITORING & READ-ONLY ───────────────────────────

    public function test_super_admin_can_monitor_global_posts_across_organizations(): void
    {
        Post::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->adminOrgA->id,
            'judul' => 'Artikel Robotik 1',
            'slug' => 'artikel-robotik-1',
            'konten' => 'Konten Robotik',
            'status' => 'published',
        ]);

        Post::create([
            'organization_id' => $this->orgB->id,
            'user_id' => $this->adminOrgB->id,
            'judul' => 'Artikel Musik 1',
            'slug' => 'artikel-musik-1',
            'konten' => 'Konten Musik',
            'status' => 'review',
        ]);

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/posts');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        $resOrgA = $this->actingAs($this->adminOrgA, 'sanctum')
            ->getJson('/api/posts');
        $resOrgA->assertStatus(200);
        $this->assertCount(1, $resOrgA->json('data'));
    }

    // ─── 7. GLOBAL ACTIVITY LOG AUDIT ───────────────────────────────────────

    public function test_super_admin_can_view_global_activity_logs(): void
    {
        ActivityLog::create([
            'user_id' => $this->adminOrgA->id,
            'organization_id' => $this->orgA->id,
            'action' => 'create',
            'module' => 'posts',
            'description' => 'Membuat artikel baru',
        ]);

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/activity-logs');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    // ─── 8. SECURITY, PRIVILEGE ESCALATION & TENANT BOUNDARIES ──────────────

    public function test_admin_org_cannot_escalate_to_super_admin_via_update(): void
    {
        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->putJson("/api/users/{$this->adminOrgA->id}", [
                'name' => 'Admin Escalated',
                'role' => 'Super Admin',
            ]);

        $this->assertTrue($this->adminOrgA->fresh()->hasRole('Admin Organisasi'));
        $this->assertFalse($this->adminOrgA->fresh()->hasRole('Super Admin'));
    }

    public function test_admin_org_cannot_spoof_organization_id(): void
    {
        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->postJson('/api/posts', [
                'judul' => 'Spoofed Post',
                'slug' => 'spoofed-post',
                'konten' => 'Spoofed Body',
                'organization_id' => $this->orgB->id,
                'status' => 'draft',
            ]);

        $response->assertStatus(201);
        $createdPost = Post::where('judul', 'Spoofed Post')->first();

        $this->assertNotNull($createdPost);
        $this->assertEquals($this->orgA->id, $createdPost->organization_id);
        $this->assertNotEquals($this->orgB->id, $createdPost->organization_id);
    }

    // ─── 9. HARDENING AUDIT TESTS ─────────────────────────────────────────────

    public function test_reject_period_with_whitespace_only_reason_fails_with_422(): void
    {
        $period = OrganizationPeriod::create([
            'organization_id' => $this->orgA->id,
            'period_name' => 'Periode 2026/2027',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson("/api/organization-periods/{$period->id}/reject", [
                'reason' => '     ',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['reason']);
    }

    public function test_reject_period_with_short_reason_fails_with_422(): void
    {
        $period = OrganizationPeriod::create([
            'organization_id' => $this->orgA->id,
            'period_name' => 'Periode 2026/2027',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson("/api/organization-periods/{$period->id}/reject", [
                'reason' => 'Nope',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['reason']);
    }

    public function test_reject_registration_with_whitespace_only_reason_fails_with_422(): void
    {
        $reg = OrganizationRegistration::create([
            'organization_name' => 'UKM Robotik Baru',
            'organization_type' => 'UKM',
            'organization_subdomain' => 'robotik-baru',
            'admin_first_name' => 'Budi',
            'admin_last_name' => 'Santoso',
            'admin_email' => 'budi@robotikbaru.test',
            'admin_password' => bcrypt('password123'),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson("/api/organization-registrations/{$reg->id}/reject", [
                'reason' => '     ',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['reason']);
    }

    public function test_reject_registration_with_short_reason_fails_with_422(): void
    {
        $reg = OrganizationRegistration::create([
            'organization_name' => 'UKM Robotik Baru',
            'organization_type' => 'UKM',
            'organization_subdomain' => 'robotik-baru',
            'admin_first_name' => 'Budi',
            'admin_last_name' => 'Santoso',
            'admin_email' => 'budi@robotikbaru.test',
            'admin_password' => bcrypt('password123'),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson("/api/organization-registrations/{$reg->id}/reject", [
                'reason' => 'Bad',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['reason']);
    }

    public function test_media_monitoring_displays_organization_relationship(): void
    {
        Media::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->adminOrgA->id,
            'filename' => 'poster-workshop.jpg',
            'path' => 'media/poster-workshop.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
        ]);

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/media');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertNotNull($data[0]['organization']);
        $this->assertEquals('UKM Robotik', $data[0]['organization']['nama']);
    }

    public function test_announcement_monitoring_displays_organization_relationship(): void
    {
        Announcement::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->adminOrgA->id,
            'title' => 'Pengumuman Penting Robotik',
            'slug' => 'pengumuman-penting-robotik',
            'content' => 'Isi pengumuman robotik',
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/announcements');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertNotNull($data[0]['organization']);
        $this->assertEquals('UKM Robotik', $data[0]['organization']['nama']);
    }

    public function test_user_cannot_deactivate_self_with_422(): void
    {
        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->postJson("/api/users/{$this->adminOrgA->id}/deactivate");

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.']);
        $this->assertEquals('active', $this->adminOrgA->fresh()->status);
    }

    public function test_last_active_super_admin_cannot_be_deactivated_with_422(): void
    {
        $secondAdmin = User::create([
            'name' => 'Second Admin Org',
            'email' => 'secondadmin@platform.test',
            'password' => bcrypt('password123'),
            'organization_id' => $this->orgA->id,
            'status' => 'active',
        ]);
        $secondAdmin->assignRole('Admin Organisasi');

        // Only 1 superAdmin exists
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->putJson("/api/users/{$this->superAdmin->id}", [
                'status' => 'inactive',
            ]);

        $response->assertStatus(422);
        $this->assertEquals('active', $this->superAdmin->fresh()->status);
    }

    public function test_password_reset_fails_if_short_password_with_422(): void
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson("/api/users/{$this->adminOrgA->id}/reset-password", [
                'password' => 'short',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_password_reset_succeeds_and_does_not_log_password_in_activity_log(): void
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson("/api/users/{$this->adminOrgA->id}/reset-password", [
                'password' => 'newSecretPassword123',
            ]);

        $response->assertStatus(200);

        // Check activity log
        $log = ActivityLog::where('action', 'reset_password')->latest()->first();
        $this->assertNotNull($log);
        $metadataJson = json_encode($log->metadata);
        $this->assertStringNotContainsString('newSecretPassword123', $metadataJson);
        $this->assertStringNotContainsString('password', $metadataJson);
    }

    public function test_admin_org_cannot_reset_super_admin_password(): void
    {
        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->postJson("/api/users/{$this->superAdmin->id}/reset-password", [
                'password' => 'hackedPassword123',
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_org_cannot_reset_other_organization_user_password(): void
    {
        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->postJson("/api/users/{$this->adminOrgB->id}/reset-password", [
                'password' => 'hackedPassword123',
            ]);

        $response->assertStatus(403);
    }
}

