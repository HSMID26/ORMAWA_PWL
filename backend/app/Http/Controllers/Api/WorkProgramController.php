<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkProgram;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WorkProgramController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAction($request, 'work_programs.view');
        $validated = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);
        $summary = WorkProgram::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as planned', [WorkProgram::STATUS_PLANNED])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as in_progress', [WorkProgram::STATUS_IN_PROGRESS])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed', [WorkProgram::STATUS_COMPLETED])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled', [WorkProgram::STATUS_CANCELLED])
            ->first();

        $programs = WorkProgram::query()
            ->with(['responsibleMember:id,name', 'organizationPeriod:id,period_name', 'creator:id,name'])
            ->orderByDesc('period_year')
            ->orderBy('name')
            ->paginate($validated['per_page'] ?? 20);

        return response()->json([
            'status' => 'success',
            'data' => $programs->items(),
            'meta' => [
                'current_page' => $programs->currentPage(),
                'last_page' => $programs->lastPage(),
                'per_page' => $programs->perPage(),
                'total' => $programs->total(),
            ],
            'summary' => [
                'total' => (int) $summary->total,
                'planned' => (int) $summary->planned,
                'in_progress' => (int) $summary->in_progress,
                'completed' => (int) $summary->completed,
                'cancelled' => (int) $summary->cancelled,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = $this->authorizeAction($request, 'work_programs.create');
        $validated = $this->validatedProgramData($request, $user, true);
        $validated['user_id'] = $user->id;

        $program = DB::transaction(function () use ($validated) {
            $program = WorkProgram::create($validated);
            ActivityLogService::log('create', 'work_programs', 'Membuat program kerja: ' . $program->name, $program);

            return $program->load(['responsibleMember:id,name', 'organizationPeriod:id,period_name', 'creator:id,name']);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Program kerja berhasil dibuat.',
            'data' => $program,
        ], 201);
    }

    public function show(Request $request, string $id)
    {
        $user = $this->authorizeAction($request, 'work_programs.view');
        $program = WorkProgram::withoutGlobalScopes()->findOrFail($id);
        $this->ensureOrganizationAccess($user, $program);

        return response()->json([
            'status' => 'success',
            'data' => $program->load(['responsibleMember:id,name', 'organizationPeriod:id,period_name', 'creator:id,name']),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $user = $this->authorizeAction($request, 'work_programs.update');
        $program = WorkProgram::withoutGlobalScopes()->findOrFail($id);
        $this->ensureOrganizationAccess($user, $program);
        $validated = $this->validatedProgramData($request, $user, false, (int) $program->organization_id);

        $program = DB::transaction(function () use ($program, $validated) {
            $program->update($validated);
            ActivityLogService::log('update', 'work_programs', 'Memperbarui program kerja: ' . $program->name, $program);

            return $program->load(['responsibleMember:id,name', 'organizationPeriod:id,period_name', 'creator:id,name']);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Program kerja berhasil diperbarui.',
            'data' => $program,
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $user = $this->authorizeAction($request, 'work_programs.delete');
        $program = WorkProgram::withoutGlobalScopes()->findOrFail($id);
        $this->ensureOrganizationAccess($user, $program);

        DB::transaction(function () use ($program) {
            ActivityLogService::log('delete', 'work_programs', 'Menghapus program kerja: ' . $program->name, $program);
            $program->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Program kerja berhasil dihapus.',
        ]);
    }

    private function authorizeAction(Request $request, string $permission): User
    {
        $user = $request->user();
        abort_unless($user && ($user->hasRole('Super Admin') || $user->can($permission)), 403, 'Anda tidak memiliki izin untuk mengakses program kerja.');

        return $user;
    }

    private function ensureOrganizationAccess(User $user, WorkProgram $program): void
    {
        abort_unless($user->hasRole('Super Admin') || (int) $program->organization_id === (int) $user->organization_id, 403, 'Anda tidak dapat mengakses program kerja organisasi lain.');
    }

    private function validatedProgramData(Request $request, User $user, bool $creating, ?int $existingOrganizationId = null): array
    {
        $organizationId = $user->hasRole('Super Admin')
            ? ($creating ? $request->input('organization_id') : $existingOrganizationId)
            : $user->organization_id;

        return $request->validate([
            'organization_id' => $user->hasRole('Super Admin') && $creating
                ? ['required', 'integer', 'exists:organizations,id']
                : ['prohibited'],
            'organization_period_id' => [
                'nullable',
                'integer',
                Rule::exists('organization_periods', 'id')->where('organization_id', $organizationId),
            ],
            'responsible_member_id' => [
                'nullable',
                'integer',
                Rule::exists('members', 'id')->where('organization_id', $organizationId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'objective' => ['required', 'string', 'max:10000'],
            'period_year' => ['required', 'integer', 'between:2000,2100'],
            'target_participants' => ['required', 'integer', 'min:0'],
            'estimated_budget' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'status' => ['required', Rule::in(WorkProgram::STATUSES)],
        ]);
    }
}
