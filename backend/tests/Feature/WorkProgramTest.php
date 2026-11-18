<?php

use App\Http\Controllers\Api\RoleController;
use App\Models\Member;
use App\Models\Organization;
use App\Models\User;
use App\Models\WorkProgram;
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

test('organization admin can create and list work programs within the tenant', function () {
    $member = Member::create([
        'organization_id' => $this->org->id,
        'nim' => 'NIM-' . uniqid(),
        'name' => 'PIC Lokal',
        'status' => 'active',
    ]);

    WorkProgram::create([
        'organization_id' => $this->otherOrg->id,
        'name' => 'Program Organisasi Lain',
        'objective' => 'Tidak boleh terlihat lintas organisasi.',
        'period_year' => 2026,
        'target_participants' => 10,
        'estimated_budget' => 0,
        'status' => WorkProgram::STATUS_PLANNED,
    ]);

    $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/work-programs', [
        'name' => 'Seminar Teknologi',
        'objective' => 'Meningkatkan literasi teknologi mahasiswa.',
        'period_year' => 2026,
        'responsible_member_id' => $member->id,
        'target_participants' => 120,
        'estimated_budget' => 1500000,
        'status' => 'planned',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.organization_id', $this->org->id)
        ->assertJsonPath('data.name', 'Seminar Teknologi')
        ->assertJsonPath('data.responsible_member.name', 'PIC Lokal');

    $list = $this->actingAs($this->admin, 'sanctum')->getJson('/api/work-programs');
    $list->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('summary.total', 1);
});

test('work program validation rejects a member from another organization', function () {
    $foreignMember = Member::create([
        'organization_id' => $this->otherOrg->id,
        'nim' => 'NIM-' . uniqid(),
        'name' => 'Anggota Organisasi Lain',
        'status' => 'active',
    ]);

    $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/work-programs', [
            'name' => 'Program Uji',
            'objective' => 'Tujuan program.',
            'period_year' => 2026,
            'responsible_member_id' => $foreignMember->id,
            'target_participants' => 10,
            'estimated_budget' => 0,
            'status' => 'planned',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('responsible_member_id');
});

test('organization admin cannot view another organization work program', function () {
    $foreignProgram = WorkProgram::create([
        'organization_id' => $this->otherOrg->id,
        'name' => 'Program Rahasia',
        'objective' => 'Tujuan program.',
        'period_year' => 2026,
        'target_participants' => 10,
        'estimated_budget' => 0,
        'status' => WorkProgram::STATUS_PLANNED,
    ]);

    $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/work-programs/' . $foreignProgram->id)
        ->assertForbidden();
});
