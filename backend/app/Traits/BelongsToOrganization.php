<?php

namespace App\Traits;

use App\Models\Organization;
use App\Models\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * @method static void addGlobalScope(\Illuminate\Database\Eloquent\Scope|\Closure $scope)
 * @method static void creating(\Closure|string $callback)
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait BelongsToOrganization
{
    /**
     * Boot trait ini untuk menambahkan Scope dan memasukkan organization_id otomatis.
     */
    protected static function bootBelongsToOrganization(): void
    {
        // 1. Terapkan Global Scope (Multi-tenant Filter)
        static::addGlobalScope(new OrganizationScope());

        // 2. Isi/timpa organization_id otomatis saat pembuatan data baru
        static::creating(function ($model) {
            if (Auth::check()) {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                
                // Jika user memiliki organization_id, paksa model menggunakan ID tersebut
                // Ini mencegah tenant bypass dari frontend.
                if (!empty($user->organization_id)) {
                    $model->organization_id = $user->organization_id;
                }
                // Jika user TIDAK memiliki organization_id (misal Super Admin),
                // JANGAN lakukan fallback otomatis ke Organization::first().
                // Biarkan model menyimpan apa yang diberikan request (jika diizinkan),
                // atau biarkan constraint DB menolaknya jika kosong dan required.
            }
        });
    }

    /**
     * Relasi ke Model Organization
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}