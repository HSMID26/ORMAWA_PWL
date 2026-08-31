<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationRegistration;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_ormawa'  => ['required', 'string', 'max:255'],
            'jenis_ormawa' => ['required', 'in:HMPS,UKM,BEM,Senat,Lainnya'],
            'subdomain'    => ['required', 'string', 'regex:/^[a-z0-9\-]+$/', 'max:50', 'unique:organizations,subdomain', 'unique:organization_registrations,organization_subdomain'],
            'admin_name'   => ['required', 'string', 'max:255'],
            'admin_email'  => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:organization_registrations,admin_email', 'unique:'.User::class.',email'],
            'password'     => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $nameParts = explode(' ', trim($request->admin_name), 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? $nameParts[0];

        $reg = OrganizationRegistration::create([
            'organization_name'        => $request->nama_ormawa,
            'organization_type'        => $request->jenis_ormawa,
            'organization_subdomain'   => strtolower($request->subdomain),
            'admin_first_name'         => $firstName,
            'admin_last_name'          => $lastName,
            'admin_email'              => $request->admin_email,
            'admin_password'           => Hash::make($request->password),
            'status'                   => 'pending',
        ]);

        ActivityLogService::log('register', 'organization_registrations', 'Pendaftaran organisasi diajukan via Web: ' . $reg->organization_name, $reg, null, null);

        NotificationService::sendToSuperAdmins(
            'organization_registration',
            'Pendaftaran Organisasi Baru',
            "{$reg->organization_name} mengajukan pendaftaran organisasi baru.",
            '/super-admin/organization-registrations',
            [
                'registration_id'   => $reg->id,
                'organization_name' => $reg->organization_name,
                'organization_type' => $reg->organization_type,
            ]
        );

        return redirect()->route('login')->with('status', 'Pendaftaran Ormawa berhasil diajukan! Harap tunggu persetujuan dari Super Admin PKA.');
    }
}