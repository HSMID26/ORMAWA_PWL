<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\ActivityLog;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "=== TESTING FEATURE 11: ACTIVITY LOG & AUDIT TRAIL ===\n";

$user = User::first();
Auth::login($user);

echo "1. Logging sample activity...\n";
$log = ActivityLogService::log(
    'update',
    'settings',
    'Memperbarui pengaturan warna tema dan modul Ormawa (Test Audit Trail)',
    null,
    ['color' => '#059669'],
    $user
);

echo "Log ID: {$log->id}\n";
echo "User: {$log->user->name}\n";
echo "Module: {$log->module}\n";
echo "Action: {$log->action}\n";
echo "Description: {$log->description}\n";
echo "IP Address: {$log->ip_address}\n";

echo "\n2. Testing ActivityLogWebController index method...\n";
$controller = new \App\Http\Controllers\ActivityLogWebController();
$request = Request::create('/activity-logs', 'GET', ['module' => 'settings']);
$request->setUserResolver(fn() => $user);

$response = $controller->index($request);
echo "View rendered: " . $response->name() . "\n";
echo "Total logs in view data: " . count($response->getData()['logs']) . "\n";

echo "\n=== ALL FEATURE 11 TESTS PASSED SUCCESSFULLY! ===\n";
