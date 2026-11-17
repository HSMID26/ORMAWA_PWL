<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'user_id',
        'judul',
        'title',
        'deskripsi',
        'description',
        'location',
        'tanggal_pelaksanaan',
        'start_time',
        'end_time',
        'status',
        'published_at',
    ];

    protected $casts = [
        'tanggal_pelaksanaan' => 'datetime',
        'start_time'          => 'datetime',
        'end_time'            => 'datetime',
        'published_at'        => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // Accessors & Mutators for Dual Compatibility (title / judul, description / deskripsi, start_time / tanggal_pelaksanaan)
    public function getTitleAttribute()
    {
        return $this->attributes['title'] ?? $this->attributes['judul'] ?? '';
    }

    public function getDescriptionAttribute()
    {
        return $this->attributes['description'] ?? $this->attributes['deskripsi'] ?? '';
    }

    public function getStartTimeAttribute()
    {
        return isset($this->attributes['start_time']) 
            ? $this->castAttribute('start_time', $this->attributes['start_time'])
            : (isset($this->attributes['tanggal_pelaksanaan']) ? $this->castAttribute('tanggal_pelaksanaan', $this->attributes['tanggal_pelaksanaan']) : null);
    }
}