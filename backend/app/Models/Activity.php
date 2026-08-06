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
        'deskripsi',
        'tanggal_pelaksanaan',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}