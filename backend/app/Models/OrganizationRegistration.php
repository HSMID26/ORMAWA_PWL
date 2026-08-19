<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_name',
        'organization_type',
        'organization_subdomain',
        'organization_logo',
        'organization_description',
        'admin_first_name',
        'admin_last_name',
        'admin_email',
        'admin_password',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $hidden = [
        'admin_password',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
