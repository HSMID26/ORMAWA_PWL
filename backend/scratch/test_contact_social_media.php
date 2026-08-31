<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Organization;
use App\Http\Controllers\OrganizationWebController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "=== TESTING FEATURE: KONTAK & MEDIA SOSIAL ORMAWA ===\n";

$user = User::first();
Auth::login($user);

$org = Organization::first();

echo "1. Testing direct update on Organization model...\n";
$org->update([
    'email'        => 'hmif@kampus.ac.id',
    'telepon'      => '081234567890',
    'alamat'       => 'Gedung PKM Lt. 2, Ruang 204',
    'media_sosial' => [
        'instagram' => '@hmif_kampus',
        'tiktok'    => '@hmif_official',
        'youtube'   => 'youtube.com/@hmif',
        'linkedin'  => 'linkedin.com/company/hmif',
        'twitter_x' => '@hmif_kampus',
        'website'   => 'https://linktr.ee/hmif',
    ]
]);

$freshOrg = $org->fresh();
echo "Email: " . $freshOrg->email . "\n";
echo "Telepon: " . $freshOrg->telepon . "\n";
echo "Alamat: " . $freshOrg->alamat . "\n";
echo "Instagram: " . $freshOrg->getSocialMedia('instagram') . "\n";
echo "WhatsApp URL: " . $freshOrg->getWhatsAppUrl() . "\n";

assert($freshOrg->email === 'hmif@kampus.ac.id', 'Email must match');
assert($freshOrg->getSocialMedia('instagram') === '@hmif_kampus', 'Instagram handle must match');
assert($freshOrg->getWhatsAppUrl() === 'https://wa.me/6281234567890', 'WhatsApp URL must match');

echo "\n2. Testing OrganizationWebController updateSettings...\n";
$controller = new OrganizationWebController();
$request = Request::create('/organization/settings', 'POST', [
    'organization_id' => $org->id,
    'nama'            => $org->nama,
    'jenis'           => $org->jenis,
    'warna_tema'      => $org->warna_tema,
    'email'           => 'sekretariat.hmif@kampus.ac.id',
    'telepon'         => '089988776655',
    'alamat'          => 'Gedung Ormawa Terpadu',
    'media_sosial'    => [
        'instagram' => '@sekretariat_hmif',
        'tiktok'    => '@hmif_tok',
    ]
]);
$request->headers->set('Accept', 'application/json');
$request->setUserResolver(fn() => $user);

$response = $controller->updateSettings($request);
echo "Response status: " . $response->getStatusCode() . "\n";
$data = json_decode($response->getContent(), true);
echo "Message: " . $data['message'] . "\n";

$updatedOrg = $org->fresh();
echo "Updated Email: " . $updatedOrg->email . "\n";
assert($updatedOrg->email === 'sekretariat.hmif@kampus.ac.id', 'Updated email must match');
assert($updatedOrg->getSocialMedia('instagram') === '@sekretariat_hmif', 'Updated Instagram must match');

echo "\n=== ALL KONTAK & MEDIA SOSIAL TESTS PASSED SUCCESSFULLY! ===\n";
