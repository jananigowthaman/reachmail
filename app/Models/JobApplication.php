<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'job_title',
        'job_url',
        'company_website',
        'contact_id',
        'hr_contact_name',
        'hr_email',
        'campaign_id',
        'resume_attachment_id',
        'cover_letter_attachment_id',
        'application_date',
        'status',
        'next_follow_up_date',
        'notes',
    ];

    protected $casts = [
        'application_date' => 'date',
        'next_follow_up_date' => 'date',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function resume()
    {
        return $this->belongsTo(Attachment::class, 'resume_attachment_id');
    }

    public function coverLetter()
    {
        return $this->belongsTo(Attachment::class, 'cover_letter_attachment_id');
    }
}
