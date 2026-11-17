<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\PostController;

echo "=== TESTING DIRECT ARTICLE PUBLISH (NO APPROVAL NEEDED) ===\n";

$user = User::first();
$controller = new PostController();

$request = Request::create('/api/posts', 'POST', [
    'judul' => 'Uji Coba Publikasi Tanpa Approval ' . time(),
    'konten' => '<p>Konten artikel yang langsung tayang tanpa approval!</p>',
    'status' => 'published',
]);
$request->setUserResolver(fn() => $user);

$response = $controller->store($request);
$data = json_decode($response->getContent(), true);

assert($response->getStatusCode() === 201, 'Status code must be 201');
assert($data['data']['status'] === 'published', 'Article status must be directly published');
assert(!empty($data['data']['published_at']), 'Article published_at must be populated');

echo "SUCCESS: Article created with status: " . $data['data']['status'] . " at " . $data['data']['published_at'] . "\n";
echo "=== ALL CHECKS PASSED ===\n";
