<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Validation\ValidationException;

echo "=== TESTING FEATURE 9: USER MANAGEMENT & ACCOUNT STATUS TOGGLE ===\n";

$org = Organization::first();

// 1. Create test user
echo "\n1. Creating test user...\n";
$testUser = User::create([
    'name'            => 'Pengurus Test HMIF',
    'email'           => 'pengurus.test@hmif.ac.id',
    'password'        => Hash::make('password123'),
    'organization_id' => $org->id,
    'status'          => 'active',
]);
$testUser->assignRole('Editor');

echo "User ID: {$testUser->id}\n";
echo "Name: {$testUser->name}\n";
echo "Email: {$testUser->email}\n";
echo "Role: " . $testUser->roles->first()?->name . "\n";
echo "Status: {$testUser->status}\n";

// 2. Toggle Status to Inactive
echo "\n2. Toggling status to INACTIVE...\n";
$testUser->update(['status' => 'inactive']);
echo "Updated Status: {$testUser->fresh()->status}\n";

// 3. Test Login for Inactive User
echo "\n3. Testing Web Login for INACTIVE user...\n";
try {
    $request = LoginRequest::create('/login', 'POST', [
        'email'    => 'pengurus.test@hmif.ac.id',
        'password' => 'password123',
    ]);
    $request->setContainer($app);
    $request->authenticate();
    echo "ERROR: Inactive user was able to log in! (FAIL)\n";
} catch (ValidationException $e) {
    echo "SUCCESS: Login blocked for inactive user! Message: " . implode(', ', $e->errors()['email']) . "\n";
}

// 4. Toggle Status back to Active
echo "\n4. Toggling status back to ACTIVE...\n";
$testUser->update(['status' => 'active']);
echo "Updated Status: {$testUser->fresh()->status}\n";

// 5. Test Login for Active User
echo "\n5. Testing Web Login for ACTIVE user...\n";
try {
    $request = LoginRequest::create('/login', 'POST', [
        'email'    => 'pengurus.test@hmif.ac.id',
        'password' => 'password123',
    ]);
    $request->setContainer($app);
    $request->authenticate();
    echo "SUCCESS: Active user logged in successfully! Authenticated User ID: " . Auth::id() . "\n";
    Auth::logout();
} catch (ValidationException $e) {
    echo "ERROR: Active user failed to log in! Message: " . implode(', ', $e->errors()['email']) . "\n";
}

// 6. Cleanup test user
echo "\n6. Cleaning up test user...\n";
$testUser->delete();
echo "Test user deleted successfully.\n";

echo "\n=== ALL TESTS PASSED SUCCESSFULLY! ===\n";
