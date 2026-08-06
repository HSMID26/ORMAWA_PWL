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
}