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

        // 2. Isi organization_id otomatis saat pembuatan data baru
        static::creating(function ($model) {
            if (Auth::check() && empty($model->organization_id)) {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                if (!empty($user->organization_id)) {
                    $model->organization_id = $user->organization_id;
                } else {
                    // Fallback jika user tidak terikat organisasi spesifik (misal: Super Admin PKA)
                    $firstOrg = Organization::first();
                    if ($firstOrg) {
                        $model->organization_id = $firstOrg->id;
                    }
                }
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