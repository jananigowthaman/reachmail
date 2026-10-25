<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowUpReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'campaign_id',
        'job_application_id',
        'reminder_date',
        'title',
        'notes',
        'status',
    ];

    protected $casts = [
        'reminder_date' => 'date',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function jobApplication()
    {
        return $this->belongsTo(JobApplication::class);
    }
}
