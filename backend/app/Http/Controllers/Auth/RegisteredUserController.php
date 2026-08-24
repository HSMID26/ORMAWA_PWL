<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use App\Models\User;
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
            'subdomain'    => ['required', 'string', 'alpha_dash', 'max:50', 'unique:pending_registrations,subdomain', 'unique:organizations,subdomain'],
            'admin_name'   => ['required', 'string', 'max:255'],
            'admin_email'  => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:pending_registrations,admin_email', 'unique:'.User::class.',email'],
            'password'     => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        PendingRegistration::create([
            'nama_ormawa'    => $request->nama_ormawa,
            'jenis_ormawa'   => $request->jenis_ormawa,
            'subdomain'      => strtolower($request->subdomain),
            'admin_name'     => $request->admin_name,
            'admin_email'    => $request->admin_email,
            'admin_password' => Hash::make($request->password),
            'status'         => 'pending',
        ]);

        return redirect()->route('login')->with('status', 'Pendaftaran Ormawa berhasil diajukan! Harap tunggu persetujuan dari Super Admin PKA.');
    }
}