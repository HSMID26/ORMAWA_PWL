<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = [
        'nama',
        'jenis',
        'subdomain',
        'logo',
        'warna_tema',
        'modul_aktif',
        'label_menu',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'modul_aktif' => 'array',
            'label_menu'  => 'array',
        ];
    }

    /**
     * Mengecek apakah modul tertentu aktif
     */
    public function isModuleActive(string $moduleKey): bool
    {
        $modules = $this->modul_aktif;
        if (empty($modules) || !is_array($modules)) {
            return true; // Default aktif jika belum dikonfigurasi
        }
        return isset($modules[$moduleKey]) ? (bool) $modules[$moduleKey] : true;
    }

    /**
     * Mendapatkan label kustom menu untuk modul tertentu
     */
    public function getMenuLabel(string $moduleKey, string $defaultLabel): string
    {
        $labels = $this->label_menu;
        if (!empty($labels) && is_array($labels) && !empty($labels[$moduleKey])) {
            return $labels[$moduleKey];
        }
        return $defaultLabel;
    }

    /**
     * Relasi ke semua User dalam organisasi ini
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relasi ke semua Period dalam organisasi ini
     */
    public function periods(): HasMany
    {
        return $this->hasMany(OrganizationPeriod::class);
    }

    /**
     * Mendapatkan period aktif saat ini (jika ada)
     */
    public function currentPeriod()
    {
        return $this->periods()
            ->where('status', 'active')
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('end_date', '>=', now()->toDateString())
            ->first();
    }

    /**
     * Mengecek apakah organisasi beroperasi (aktif & punya period aktif)
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        
        $periodsCount = $this->periods()->count();
        if ($periodsCount === 0) {
            return true;
        }

        return $this->currentPeriod() !== null;
    }
}