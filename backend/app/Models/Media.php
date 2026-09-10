<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'user_id',
        'name',
        'caption',
        'taken_at',
        'alt_text',
        'filename',
        'path',
        'mime_type',
        'category',
        'visibility',
        'is_published_to_gallery',
        'size',
    ];

    protected $casts = [
        'taken_at'                => 'date',
        'is_published_to_gallery' => 'boolean',
    ];

    protected $appends = [
        'url',
        'formatted_size',
        'is_image',
        'is_video',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function getUrlAttribute(): string
    {
        return url(Storage::url($this->path));
    }

    /**
     * Format ukuran file menjadi satuan yang mudah dibaca (KB / MB)
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->size;
        if ($bytes <= 0) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        return round($bytes / pow(1024, $power), 1) . ' ' . $units[$power];
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function getIsVideoAttribute(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'video/');
    }

    /**
     * Cek apakah media ini sedang digunakan oleh artikel organisasi
     */
    public function getUsedInArticlesAttribute(): array
    {
        $path = $this->path;
        $filename = $this->filename;

        return Post::withoutGlobalScopes()
            ->where('organization_id', $this->organization_id)
            ->where(function ($q) use ($path, $filename) {
                $q->where('cover_image', 'like', "%{$path}%")
                  ->orWhere('cover_image', 'like', "%{$filename}%")
                  ->orWhere('konten', 'like', "%{$path}%")
                  ->orWhere('konten', 'like', "%{$filename}%");
            })
            ->get(['id', 'judul', 'slug', 'status'])
            ->toArray();
    }
}
