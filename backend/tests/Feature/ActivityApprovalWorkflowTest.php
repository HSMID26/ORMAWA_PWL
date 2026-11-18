<?php

use App\Http\Controllers\Api\RoleController;
use App\Models\Activity;
use App\Models\ActivityLog;
use App\Models\Organization;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    RoleController::ensureDefaultPermissions();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $suffix = str_replace('.', '', (string) microtime(true));
    $this->organization = Organization::create([
        'nama' => 'Organisasi ' . $suffix,
        'jenis' => 'HMPS',
        'subdomain' => 'org' . $suffix,
        'status' => 'active',
    ]);
    $this->editor = User::create([
        'name' => 'Editor ' . $suffix,
        'email' => 'editor' . $suffix . '@example.com',
        'password' => 'password',
        'organization_id' => $this->organization->id,
        'status' => 'active',
    ]);
    $this->editor->assignRole('Editor');
    $this->admin = User::create([
        'name' => 'Admin ' . $suffix,
        'email' => 'admin' . $suffix . '@example.com',
        'password' => 'password',
        'organization_id' => $this->organization->id,
        'status' => 'active',
    ]);
    $this->admin->assignRole('Admin Organisasi');
});

test('editor can submit an agenda and an admin can request revision then approve it', function () {
    $response = $this->actingAs($this->editor, 'sanctum')->postJson('/api/activities', [
        'judul' => 'Seminar Teknologi',
        'deskripsi' => 'Seminar pengembangan teknologi.',
        'tanggal_pelaksanaan' => '2026-12-01',
        'status' => 'review',
    ]);
    $response->assertCreated()->assertJsonPath('data.status', 'review');
    $activityId = $response->json('data.id');

    $this->actingAs($this->admin, 'sanctum')->putJson('/api/activities/' . $activityId, [
        'judul' => 'Seminar Teknologi',
        'deskripsi' => 'Seminar pengembangan teknologi.',
        'tanggal_pelaksanaan' => '2026-12-01',
        'status' => 'revision_requested',
        'review_note' => 'Tambahkan target peserta.',
    ])->assertOk()->assertJsonPath('data.status', 'revision_requested')
        ->assertJsonPath('data.reviewed_by', $this->admin->id)
        ->assertJsonPath('data.review_note', 'Tambahkan target peserta.');

    $revisionLog = ActivityLog::query()
        ->where('module', 'activities')
        ->where('action', 'request_revision')
        ->latest()
        ->first();
    expect($revisionLog->metadata)->toMatchArray([
        'status' => 'revision_requested',
        'review_note' => 'Tambahkan target peserta.',
    ]);

    $this->actingAs($this->editor, 'sanctum')->putJson('/api/activities/' . $activityId, [
        'judul' => 'Seminar Teknologi',
        'deskripsi' => 'Seminar dengan target peserta.',
        'tanggal_pelaksanaan' => '2026-12-01',
        'status' => 'review',
    ])->assertOk()->assertJsonPath('data.status', 'review');

    $this->actingAs($this->admin, 'sanctum')->putJson('/api/activities/' . $activityId, [
        'judul' => 'Seminar Teknologi',
        'deskripsi' => 'Seminar dengan target peserta.',
        'tanggal_pelaksanaan' => '2026-12-01',
        'status' => 'published',
    ])->assertOk()->assertJsonPath('data.status', 'published');

    expect(Activity::findOrFail($activityId)->published_at)->not->toBeNull();
});

test('agenda reviewer must provide a note when requesting revision', function () {
    $activity = Activity::create([
        'organization_id' => $this->organization->id,
        'user_id' => $this->editor->id,
        'judul' => 'Rapat Kerja',
        'deskripsi' => 'Pembahasan program tahunan.',
        'tanggal_pelaksanaan' => '2026-12-01',
        'status' => 'review',
    ]);

    $this->actingAs($this->admin, 'sanctum')->putJson('/api/activities/' . $activity->id, [
        'judul' => 'Rapat Kerja',
        'deskripsi' => 'Pembahasan program tahunan.',
        'tanggal_pelaksanaan' => '2026-12-01',
        'status' => 'revision_requested',
    ])->assertUnprocessable()->assertJsonValidationErrors('review_note');
});
