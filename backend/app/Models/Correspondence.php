<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Correspondence extends Model
{
    use BelongsToOrganization;

    public const TYPES = ['incoming', 'outgoing'];
    public const STATUSES = ['draft', 'submitted', 'approved', 'rejected', 'revision_requested'];

    protected $fillable = [
        'organization_id',
        'created_by',
        'reviewed_by',
        'reference_number',
        'type',
        'sender',
        'recipient',
        'subject',
        'letter_date',
        'notes',
        'status',
        'review_note',
        'reviewed_at',
        'attachment_path',
        'attachment_name',
        'attachment_mime_type',
        'attachment_size',
    ];

    protected function casts(): array
    {
        return [
            'letter_date' => 'date:Y-m-d',
            'reviewed_at' => 'datetime',
            'attachment_size' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
