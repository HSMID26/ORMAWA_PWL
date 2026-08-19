<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory, BelongsToOrganization; // filter berdasarkan organization_id

    protected $fillable = [
        'organization_id',
        'user_id',
        'filename',
        'path',
        'mime_type',
        'size',
    ];

    // Relasi ke User pembuat/pengunggah
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper Accessor untuk mendapatkan Full URL Gambar
    public function getUrlAttribute()
    {
        return url(\Illuminate\Support\Facades\Storage::url($this->path));
    }
}