<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CommitteeController extends Controller
{
    // 1. Ambil daftar pengurus (bisa difilter berdasarkan periode)
    public function index(Request $request)
    {
        $query = Committee::query();

        if ($request->has('period')) {
            $query->where('period', $request->period);
        }

        $committees = $query->latest()->get()->map(function ($item) {
            return [
                'id'          => $item->id,
                'name'        => $item->name,
                'position'    => $item->position,
                'department'  => $item->department,
                'period'      => $item->period,
                'photo_url'   => $item->photo_url,
                'status'      => $item->status,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $committees
        ]);
    }

    // 2. Tambah Data Pengurus Baru
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'position'   => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'period'     => 'required|string|max:20', // Misal: "2025/2026"
            'photo'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('committees', 'public');
        }

        $committee = Committee::create([
            'name'       => $request->name,
            'position'   => $request->position,
            'department' => $request->department,
            'period'     => $request->period,
            'photo'      => $photoPath,
            'status'     => 'active',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data pengurus berhasil ditambahkan!',
            'data'    => $committee
        ], 201);
    }

    // 3. Update Data Pengurus
    public function update(Request $request, $id)
    {
        $committee = Committee::findOrFail($id);

        $request->validate([
            'name'       => 'sometimes|required|string|max:255',
            'position'   => 'sometimes|required|string|max:255',
            'department' => 'nullable|string|max:255',
            'period'     => 'sometimes|required|string|max:20',
            'status'     => 'sometimes|required|in:active,inactive',
            'photo'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($committee->photo && Storage::disk('public')->exists($committee->photo)) {
                Storage::disk('public')->delete($committee->photo);
            }
            $committee->photo = $request->file('photo')->store('committees', 'public');
        }

        $committee->update($request->only(['name', 'position', 'department', 'period', 'status']));

        return response()->json([
            'status'  => 'success',
            'message' => 'Data pengurus berhasil diperbarui!',
            'data'    => $committee
        ]);
    }

    // 4. Hapus Data Pengurus
    public function destroy($id)
    {
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