<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PlatformSettingController extends Controller
{
    public const CACHE_KEY = 'platform_settings';

    public static function getDefaultSettings(): array
    {
        return [
            'campusName'          => 'Institut Teknologi Indonesia',
            'mainDomain'          => 'iti.ac.id',
            'defaultStorageLimit' => 20, // GB
            'autoApproveNewOrg'   => false,
            'maintenanceMode'     => false,
            // Homepage Customization (Task 4)
            'heroBadge'           => 'PORTAL ORGANISASI KEMAHASISWAAN ITI',
            'heroTitle'           => "Temukan Organisasi,\nKegiatan, dan Kabar Mahasiswa",
            'heroSubtitle'        => 'Platform resmi untuk menemukan organisasi mahasiswa, warta, agenda, dan informasi kemahasiswaan Institut Teknologi Indonesia.',
            'heroImage'           => null,
            'heroCtaText'         => 'Jelajahi Organisasi',
            'heroCtaLink'         => '/organizations',
            'heroSecondaryCtaText'=> 'Lihat Berita',
            'heroSecondaryCtaLink'=> '/berita',
            'introBadge'          => 'Pusat Kemahasiswaan ITI',
            'introTitle'          => 'Ekosistem Organisasi Mahasiswa ITI',
            'introDescription'    => 'ORMAWA ITI menghimpun informasi publik organisasi mahasiswa Institut Teknologi Indonesia dalam satu portal yang terintegrasi, transparan, dan mudah diakses.',
            'showStatsSection'    => true,
            'showIntroSection'    => true,
            'showQuickLinks'      => true,
            'showLatestArticles'  => true,
            'showUpcomingAgenda'  => true,
            'showAnnouncements'   => true,
            // Platform Footer Settings
            'footerDescription'   => 'Portal publik resmi tata kelola, warta berita, agenda kegiatan, dan dokumen seluruh organisasi kemahasiswaan di lingkungan Institut Teknologi Indonesia.',
            'footerAddress'       => "Jl. Raya Puspiptek Serpong, Tangerang Selatan, Banten 15314.\nPusat Kemahasiswaan & Alumni (PKA) ITI",
            'footerEmail'         => 'pka@iti.ac.id',
            'footerPhone'         => '021-7561092',
            'footerWhatsapp'      => '081234567890',
            'footerInstagram'     => 'https://instagram.com/iti_official',
            'footerFacebook'      => 'https://facebook.com/itiofficial',
            'footerYoutube'       => 'https://youtube.com/@itiofficial',
            'footerTiktok'        => 'https://tiktok.com/@iti_official',
            'footerWebsite'       => 'https://iti.ac.id',
            'footerCopyright'     => 'Institut Teknologi Indonesia. Hak Cipta Dilindungi.',
        ];
    }

    /**
     * Get current platform settings
     */
    public function show(Request $request)
    {
        $defaults = self::getDefaultSettings();
        $cached = Cache::get(self::CACHE_KEY, []);
        $settings = array_merge($defaults, is_array($cached) ? $cached : []);

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
            // Homepage Customization fields
            'heroBadge'           => 'nullable|string|max:255',
            'heroTitle'           => 'nullable|string|max:255',
            'heroSubtitle'        => 'nullable|string|max:1000',
            'heroImage'           => 'nullable|string|max:1000',
            'heroCtaText'         => 'nullable|string|max:100',
            'heroCtaLink'         => 'nullable|string|max:255',
            'heroSecondaryCtaText'=> 'nullable|string|max:100',
            'heroSecondaryCtaLink'=> 'nullable|string|max:255',
            'introBadge'          => 'nullable|string|max:255',
            'introTitle'          => 'nullable|string|max:255',
            'introDescription'    => 'nullable|string|max:2000',
            'showStatsSection'    => 'nullable|boolean',
            'showIntroSection'    => 'nullable|boolean',
            'showQuickLinks'      => 'nullable|boolean',
            'showLatestArticles'  => 'nullable|boolean',
            'showUpcomingAgenda'  => 'nullable|boolean',
            'showAnnouncements'   => 'nullable|boolean',
            // Platform Footer fields
            'footerDescription'   => 'nullable|string|max:1000',
            'footerAddress'       => 'nullable|string|max:500',
            'footerEmail'         => 'nullable|string|max:255',
            'footerPhone'         => 'nullable|string|max:50',
            'footerWhatsapp'      => 'nullable|string|max:50',
            'footerInstagram'     => 'nullable|string|max:255',
            'footerFacebook'      => 'nullable|string|max:255',
            'footerYoutube'       => 'nullable|string|max:255',
            'footerTiktok'        => 'nullable|string|max:255',
            'footerWebsite'       => 'nullable|string|max:255',
            'footerCopyright'     => 'nullable|string|max:255',
        ]);

        $defaults = self::getDefaultSettings();
        $merged = array_merge($defaults, $validated);

        Cache::forever(self::CACHE_KEY, $merged);

        ActivityLogService::log(
            'update',
            'platform_settings',
            'Memperbarui konfigurasi sistem platform global',
            null,
            $merged
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Konfigurasi sistem pusat berhasil diperbarui.',
            'data'    => $merged,
        ]);
    }
}
