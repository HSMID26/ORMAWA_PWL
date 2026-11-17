<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Organization;
use App\Models\PendingRegistration;
use App\Http\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

echo "=== TESTING FEATURE: ALUR PERSETUJUAN ORMAWA (APPROVAL WORKFLOW) ===\n";

$superAdmin = User::first();
Auth::login($superAdmin);

// 1. Create a Pending Registration
echo "1. Creating mock PendingRegistration for Approval Test...\n";
$pendingApprove = PendingRegistration::create([
    'nama_ormawa'    => 'UKM Robotika & IoT',
    'jenis_ormawa'   => 'UKM',
    'subdomain'      => 'robotika-' . time(),
    'admin_name'     => 'Budi Santoso',
    'admin_email'    => 'budi.robotika.' . time() . '@kampus.ac.id',
    'admin_password' => Hash::make('password123'),
    'status'         => 'pending',
]);
echo "Created Pending ID: {$pendingApprove->id} ({$pendingApprove->nama_ormawa})\n";

// 2. Test Approve
$controller = new SuperAdminController();
$controller->approve($pendingApprove->id);

$freshPending = $pendingApprove->fresh();
echo "Status after approve: {$freshPending->status}\n";
assert($freshPending->status === 'approved', 'Status must be approved');

$createdOrg = Organization::where('subdomain', $pendingApprove->subdomain)->first();
echo "Created Org: {$createdOrg->nama} (Status: {$createdOrg->status})\n";
assert($createdOrg !== null, 'Organization must be created');

$createdUser = User::where('email', $pendingApprove->admin_email)->first();
echo "Created Admin User: {$createdUser->name} (Role: " . $createdUser->roles->pluck('name')->join(', ') . ")\n";
assert($createdUser !== null, 'Admin user must be created');
assert($createdUser->status === 'active', 'Admin user must be active');

// 3. Test Reject
echo "\n3. Testing Reject Workflow...\n";
$pendingReject = PendingRegistration::create([
    'nama_ormawa'    => 'Komunitas Game Non-Resmi',
    'jenis_ormawa'   => 'Lainnya',
    'subdomain'      => 'game-' . time(),
    'admin_name'     => 'Doni Gamer',
    'admin_email'    => 'doni.game.' . time() . '@kampus.ac.id',
    'admin_password' => Hash::make('password123'),
    'status'         => 'pending',
]);

$rejectReq = Request::create("/superadmin/pending-registrations/{$pendingReject->id}/reject", 'POST', [
    'alasan_penolakan' => 'Organisasi belum terdaftar di SK Kemahasiswaan Kampus.'
]);
$controller->reject($rejectReq, $pendingReject->id);

$freshReject = $pendingReject->fresh();
echo "Status after reject: {$freshReject->status}\n";
echo "Alasan penolakan: {$freshReject->alasan_penolakan}\n";
assert($freshReject->status === 'rejected', 'Status must be rejected');
assert($freshReject->alasan_penolakan === 'Organisasi belum terdaftar di SK Kemahasiswaan Kampus.', 'Reason must match');

echo "\n=== ALL APPROVAL WORKFLOW TESTS PASSED SUCCESSFULLY! ===\n";
