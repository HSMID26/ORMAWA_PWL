<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthSecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $orgA;
    protected Organization $orgB;
    protected User $adminOrgA;
    protected User $adminOrgB;
    protected User $editorA;
    protected User $contributorA;
    protected User $superAdmin;
    protected User $inactiveUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin Organisasi', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Kontributor', 'guard_name' => 'web']);

        $this->superAdmin = User::factory()->create([
            'name' => 'Super Admin Test',
            'email' => 'superadmin@security.test',
            'password' => bcrypt('password123'),
            'organization_id' => null,
            'status' => 'active',
        ]);
        $this->superAdmin->assignRole('Super Admin');

        $this->orgA = Organization::create([
            'nama' => 'HIMATIF Security A',
            'jenis' => 'HMPS',
            'subdomain' => 'himatif-sec',
            'status' => 'active',
        ]);

        $this->orgB = Organization::create([
            'nama' => 'BEM Security B',
            'jenis' => 'BEM',
            'subdomain' => 'bem-sec',
            'status' => 'active',
        ]);

        $this->adminOrgA = User::factory()->create([
            'name' => 'Admin Org A',
            'email' => 'adminA@security.test',
            'password' => bcrypt('password123'),
            'organization_id' => $this->orgA->id,
            'status' => 'active',
        ]);
        $this->adminOrgA->assignRole('Admin Organisasi');

        $this->adminOrgB = User::factory()->create([
            'name' => 'Admin Org B',
            'email' => 'adminB@security.test',
            'password' => bcrypt('password123'),
            'organization_id' => $this->orgB->id,
            'status' => 'active',
        ]);
        $this->adminOrgB->assignRole('Admin Organisasi');

        $this->editorA = User::factory()->create([
            'name' => 'Editor Org A',
            'email' => 'editorA@security.test',
            'password' => bcrypt('password123'),
            'organization_id' => $this->orgA->id,
            'status' => 'active',
        ]);
        $this->editorA->assignRole('Editor');

        $this->contributorA = User::factory()->create([
            'name' => 'Contributor Org A',
            'email' => 'contributorA@security.test',
            'password' => bcrypt('password123'),
            'organization_id' => $this->orgA->id,
            'status' => 'active',
        ]);
        $this->contributorA->assignRole('Kontributor');

        $this->inactiveUser = User::factory()->create([
            'name' => 'Inactive User',
            'email' => 'inactive@security.test',
            'password' => bcrypt('password123'),
            'organization_id' => $this->orgA->id,
            'status' => 'inactive',
        ]);
        $this->inactiveUser->assignRole('Editor');
    }

    // ─── 1. GUEST REJECTION TESTS (HTTP 401) ─────────────────────────────────

    public function test_guest_cannot_access_me_endpoint(): void
    {
        $response = $this->getJson('/api/me');
        $response->assertStatus(401);
    }

    public function test_guest_cannot_access_organization_dashboard(): void
    {
        $response = $this->getJson('/api/organization/dashboard');
        $response->assertStatus(401);
    }

    public function test_guest_cannot_access_super_admin_dashboard(): void
    {
        $response = $this->getJson('/api/super-admin/dashboard');
        $response->assertStatus(401);
    }

    public function test_guest_cannot_access_posts(): void
    {
        $response = $this->getJson('/api/posts');
        $response->assertStatus(401);
    }

    public function test_guest_cannot_access_activities(): void
    {
        $response = $this->getJson('/api/activities');
        $response->assertStatus(401);
    }

    public function test_guest_cannot_access_users(): void
    {
        $response = $this->getJson('/api/users');
        $response->assertStatus(401);
    }

    public function test_guest_cannot_access_organizations(): void
    {
        $response = $this->getJson('/api/organizations');
        $response->assertStatus(401);
    }

    public function test_guest_cannot_access_committees(): void
    {
        $response = $this->getJson('/api/committees');
        $response->assertStatus(401);
    }

    // ─── 2. AUTHENTICATION & LOGIN FLOW ─────────────────────────────────────

    public function test_invalid_credentials_cannot_login(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'adminA@security.test',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_nonexistent_email_cannot_login(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'notfound@security.test',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'inactive@security.test',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Akun Anda dinonaktifkan. Silakan hubungi admin.',
        ]);
    }

    public function test_valid_credentials_can_login_and_receive_token(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'adminA@security.test',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'access_token',
            'token_type',
            'user' => [
                'id',
                'name',
                'email',
                'role',
                'organization',
            ],
        ]);
        $this->assertEquals('Bearer', $response->json('token_type'));
        $this->assertNotEmpty($response->json('access_token'));
        $this->assertEquals('Admin Organisasi', $response->json('user.role'));
    }

    public function test_authenticated_user_can_access_me_endpoint_with_bearer_token(): void
    {
        $loginRes = $this->postJson('/api/login', [
            'email' => 'adminA@security.test',
            'password' => 'password123',
        ]);
        $token = $loginRes->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/me');

        $response->assertStatus(200);
        $response->assertJson([
            'user' => [
                'id' => $this->adminOrgA->id,
                'email' => 'adminA@security.test',
                'role' => 'Admin Organisasi',
            ],
        ]);
    }

    public function test_login_creates_token_with_24_hour_expiration(): void
    {
        $loginRes = $this->postJson('/api/login', [
            'email' => 'adminA@security.test',
            'password' => 'password123',
        ]);

        $loginRes->assertStatus(200);
        $token = $loginRes->json('access_token');
        $this->assertNotEmpty($token);

        // Verify token in database has expires_at set to ~24 hours (1440 mins) from now
        $tokenRecord = \DB::table('personal_access_tokens')
            ->where('tokenable_id', $this->adminOrgA->id)
            ->first();

        $this->assertNotNull($tokenRecord);
        $this->assertNotNull($tokenRecord->expires_at);

        $expiresAt = \Carbon\Carbon::parse($tokenRecord->expires_at);
        $expectedExpiration = now()->addMinutes(1440);

        // Expiration timestamp should be within 5 seconds of expected 24 hours
        $this->assertTrue(abs($expiresAt->diffInSeconds($expectedExpiration)) < 10);
    }

    public function test_expired_token_returns_401_on_protected_endpoints(): void
    {
        // Create an already-expired token (expired 1 hour ago)
        $newAccessToken = $this->adminOrgA->createToken('expired_test_token', ['*'], now()->subHour());
        $expiredToken = $newAccessToken->plainTextToken;

        // 1. /api/me should return 401
        $resMe = $this->withHeader('Authorization', "Bearer {$expiredToken}")
            ->getJson('/api/me');
        $resMe->assertStatus(401);

        // Reset auth guard between test requests
        app('auth')->forgetGuards();

        // 2. /api/organization/dashboard should return 401
        $resDashboard = $this->withHeader('Authorization', "Bearer {$expiredToken}")
            ->getJson('/api/organization/dashboard');
        $resDashboard->assertStatus(401);

        // Reset auth guard between test requests
        app('auth')->forgetGuards();

        // 3. /api/posts should return 401
        $resPosts = $this->withHeader('Authorization', "Bearer {$expiredToken}")
            ->getJson('/api/posts');
        $resPosts->assertStatus(401);
    }

    public function test_login_after_logout_generates_new_distinct_valid_token(): void
    {
        // 1. First login
        $loginRes1 = $this->postJson('/api/login', [
            'email' => 'adminA@security.test',
            'password' => 'password123',
        ]);
        $loginRes1->assertStatus(200);
        $token1 = $loginRes1->json('access_token');

        // 2. Logout
        $this->withHeader('Authorization', "Bearer {$token1}")
            ->postJson('/api/logout');

        app('auth')->forgetGuards();

        // 3. Second login
        $loginRes2 = $this->postJson('/api/login', [
            'email' => 'adminA@security.test',
            'password' => 'password123',
        ]);
        $loginRes2->assertStatus(200);
        $token2 = $loginRes2->json('access_token');

        $this->assertNotEquals($token1, $token2);

        // Old token must be 401
        app('auth')->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$token1}")
            ->getJson('/api/me')
            ->assertStatus(401);

        // New token must be 200
        app('auth')->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$token2}")
            ->getJson('/api/me')
            ->assertStatus(200);
    }

    // ─── 3. LOGOUT & TOKEN REVOCATION ───────────────────────────────────────

    public function test_logout_revokes_token_and_subsequent_request_returns_401(): void
    {
        $loginRes = $this->postJson('/api/login', [
            'email' => 'adminA@security.test',
            'password' => 'password123',
        ]);
        $token = $loginRes->json('access_token');

        // Logout
        $logoutRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout');

        $logoutRes->assertStatus(200);

        // Verify token deleted from database
        $this->assertDatabaseCount('personal_access_tokens', 0);

        // Reset auth guard in-memory state for the subsequent request
        app('auth')->forgetGuards();

        // Try to access /api/me again with the revoked token
        $subsequentRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/me');

        $subsequentRes->assertStatus(401);
    }

    // ─── 4. ROLE-BASED ACCESS CONTROL (RBAC) BOUNDARIES ─────────────────────

    public function test_editor_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->editorA, 'sanctum')
            ->getJson('/api/users');

        $response->assertStatus(403);
    }

    public function test_contributor_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->contributorA, 'sanctum')
            ->getJson('/api/users');

        $response->assertStatus(403);
    }

    public function test_editor_cannot_access_organization_settings(): void
    {
        $response = $this->actingAs($this->editorA, 'sanctum')
            ->putJson("/api/organizations/{$this->orgA->id}", [
                'nama' => 'Hacked Organization Name',
            ]);

        $response->assertStatus(403);
    }

    public function test_contributor_cannot_access_organization_settings(): void
    {
        $response = $this->actingAs($this->contributorA, 'sanctum')
            ->putJson("/api/organizations/{$this->orgA->id}", [
                'nama' => 'Hacked Organization Name',
            ]);

        $response->assertStatus(403);
    }

    public function test_contributor_cannot_access_announcements(): void
    {
        $response = $this->actingAs($this->contributorA, 'sanctum')
            ->getJson('/api/announcements');

        $response->assertStatus(403);
    }

    public function test_contributor_cannot_access_gallery_media(): void
    {
        $response = $this->actingAs($this->contributorA, 'sanctum')
            ->getJson('/api/media');

        $response->assertStatus(403);
    }

    // ─── 5. MULTI-TENANT ISOLATION BOUNDARIES ───────────────────────────────

    public function test_admin_org_cannot_access_other_organization_data(): void
    {
        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->getJson("/api/organizations/{$this->orgB->id}");

        $response->assertStatus(403);
    }

    public function test_organization_id_cannot_be_spoofed_from_request_payload(): void
    {
        $response = $this->actingAs($this->adminOrgA, 'sanctum')
            ->postJson('/api/posts', [
                'judul' => 'Post Spoofing Tenant Test',
                'konten' => 'Isi konten post',
                'status' => 'published',
                'organization_id' => $this->orgB->id,
            ]);

        $response->assertStatus(201);
        $createdPostId = $response->json('data.id');

        $post = Post::find($createdPostId);
        $this->assertEquals($this->orgA->id, $post->organization_id);
    }
}
