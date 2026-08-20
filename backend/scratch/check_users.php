<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Organization;
use Spatie\Permission\Models\Role;

echo "--- ROLES ---\n";
foreach (Role::all() as $r) {
    echo "Role: {$r->name}\n";
}

echo "\n--- USERS ---\n";
foreach (User::with(['organization', 'roles'])->get() as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Email: {$u->email} | Status: {$u->status} | Role: " . ($u->roles->first()?->name ?? 'None') . " | Org: " . ($u->organization?->nama ?? 'None') . "\n";
}

echo "\n--- ORGANIZATIONS ---\n";
foreach (Organization::all() as $o) {
    echo "ID: {$o->id} | Nama: {$o->nama} | Status: {$o->status}\n";
}
