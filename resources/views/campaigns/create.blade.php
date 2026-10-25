@extends('layouts.app')

@section('title', 'Create Outreach Campaign')

@section('content')
<div class="rm-header">
    <div>
        <h1 class="rm-page-title">New Campaign</h1>
        <p class="rm-page-subtitle">Configure recipient categories, choose templates, attach your resume, and schedule delivery.</p>
    </div>
    <a href="{{ route('campaigns.index') }}" class="rm-btn rm-btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Campaigns
    </a>
</div>

<form method="POST" action="{{ route('campaigns.store') }}">
    @csrf
    <div class="row g-4 justify-content-center">
        <div class="col-12 col-lg-8">
            <!-- Step 1: Campaign Details -->
            <div class="rm-card mb-4">
                <div class="rm-card-header pb-2 border-bottom">
                    <h3 class="rm-card-title">1. Campaign Purpose</h3>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-12 col-md-8">
                        <label class="rm-label">Campaign Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="rm-input" required placeholder="e.g. Chennai Software Engineering Outreach - Oct">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="rm-label">Campaign Type</label>
                        <select name="type" class="rm-select">
                            <option value="Job Application">Job Application</option>
                            <option value="HR Enquiry">HR Enquiry</option>
                            <option value="Recruiter Outreach">Recruiter Outreach</option>
                            <option value="Follow-up">Follow-up</option>
                            <option value="Custom">Custom</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="rm-label">Target Role / Job Title</label>
                        <input type="text" name="job_title" class="rm-input" placeholder="e.g. Senior Laravel Developer">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="rm-label">Notes (Optional)</label>
                        <input type="text" name="internal_notes" class="rm-input" placeholder="e.g. External sourcing via LinkedIn">
                    </div>
                </div>
            </div>

            <!-- Step 2: Target Recipients -->
            <div class="rm-card mb-4">
                <div class="rm-card-header pb-2 border-bottom">
                    <h3 class="rm-card-title">2. Target Categories</h3>
                </div>
                <p class="text-muted small mt-2 mb-3">Contacts marked as 'Do Not Contact' or lacking an email address are excluded automatically.</p>
                <div class="row g-2">
                    @forelse($categories as $cat)
                        <div class="col-12 col-md-6">
                            <label class="p-3 border rounded d-flex align-items-center justify-content-between bg-light cursor-pointer">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="checkbox" name="categories[]" value="{{ $cat->id }}" class="form-check-input mt-0">
                                    <span class="fw-medium text-dark">{{ $cat->name }}</span>
                                </div>
                                <span class="badge bg-white text-dark border">{{ $cat->contacts_count }}</span>
                            </label>
                        </div>
                    @empty
                        <div class="col-12">
                            <p class="text-muted small">No contact categories found. Please create or import contacts into a category first.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Step 3: Select Template & Resume Attachment -->
            <div class="rm-card mb-4">
                <div class="rm-card-header pb-2 border-bottom">
                    <h3 class="rm-card-title">3. Template & Attachments</h3>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-12 col-md-6">
                        <label class="rm-label">Email Template <span class="text-danger">*</span></label>
                        <select name="email_template_id" class="rm-select" required>
                            @foreach($templates as $tpl)
                                <option value="{{ $tpl->id }}">{{ $tpl->name }} ({{ $tpl->category }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="rm-label">Resume / Document Attachment</label>
                        <select name="attachment_id" class="rm-select">
                            <option value="">No attachment</option>
                            @foreach($attachments as $att)
                                <option value="{{ $att->id }}" {{ $defaultResume && $defaultResume->id == $att->id ? 'selected' : '' }}>
                                    {{ $att->display_name }} ({{ $att->formatted_size }}) {{ $att->is_default_resume ? '[Default]' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Step 4: Sending Options -->
            <div class="rm-card mb-4">
                <div class="rm-card-header pb-2 border-bottom">
                    <h3 class="rm-card-title">4. Delivery Schedule</h3>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-12 col-md-6">
                        <label class="rm-label">Send Strategy</label>
                        <select name="schedule_type" id="schedule_type_select" class="rm-select">
                            <option value="now">Send Immediately in Background</option>
                            <option value="scheduled">Schedule for Future Date/Time</option>
                            <option value="draft">Save as Draft Only</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 d-none" id="scheduled_date_group">
                        <label class="rm-label">Scheduled Date & Time</label>
                        <input type="datetime-local" name="scheduled_at" class="rm-input">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="rm-label">Delay Between Sends (Seconds)</label>
                        <input type="number" name="delay_between_sends" value="5" min="0" max="60" class="rm-input">
                        <small class="text-muted">Protects against email provider rate limiting.</small>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pb-5">
                <a href="{{ route('campaigns.index') }}" class="rm-btn rm-btn-secondary">Cancel</a>
                <button type="submit" class="rm-btn rm-btn-primary px-4 py-2">
                    Review & Confirm Campaign <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    document.getElementById('schedule_type_select')?.addEventListener('change', function(e) {
        const dateGroup = document.getElementById('scheduled_date_group');
        if (e.target.value === 'scheduled') {
            dateGroup.classList.remove('d-none');
        } else {
            dateGroup.classList.add('d-none');
        }
    });
</script>
@endpush
@endsection
