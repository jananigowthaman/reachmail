<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_recipient_id',
        'event_type',
        'ip_address',
        'user_agent',
        'payload',
        'occurred_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function recipient()
    {
        return $this->belongsTo(CampaignRecipient::class, 'campaign_recipient_id');
    }
}
