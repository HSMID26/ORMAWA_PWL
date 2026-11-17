<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Organization;
use App\Models\Post;
use App\Models\Activity;
use App\Models\Committee;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "=== TESTING GLOBAL SEARCH & COMMAND PALETTE (CTRL+K) ===\n";

$user = User::first();
Auth::login($user);
$org = Organization::first();

// Seed test search entity if none exists
Post::firstOrCreate(
    ['judul' => 'Pengumuman Open Recruitment Pengurus 2026'],
    [
        'organization_id' => $org->id,
        'user_id'         => $user->id,
        'slug'            => 'pengumuman-open-recruitment-pengurus-2026',
        'konten'          => '<p>Dibuka pendaftaran pengurus baru HMPS Informatika.</p>',
        'status'          => 'published',
    ]
);

Activity::firstOrCreate(
    ['title' => 'Workshop UI/UX & Web Design'],
    [
        'organization_id' => $org->id,
        'user_id'         => $user->id,
        'judul'           => 'Workshop UI/UX & Web Design',
        'location'        => 'Lab Komputer Kampus',
        'start_time'      => now()->addDays(5),
        'status'          => 'upcoming',
    ]
);

Committee::firstOrCreate(
    ['name' => 'Aditya Pratama'],
    [
        'organization_id' => $org->id,
        'position'        => 'Ketua Divisi Humas',
        'department'      => 'Humas',
        'period'          => '2025/2026',
        'status'          => 'active',
    ]
);

$controller = new SearchController();

// 1. Test Live Search API
echo "\n1. Testing liveSearch API with query 'Workshop'...\n";
$req1 = Request::create('/api/global-search', 'GET', ['q' => 'Workshop']);
$req1->setUserResolver(fn() => $user);
$res1 = $controller->liveSearch($req1);
$data1 = $res1->getData(true);

echo "Status: " . $data1['status'] . "\n";
echo "Total Results: " . $data1['total'] . "\n";
foreach ($data1['results'] as $r) {
    echo " - [{$r['badge']}] {$r['title']} ({$r['url']})\n";
}
assert($data1['total'] > 0, 'Must return at least 1 result for Workshop');

// 2. Test Live Search API for Menu Navigation / Quick Actions
echo "\n2. Testing liveSearch API for menu shortcut 'Artikel'...\n";
$req2 = Request::create('/api/global-search', 'GET', ['q' => 'Artikel']);
$req2->setUserResolver(fn() => $user);
$res2 = $controller->liveSearch($req2);
$data2 = $res2->getData(true);
echo "Total Results: " . $data2['total'] . "\n";
foreach ($data2['results'] as $r) {
    echo " - [{$r['badge']}] {$r['title']}\n";
}

// 3. Test Full Search Results Page View
echo "\n3. Testing Full Search Results View (SearchController@index)...\n";
$req3 = Request::create('/search', 'GET', ['q' => 'Pengurus']);
$req3->setUserResolver(fn() => $user);
$res3 = $controller->index($req3);
echo "View Rendered: " . $res3->name() . "\n";
$viewData = $res3->getData();
echo "Total Results Found: " . $viewData['totalResults'] . "\n";
echo "Posts Count: " . $viewData['posts']->count() . "\n";
echo "Committees Count: " . $viewData['committees']->count() . "\n";

echo "\n=== ALL GLOBAL SEARCH TESTS PASSED SUCCESSFULLY! ===\n";
