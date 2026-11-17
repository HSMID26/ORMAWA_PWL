<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrganizationRegistration;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Services\ActivityLogService;

class OrganizationRegistrationController extends Controller
{
    /**
     * PUBLIC API: Submit pendaftaran baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'organization_name' => 'required|string|max:255',
            'organization_type' => ['required', Rule::in(['HMPS', 'UKM', 'BEM', 'Senat', 'Lainnya'])],
            'organization_subdomain' => [
                'required',
                'string',
                'regex:/^[a-z0-9\-]+$/',
                function ($attribute, $value, $fail) {
                    if (Organization::where('subdomain', $value)->exists() ||
                        OrganizationRegistration::where('organization_subdomain', $value)
                            ->where('status', 'pending')->exists()) {
                        $fail('Subdomain sudah digunakan atau sedang dalam pengajuan.');
                    }
                }
            ],
            'organization_logo' => 'nullable|string',
            'organization_description' => 'nullable|string',

            'admin_first_name' => 'required|string|max:255',
            'admin_last_name' => 'required|string|max:255',
            'admin_email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    if (User::where('email', $value)->exists() ||
                        OrganizationRegistration::where('admin_email', $value)
                            ->where('status', 'pending')->exists()) {
                        $fail('Email sudah digunakan atau sedang dalam pengajuan.');
                    }
                }
            ],
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        $registration = OrganizationRegistration::create([
            'organization_name' => $validated['organization_name'],
            'organization_type' => $validated['organization_type'],
            'organization_subdomain' => $validated['organization_subdomain'],
            'organization_logo' => $validated['organization_logo'] ?? null,
            'organization_description' => $validated['organization_description'] ?? null,
            'admin_first_name' => $validated['admin_first_name'],
            'admin_last_name' => $validated['admin_last_name'],
            'admin_email' => $validated['admin_email'],
            'admin_password' => Hash::make($validated['admin_password']),
            'status' => 'pending',
        ]);

        // Explicitly set user to null since guest makes this request
        ActivityLogService::log('register', 'organization_registrations', 'Pendaftaran organisasi baru disubmit: ' . $registration->organization_name, $registration, null, null);

        // Notify Super Admins
        \App\Services\NotificationService::sendToSuperAdmins(
            'organization_registration',
            'Pendaftaran Organisasi Baru',
            "{$registration->organization_name} mengajukan pendaftaran organisasi baru.",
            '/super-admin/organization-registrations',
            [
                'registration_id' => $registration->id,
                'organization_name' => $registration->organization_name,
                'organization_type' => $registration->organization_type,
            ]
        );

        return response()->json([
            'message' => 'Pendaftaran organisasi berhasil dikirim dan menunggu persetujuan Super Admin.',
            'data' => [
                'id' => $registration->id,
                'organization_name' => $registration->organization_name,
                'status' => $registration->status,
            ]
        ], 201);
    }

    /**
     * SUPER ADMIN: List pendaftaran
     */
    public function index(Request $request)
    {
        // Must be Super Admin
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Unauthorized.');
        }

        $query = OrganizationRegistration::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            // Default to pending first, but if want to just show all, we can order by
            $query->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')");
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('organization_name', 'like', "%{$search}%")
                  ->orWhere('admin_email', 'like', "%{$search}%")
                  ->orWhere('organization_subdomain', 'like', "%{$search}%");
            });
        }

        $query->orderBy('created_at', 'desc');

        $registrations = $query->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $registrations->items(),
            'meta' => [
                'current_page' => $registrations->currentPage(),
                'last_page' => $registrations->lastPage(),
                'total' => $registrations->total(),
            ]
        ]);
    }

    /**
     * SUPER ADMIN: Detail pendaftaran
     */
    public function show($id)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Unauthorized.');
        }

        $registration = OrganizationRegistration::with('reviewer:id,name,email')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $registration
        ]);
    }

    /**
     * SUPER ADMIN: Approve pendaftaran
     */
    public function approve($id)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Unauthorized.');
        }

        try {
            DB::beginTransaction();

            $registration = OrganizationRegistration::where('id', $id)->lockForUpdate()->firstOrFail();

            if ($registration->status === 'approved') {
                DB::rollBack();
                return response()->json([
                    'message' => 'Pendaftaran ini sudah diproses.'
                ], 422);
            }

            if ($registration->status !== 'pending') {
                DB::rollBack();
                return response()->json([
                    'message' => 'Hanya pendaftaran dengan status pending yang dapat disetujui.'
                ], 422);
            }

            // Re-validate uniqueness just in case
            if (Organization::where('subdomain', $registration->organization_subdomain)->exists() ||
                User::where('email', $registration->admin_email)->exists()) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Subdomain atau email sudah digunakan oleh entitas lain.'
                ], 422);
            }

            // Create Organization
            $org = Organization::create([
                'nama' => $registration->organization_name,
                'jenis' => $registration->organization_type,
                'subdomain' => $registration->organization_subdomain,
                'logo' => $registration->organization_logo,
                'warna_tema' => '#00346F', // Institutional Default
            ]);

            // Provision default categories for the new organization
            \App\Http\Controllers\CategoryController::ensureDefaultCategories($org->id);

            // Create User
            $user = User::create([
                'name' => $registration->admin_first_name . ' ' . $registration->admin_last_name,
                'email' => $registration->admin_email,
                'password' => $registration->admin_password, // Already hashed
                'organization_id' => $org->id,
                'status' => 'active',
            ]);

            $user->assignRole('Admin Organisasi');

            // Update registration
            $registration->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            DB::commit();

            ActivityLogService::log('approve', 'organization_registrations', 'Pendaftaran organisasi disetujui: ' . $registration->organization_name, $registration);

            // Trigger Notification to the new Admin Organisasi
            \App\Services\NotificationService::send(
                $user,
                'organization_registration_approved',
                'Pendaftaran Organisasi Disetujui',
                "Pendaftaran organisasi Anda telah disetujui. Anda sekarang dapat login ke ORMAWA ITI.",
                '/login'
            );

            return response()->json([
                'message' => 'Pendaftaran organisasi berhasil disetujui.',
                'organization' => $org,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => 'Admin Organisasi',
                    'organization_id' => $user->organization_id
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyetujui pendaftaran.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * SUPER ADMIN: Reject pendaftaran
     */
    public function reject(Request $request, $id)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Unauthorized.');
        }

        $reason = is_string($request->reason) ? trim($request->reason) : '';
        $request->merge(['reason' => $reason]);

        $validated = $request->validate([
            'reason' => 'required|string|min:5',
        ], [
            'reason.required' => 'Alasan penolakan wajib diisi.',
            'reason.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $registration = OrganizationRegistration::findOrFail($id);

        if ($registration->status !== 'pending') {
            return response()->json([
                'message' => 'Hanya pendaftaran dengan status pending yang dapat ditolak.'
            ], 422);
        }

        $registration->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['reason'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        ActivityLogService::log('reject', 'organization_registrations', 'Pendaftaran organisasi ditolak: ' . $registration->organization_name, $registration, ['reason' => $validated['reason']]);

        return response()->json([
            'message' => 'Pendaftaran berhasil ditolak.',
            'data' => $registration
        ]);
    }
}
