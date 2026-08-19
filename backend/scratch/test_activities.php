<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Activity;
use App\Models\Organization;
use App\Models\User;

echo "--- Testing Activity Creation ---\n";
$org = Organization::first() ?? Organization::create(['nama' => 'Ormawa Test', 'slug' => 'ormawa-test']);
$user = User::first() ?? User::factory()->create();

// Create sample activity
$activity = Activity::create([
    'organization_id' => $org->id,
    'user_id'         => $user->id,
    'title'           => 'Workshop Web Development 2026',
    'description'     => 'Pelatihan pembuatan CMS Ormawa dengan Laravel dan Tailwind.',
    'location'        => 'Auditorium Kampus & Zoom',
    'start_time'      => now()->addDays(2),
    'end_time'        => now()->addDays(2)->addHours(3),
    'status'          => 'upcoming',
]);

echo "Activity ID: " . $activity->id . "\n";
echo "Title: " . $activity->title . "\n";
echo "Location: " . $activity->location . "\n";
echo "Start Time: " . $activity->start_time . "\n";

echo "\n--- Testing iCal Feed Generation ---\n";
$controller = new \App\Http\Controllers\ActivityWebController();
$response = $controller->icalFeed();
echo "HTTP Status: " . $response->getStatusCode() . "\n";
echo "Content Type: " . $response->headers->get('Content-Type') . "\n";
echo "Content Disposition: " . $response->headers->get('Content-Disposition') . "\n";
echo "--- iCal File Content Preview ---\n";
echo $response->getContent() . "\n";
