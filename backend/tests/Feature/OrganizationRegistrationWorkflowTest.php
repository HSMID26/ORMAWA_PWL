<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\Organization;
use App\Models\OrganizationRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrganizationRegistrationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $adminOrg;
    protected User $editor;
    protected User $kontributor;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin Organisasi', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Kontributor', 'guard_name' => 'web']);

        $this->superAdmin = User::factory()->create(['status' => 'active']);
        $this->superAdmin->assignRole('Super Admin');

        $existingOrg = Organization::create([
            'nama' => 'Himpunan Existing',
            'jenis' => 'HMPS',
            'subdomain' => 'hmps-existing',
            'status' => 'active',
        ]);

        $this->adminOrg = User::factory()->create([
            'organization_id' => $existingOrg->id,
            'status' => 'active',
        ]);
        $this->adminOrg->assignRole('Admin Organisasi');

        $this->editor = User::factory()->create([
            'organization_id' => $existingOrg->id,
            'status' => 'active',
        ]);
        $this->editor->assignRole('Editor');

        $this->kontributor = User::factory()->create([
            'organization_id' => $existingOrg->id,
            'status' => 'active',
        ]);
        $this->kontributor->assignRole('Kontributor');
    }

    public function test_1_guest_can_submit_valid_organization_registration(): void
    {
        $payload = [
            'organization_name' => 'UKM Robotika ITI',
            'organization_type' => 'UKM',
            'organization_subdomain' => 'ukm-robotika',
            'organization_description' => 'Wadah minat robotika dan otomatisasi.',
            'admin_first_name' => 'Ahmad',
            'admin_last_name' => 'Fauzi',
            'admin_email' => 'ahmad.fauzi@robotika.iti.ac.id',
            'admin_password' => 'SecurePass123!',
            'admin_password_confirmation' => 'SecurePass123!',
        ];

        $response = $this->postJson('/api/register/organization', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Pendaftaran organisasi berhasil dikirim dan menunggu persetujuan Super Admin.',
                'data' => [
                    'organization_name' => 'UKM Robotika ITI',
                    'status' => 'pending',
                ]
            ]);

        // Organization and user MUST NOT be created before approval
        $this->assertDatabaseMissing('organizations', ['subdomain' => 'ukm-robotika']);
        $this->assertDatabaseMissing('users', ['email' => 'ahmad.fauzi@robotika.iti.ac.id']);

        // Registration record must exist with pending status
        $this->assertDatabaseHas('organization_registrations', [
            'organization_name' => 'UKM Robotika ITI',
            'organization_subdomain' => 'ukm-robotika',
            'admin_email' => 'ahmad.fauzi@robotika.iti.ac.id',
            'status' => 'pending',
        ]);

        // Password must not be leaked
        $json = $response->json();
        $this->assertArrayNotHasKey('admin_password', $json['data']);
    }

    public function test_2_alias_endpoint_also_accepts_guest_registration(): void
    {
        $payload = [
            'organization_name' => 'UKM Paduan Suara',
            'organization_type' => 'UKM',
            'organization_subdomain' => 'ukm-padus',
            'admin_first_name' => 'Siti',
            'admin_last_name' => 'Rahma',
            'admin_email' => 'siti@padus.iti.ac.id',
            'admin_password' => 'SecurePass123!',
            'admin_password_confirmation' => 'SecurePass123!',
        ];

        $response = $this->postJson('/api/organization-registrations', $payload);
        $response->assertStatus(201);
    }

    public function test_3_invalid_registration_data_is_rejected(): void
    {
        $response = $this->postJson('/api/register/organization', [
            'organization_name' => '', // empty
            'organization_type' => 'INVALID_TYPE',
            'organization_subdomain' => 'invalid subdomain with spaces',
            'admin_first_name' => '',
            'admin_last_name' => '',
            'admin_email' => 'not-an-email',
            'admin_password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'organization_name',
                'organization_type',
                'organization_subdomain',
                'admin_first_name',
                'admin_last_name',
                'admin_email',
                'admin_password',
            ]);
    }

    public function test_4_duplicate_organization_subdomain_or_email_is_blocked(): void
    {
        OrganizationRegistration::create([
            'organization_name' => 'UKM Debat',
            'organization_type' => 'UKM',
            'organization_subdomain' => 'ukm-debat',
            'admin_first_name' => 'Rina',
            'admin_last_name' => 'N',
            'admin_email' => 'rina@debat.iti.ac.id',
            'admin_password' => bcrypt('password123'),
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/register/organization', [
            'organization_name' => 'UKM Debat Baru',
            'organization_type' => 'UKM',
            'organization_subdomain' => 'ukm-debat', // Duplicate
            'admin_first_name' => 'Budi',
            'admin_last_name' => 'K',
            'admin_email' => 'rina@debat.iti.ac.id', // Duplicate
            'admin_password' => 'Password123!',
            'admin_password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['organization_subdomain', 'admin_email']);
    }

    public function test_5_super_admin_can_list_and_filter_registrations(): void
    {
        OrganizationRegistration::create([
            'organization_name' => 'HMPS Elektro',
            'organization_type' => 'HMPS',
            'organization_subdomain' => 'hmps-elektro',
            'admin_first_name' => 'Eko',
            'admin_last_name' => 'P',
            'admin_email' => 'eko@elektro.iti.ac.id',
            'admin_password' => bcrypt('password123'),
            'status' => 'pending',
        ]);

        OrganizationRegistration::create([
            'organization_name' => 'HMPS Mesin',
            'organization_type' => 'HMPS',
            'organization_subdomain' => 'hmps-mesin',
            'admin_first_name' => 'Doni',
            'admin_last_name' => 'W',
            'admin_email' => 'doni@mesin.iti.ac.id',
            'admin_password' => bcrypt('password123'),
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/organization-registrations?status=pending');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data',
                'meta' => ['current_page', 'last_page', 'total'],
            ]);

        $items = $response->json('data');
        $this->assertCount(1, $items);
        $this->assertEquals('HMPS Elektro', $items[0]['organization_name']);
    }

    public function test_6_non_super_admins_and_guests_are_forbidden_from_admin_endpoints(): void
    {
        $reg = OrganizationRegistration::create([
            'organization_name' => 'UKM Mapala',
            'organization_type' => 'UKM',
            'organization_subdomain' => 'ukm-mapala',
            'admin_first_name' => 'Gunawan',
            'admin_last_name' => 'W',
            'admin_email' => 'gunawan@mapala.iti.ac.id',
            'admin_password' => bcrypt('password123'),
            'status' => 'pending',
        ]);

        // Guest -> 401
        $this->getJson('/api/organization-registrations')->assertStatus(401);
        $this->postJson("/api/organization-registrations/{$reg->id}/approve")->assertStatus(401);
        $this->postJson("/api/organization-registrations/{$reg->id}/reject", ['reason' => 'valid reason'])->assertStatus(401);

        // Admin Organisasi -> 403
        $this->actingAs($this->adminOrg)->getJson('/api/organization-registrations')->assertStatus(403);
        $this->actingAs($this->adminOrg)->postJson("/api/organization-registrations/{$reg->id}/approve")->assertStatus(403);
        $this->actingAs($this->adminOrg)->postJson("/api/organization-registrations/{$reg->id}/reject", ['reason' => 'valid reason'])->assertStatus(403);

        // Editor -> 403
        $this->actingAs($this->editor)->postJson("/api/organization-registrations/{$reg->id}/approve")->assertStatus(403);

        // Kontributor -> 403
        $this->actingAs($this->kontributor)->postJson("/api/organization-registrations/{$reg->id}/approve")->assertStatus(403);
    }

    public function test_7_super_admin_can_approve_registration_and_provisions_tenant_correctly(): void
    {
        $reg = OrganizationRegistration::create([
            'organization_name' => 'UKM Fotografi',
            'organization_type' => 'UKM',
            'organization_subdomain' => 'ukm-fotografi',
            'organization_description' => 'Komunitas fotografi kampus.',
            'admin_first_name' => 'Rangga',
            'admin_last_name' => 'Sasana',
            'admin_email' => 'rangga@fotografi.iti.ac.id',
            'admin_password' => bcrypt('password123'),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->postJson("/api/organization-registrations/{$reg->id}/approve");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Pendaftaran organisasi berhasil disetujui.',
            ]);

        // Registration record updated
        $reg->refresh();
        $this->assertEquals('approved', $reg->status);
        $this->assertEquals($this->superAdmin->id, $reg->reviewed_by);
        $this->assertNotNull($reg->reviewed_at);

        // Organization created
        $org = Organization::where('subdomain', 'ukm-fotografi')->first();
        $this->assertNotNull($org);
        $this->assertEquals('UKM Fotografi', $org->nama);
        $this->assertEquals('UKM', $org->jenis);
        $this->assertEquals('#00346F', $org->warna_tema);

        // Admin user created with Admin Organisasi role
        $adminUser = User::where('email', 'rangga@fotografi.iti.ac.id')->first();
        $this->assertNotNull($adminUser);
        $this->assertEquals('Rangga Sasana', $adminUser->name);
        $this->assertEquals($org->id, $adminUser->organization_id);
        $this->assertTrue($adminUser->hasRole('Admin Organisasi'));

        // Activity log recorded
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'approve',
            'subject_type' => OrganizationRegistration::class,
        ]);
    }

    public function test_8_super_admin_can_reject_registration_with_required_reason(): void
    {
        $reg = OrganizationRegistration::create([
            'organization_name' => 'UKM Tidak Valid',
            'organization_type' => 'UKM',
            'organization_subdomain' => 'ukm-invalid',
            'admin_first_name' => 'Penipu',
            'admin_last_name' => 'S',
            'admin_email' => 'penipu@invalid.com',
            'admin_password' => bcrypt('password123'),
            'status' => 'pending',
        ]);

        // Reject with empty reason -> 422
        $this->actingAs($this->superAdmin)
            ->postJson("/api/organization-registrations/{$reg->id}/reject", ['reason' => ''])
            ->assertStatus(422);

        // Reject with whitespace only -> 422
        $this->actingAs($this->superAdmin)
            ->postJson("/api/organization-registrations/{$reg->id}/reject", ['reason' => '    '])
            ->assertStatus(422);

        // Reject with short reason (<5 chars) -> 422
        $this->actingAs($this->superAdmin)
            ->postJson("/api/organization-registrations/{$reg->id}/reject", ['reason' => 'bad'])
            ->assertStatus(422);

        // Valid reject
        $response = $this->actingAs($this->superAdmin)
            ->postJson("/api/organization-registrations/{$reg->id}/reject", [
                'reason' => 'Dokumen AD/ART belum lengkap dan tidak valid.',
            ]);

        $response->assertStatus(200);

        $reg->refresh();
        $this->assertEquals('rejected', $reg->status);
        $this->assertEquals('Dokumen AD/ART belum lengkap dan tidak valid.', $reg->rejection_reason);
        $this->assertEquals($this->superAdmin->id, $reg->reviewed_by);

        // Organization and user MUST NOT be created
        $this->assertDatabaseMissing('organizations', ['subdomain' => 'ukm-invalid']);
        $this->assertDatabaseMissing('users', ['email' => 'penipu@invalid.com']);
    }

    public function test_9_cannot_re_approve_or_re_reject_already_processed_registration(): void
    {
        $reg = OrganizationRegistration::create([
            'organization_name' => 'UKM Caving',
            'organization_type' => 'UKM',
            'organization_subdomain' => 'ukm-caving',
            'admin_first_name' => 'Ardi',
            'admin_last_name' => 'W',
            'admin_email' => 'ardi@caving.iti.ac.id',
            'admin_password' => bcrypt('password123'),
            'status' => 'approved',
        ]);

        $this->actingAs($this->superAdmin)
            ->postJson("/api/organization-registrations/{$reg->id}/approve")
            ->assertStatus(422);

        $this->actingAs($this->superAdmin)
            ->postJson("/api/organization-registrations/{$reg->id}/reject", ['reason' => 'Alasan penolakan'])
            ->assertStatus(422);
    }
}
