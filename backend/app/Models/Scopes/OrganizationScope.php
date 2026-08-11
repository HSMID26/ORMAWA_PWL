<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class OrganizationScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Jika user sedang login dan mempunyai organization_id
        if (Auth::check() && Auth::user()->organization_id) {
            // Filter otomatis query berdasarkan organization_id user
            $builder->where($model->getTable() . '.organization_id', Auth::user()->organization_id);
        }
    }
}