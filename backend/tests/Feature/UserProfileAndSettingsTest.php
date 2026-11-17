<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserProfileAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organization $org;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin Organisasi', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Kontributor', 'guard_name' => 'web']);

        $this->org = Organization::create([
            'nama' => 'UKM Podcast ITI',
            'jenis' => 'UKM',
            'subdomain' => 'podcast',
            'status' => 'active',
        ]);

        $this->user = User::create([
            'name' => 'Admin Podcast',
            'email' => 'admin.podcast@kampus.ac.id',
            'password' => Hash::make('password123'),
            'organization_id' => $this->org->id,
            'status' => 'active',
        ]);
        $this->user->assignRole('Admin Organisasi');
    }

    public function test_authenticated_user_can_open_profile(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/me');

        $response->assertStatus(200);
        $response->assertJson([
            'user' => [
                'id' => $this->user->id,
                'name' => 'Admin Podcast',
                'email' => 'admin.podcast@kampus.ac.id',
                'role' => 'Admin Organisasi',
                'organization' => [
                    'id' => $this->org->id,
                    'nama' => 'UKM Podcast ITI',
                ],
            ]
        ]);

        $this->assertArrayNotHasKey('password', $response->json('user'));
        $this->assertArrayNotHasKey('remember_token', $response->json('user'));

        // Also test GET /api/profile alias
        $resProfile = $this->actingAs($this->user, 'sanctum')->getJson('/api/profile');
        $resProfile->assertStatus(200);
        $this->assertEquals('Admin Podcast', $resProfile->json('user.name'));
    }

    public function test_guest_cannot_access_profile(): void
    {
        $response = $this->getJson('/api/me');
        $response->assertStatus(401);

        $resProfile = $this->getJson('/api/profile');
        $resProfile->assertStatus(401);

        $resUpdate = $this->patchJson('/api/profile', ['name' => 'New Name']);
        $resUpdate->assertStatus(401);

        $resPass = $this->putJson('/api/password', [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        $resPass->assertStatus(401);
    }

    public function test_user_can_update_own_name(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->patchJson('/api/profile', [
            'name' => 'Admin Podcast Updated',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Profil berhasil diperbarui.',
            'user' => [
                'id' => $this->user->id,
                'name' => 'Admin Podcast Updated',
            ]
        ]);

        $this->user->refresh();
        $this->assertEquals('Admin Podcast Updated', $this->user->name);
    }

    public function test_whitespace_only_name_is_rejected(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->patchJson('/api/profile', [
            'name' => '     ',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);

        $this->user->refresh();
        $this->assertEquals('Admin Podcast', $this->user->name);
    }

    public function test_role_and_organization_cannot_be_escalated_or_spoofed_via_profile_update(): void
    {
        $otherOrg = Organization::create([
            'nama' => 'UKM Robotik ITI',
            'jenis' => 'UKM',
            'subdomain' => 'robotik',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->patchJson('/api/profile', [
            'name' => 'Attempt Escalation',
            'role' => 'Super Admin',
            'organization_id' => $otherOrg->id,
            'email' => 'hacked@iti.ac.id',
            'status' => 'inactive',
        ]);

        $response->assertStatus(200);

        $this->user->refresh();
        $this->assertEquals('Attempt Escalation', $this->user->name);
        $this->assertEquals('admin.podcast@kampus.ac.id', $this->user->email);
        $this->assertEquals($this->org->id, $this->user->organization_id);
        $this->assertTrue($this->user->hasRole('Admin Organisasi'));
        $this->assertFalse($this->user->hasRole('Super Admin'));
        $this->assertEquals('active', $this->user->status);
    }

    public function test_avatar_upload_works_with_valid_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/profile', [
            'name' => 'Admin Podcast With Photo',
            'avatar' => $file,
        ]);

        $response->assertStatus(200);
        $avatarUrl = $response->json('user.avatar');
        $this->assertNotNull($avatarUrl);
        $this->assertStringStartsWith('/storage/avatars/', $avatarUrl);

        $this->user->refresh();
        $this->assertEquals($avatarUrl, $this->user->avatar);

        $filename = basename($avatarUrl);
        Storage::disk('public')->assertExists('avatars/' . $filename);
    }

    public function test_invalid_avatar_format_rejected(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/profile', [
            'name' => 'Admin Podcast',
            'avatar' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['avatar']);
    }

    public function test_current_password_is_required_for_password_change(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/password', [
            'password' => 'newsecretpassword123',
            'password_confirmation' => 'newsecretpassword123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['current_password']);
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/password', [
            'current_password' => 'wrongpassword',
            'password' => 'newsecretpassword123',
            'password_confirmation' => 'newsecretpassword123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['current_password']);

        // Password in database must remain original
        $this->user->refresh();
        $this->assertTrue(Hash::check('password123', $this->user->password));
    }

    public function test_short_new_password_and_confirmation_mismatch_rejected(): void
    {
        // 1. Short (< 8 chars)
        $resShort = $this->actingAs($this->user, 'sanctum')->putJson('/api/password', [
            'current_password' => 'password123',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);
        $resShort->assertStatus(422);
        $resShort->assertJsonValidationErrors(['password']);

        // 2. Mismatch confirmation
        $resMismatch = $this->actingAs($this->user, 'sanctum')->putJson('/api/password', [
            'current_password' => 'password123',
            'password' => 'newsecretpassword123',
            'password_confirmation' => 'differentpassword123',
        ]);
        $resMismatch->assertStatus(422);
        $resMismatch->assertJsonValidationErrors(['password']);
    }

    public function test_password_successfully_changes_and_old_password_rejected_afterward(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/password', [
            'current_password' => 'password123',
            'password' => 'brandnewpassword123',
            'password_confirmation' => 'brandnewpassword123',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Kata sandi berhasil diperbarui.']);

        $this->user->refresh();
        $this->assertTrue(Hash::check('brandnewpassword123', $this->user->password));
        $this->assertFalse(Hash::check('password123', $this->user->password));

        // Test login with new password succeeds
        $resLoginNew = $this->postJson('/api/login', [
            'email' => 'admin.podcast@kampus.ac.id',
            'password' => 'brandnewpassword123',
        ]);
        $resLoginNew->assertStatus(200);

        // Test login with old password fails
        $resLoginOld = $this->postJson('/api/login', [
            'email' => 'admin.podcast@kampus.ac.id',
            'password' => 'password123',
        ]);
        $resLoginOld->assertStatus(422);
    }

    public function test_password_and_tokens_never_appear_in_activity_log(): void
    {
        $this->actingAs($this->user, 'sanctum')->putJson('/api/password', [
            'current_password' => 'password123',
            'password' => 'supersecretnew123',
            'password_confirmation' => 'supersecretnew123',
        ]);

        $logs = ActivityLog::where('user_id', $this->user->id)->get();
        foreach ($logs as $log) {
            $json = json_encode($log->toArray());
            $this->assertStringNotContainsString('password123', $json);
            $this->assertStringNotContainsString('supersecretnew123', $json);
        }
    }

    public function test_logout_revokes_current_token(): void
    {
        $token = $this->user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200);

        $this->assertDatabaseCount('personal_access_tokens', 0);

        app('auth')->forgetGuards();

        // Request with revoked token must fail with 401
        $resAfter = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/me');
        $resAfter->assertStatus(401);
    }
}
