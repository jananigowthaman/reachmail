@extends('layouts.app')

@section('title', 'Log Job Application')

@section('content')
<div class="rm-header">
    <div>
        <h1 class="rm-page-title">Log Job Application</h1>
        <p class="rm-page-subtitle">Record opportunities, targeted companies, and resume versions.</p>
    </div>
    <a href="{{ route('applications.index') }}" class="rm-btn rm-btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Applications
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="rm-card">
            <form method="POST" action="{{ route('applications.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="rm-label">Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="company_name" class="rm-input" required placeholder="e.g. Stripe">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="rm-label">Job Title / Role <span class="text-danger">*</span></label>
                        <input type="text" name="job_title" class="rm-input" required placeholder="e.g. Full Stack Developer">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="rm-label">Job Posting URL</label>
                        <input type="url" name="job_url" class="rm-input" placeholder="https://careers.stripe.com/job/123">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="rm-label">Company Website</label>
                        <input type="url" name="company_website" class="rm-input" placeholder="https://stripe.com">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="rm-label">HR / Recruiter Contact Name</label>
                        <input type="text" name="hr_contact_name" class="rm-input" placeholder="e.g. Emma Watson">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="rm-label">HR Email Address</label>
                        <input type="email" name="hr_email" class="rm-input" placeholder="e.g. emma@stripe.com">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="rm-label">Resume Used</label>
                        <select name="resume_attachment_id" class="rm-select">
                            <option value="">None selected</option>
                            @foreach($resumes as $res)
                                <option value="{{ $res->id }}" {{ $defaultResume && $defaultResume->id == $res->id ? 'selected' : '' }}>
                                    {{ $res->display_name }} {{ $res->is_default_resume ? '(Default)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="rm-label">Cover Letter Used</label>
                        <select name="cover_letter_attachment_id" class="rm-select">
                            <option value="">None selected</option>
                            @foreach($coverLetters as $cl)
                                <option value="{{ $cl->id }}">{{ $cl->display_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="rm-label">Application Date <span class="text-danger">*</span></label>
                        <input type="date" name="application_date" class="rm-input" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="rm-label">Application Status</label>
                        <select name="status" class="rm-select">
                            <option value="Applied">Applied</option>
                            <option value="Interested">Interested</option>
                            <option value="Response Received">Response Received</option>
                            <option value="Interview">Interview</option>
                            <option value="Offer">Offer</option>
                            <option value="Rejected">Rejected</option>
                            <option value="Closed">Closed</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="rm-label">Next Follow-up Date</label>
                        <input type="date" name="next_follow_up_date" class="rm-input" value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                    </div>

                    <div class="col-12">
                        <label class="rm-label">Notes & Observations</label>
                        <textarea name="notes" rows="3" class="rm-textarea" placeholder="Key technologies mentioned, referral contact info, or interview prep notes..."></textarea>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('applications.index') }}" class="rm-btn rm-btn-secondary">Cancel</a>
                        <button type="submit" class="rm-btn rm-btn-primary">Save Application</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
