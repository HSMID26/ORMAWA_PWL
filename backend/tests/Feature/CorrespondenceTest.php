<?php

use App\Http\Controllers\Api\RoleController;
use App\Models\ActivityLog;
use App\Models\Correspondence;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    RoleController::ensureDefaultPermissions();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $suffix = str_replace('.', '', (string) microtime(true));
    $this->org = Organization::create([
        'nama' => 'Organisasi ' . $suffix,
        'jenis' => 'HMPS',
        'subdomain' => 'org' . $suffix,
        'status' => 'active',
    ]);
    $this->otherOrg = Organization::create([
        'nama' => 'Organisasi Lain ' . $suffix,
        'jenis' => 'HMPS',
        'subdomain' => 'other' . $suffix,
        'status' => 'active',
    ]);
    $this->admin = User::create([
        'name' => 'Admin ' . $suffix,
        'email' => 'admin' . $suffix . '@example.com',
        'password' => 'password',
        'organization_id' => $this->org->id,
        'status' => 'active',
    ]);
    $this->admin->assignRole('Admin Organisasi');
});

test('organization admin can store an attached letter and complete its review workflow', function () {
    Storage::fake('local');
    $response = $this->actingAs($this->admin, 'sanctum')->post('/api/correspondences', [
        'reference_number' => '001/ORMAWA/2026',
        'type' => 'incoming',
        'sender' => 'Biro Kemahasiswaan',
        'recipient' => 'HMIF',
        'subject' => 'Undangan koordinasi',
        'letter_date' => '2026-11-17',
        'notes' => 'Mohon ditindaklanjuti.',
        'attachment' => UploadedFile::fake()->create('surat.pdf', 20, 'application/pdf'),
    ], ['Accept' => 'application/json']);

    $response->assertCreated()
        ->assertJsonPath('data.organization_id', $this->org->id)
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.attachment_name', 'surat.pdf');
    $letterId = $response->json('data.id');

    $this->getJson('/api/correspondences/' . $letterId . '/attachment')
        ->assertOk()
        ->assertDownload('surat.pdf');
    $this->postJson('/api/correspondences/' . $letterId . '/submit')
        ->assertOk()
        ->assertJsonPath('data.status', 'submitted');
    $this->postJson('/api/correspondences/' . $letterId . '/review', [
        'action' => 'request_revision',
        'review_note' => 'Lengkapi tujuan surat.',
    ])->assertOk()->assertJsonPath('data.status', 'revision_requested')
        ->assertJsonPath('data.review_note', 'Lengkapi tujuan surat.');

    expect(ActivityLog::query()->where('module', 'correspondence')->where('action', 'review')->latest()->first()->metadata)
        ->toMatchArray(['status' => 'revision_requested', 'review_note' => 'Lengkapi tujuan surat.']);

    $this->putJson('/api/correspondences/' . $letterId, [
        'reference_number' => '001/ORMAWA/2026',
        'type' => 'incoming',
        'sender' => 'Biro Kemahasiswaan',
        'recipient' => 'HMIF',
        'subject' => 'Undangan koordinasi',
        'letter_date' => '2026-11-17',
        'notes' => 'Tujuan sudah dilengkapi.',
    ])->assertOk()->assertJsonPath('data.status', 'revision_requested');

    $this->postJson('/api/correspondences/' . $letterId . '/submit')
        ->assertOk()
        ->assertJsonPath('data.status', 'submitted');
    $this->postJson('/api/correspondences/' . $letterId . '/review', [
        'action' => 'approve',
    ])->assertOk()->assertJsonPath('data.status', 'approved');
});

test('organization admin cannot access a correspondence record from another tenant', function () {
    $foreignLetter = Correspondence::create([
        'organization_id' => $this->otherOrg->id,
        'reference_number' => '999/OTHER/2026',
        'type' => 'outgoing',
        'sender' => 'Organisasi Lain',
        'recipient' => 'Penerima',
        'subject' => 'Surat lintas tenant',
        'letter_date' => '2026-11-17',
        'status' => 'draft',
    ]);

    $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/correspondences/' . $foreignLetter->id)
        ->assertForbidden();

    $this->getJson('/api/correspondences')
        ->assertOk()
        ->assertJsonPath('meta.total', 0);
});

test('empty correspondence filters do not invalidate the list request', function () {
    $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/correspondences?type=&status=')
        ->assertOk()
        ->assertJsonPath('meta.total', 0);
});

test('reviewing a submitted letter requires a note for rejection or revision', function () {
    $letter = Correspondence::create([
        'organization_id' => $this->org->id,
        'reference_number' => '002/ORMAWA/2026',
        'type' => 'outgoing',
        'sender' => 'HMIF',
        'recipient' => 'Biro Kemahasiswaan',
        'subject' => 'Permohonan kegiatan',
        'letter_date' => '2026-11-17',
        'status' => 'submitted',
    ]);

    $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/correspondences/' . $letter->id . '/review', [
            'action' => 'request_revision',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('review_note');
});

test('super admin can create correspondence for a selected organization', function () {
    $superAdmin = User::create([
        'name' => 'Super Admin',
        'email' => 'super-admin-' . uniqid() . '@example.com',
        'password' => 'password',
        'status' => 'active',
    ]);
    $superAdmin->assignRole('Super Admin');

    $this->actingAs($superAdmin, 'sanctum')
        ->postJson('/api/correspondences', [
            'organization_id' => $this->org->id,
            'reference_number' => 'PKA/001/' . uniqid(),
            'type' => 'outgoing',
            'sender' => 'PKA',
            'recipient' => $this->org->nama,
            'subject' => 'Pemberitahuan kegiatan',
            'letter_date' => '2026-11-18',
        ])
        ->assertCreated()
        ->assertJsonPath('data.organization_id', $this->org->id);

    $this->actingAs($superAdmin, 'sanctum')
        ->getJson('/api/correspondences')
        ->assertOk()
        ->assertJsonPath('data.0.organization.nama', $this->org->nama);
});

test('super admin must choose an organization when creating correspondence', function () {
    $superAdmin = User::create([
        'name' => 'Super Admin',
        'email' => 'super-admin-' . uniqid() . '@example.com',
        'password' => 'password',
        'status' => 'active',
    ]);
    $superAdmin->assignRole('Super Admin');

    $this->actingAs($superAdmin, 'sanctum')
        ->postJson('/api/correspondences', [
            'reference_number' => 'PKA/002/' . uniqid(),
            'type' => 'outgoing',
            'sender' => 'PKA',
            'recipient' => $this->org->nama,
            'subject' => 'Pemberitahuan kegiatan',
            'letter_date' => '2026-11-18',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('organization_id');
});
