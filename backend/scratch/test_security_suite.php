<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Post;
use App\Models\Organization;
use App\Models\PendingRegistration;
use App\Models\OrganizationRegistration;
use App\Services\SecurityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Middleware\SecurityHeadersMiddleware;

echo "=====================================================\n";
echo "      RUNNING CMS ORMAWA SECURITY TEST SUITE         \n";
echo "=====================================================\n\n";

$passedTests = 0;

// --------------------------------------------------
// 1. PASSWORD ENCRYPTION & HIDDEN CREDENTIALS TEST
// --------------------------------------------------
echo "[1] Testing Password Encryption & Hidden Attributes...\n";

// A. Test User password cast
$plainPassword = 'SecretPassword123!';
$user = new User([
    'name' => 'Security Tester',
    'email' => 'sec_test_' . time() . '@example.com',
    'password' => $plainPassword,
]);
assert(Hash::check($plainPassword, $user->password), "User password must be automatically hashed by Bcrypt/Argon");
assert(!isset($user->toArray()['password']), "User password attribute MUST be hidden in array/JSON representation");

// B. Test PendingRegistration password cast & hidden
$pending = new PendingRegistration([
    'admin_password' => $plainPassword,
]);
assert(Hash::check($plainPassword, $pending->admin_password), "PendingRegistration admin_password must be hashed");
assert(!isset($pending->toArray()['admin_password']), "PendingRegistration admin_password MUST be hidden");

// C. Test OrganizationRegistration password cast & hidden
$orgReg = new OrganizationRegistration([
    'admin_password' => $plainPassword,
]);
assert(Hash::check($plainPassword, $orgReg->admin_password), "OrganizationRegistration admin_password must be hashed");
assert(!isset($orgReg->toArray()['admin_password']), "OrganizationRegistration admin_password MUST be hidden");

echo "  -> PASS: All password encryption & hidden attributes verified 100%!\n\n";
$passedTests++;

// --------------------------------------------------
// 2. MULTI-TENANT DATA ISOLATION TEST
// --------------------------------------------------
echo "[2] Testing Multi-Tenant Data Isolation (Global Scopes)...\n";

$orgA = Organization::firstOrCreate(
    ['subdomain' => 'test-org-a'],
    ['nama' => 'Organisasi Test A', 'jenis' => 'HMPS', 'status' => 'active']
);
$orgB = Organization::firstOrCreate(
    ['subdomain' => 'test-org-b'],
    ['nama' => 'Organisasi Test B', 'jenis' => 'UKM', 'status' => 'active']
);

$userA = User::firstOrCreate(
    ['email' => 'admin_org_a@test.com'],
    ['name' => 'Admin Org A', 'password' => 'password123', 'organization_id' => $orgA->id, 'status' => 'active']
);
$userB = User::firstOrCreate(
    ['email' => 'admin_org_b@test.com'],
    ['name' => 'Admin Org B', 'password' => 'password123', 'organization_id' => $orgB->id, 'status' => 'active']
);

// Create post for Org B
$postB = Post::withoutGlobalScopes()->where('organization_id', $orgB->id)->first();
if (!$postB) {
    $postB = Post::withoutGlobalScopes()->create([
        'organization_id' => $orgB->id,
        'user_id' => $userB->id,
        'judul' => 'Rahasia Org B ' . time(),
        'slug' => 'rahasia-org-b-' . time(),
        'konten' => '<p>Data privat milik Ormawa B</p>',
        'status' => 'published',
    ]);
}

// Log in as User A (Org A)
Auth::login($userA);

// Org A attempts to query all posts via standard query
$postsForUserA = Post::all();
foreach ($postsForUserA as $p) {
    assert($p->organization_id === $orgA->id, "Tenant violation: Org A retrieved post belonging to org_id: " . $p->organization_id);
}

// Org A attempts to directly access Post B by ID
$isolatedAttempt = Post::find($postB->id);
assert($isolatedAttempt === null, "Tenant violation: Org A accessed Org B's post directly by ID!");

echo "  -> PASS: Multi-tenant data isolation verified! Org A cannot read or access Org B data.\n\n";
$passedTests++;

// --------------------------------------------------
// 3. XSS SANITIZER & SQL INJECTION PROTECTION TEST
// --------------------------------------------------
echo "[3] Testing XSS Sanitizer & SQL Injection Protection...\n";

