<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkProgram extends Model
{
    use BelongsToOrganization;

    public const STATUS_PLANNED = 'planned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PLANNED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'organization_id',
        'organization_period_id',
        'responsible_member_id',
        'user_id',
        'name',
        'objective',
        'period_year',
        'target_participants',
        'estimated_budget',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'period_year' => 'integer',
            'target_participants' => 'integer',
            'estimated_budget' => 'decimal:2',
        ];
    }

    public function responsibleMember(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'responsible_member_id');
    }

    public function organizationPeriod(): BelongsTo
    {
        return $this->belongsTo(OrganizationPeriod::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
