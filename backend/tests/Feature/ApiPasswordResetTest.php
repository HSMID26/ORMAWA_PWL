<?php

use App\Models\User;
use App\Models\Organization;
use App\Models\ActivityLog;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Super Admin']);
    Role::firstOrCreate(['name' => 'Admin Organisasi']);
    Role::firstOrCreate(['name' => 'Editor']);
    Role::firstOrCreate(['name' => 'Kontributor']);
});

test('1. Guest can request password reset via API and notification is sent', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'member@iti.ac.id']);
    $user->assignRole('Admin Organisasi');

    $response = $this->postJson('/api/forgot-password', [
        'email' => 'member@iti.ac.id',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('2. Invalid email format returns 422 validation error', function () {
    $response = $this->postJson('/api/forgot-password', [
        'email' => 'not-a-valid-email',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('3. Reset request for non-existent email returns generic message without exposing user enumeration', function () {
    Notification::fake();

    $response = $this->postJson('/api/forgot-password', [
        'email' => 'nonexistent@iti.ac.id',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Jika email Anda terdaftar dalam sistem, tautan untuk mengatur ulang kata sandi telah dikirimkan ke email Anda.',
        ]);

    Notification::assertNothingSent();
});

test('4. Valid reset token allows resetting password and subsequent login', function () {
    $org = Organization::create([
        'nama' => 'Himpunan ITI',
        'jenis' => 'HMPS',
        'subdomain' => 'himpunan-iti',
        'status' => 'active',
    ]);

    $user = User::factory()->create([
        'email' => 'admin@himpunan.iti.ac.id',
        'password' => Hash::make('oldpassword123'),
        'organization_id' => $org->id,
        'status' => 'active',
    ]);
    $user->assignRole('Admin Organisasi');

    $token = Password::createToken($user);

    $response = $this->postJson('/api/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewSecurePassword123!',
        'password_confirmation' => 'NewSecurePassword123!',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Kata sandi Anda berhasil diperbarui. Silakan login kembali dengan kata sandi baru Anda.',
        ]);

    // Verify user can now log in with new password
    $loginResponse = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'NewSecurePassword123!',
    ]);

    $loginResponse->assertStatus(200)
        ->assertJsonStructure(['access_token', 'user']);
});

test('5. Invalid token is rejected with 422', function () {
    $user = User::factory()->create(['email' => 'editor@iti.ac.id']);

    $response = $this->postJson('/api/reset-password', [
        'token' => 'invalid-or-fake-token-12345',
        'email' => $user->email,
        'password' => 'NewSecurePassword123!',
        'password_confirmation' => 'NewSecurePassword123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('6. Password confirmation mismatch is rejected with 422', function () {
    $user = User::factory()->create(['email' => 'editor2@iti.ac.id']);
    $token = Password::createToken($user);

    $response = $this->postJson('/api/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewSecurePassword123!',
        'password_confirmation' => 'DifferentPassword123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('7. Short password (< 8 chars) is rejected with 422', function () {
    $user = User::factory()->create(['email' => 'editor3@iti.ac.id']);
    $token = Password::createToken($user);

    $response = $this->postJson('/api/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('8. Reset does not change user role, organization, or create unauthorized session', function () {
    $org = Organization::create([
        'nama' => 'BEM ITI',
        'jenis' => 'BEM',
        'subdomain' => 'bem-iti',
        'status' => 'active',
    ]);

    $user = User::factory()->create([
        'email' => 'kontributor@bem.iti.ac.id',
        'organization_id' => $org->id,
        'status' => 'active',
    ]);
    $user->assignRole('Kontributor');

    $token = Password::createToken($user);

    $response = $this->postJson('/api/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewSecurePass888!',
        'password_confirmation' => 'NewSecurePass888!',
    ]);

    $response->assertStatus(200);

    $freshUser = $user->fresh();
    expect($freshUser->hasRole('Kontributor'))->toBeTrue();
    expect($freshUser->organization_id)->toBe($org->id);
    expect($freshUser->status)->toBe('active');

    // Verify unauthenticated request to /api/me fails (no auto-login bypass)
    $meResponse = $this->getJson('/api/me');
    $meResponse->assertStatus(401);
});

test('9. Token cannot be reused a second time', function () {
    $user = User::factory()->create(['email' => 'singleuse@iti.ac.id']);
    $token = Password::createToken($user);

    // First use: success
    $this->postJson('/api/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'FirstPassword123!',
        'password_confirmation' => 'FirstPassword123!',
    ])->assertStatus(200);

    // Second use: must fail
    $this->postJson('/api/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'SecondPassword123!',
        'password_confirmation' => 'SecondPassword123!',
    ])->assertStatus(422);
});

test('10. Password and reset token never appear in Activity Logs', function () {
    $user = User::factory()->create(['email' => 'audituser@iti.ac.id']);
    $token = Password::createToken($user);

    $this->postJson('/api/forgot-password', ['email' => $user->email]);

    $this->postJson('/api/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'SecretPass999!',
        'password_confirmation' => 'SecretPass999!',
    ]);

    $logs = ActivityLog::where('user_id', $user->id)->get();
    foreach ($logs as $log) {
        expect($log->description)->not->toContain('SecretPass999!');
        expect($log->description)->not->toContain($token);
        if ($log->metadata) {
            $jsonMeta = json_encode($log->metadata);
            expect($jsonMeta)->not->toContain('SecretPass999!');
            expect($jsonMeta)->not->toContain($token);
        }
    }
});
