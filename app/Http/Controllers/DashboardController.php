<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\EmailReply;
use App\Models\FollowUpReminder;
use App\Models\JobApplication;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $metrics = [
            'total_contacts' => Contact::count(),
            'ready_to_contact' => Contact::where('status', 'Ready to Contact')->where('do_not_contact', false)->count(),
            'total_campaigns' => Campaign::count(),
            'scheduled_campaigns' => Campaign::where('status', 'scheduled')->count(),
            'emails_sent' => CampaignRecipient::whereIn('status', ['sent', 'delivered'])->count(),
            'emails_delivered' => CampaignRecipient::where('status', 'delivered')->count(),
            'replies_received' => EmailReply::count(),
            'follow_ups_due' => FollowUpReminder::where('status', 'pending')->where('reminder_date', '<=', now()->toDateString())->count(),
            'job_applications' => JobApplication::count(),
        ];

        $recentCampaigns = Campaign::with('template')
            ->latest()
            ->take(5)
            ->get();

        $recentActivity = ActivityLog::latest()
            ->take(6)
            ->get();

        $upcomingSchedules = Campaign::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->orderBy('scheduled_at')
            ->take(5)
            ->get();

        $upcomingFollowUps = FollowUpReminder::with(['contact', 'jobApplication'])
            ->where('status', 'pending')
            ->orderBy('reminder_date')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'metrics',
            'recentCampaigns',
            'recentActivity',
            'upcomingSchedules',
            'upcomingFollowUps'
        ));
    }
}
