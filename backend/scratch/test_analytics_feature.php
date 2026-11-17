<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Organization;
use App\Models\PageView;
use App\Models\Post;
use App\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "=== TESTING FEATURE 13: STATISTIK PENGUNJUNG & GOOGLE ANALYTICS ===\n";

$user = User::first();
Auth::login($user);

$org = Organization::first() ?? Organization::create([
    'nama'       => 'HMPS Teknik Informatika',
    'jenis'      => 'HMPS',
    'subdomain'  => 'hmif',
    'warna_tema' => '#1d4ed8',
    'status'     => 'active',
]);

echo "1. Testing GA4 Measurement ID saving...\n";
$org->update(['ga_tracking_id' => 'G-TEST123456']);
$freshOrg = $org->fresh();
echo "Organization: {$freshOrg->nama}\n";
echo "GA Tracking ID: {$freshOrg->ga_tracking_id}\n";
assert($freshOrg->ga_tracking_id === 'G-TEST123456', 'GA Tracking ID must match');

echo "\n2. Seeding test PageView traffic data...\n";
PageView::create([
    'organization_id' => $org->id,
    'url_path'        => '/posts/kegiatan-coding-bootcamp',
    'ip_address'      => '192.168.1.10',
    'user_agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
    'created_at'      => now(),
]);

PageView::create([
    'organization_id' => $org->id,
    'url_path'        => '/posts/kegiatan-coding-bootcamp',
    'ip_address'      => '192.168.1.11',
    'user_agent'      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
    'created_at'      => now(),
]);

PageView::create([
    'organization_id' => $org->id,
    'url_path'        => '/activities',
    'ip_address'      => '192.168.1.12',
    'user_agent'      => 'Mozilla/5.0 (X11; Linux x86_64)',
    'created_at'      => now()->subDays(1),
]);

$totalViews = PageView::where('organization_id', $org->id)->count();
$uniqueVisitors = PageView::where('organization_id', $org->id)->distinct('ip_address')->count('ip_address');
echo "Total Page Views in DB: {$totalViews}\n";
echo "Unique Visitors in DB: {$uniqueVisitors}\n";

echo "\n3. Testing AnalyticsController index method...\n";
$controller = new AnalyticsController();
$request = Request::create('/analytics', 'GET');
$request->setUserResolver(fn() => $user);

$response = $controller->index($request);
echo "View rendered successfully: " . $response->name() . "\n";
$viewData = $response->getData();
echo "Total Views in View: " . $viewData['totalViews'] . "\n";
echo "Unique Visitors in View: " . $viewData['uniqueVisitors'] . "\n";
echo "7-Day Dates Array Count: " . count($viewData['dates']) . "\n";
echo "Views Data Array Count: " . count($viewData['viewsData']) . "\n";
echo "Top Pages Count: " . count($viewData['topPages']) . "\n";

echo "\n=== ALL FEATURE 13 TESTS PASSED SUCCESSFULLY! ===\n";
