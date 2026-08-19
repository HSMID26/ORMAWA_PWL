<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Committee extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'user_id',
        'name',
        'position',
        'department',
        'period',
        'photo',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor untuk dapetin full URL foto
    public function getPhotoUrlAttribute()
    {
        return $this->photo ? url(Storage::url($this->photo)) : null;
    }
}