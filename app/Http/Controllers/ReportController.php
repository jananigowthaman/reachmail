<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\EmailReply;
use App\Models\JobApplication;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $totalContacts = Contact::count();
        $totalContacted = Contact::where('status', '!=', 'New')->count();
        $totalCampaigns = Campaign::count();
        $emailsSent = CampaignRecipient::whereIn('status', ['sent', 'delivered'])->count();
        $estimatedOpens = CampaignRecipient::whereNotNull('opened_at')->count();
        $repliesReceived = EmailReply::count();
        $applicationsLogged = JobApplication::count();
        $interviewsScheduled = JobApplication::where('status', 'Interview')->count();
        $offersReceived = JobApplication::where('status', 'Offer')->count();

        // Calculate legitimate conversion rates
        $openRate = $emailsSent > 0 ? round(($estimatedOpens / $emailsSent) * 100, 1) : 0;
        $replyRate = $emailsSent > 0 ? round(($repliesReceived / $emailsSent) * 100, 1) : 0;

        $campaigns = Campaign::latest()->take(10)->get();

        return view('reports.index', compact(
            'totalContacts',
            'totalContacted',
            'totalCampaigns',
            'emailsSent',
            'estimatedOpens',
            'repliesReceived',
            'applicationsLogged',
            'interviewsScheduled',
            'offersReceived',
            'openRate',
            'replyRate',
            'campaigns'
        ));
    }

    public function export()
    {
        $campaigns = Campaign::with('recipients')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="reachmail_campaign_report_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($campaigns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Campaign Name', 'Type', 'Status', 'Total Recipients', 'Sent Count', 'Estimated Opens', 'Replies', 'Created Date']);

            foreach ($campaigns as $c) {
                fputcsv($file, [
                    $c->name,
                    $c->type,
                    $c->status,
                    $c->total_recipients,
                    $c->sent_count,
                    $c->opened_count,
                    $c->replied_count,
                    $c->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
