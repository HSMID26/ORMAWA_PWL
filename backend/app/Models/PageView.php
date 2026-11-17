<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageView extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'url_path',
        'ip_address',
        'user_agent',
    ];

    /**
     * Relasi ke Organisasi
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