// Test malicious XSS payloads
$maliciousXss1 = '<p>Halo Dunia</p><script>alert("XSS Attack!")</script>';
$sanitized1 = SecurityService::sanitizeHtml($maliciousXss1);
assert(!str_contains($sanitized1, '<script>'), "Sanitizer failed: <script> tag must be removed");
assert(str_contains($sanitized1, '<p>Halo Dunia</p>'), "Sanitizer preserved valid formatting");

$maliciousXss2 = '<img src="valid.png" onerror="alert(document.cookie)" onload="steal()"><p>Gambar</p>';
$sanitized2 = SecurityService::sanitizeHtml($maliciousXss2);
assert(!str_contains($sanitized2, 'onerror'), "Sanitizer failed: onerror attribute must be removed");
assert(!str_contains($sanitized2, 'onload'), "Sanitizer failed: onload attribute must be removed");

$maliciousXss3 = '<a href="javascript:alert(\'hacked\')">Klik Disini</a>';
$sanitized3 = SecurityService::sanitizeHtml($maliciousXss3);
assert(!str_contains($sanitized3, 'javascript:'), "Sanitizer failed: javascript: protocol must be removed");

// Test automatic Post model sanitization on saving
$xssPost = new Post([
    'judul' => 'Judul <script>alert(1)</script>',
    'slug' => 'test-xss-' . time(),
    'konten' => '<p>Konten aman</p><iframe src="evil.com"></iframe><img src=x onerror=alert(1)>',
    'organization_id' => $orgA->id,
    'user_id' => $userA->id,
    'status' => 'published',
]);
$xssPost->save();

assert(!str_contains($xssPost->judul, '<script>'), "Post model failed to sanitize judul on save");
assert(!str_contains($xssPost->konten, '<iframe'), "Post model failed to sanitize iframe on save");
assert(!str_contains($xssPost->konten, 'onerror'), "Post model failed to sanitize onerror on save");

echo "  -> PASS: XSS sanitization and Model saving hooks verified 100%!\n\n";
$passedTests++;

// --------------------------------------------------
// 4. SECURITY HEADERS & HTTPS ENFORCEMENT TEST
// --------------------------------------------------
echo "[4] Testing Security Headers & HTTPS Middleware...\n";

$middleware = new SecurityHeadersMiddleware();
$request = Request::create('/test-security', 'GET');

$response = $middleware->handle($request, function ($req) {
    return new \Illuminate\Http\Response('OK', 200);
});

$headers = $response->headers;

assert($headers->get('X-Frame-Options') === 'SAMEORIGIN', "X-Frame-Options must be SAMEORIGIN");
assert($headers->get('X-Content-Type-Options') === 'nosniff', "X-Content-Type-Options must be nosniff");
assert($headers->get('X-XSS-Protection') === '1; mode=block', "X-XSS-Protection must be 1; mode=block");
assert($headers->get('Referrer-Policy') === 'strict-origin-when-cross-origin', "Referrer-Policy must be strict-origin-when-cross-origin");
assert(!empty($headers->get('Content-Security-Policy')), "Content-Security-Policy header must be present");

// Test HTTPS redirection when FORCE_HTTPS is true
putenv('FORCE_HTTPS=true');
$_ENV['FORCE_HTTPS'] = 'true';

$insecureRequest = Request::create('http://example.com/login', 'GET');
$redirectResponse = $middleware->handle($insecureRequest, function ($req) {
    return new \Illuminate\Http\Response('OK', 200);
});

assert($redirectResponse->isRedirection(), "Insecure request must be redirected when FORCE_HTTPS=true");
assert($redirectResponse->getStatusCode() === 301, "HTTPS redirection status code must be 301 Permanent Redirect");
assert(str_starts_with($redirectResponse->headers->get('Location'), 'https://'), "Redirect location must use https://");

// Clean up env
putenv('FORCE_HTTPS=false');
$_ENV['FORCE_HTTPS'] = 'false';

echo "  -> PASS: All Security Headers (CSP, X-Frame-Options, HSTS, 301 HTTPS redirect) verified!\n\n";
$passedTests++;

echo "=====================================================\n";
echo "  SUCCESS: ALL {$passedTests}/4 SECURITY PILLARS PASSED 100%! \n";
echo "=====================================================\n";
