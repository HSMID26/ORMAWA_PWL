<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Member;
use App\Models\Organization;
use App\Models\OrganizationPeriod;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicStructureTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $orgA;
    protected Organization $orgB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orgA = Organization::create([
            'nama' => 'Himpunan Mahasiswa Informatika',
            'subdomain' => 'hmif',
            'jenis' => 'HMPS',
            'modul_aktif' => ['structure' => true],
            'status' => 'active',
        ]);

        $this->orgB = Organization::create([
            'nama' => 'Himpunan Mahasiswa Mesin',
            'subdomain' => 'hmm',
            'jenis' => 'HMPS',
            'modul_aktif' => ['structure' => true],
            'status' => 'active',
        ]);
    }

    /**
     * 1. Kondisi: Organisasi belum memiliki OrganizationPeriod
     */
    public function test_structure_org_without_periods_shows_active_and_valid_committees(): void
    {
        // Pengurus aktif valid
        Committee::create([
            'organization_id' => $this->orgA->id,
            'name' => 'Budi Santoso',
            'position' => 'Ketua Umum',
            'department' => 'BPH',
            'status' => 'active',
        ]);

        // Pengurus non-aktif (tidak boleh tampil)
        Committee::create([
            'organization_id' => $this->orgA->id,
            'name' => 'Mantan Pengurus',
            'position' => 'Sekretaris',
            'department' => 'BPH',
            'status' => 'inactive',
        ]);

        // Pengurus dengan masa tugas kedaluwarsa (tidak boleh tampil)
        Committee::create([
            'organization_id' => $this->orgA->id,
            'name' => 'Pengurus Lewat Waktu',
            'position' => 'Bendahara',
            'department' => 'BPH',
            'status' => 'active',
            'end_date' => Carbon::yesterday()->toDateString(),
        ]);

        // Pengurus dengan anggota non-aktif (tidak boleh tampil)
        $inactiveMember = Member::create([
            'organization_id' => $this->orgA->id,
            'name' => 'Anggota Keluar',
            'nim' => '11520001',
            'email' => 'keluar@kampus.ac.id',
            'status' => 'inactive',
        ]);
        Committee::create([
            'organization_id' => $this->orgA->id,
            'member_id' => $inactiveMember->id,
            'name' => 'Anggota Keluar',
            'position' => 'Divisi Kominfo',
            'department' => 'Kominfo',
            'status' => 'active',
        ]);

        $res = $this->getJson('/api/public/organizations/hmif/structure');

        $res->assertStatus(200);
        $res->assertJsonPath('status', 'success');
        $members = $res->json('data.members');

        $this->assertCount(1, $members);
        $this->assertEquals('Budi Santoso', $members[0]['name']);
        $this->assertNull($res->json('data.selected_period'));
        $this->assertEmpty($res->json('data.available_periods'));
    }

    /**
     * 2. Kondisi: Organisasi memiliki periode aktif (ongoing)
     */
    public function test_structure_org_with_active_period_shows_only_ongoing_committees(): void
    {
        $today = Carbon::today();
        $periodOngoing = OrganizationPeriod::create([
            'organization_id' => $this->orgA->id,
            'period_name' => 'Periode 2026/2027',
            'start_date' => $today->copy()->subMonths(2)->toDateString(),
            'end_date' => $today->copy()->addMonths(10)->toDateString(),
            'status' => 'active',
        ]);

        Committee::create([
            'organization_id' => $this->orgA->id,
            'organization_period_id' => $periodOngoing->id,
            'name' => 'Fakhri Pratama',
            'position' => 'Ketua Himpunan',
            'department' => 'BPH',
            'status' => 'active',
        ]);

        $res = $this->getJson('/api/public/organizations/hmif/structure');

        $res->assertStatus(200);
        $this->assertCount(1, $res->json('data.members'));
        $this->assertEquals('Fakhri Pratama', $res->json('data.members.0.name'));
        $this->assertEquals($periodOngoing->id, $res->json('data.selected_period.id'));
        $this->assertTrue($res->json('data.selected_period.is_ongoing'));
    }

    /**
     * 3. Kondisi: Organisasi hanya memiliki periode yang sudah kedaluwarsa
     */
    public function test_structure_org_with_only_expired_period_hides_from_default_view(): void
    {
        $pastPeriod = OrganizationPeriod::create([
            'organization_id' => $this->orgA->id,
            'period_name' => 'Periode 2023/2024 (Lampau)',
            'start_date' => '2023-01-01',
            'end_date' => '2023-12-31',
            'status' => 'active',
        ]);

        Committee::create([
            'organization_id' => $this->orgA->id,
            'organization_period_id' => $pastPeriod->id,
            'name' => 'Demisioner 2023',
            'position' => 'Ketua Demisioner',
            'department' => 'BPH',
            'status' => 'active',
        ]);

        // Default request tanpa period_id: TIDAK BOLEH memunculkan demisioner sebagai pengurus saat ini!
        $resDefault = $this->getJson('/api/public/organizations/hmif/structure');
        $resDefault->assertStatus(200);
        $this->assertCount(0, $resDefault->json('data.members'));
        $this->assertNull($resDefault->json('data.selected_period'));

        // Arsip request dengan ?period_id=...: HARUS memunculkan pengurus arsip periode tersebut
        $resArchive = $this->getJson("/api/public/organizations/hmif/structure?period_id={$pastPeriod->id}");
        $resArchive->assertStatus(200);
        $this->assertCount(1, $resArchive->json('data.members'));
        $this->assertEquals('Demisioner 2023', $resArchive->json('data.members.0.name'));
        $this->assertEquals($pastPeriod->id, $resArchive->json('data.selected_period.id'));
    }

    /**
     * 4. Kondisi: Organisasi memiliki periode tetapi tidak ada periode berjalan (misal hanya upcoming)
     */
    public function test_structure_org_with_upcoming_period_hides_from_default(): void
    {
        $futurePeriod = OrganizationPeriod::create([
            'organization_id' => $this->orgA->id,
            'period_name' => 'Periode Masa Depan',
            'start_date' => Carbon::today()->addMonths(2)->toDateString(),
            'end_date' => Carbon::today()->addMonths(14)->toDateString(),
            'status' => 'active',
        ]);

        Committee::create([
            'organization_id' => $this->orgA->id,
            'organization_period_id' => $futurePeriod->id,
            'name' => 'Calon Pengurus',
            'position' => 'Ketua Terpilih',
            'department' => 'BPH',
            'status' => 'active',
        ]);

        $res = $this->getJson('/api/public/organizations/hmif/structure');
        $res->assertStatus(200);
        $this->assertCount(0, $res->json('data.members'));
    }

    /**
     * 5. Kondisi: Pengurus aktif dari periode lama tidak bocor ke periode aktif saat ini
     */
    public function test_active_committee_from_old_period_does_not_leak_into_current_period(): void
    {
        $today = Carbon::today();

        $oldPeriod = OrganizationPeriod::create([
            'organization_id' => $this->orgA->id,
            'period_name' => 'Periode 2024/2025',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'status' => 'active',
        ]);

        $currentPeriod = OrganizationPeriod::create([
            'organization_id' => $this->orgA->id,
            'period_name' => 'Periode 2026/2027',
            'start_date' => $today->copy()->subMonths(1)->toDateString(),
            'end_date' => $today->copy()->addMonths(11)->toDateString(),
            'status' => 'active',
        ]);

        // Pengurus periode lama (status aktif di arsipnya)
        Committee::create([
            'organization_id' => $this->orgA->id,
            'organization_period_id' => $oldPeriod->id,
            'name' => 'Pengurus Lama',
            'position' => 'Kadiv Humas',
            'department' => 'Humas',
            'status' => 'active',
        ]);

        // Pengurus periode berjalan
        Committee::create([
            'organization_id' => $this->orgA->id,
            'organization_period_id' => $currentPeriod->id,
            'name' => 'Pengurus Baru',
            'position' => 'Kadiv Humas',
            'department' => 'Humas',
            'status' => 'active',
        ]);

        $res = $this->getJson('/api/public/organizations/hmif/structure');
        $res->assertStatus(200);
        $members = $res->json('data.members');

        $this->assertCount(1, $members);
        $this->assertEquals('Pengurus Baru', $members[0]['name']);
    }

    /**
     * 6. Kondisi: Organisasi tanpa pengurus
     */
    public function test_structure_org_without_committees_returns_empty_structure(): void
    {
        $res = $this->getJson('/api/public/organizations/hmif/structure');

        $res->assertStatus(200);
        $this->assertEquals([], $res->json('data.members'));
        $this->assertEmpty($res->json('data.by_department'));
    }

    /**
     * 7. Tenant Isolation: Organisasi B tidak bisa melihat pengurus Organisasi A,
     * dan period_id milik ormawa lain ditolak (404).
     */
    public function test_structure_enforces_tenant_isolation(): void
    {
        $periodA = OrganizationPeriod::create([
            'organization_id' => $this->orgA->id,
            'period_name' => 'Periode HMIF',
            'start_date' => Carbon::today()->subMonths(1)->toDateString(),
            'end_date' => Carbon::today()->addMonths(11)->toDateString(),
            'status' => 'active',
        ]);

        Committee::create([
            'organization_id' => $this->orgA->id,
            'organization_period_id' => $periodA->id,
            'name' => 'Ketua HMIF',
            'position' => 'Ketua',
            'department' => 'BPH',
            'status' => 'active',
        ]);

        // Request ke HMM tidak boleh memunculkan pengurus HMIF
        $resHMM = $this->getJson('/api/public/organizations/hmm/structure');
        $resHMM->assertStatus(200);
        $this->assertCount(0, $resHMM->json('data.members'));

        // Request ke HMM dengan period_id milik HMIF harus mengembalikan 404
        $resCross = $this->getJson("/api/public/organizations/hmm/structure?period_id={$periodA->id}");
        $resCross->assertStatus(404);
    }

    /**
     * 8. Module Disabled Protection
     */
    public function test_structure_returns_404_when_module_is_disabled(): void
    {
        $this->orgA->update([
            'modul_aktif' => ['structure' => false],
        ]);

        $res = $this->getJson('/api/public/organizations/hmif/structure');
        $res->assertStatus(404);
    }
}
