<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityWebController extends Controller
{
    /**
     * Tampilkan Halaman Agenda Kegiatan Ormawa
     */
    public function index()
    {
        $activities = Activity::latest()->get(); 
        return view('activities.index', compact('activities'));
    }

    /**
     * Simpan Agenda Baru (Web CMS / AJAX)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'start_time'  => 'required|date',
            'end_time'    => 'nullable|date|after_or_equal:start_time',
            'location'    => 'nullable|string|max:255',
            'status'      => 'required|string|in:upcoming,ongoing,completed,cancelled,draft,published',
            'description' => 'nullable|string',
        ]);

        $activity = Activity::create([
            'user_id'             => Auth::id(),
            'title'               => $validated['title'],
            'judul'               => $validated['title'],
            'description'         => $validated['description'] ?? null,
            'deskripsi'           => $validated['description'] ?? null,
            'location'            => $validated['location'] ?? null,
            'start_time'          => $validated['start_time'],
            'tanggal_pelaksanaan' => $validated['start_time'],
            'end_time'            => $validated['end_time'] ?? null,
            'status'              => $validated['status'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Agenda kegiatan berhasil disimpan!',
                'data'    => $activity
            ]);
        }

        return redirect()->route('activities.index')->with('success', 'Agenda kegiatan berhasil disimpan!');
    }

    /**
     * Update Agenda Kegiatan (Web CMS / AJAX)
     */
    public function update(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'start_time'  => 'required|date',
            'end_time'    => 'nullable|date|after_or_equal:start_time',
            'location'    => 'nullable|string|max:255',
            'status'      => 'required|string|in:upcoming,ongoing,completed,cancelled,draft,published',
            'description' => 'nullable|string',
        ]);

        $activity->update([
            'title'               => $validated['title'],
            'judul'               => $validated['title'],
            'description'         => $validated['description'] ?? null,
            'deskripsi'           => $validated['description'] ?? null,
            'location'            => $validated['location'] ?? null,
            'start_time'          => $validated['start_time'],
            'tanggal_pelaksanaan' => $validated['start_time'],
            'end_time'            => $validated['end_time'] ?? null,
            'status'              => $validated['status'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Agenda kegiatan berhasil diperbarui!',
                'data'    => $activity
            ]);
        }

        return redirect()->route('activities.index')->with('success', 'Agenda kegiatan berhasil diperbarui!');
    }

    /**
     * Hapus Agenda Kegiatan (Web CMS / AJAX)
     */
    public function destroy(string $id)
    {
        $activity = Activity::findOrFail($id);
        $activity->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Agenda berhasil dihapus!'
            ]);
        }

        return redirect()->route('activities.index')->with('success', 'Agenda berhasil dihapus!');
    }

    /**
     * Generate iCal Feed (.ics) untuk Sinkronisasi Kalender Publik (Google / Apple Calendar)
     */
    public function icalFeed()
    {
        // Ambil semua agenda kegiatan publik
        $activities = Activity::withoutGlobalScopes()->latest()->get();

        $ics  = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//CMS ORMAWA//Agenda Kegiatan//ID\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";
        $ics .= "X-WR-CALNAME:Kalender Agenda Ormawa\r\n";
        $ics .= "X-WR-TIMEZONE:Asia/Jakarta\r\n";

        foreach ($activities as $act) {
            $startTime = $act->start_time ?? $act->tanggal_pelaksanaan ?? $act->created_at;
            $endTime   = $act->end_time ?? ($startTime ? $startTime->copy()->addHours(2) : now()->addHours(2));

            $title       = $this->escapeIcal($act->title ?? $act->judul ?? 'Agenda Kegiatan');
            $description = $this->escapeIcal($act->description ?? $act->deskripsi ?? '');
            $location    = $this->escapeIcal($act->location ?? 'Lokasi Ormawa');
            $uid         = 'activity-' . $act->id . '@cms-ormawa';

            $ics .= "BEGIN:VEVENT\r\n";
            $ics .= "UID:{$uid}\r\n";
            $ics .= "DTSTAMP:" . now()->utc()->format('Ymd\THis\Z') . "\r\n";
            $ics .= "DTSTART:" . ($startTime ? $startTime->utc()->format('Ymd\THis\Z') : now()->utc()->format('Ymd\THis\Z')) . "\r\n";
            $ics .= "DTEND:" . ($endTime ? $endTime->utc()->format('Ymd\THis\Z') : now()->addHours(2)->utc()->format('Ymd\THis\Z')) . "\r\n";
            $ics .= "SUMMARY:{$title}\r\n";
            if (!empty($description)) {
                $ics .= "DESCRIPTION:{$description}\r\n";
            }
            if (!empty($location)) {
                $ics .= "LOCATION:{$location}\r\n";
            }
            $ics .= "STATUS:CONFIRMED\r\n";
            $ics .= "END:VEVENT\r\n";
        }

        $ics .= "END:VCALENDAR\r\n";

        return response($ics, 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="calendar.ics"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);
    }

    private function escapeIcal($text)
    {
        return str_replace(["\\", ";", ",", "\n", "\r"], ["\\\\", "\\;", "\\,", "\\n", ""], strip_tags($text ?? ''));
    }
}