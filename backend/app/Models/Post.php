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
        'category_id',
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

    protected static function booted(): void
    {
        static::saving(function ($post) {
            if (!empty($post->konten)) {
                $post->konten = \App\Services\SecurityService::sanitizeHtml($post->konten);
            }
            if (!empty($post->judul)) {
                $post->judul = \App\Services\SecurityService::sanitizePlainString($post->judul);
            }
            if (!empty($post->excerpt)) {
                $post->excerpt = \App\Services\SecurityService::sanitizePlainString($post->excerpt);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}