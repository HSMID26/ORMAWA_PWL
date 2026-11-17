<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = \App\Models\Activity::count();
echo "Total activities: $count\n";
if ($count > 0) {
    print_r(\App\Models\Activity::all()->toArray());
}
