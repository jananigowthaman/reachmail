<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'normalized_email',
        'full_name',
        'first_name',
        'last_name',
        'company_name',
        'job_title',
        'company_website',
        'linkedin_url',
        'location',
        'city',
        'state',
        'country',
        'source',
        'source_url',
        'date_collected',
        'email_verification_status',
        'status',
        'do_not_contact',
        'last_contacted_at',
        'last_campaign_name',
        'reply_status',
        'follow_up_date',
        'notes',
    ];

    protected $casts = [
        'do_not_contact' => 'boolean',
        'last_contacted_at' => 'datetime',
        'date_collected' => 'date',
        'follow_up_date' => 'date',
    ];

    public static function normalizeEmail(?string $email): string
    {
        return strtolower(trim($email ?? ''));
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_contact');
    }

    public function campaignRecipients()
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function emailReplies()
    {
        return $this->hasMany(EmailReply::class);
    }

    public function followUpReminders()
    {
        return $this->hasMany(FollowUpReminder::class);
    }

    public function getDisplayNameAttribute(): string
    {
        if (!empty($this->full_name)) {
            return $this->full_name;
        }
        $combined = trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
        return !empty($combined) ? $combined : $this->email;
    }
}
