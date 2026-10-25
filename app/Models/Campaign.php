<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'purpose',
        'type',
        'job_title',
        'internal_notes',
        'status',
        'email_template_id',
        'attachment_id',
        'subject_snapshot',
        'body_html_snapshot',
        'scheduled_at',
        'started_at',
        'completed_at',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'failed_count',
        'opened_count',
        'replied_count',
        'delay_between_sends',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'attachment_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'campaign_categories');
    }

    public function recipients()
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    public function replies()
    {
        return $this->hasMany(EmailReply::class);
    }
}
