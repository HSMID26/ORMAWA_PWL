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
        'status',
    ];

    protected function casts(): array
    {
        return [
            'modul_aktif' => 'array',
        ];
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

    public function committees(): HasMany
    {
        return $this->hasMany(Committee::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
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
     * "TETAPI jangan membuat existing organization inaccessible hanya karena initial period belum dikonfigurasi.
     * Gunakan transition behavior yang aman" - Sesuai instruksi: 
     * Jika period = 0 (belum pernah dikonfigurasi sama sekali), anggap aktif untuk transisi.
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        
        $periodsCount = $this->periods()->count();
        if ($periodsCount === 0) {
            // Backward compatibility transition: if no periods exist at all, allow operation
            return true;
        }

        return $this->currentPeriod() !== null;
    }
}