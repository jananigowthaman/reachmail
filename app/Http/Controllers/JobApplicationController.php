<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\Contact;
use App\Models\Campaign;
use App\Models\Attachment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = JobApplication::with(['contact', 'resume', 'coverLetter']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $applications = $query->latest()->paginate(20);
        return view('applications.index', compact('applications'));
    }

    public function create()
    {
        $contacts = Contact::all();
        $resumes = Attachment::where('file_type', 'resume')->get();
        $coverLetters = Attachment::where('file_type', 'cover_letter')->get();
        $defaultResume = Attachment::where('is_default_resume', true)->first();

        return view('applications.create', compact('contacts', 'resumes', 'coverLetters', 'defaultResume'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
            'job_url' => 'nullable|url|max:255',
            'company_website' => 'nullable|url|max:255',
            'contact_id' => 'nullable|exists:contacts,id',
            'hr_contact_name' => 'nullable|string|max:255',
            'hr_email' => 'nullable|email|max:255',
            'resume_attachment_id' => 'nullable|exists:attachments,id',
            'cover_letter_attachment_id' => 'nullable|exists:attachments,id',
            'application_date' => 'required|date',
            'status' => 'required|string',
            'next_follow_up_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $app = JobApplication::create($validated);

        ActivityLog::log('application_logged', "Logged job application for '{$app->job_title}' at {$app->company_name}", $app);

        return redirect()->route('applications.index')->with('success', 'Job application logged successfully.');
    }

    public function edit(JobApplication $application)
    {
        $contacts = Contact::all();
        $resumes = Attachment::where('file_type', 'resume')->get();
        $coverLetters = Attachment::where('file_type', 'cover_letter')->get();

        return view('applications.edit', compact('application', 'contacts', 'resumes', 'coverLetters'));
    }

    public function update(Request $request, JobApplication $application)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
            'job_url' => 'nullable|url|max:255',
            'company_website' => 'nullable|url|max:255',
            'contact_id' => 'nullable|exists:contacts,id',
            'hr_contact_name' => 'nullable|string|max:255',
            'hr_email' => 'nullable|email|max:255',
            'resume_attachment_id' => 'nullable|exists:attachments,id',
            'cover_letter_attachment_id' => 'nullable|exists:attachments,id',
            'application_date' => 'required|date',
            'status' => 'required|string',
            'next_follow_up_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $application->update($validated);

        ActivityLog::log('application_updated', "Updated application for {$application->company_name}", $application);

        return redirect()->route('applications.index')->with('success', 'Application details updated.');
    }

    public function destroy(JobApplication $application)
    {
        $title = $application->job_title . ' at ' . $application->company_name;
        $application->delete();

        ActivityLog::log('application_deleted', "Deleted application {$title}");

        return redirect()->route('applications.index')->with('success', "Application record deleted.");
    }
}
