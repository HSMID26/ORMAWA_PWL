<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'user_id',
        'title',
        'slug',
        'content',
        'effective_date',
        'expires_at',
        'priority',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expires_at'     => 'date',
        'published_at'   => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function ($announcement) {
            if (!empty($announcement->content)) {
                $announcement->content = \App\Services\SecurityService::sanitizeHtml($announcement->content);
            }
            if (!empty($announcement->title)) {
                $announcement->title = \App\Services\SecurityService::sanitizePlainString($announcement->title);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
