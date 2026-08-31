<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PlatformSettingController extends Controller
{
    private const CACHE_KEY = 'platform_settings';

    private array $defaultSettings = [
        'campusName'          => 'Institut Teknologi Indonesia',
        'mainDomain'          => 'iti.ac.id',
        'defaultStorageLimit' => 20, // GB
        'autoApproveNewOrg'   => false,
        'maintenanceMode'     => false,
    ];

    /**
     * Get current platform settings
     */
    public function show(Request $request)
    {
        $settings = Cache::get(self::CACHE_KEY, $this->defaultSettings);

        return response()->json([
            'status' => 'success',
            'data'   => $settings,
        ]);
    }

    /**
     * Update platform settings (Super Admin only)
     */
    public function update(Request $request)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('Super Admin') && !$user->can('platform_settings.manage'))) {
            abort(403, 'Unauthorized. Hanya Super Admin yang dapat mengubah konfigurasi platform.');
        }

        $validated = $request->validate([
            'campusName'          => 'required|string|max:255',
            'mainDomain'          => 'required|string|max:255',
            'defaultStorageLimit' => 'required|numeric|min:1|max:1000',
            'autoApproveNewOrg'   => 'required|boolean',
            'maintenanceMode'     => 'required|boolean',
        ]);

        Cache::forever(self::CACHE_KEY, $validated);

        ActivityLogService::log(
            'update',
            'platform_settings',
            'Memperbarui konfigurasi sistem platform global',
            $validated
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Konfigurasi sistem pusat berhasil diperbarui.',
            'data'    => $validated,
        ]);
    }
}
