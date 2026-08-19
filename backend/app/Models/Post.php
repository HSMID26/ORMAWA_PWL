<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'user_id',
        'judul',
        'slug',
        'konten',
        'excerpt',
        'cover_image',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi Ke Category (One-to-Many)
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relasi Ke Tag (Many-to-Many)
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}