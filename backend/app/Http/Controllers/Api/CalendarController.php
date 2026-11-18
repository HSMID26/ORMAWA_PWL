<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function events(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $validated = $request->validate([
            'start' => ['required', 'date_format:Y-m-d'],
            'end' => ['required', 'date_format:Y-m-d', 'after:start'],
        ]);

        $query = Activity::query()
            ->with('organization:id,nama')
            ->whereDate('tanggal_pelaksanaan', '>=', $validated['start'])
            ->whereDate('tanggal_pelaksanaan', '<', $validated['end']);

        if (!$user->hasRole('Super Admin') && !$user->can('agenda.view')) {
            $query->where('status', 'published');
        }

        $events = $query->orderBy('tanggal_pelaksanaan')
            ->get()
            ->map(fn (Activity $activity) => [
                'id' => (string) $activity->id,
                'title' => $activity->judul,
                'start' => $activity->tanggal_pelaksanaan?->toDateString(),
                'allDay' => true,
                'extendedProps' => [
                    'description' => $activity->deskripsi,
                    'status' => $activity->status,
                    'organization' => $activity->organization?->nama,
                ],
            ]);

        return response()->json([
            'status' => 'success',
            'data' => $events,
        ]);
    }
}
