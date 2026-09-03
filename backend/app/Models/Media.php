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
        'filename',
        'path',
        'mime_type',
        'size',
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
}
