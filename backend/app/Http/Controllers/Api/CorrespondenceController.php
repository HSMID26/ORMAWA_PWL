<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Correspondence;
use App\Models\User;
use App\Services\CorrespondenceWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CorrespondenceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAction($request, 'correspondence.view');
        $validated = $request->validate([
            'type' => ['sometimes', 'nullable', Rule::in(Correspondence::TYPES)],
            'status' => ['sometimes', 'nullable', Rule::in(Correspondence::STATUSES)],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);
        $query = Correspondence::with(['creator:id,name', 'reviewer:id,name', 'organization:id,nama'])
            ->latest('letter_date');

        if (isset($validated['type'])) {
            $query->where('type', $validated['type']);
        }
        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $letters = $query->paginate($validated['per_page'] ?? 20);

        return response()->json([
            'status' => 'success',
            'data' => $letters->items(),
            'meta' => [
                'current_page' => $letters->currentPage(),
                'last_page' => $letters->lastPage(),
                'per_page' => $letters->perPage(),
                'total' => $letters->total(),
            ],
        ]);
    }

    public function store(Request $request, CorrespondenceWorkflowService $workflow)
    {
        $user = $this->authorizeAction($request, 'correspondence.create');
        $data = $this->validatedData($request, $user, true);
        $letter = $workflow->create($data, $user, $request->file('attachment'));

        return response()->json(['status' => 'success', 'data' => $letter], 201);
    }

    public function show(Request $request, string $id)
    {
        $user = $this->authorizeAction($request, 'correspondence.view');
        $letter = Correspondence::withoutGlobalScopes()->findOrFail($id);
        $this->ensureOrganizationAccess($user, $letter);

        return response()->json(['status' => 'success', 'data' => $letter->load(['creator:id,name', 'reviewer:id,name', 'organization:id,nama'])]);
    }

    public function update(Request $request, string $id, CorrespondenceWorkflowService $workflow)
    {
        $user = $this->authorizeAction($request, 'correspondence.update');
        $letter = Correspondence::withoutGlobalScopes()->findOrFail($id);
        $this->ensureOrganizationAccess($user, $letter);
        abort_unless(in_array($letter->status, ['draft', 'revision_requested'], true), 409, 'Surat yang sudah diajukan tidak dapat diubah.');

        $data = $this->validatedData($request, $user, false, (int) $letter->organization_id, (int) $letter->id);
        $letter = $workflow->update($letter, $data, $request->file('attachment'));

        return response()->json(['status' => 'success', 'data' => $letter]);
    }

    public function submit(Request $request, string $id, CorrespondenceWorkflowService $workflow)
    {
        $user = $this->authorizeAction($request, 'correspondence.update');
        $letter = Correspondence::withoutGlobalScopes()->findOrFail($id);
        $this->ensureOrganizationAccess($user, $letter);
        abort_unless(in_array($letter->status, ['draft', 'revision_requested'], true), 409, 'Status surat tidak dapat diajukan.');

        return response()->json(['status' => 'success', 'data' => $workflow->submit($letter)]);
    }

    public function review(Request $request, string $id, CorrespondenceWorkflowService $workflow)
    {
        $user = $this->authorizeAction($request, 'correspondence.review');
        $letter = Correspondence::withoutGlobalScopes()->findOrFail($id);
        $this->ensureOrganizationAccess($user, $letter);
        abort_unless($letter->status === 'submitted', 409, 'Hanya surat yang diajukan dapat ditinjau.');
        $validated = $request->validate([
            'action' => ['required', Rule::in(['approve', 'reject', 'request_revision'])],
            'review_note' => ['required_if:action,reject,request_revision', 'nullable', 'string', 'max:2000'],
        ]);
        $status = [
            'approve' => 'approved',
            'reject' => 'rejected',
            'request_revision' => 'revision_requested',
        ][$validated['action']];

        $letter = $workflow->review($letter, $user, $status, $validated['review_note'] ?? null);
        return response()->json(['status' => 'success', 'data' => $letter]);
    }

    public function attachment(Request $request, string $id)
    {
        $user = $this->authorizeAction($request, 'correspondence.view');
        $letter = Correspondence::withoutGlobalScopes()->findOrFail($id);
        $this->ensureOrganizationAccess($user, $letter);
        abort_unless($letter->attachment_path && Storage::disk('local')->exists($letter->attachment_path), 404, 'Lampiran tidak ditemukan.');

        return Storage::disk('local')->download($letter->attachment_path, $letter->attachment_name);
    }

    public function destroy(Request $request, string $id, CorrespondenceWorkflowService $workflow)
    {
        $user = $this->authorizeAction($request, 'correspondence.delete');
        $letter = Correspondence::withoutGlobalScopes()->findOrFail($id);
        $this->ensureOrganizationAccess($user, $letter);
        abort_unless(in_array($letter->status, ['draft', 'rejected', 'revision_requested'], true), 409, 'Surat yang sedang ditinjau atau disetujui tidak dapat dihapus.');

        $workflow->delete($letter);

        return response()->json(['status' => 'success', 'message' => 'Surat berhasil dihapus.']);
    }

    private function authorizeAction(Request $request, string $permission): User
    {
        $user = $request->user();
        abort_unless($user && ($user->hasRole('Super Admin') || $user->can($permission)), 403, 'Anda tidak memiliki izin untuk mengakses surat-menyurat.');
        return $user;
    }

    private function ensureOrganizationAccess(User $user, Correspondence $letter): void
    {
        abort_unless($user->hasRole('Super Admin') || (int) $letter->organization_id === (int) $user->organization_id, 403, 'Anda tidak dapat mengakses surat organisasi lain.');
    }

    private function validatedData(Request $request, User $user, bool $creating, ?int $existingOrganizationId = null, ?int $existingId = null): array
    {
        $organizationId = $user->hasRole('Super Admin')
            ? ($creating ? $request->input('organization_id') : $existingOrganizationId)
            : $user->organization_id;

        return $request->validate([
            'organization_id' => $user->hasRole('Super Admin') && $creating
                ? ['required', 'integer', 'exists:organizations,id']
                : ['prohibited'],
            'reference_number' => [
                'required', 'string', 'max:80',
                Rule::unique('correspondences', 'reference_number')
                    ->where('organization_id', $organizationId)
                    ->ignore($existingId),
            ],
            'type' => ['required', Rule::in(Correspondence::TYPES)],
            'sender' => ['required', 'string', 'max:255'],
            'recipient' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'letter_date' => ['required', 'date_format:Y-m-d'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'attachment' => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx'],
        ]);
    }
}
