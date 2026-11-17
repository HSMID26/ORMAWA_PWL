<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Organization;
use App\Models\Media;
use App\Http\Controllers\MediaWebController;
use App\Http\Controllers\Api\MediaController as ApiMediaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

echo "=== TESTING FEATURE: MEDIA LIBRARY ORMAWA ===\n";

$user = User::first();
Auth::login($user);
$org = Organization::first();

echo "1. Testing Media Model Accessors...\n";
$media = new Media([
    'filename'  => 'banner_test.webp',
    'path'      => 'media-gallery/banner_test.webp',
    'mime_type' => 'image/webp',
    'size'      => 1024 * 350, // 350 KB
]);

echo "Size (350 KB): " . $media->formatted_size . "\n";
assert($media->formatted_size === '350 KB', 'Formatted size should be 350 KB');
assert($media->is_image === true, 'is_image should be true');
assert($media->is_video === false, 'is_video should be false');

$videoMedia = new Media([
    'filename'  => 'intro.mp4',
    'path'      => 'media-gallery/intro.mp4',
    'mime_type' => 'video/mp4',
    'size'      => 1024 * 1024 * 15, // 15 MB
]);
echo "Video Size (15 MB): " . $videoMedia->formatted_size . "\n";
assert($videoMedia->formatted_size === '15 MB', 'Formatted size should be 15 MB');
assert($videoMedia->is_video === true, 'is_video should be true');

echo "\n2. Testing MediaWebController@index...\n";
$controller = new MediaWebController();
$request = Request::create('/media', 'GET');
$view = $controller->index($request);
$data = $view->getData();

echo "Total Files: " . $data['totalFiles'] . "\n";
echo "Storage Formatted: " . $data['totalStorageFormatted'] . "\n";
echo "Images Count: " . $data['imagesCount'] . "\n";
assert(isset($data['mediaList']), 'mediaList must exist');

echo "\n3. Testing MediaWebController@picker (JSON API)...\n";
$pickerReq = Request::create('/media/picker', 'GET');
$pickerRes = $controller->picker($pickerReq);
echo "Picker status: " . $pickerRes->getStatusCode() . "\n";
$pickerJson = json_decode($pickerRes->getContent(), true);
assert($pickerJson['status'] === 'success', 'Picker status must be success');
echo "Picker returned " . count($pickerJson['data']) . " items\n";

echo "\n4. Testing ApiMediaController@upload (Simulated Image Upload)...\n";
Storage::fake('public');
$fakeImage = UploadedFile::fake()->image('documentasi_kegiatan.png', 800, 600);

$apiController = new ApiMediaController();
$uploadReq = Request::create('/api/upload-image', 'POST', [], [], ['image' => $fakeImage]);
$uploadReq->setUserResolver(fn() => $user);

$uploadRes = $apiController->upload($uploadReq);
echo "Upload response status: " . $uploadRes->getStatusCode() . "\n";
$uploadJson = json_decode($uploadRes->getContent(), true);
echo "Upload URL: " . ($uploadJson['url'] ?? '-') . "\n";
assert($uploadRes->getStatusCode() === 200, 'Upload must succeed with 200');
assert(isset($uploadJson['id']), 'Uploaded media ID must exist');

$uploadedId = $uploadJson['id'];
$uploadedMedia = Media::find($uploadedId);
assert($uploadedMedia !== null, 'Media must be in database');
echo "Created media filename: " . $uploadedMedia->filename . "\n";

echo "\n5. Testing ApiMediaController@destroy...\n";
$destroyReq = Request::create("/api/media/{$uploadedId}", 'DELETE');
$destroyReq->setUserResolver(fn() => $user);
$destroyRes = $apiController->destroy($destroyReq, (string) $uploadedId);
echo "Destroy status: " . $destroyRes->getStatusCode() . "\n";
assert($destroyRes->getStatusCode() === 200, 'Destroy must succeed');
assert(Media::find($uploadedId) === null, 'Media record must be deleted');

echo "\n=== ALL MEDIA LIBRARY TESTS PASSED SUCCESSFULLY! ===\n";
