<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Category;
use App\Models\Contact;
use App\Models\EmailTemplate;
use App\Models\Attachment;
use App\Models\Setting;
use App\Models\ActivityLog;
use App\Jobs\ProcessCampaignJob;
use App\Services\EmailDeliveryService;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = Campaign::with('template');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $campaigns = $query->latest()->paginate(15);
        return view('campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $categories = Category::withCount('contacts')->get();
        $templates = EmailTemplate::all();
        $attachments = Attachment::all();
        $defaultResume = Attachment::where('is_default_resume', true)->first();

        return view('campaigns.create', compact('categories', 'templates', 'attachments', 'defaultResume'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'job_title' => 'nullable|string|max:255',
            'purpose' => 'nullable|string|max:255',
            'internal_notes' => 'nullable|string',
            'categories' => 'required|array',
            'email_template_id' => 'required|exists:email_templates,id',
            'attachment_id' => 'nullable|exists:attachments,id',
            'schedule_type' => 'required|in:now,scheduled,draft',
            'scheduled_at' => 'nullable|date',
            'delay_between_sends' => 'nullable|integer|min:0|max:60',
        ]);

        $template = EmailTemplate::findOrFail($validated['email_template_id']);

        // Gather contacts from selected categories, excluding Do Not Contact & duplicates
        $contacts = Contact::whereHas('categories', function ($q) use ($validated) {
            $q->whereIn('categories.id', $validated['categories']);
        })
        ->where('do_not_contact', false)
        ->whereNotNull('email')
        ->get()
        ->unique('normalized_email');

        if ($contacts->isEmpty()) {
            return back()->withInput()->with('error', 'No eligible contacts found in the selected categories.');
        }

        $status = 'draft';
        if ($validated['schedule_type'] === 'now') {
            $status = 'processing';
        } elseif ($validated['schedule_type'] === 'scheduled') {
            $status = 'scheduled';
        }

        $campaign = Campaign::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'job_title' => $validated['job_title'] ?? null,
            'purpose' => $validated['purpose'] ?? null,
            'internal_notes' => $validated['internal_notes'] ?? null,
            'status' => $status,
            'email_template_id' => $template->id,
            'attachment_id' => $validated['attachment_id'] ?? null,
            'subject_snapshot' => $template->subject,
            'body_html_snapshot' => $template->body_html,
            'scheduled_at' => $validated['schedule_type'] === 'scheduled' ? $validated['scheduled_at'] : null,
            'total_recipients' => $contacts->count(),
            'delay_between_sends' => $validated['delay_between_sends'] ?? 10,
        ]);

        $campaign->categories()->sync($validated['categories']);

        $myName = Setting::get('sender_name', 'ReachMail User');
        $myEmail = Setting::get('sender_email', 'me@reachmail.local');

        // Create Recipient Snapshots with Personalized Placeholders
        foreach ($contacts as $contact) {
            $subject = $template->subject;
            $body = $template->body_html;

            $replacements = [
                '{{first_name}}' => $contact->first_name ?: ($contact->full_name ?: 'Hiring Manager'),
                '{{last_name}}' => $contact->last_name ?: '',
                '{{full_name}}' => $contact->display_name,
                '{{company_name}}' => $contact->company_name ?: 'your company',
                '{{job_title}}' => $campaign->job_title ?: ($contact->job_title ?: 'Position'),
                '{{company_website}}' => $contact->company_website ?: '',
                '{{city}}' => $contact->city ?: '',
                '{{my_name}}' => $myName,
                '{{my_email}}' => $myEmail,
            ];

            foreach ($replacements as $key => $val) {
                $subject = str_replace($key, $val, $subject);
                $body = str_replace($key, $val, $body);
            }

            CampaignRecipient::create([
                'campaign_id' => $campaign->id,
                'contact_id' => $contact->id,
                'email' => $contact->email,
                'rendered_subject' => $subject,
                'rendered_body' => $body,
                'status' => 'pending',
            ]);
        }

        ActivityLog::log('campaign_created', "Created campaign '{$campaign->name}' with {$contacts->count()} recipients", $campaign);

        if ($validated['schedule_type'] === 'now') {
            dispatch(new ProcessCampaignJob($campaign->id));
            return redirect()->route('campaigns.show', $campaign)->with('success', 'Campaign launched! Outreach emails are processing.');
        }

        return redirect()->route('campaigns.show', $campaign)->with('success', 'Campaign configured successfully.');
    }

    public function show(Campaign $campaign)
    {
        $campaign->load(['template', 'attachment', 'categories', 'recipients.contact']);
        return view('campaigns.show', compact('campaign'));
    }

    public function launchNow(Campaign $campaign)
    {
        if (in_array($campaign->status, ['processing', 'completed'])) {
            return back()->with('error', 'Campaign is already active or completed.');
        }

        $campaign->update(['status' => 'processing']);
        dispatch(new ProcessCampaignJob($campaign->id));

        return back()->with('success', 'Campaign is now sending emails in the background.');
    }

    public function cancel(Campaign $campaign)
    {
        $campaign->update(['status' => 'cancelled']);
        ActivityLog::log('campaign_cancelled', "Cancelled campaign '{$campaign->name}'", $campaign);

        return back()->with('success', 'Campaign was cancelled.');
    }

    public function sendTest(Request $request, Campaign $campaign, EmailDeliveryService $delivery)
    {
        $testEmail = $request->input('test_email');
        if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            return back()->with('error', 'Please provide a valid test email address.');
        }

        // Create temporary dummy recipient
        $dummyContact = new Contact([
            'email' => $testEmail,
            'full_name' => 'Test User',
            'first_name' => 'Test',
            'company_name' => 'ReachMail Preview Corp',
            'do_not_contact' => false,
        ]);

        $dummyRecipient = new CampaignRecipient([
            'campaign_id' => $campaign->id,
            'email' => $testEmail,
            'rendered_subject' => '[TEST] ' . ($campaign->subject_snapshot ?: $campaign->name),
            'rendered_body' => $campaign->body_html_snapshot ?: '<p>This is a test outreach message preview.</p>',
            'status' => 'pending',
        ]);
        $dummyRecipient->setRelation('campaign', $campaign);
        $dummyRecipient->setRelation('contact', $dummyContact);

        $sent = $delivery->sendRecipient($dummyRecipient);

        if ($sent) {
            return back()->with('success', "Test message sent to {$testEmail}.");
        } else {
            return back()->with('error', 'Failed to send test message. Check your mail configuration or Gmail account.');
        }
    }
}
