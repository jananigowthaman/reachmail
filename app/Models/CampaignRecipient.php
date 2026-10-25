<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'contact_id',
        'email',
        'rendered_subject',
        'rendered_body',
        'status',
        'provider_message_id',
        'provider_thread_id',
        'sent_at',
        'delivered_at',
        'opened_at',
        'open_count',
        'replied_at',
        'tracking_token',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'replied_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function events()
    {
        return $this->hasMany(EmailEvent::class);
    }
}
