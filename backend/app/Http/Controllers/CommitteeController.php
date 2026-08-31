<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use App\Models\OrganizationPeriod;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CommitteeController extends Controller
{
    // 1. Ambil daftar pengurus (bisa difilter berdasarkan periode string, organization_period_id, atau status)
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('structure.view'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk melihat data pengurus.');
        }

        $query = Committee::with(['user:id,name,email', 'organizationPeriod', 'organization:id,nama']);

        if (!$user->hasRole('Super Admin')) {
            $query->where('organization_id', $user->organization_id);
        }

        if ($request->has('period') && !empty($request->period)) {
            $query->where(function($q) use ($request) {
                $q->where('period', $request->period)
                  ->orWhereHas('organizationPeriod', function($qp) use ($request) {
                      $qp->where('period_name', $request->period);
                  });
            });
        }

        if ($request->has('organization_period_id') && !empty($request->organization_period_id)) {
            $query->where('organization_period_id', $request->organization_period_id);
        }

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%' . trim($request->search) . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('position', 'like', $searchTerm)
                  ->orWhere('department', 'like', $searchTerm);
            });
        }

        $committees = $query->latest()->get()->map(function ($item) {
            return [
                'id'                     => $item->id,
                'organization_id'        => $item->organization_id,
                'organization_period_id' => $item->organization_period_id,
                'period_name'            => $item->organizationPeriod?->period_name ?? $item->period,
                'name'                   => $item->name,
                'position'               => $item->position,
                'department'             => $item->department,
                'period'                 => $item->period,
                'photo'                  => $item->photo,
                'photo_url'              => $item->photo_url,
                'status'                 => $item->status,
                'user_id'                => $item->user_id,
                'user'                   => $item->user,
                'organization_period'    => $item->organizationPeriod,
                'organization'           => $item->organization,
                'created_at'             => $item->created_at?->toIso8601String(),
                'updated_at'             => $item->updated_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $committees
        ]);
    }

    /**
     * 2. Ambil detail 1 pengurus
     */
    public function show($id)
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('structure.view'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk melihat data pengurus.');
        }

        $committee = Committee::with(['user:id,name,email', 'organizationPeriod', 'organization:id,nama'])->findOrFail($id);

        if (!$user->hasRole('Super Admin') && (int)$committee->organization_id !== (int)$user->organization_id) {
            abort(403, 'Unauthorized. Anda tidak dapat melihat pengurus organisasi lain.');
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'                     => $committee->id,
                'organization_id'        => $committee->organization_id,
                'organization_period_id' => $committee->organization_period_id,
                'period_name'            => $committee->organizationPeriod?->period_name ?? $committee->period,
                'name'                   => $committee->name,
                'position'               => $committee->position,
                'department'             => $committee->department,
                'period'                 => $committee->period,
                'photo'                  => $committee->photo,
                'photo_url'              => $committee->photo_url,
                'status'                 => $committee->status,
                'user_id'                => $committee->user_id,
                'user'                   => $committee->user,
                'organization_period'    => $committee->organizationPeriod,
                'organization'           => $committee->organization,
                'created_at'             => $committee->created_at?->toIso8601String(),
                'updated_at'             => $committee->updated_at?->toIso8601String(),
            ]
        ]);
    }

    /**
     * 3. Ambil daftar user yang valid untuk ditautkan ke pengurus (Tenant-Isolated, No Super Admin, Active Only)
     */
    public function linkableUsers(Request $request)
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('structure.manage'))) {
            abort(403, 'Unauthorized. Anda tidak memiliki izin untuk mengelola struktur pengurus.');
        }

        // Tentukan organization_id dari konteks login
        $organizationId = $user->hasRole('Super Admin') && $request->has('organization_id')
            ? (int) $request->organization_id
            : (int) $user->organization_id;

        if (!$organizationId) {
            return response()->json([
                'status' => 'success',
                'data'   => []
            ]);
        }

        $periodId = $request->get('organization_period_id');
        $currentCommitteeId = $request->get('current_committee_id');

        // Query user aktif di organisasi ini, BUKAN Super Admin, organization_id tidak null
        $users = User::query()
            ->where('organization_id', $organizationId)
            ->where('status', 'active')
            ->whereNotNull('organization_id')
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Super Admin');
            })
            ->with('roles')
            ->orderBy('name')
            ->get();

        // Identifikasi user yang sudah ditautkan ke pengurus lain pada periode yang sama
        $alreadyLinkedUserIds = [];
        if ($periodId) {
            $alreadyLinkedQuery = Committee::where('organization_id', $organizationId)
                ->where('organization_period_id', $periodId)
                ->whereNotNull('user_id');

            if ($currentCommitteeId) {
                $alreadyLinkedQuery->where('id', '!=', (int)$currentCommitteeId);
            }

            $alreadyLinkedUserIds = $alreadyLinkedQuery->pluck('user_id')->map(fn($id) => (int)$id)->all();
        }

        $data = $users->map(function ($u) use ($alreadyLinkedUserIds) {
            $roleName = $u->roles->first()?->name ?? 'Anggota';
            return [
                'id'                => $u->id,
                'name'              => $u->name,
                'email'             => $u->email,
                'role'              => $roleName,
                'organization_id'   => $u->organization_id,
                'status'            => $u->status,
                'is_already_linked' => in_array((int)$u->id, $alreadyLinkedUserIds, true),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $data
        ]);
    }

    /**
     * 4. Tambah Data Pengurus Baru (Hanya Admin Organisasi / Super Admin)
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('structure.manage'))) {
            abort(403, 'Anda tidak memiliki izin untuk menambah data pengurus.');
        }

        $request->validate([
            'name'                   => 'required|string|max:255',
            'position'               => 'required|string|max:255',
            'department'             => 'nullable|string|max:255',
            'period'                 => 'required_without:organization_period_id|nullable|string|max:100',
            'organization_period_id' => 'nullable|exists:organization_periods,id',
            'photo'                  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'user_id'                => 'nullable|exists:users,id',
            'status'                 => 'nullable|in:active,inactive',
        ]);

        // Tentukan organization_id dari authenticated user (Super Admin boleh kirim organization_id)
        $organizationId = $user->hasRole('Super Admin') && $request->has('organization_id') 
            ? (int) $request->organization_id 
            : (int) $user->organization_id;

        // Validasi kepemilikan organization_period_id
        if ($request->organization_period_id) {
            $periodModel = OrganizationPeriod::find($request->organization_period_id);
            if ($periodModel && (int)$periodModel->organization_id !== $organizationId && !$user->hasRole('Super Admin')) {
                abort(422, 'Periode kepengurusan yang dipilih tidak valid untuk organisasi ini.');
            }
        }

        // Validasi user_id (Keamanan Linking Akun)
        $linkedUser = null;
        if ($request->user_id) {
            $linkedUser = User::with('roles')->find($request->user_id);
            if (!$linkedUser) {
                abort(422, 'Akun yang dipilih tidak ditemukan.');
            }

            // 1. Cegah Super Admin ditautkan (Super Admin tidak boleh ditautkan ke ormawa manapun)
            if ($linkedUser->hasRole('Super Admin')) {
                abort(422, 'Akun Super Admin tidak dapat ditautkan sebagai pengurus organisasi.');
            }

            // 2. Tenant Isolation
            if ((int)$linkedUser->organization_id !== $organizationId && !$user->hasRole('Super Admin')) {
                abort(422, 'Akun yang dipilih tidak dapat ditautkan ke organisasi ini.');
            }

            // 3. Pastikan user aktif
            if ($linkedUser->status !== 'active') {
                abort(422, 'Akun yang dipilih tidak aktif dan tidak dapat ditautkan.');
            }

            // 4. Cegah duplicate linking dalam periode yang sama
            $periodIdToCheck = $request->organization_period_id;
            if ($periodIdToCheck) {
                $isAlreadyLinked = Committee::where('organization_id', $organizationId)
                    ->where('organization_period_id', $periodIdToCheck)
                    ->where('user_id', $linkedUser->id)
                    ->exists();

                if ($isAlreadyLinked) {
                    abort(422, 'Akun ini sudah ditautkan ke pengurus lain pada periode tersebut.');
                }
            }
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('committees', 'public');
        }

        $periodString = $request->period;
        if ($request->organization_period_id && !$periodString) {
            $orgPeriod = OrganizationPeriod::find($request->organization_period_id);
            $periodString = $orgPeriod?->period_name;
        }

        $committee = Committee::create([
            'organization_id'        => $organizationId,
            'organization_period_id' => $request->organization_period_id,
            'user_id'                => $request->user_id,
            'name'                   => trim($request->name),
            'position'               => trim($request->position),
            'department'             => $request->department ? trim($request->department) : null,
            'period'                 => $periodString ?? '-',
            'photo'                  => $photoPath,
            'status'                 => $request->status ?? 'active',
        ]);

        // Audit Trail Activity Log jika akun ditautkan
        if ($committee->user_id && $linkedUser) {
            ActivityLogService::log(
                'committee_account_linked',
                'committees',
                "Akun {$linkedUser->name} ditautkan ke pengurus {$committee->name}",
                $committee,
                [
                    'organization_id'   => $organizationId,
                    'committee_id'      => $committee->id,
                    'committee_name'    => $committee->name,
                    'user_id'           => $linkedUser->id,
                    'user_name'         => $linkedUser->name,
                    'period_id'         => $committee->organization_period_id,
                    'period_name'       => $committee->period,
                ]
            );
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Data pengurus berhasil ditambahkan!',
            'data'    => $committee->load(['user:id,name,email', 'organizationPeriod'])
        ], 201);
    }

    /**
     * 5. Update Data Pengurus (Hanya Admin Organisasi / Super Admin)
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('structure.manage'))) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah data pengurus.');
        }

        $committee = Committee::with('user')->findOrFail($id);
        $organizationId = (int) $committee->organization_id;

        $request->validate([
            'name'                   => 'sometimes|required|string|max:255',
            'position'               => 'sometimes|required|string|max:255',
            'department'             => 'nullable|string|max:255',
            'period'                 => 'nullable|string|max:100',
            'organization_period_id' => 'nullable|exists:organization_periods,id',
            'status'                 => 'sometimes|required|in:active,inactive',
            'photo'                  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'user_id'                => 'nullable|exists:users,id',
        ]);

        // Validasi kepemilikan organization_period_id
        if ($request->has('organization_period_id') && $request->organization_period_id) {
            $periodModel = OrganizationPeriod::find($request->organization_period_id);
            if ($periodModel && (int)$periodModel->organization_id !== $organizationId && !$user->hasRole('Super Admin')) {
                abort(422, 'Periode kepengurusan yang dipilih tidak valid untuk organisasi ini.');
            }
        }

        // Validasi user_id jika diubah atau dikirim
        $newLinkedUser = null;
        if ($request->has('user_id') && $request->user_id) {
            $newLinkedUser = User::with('roles')->find($request->user_id);
            if (!$newLinkedUser) {
                abort(422, 'Akun yang dipilih tidak ditemukan.');
            }

            // 1. Cegah Super Admin ditautkan (Super Admin tidak boleh ditautkan ke ormawa manapun)
            if ($newLinkedUser->hasRole('Super Admin')) {
                abort(422, 'Akun Super Admin tidak dapat ditautkan sebagai pengurus organisasi.');
            }

            // 2. Tenant Isolation
            if ((int)$newLinkedUser->organization_id !== $organizationId && !$user->hasRole('Super Admin')) {
                abort(422, 'Akun yang dipilih tidak dapat ditautkan ke organisasi ini.');
            }

            // 3. Pastikan user aktif
            if ($newLinkedUser->status !== 'active') {
                abort(422, 'Akun yang dipilih tidak aktif dan tidak dapat ditautkan.');
            }

            // 4. Cegah duplicate linking dalam periode yang sama
            $periodIdToCheck = $request->organization_period_id ?? $committee->organization_period_id;
            if ($periodIdToCheck) {
                $isAlreadyLinked = Committee::where('organization_id', $organizationId)
                    ->where('organization_period_id', $periodIdToCheck)
                    ->where('user_id', $newLinkedUser->id)
                    ->where('id', '!=', $committee->id)
                    ->exists();

                if ($isAlreadyLinked) {
                    abort(422, 'Akun ini sudah ditautkan ke pengurus lain pada periode tersebut.');
                }
            }
        }

        $updateData = $request->only(['name', 'position', 'department', 'period', 'organization_period_id', 'status', 'user_id']);

        if ($request->hasFile('photo')) {
            if ($committee->photo && Storage::disk('public')->exists($committee->photo)) {
                Storage::disk('public')->delete($committee->photo);
            }
            $updateData['photo'] = $request->file('photo')->store('committees', 'public');
        }

        if ($request->has('organization_period_id') && !$request->has('period')) {
            $orgPeriod = OrganizationPeriod::find($request->organization_period_id);
            if ($orgPeriod) {
                $updateData['period'] = $orgPeriod->period_name;
            }
        }

        $previousUserId = $committee->user_id;
        $previousUser = $committee->user;

        $committee->update($updateData);

        // Audit Trail Activity Log untuk linking / unlinking
        if ($request->has('user_id')) {
            $newUserId = $request->user_id ? (int)$request->user_id : null;
            if ($previousUserId && !$newUserId) {
                // Unlinked
                ActivityLogService::log(
                    'committee_account_unlinked',
                    'committees',
                    "Tautan akun dilepas dari pengurus {$committee->name}",
                    $committee,
                    [
                        'organization_id'    => $organizationId,
                        'committee_id'       => $committee->id,
                        'previous_user_id'   => $previousUserId,
                        'previous_user_name' => $previousUser?->name,
                        'period_id'          => $committee->organization_period_id,
                    ]
                );
            } elseif ($newUserId && $newUserId !== (int)$previousUserId && $newLinkedUser) {
                // Linked to a new user
                ActivityLogService::log(
                    'committee_account_linked',
                    'committees',
                    "Akun {$newLinkedUser->name} ditautkan ke pengurus {$committee->name}",
                    $committee,
                    [
                        'organization_id'   => $organizationId,
                        'committee_id'      => $committee->id,
                        'committee_name'    => $committee->name,
                        'user_id'           => $newLinkedUser->id,
                        'user_name'         => $newLinkedUser->name,
                        'period_id'         => $committee->organization_period_id,
                        'period_name'       => $committee->period,
                    ]
                );
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Data pengurus berhasil diperbarui!',
            'data'    => $committee->load(['user:id,name,email', 'organizationPeriod'])
        ]);
    }

    /**
     * 6. Hapus Data Pengurus (Hanya Admin Organisasi / Super Admin)
     */
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('structure.manage'))) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus data pengurus.');
        }

        $committee = Committee::findOrFail($id);

        if ($committee->photo && Storage::disk('public')->exists($committee->photo)) {
            Storage::disk('public')->delete($committee->photo);
        }

        $committee->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data pengurus berhasil dihapus!'
        ]);
    }
}
