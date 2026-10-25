<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'campaign_id',
        'campaign_recipient_id',
        'sender_email',
        'sender_name',
        'subject',
        'snippet',
        'body_html',
        'body_text',
        'provider_message_id',
        'provider_thread_id',
        'received_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function campaignRecipient()
    {
        return $this->belongsTo(CampaignRecipient::class);
    }
}
