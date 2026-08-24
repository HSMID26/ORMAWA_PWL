<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_ormawa',
        'jenis_ormawa',
        'subdomain',
        'admin_name',
        'admin_email',
        'admin_password',
        'status',
        'alasan_penolakan',
    ];
}