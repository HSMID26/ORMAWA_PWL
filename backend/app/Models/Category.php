<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = ['organization_id', 'name', 'slug'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $baseSlug = Str::slug($category->name) ?: 'kategori';
                $slug = $baseSlug;
                $count = 1;
                while (static::withoutGlobalScopes()
                    ->where('organization_id', $category->organization_id)
                    ->where('slug', $slug)
                    ->exists()) {
                    $slug = "{$baseSlug}-{$count}";
                    $count++;
                }
                $category->slug = $slug;
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
