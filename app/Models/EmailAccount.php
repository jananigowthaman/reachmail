<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'email_address',
        'sender_name',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'is_active',
        'connection_status',
        'last_synced_at',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'is_active' => 'boolean',
        // In production, encrypt access_token and refresh_token
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
    ];
}
