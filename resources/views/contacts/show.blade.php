@extends('layouts.app')

@section('title', $contact->display_name . ' | ReachMail')

@section('content')
<div class="rm-header">
    <div>
        <h1 class="rm-page-title">{{ $contact->display_name }}</h1>
        <p class="rm-page-subtitle">{{ $contact->job_title ?? 'Role not specified' }} • {{ $contact->company_name ?? 'Individual' }}</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('contacts.edit', $contact) }}" class="rm-btn rm-btn-secondary">
            <i class="bi bi-pencil"></i> Edit Details
        </a>
        <a href="{{ route('contacts.index') }}" class="rm-btn rm-btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Contacts
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Details -->
    <div class="col-12 col-lg-5">
        <div class="rm-card mb-4">
            <h3 class="rm-card-title mb-3">Contact Information</h3>
            <div class="d-flex flex-column gap-3">
                <div>
                    <span class="rm-stat-label d-block mb-1">Email Address</span>
                    <span class="fw-semibold text-dark">{{ $contact->email }}</span>
                    @if($contact->do_not_contact)
                        <span class="badge bg-danger-subtle text-danger ms-2 small">Do Not Contact</span>
                    @endif
                </div>
                <div>
                    <span class="rm-stat-label d-block mb-1">Company & Role</span>
                    <span class="text-dark">{{ $contact->job_title ?: 'Not specified' }} at {{ $contact->company_name ?: 'Not specified' }}</span>
                </div>
                @if($contact->company_website)
                    <div>
                        <span class="rm-stat-label d-block mb-1">Website</span>
                        <a href="{{ $contact->company_website }}" target="_blank" class="text-decoration-none">
                            {{ $contact->company_website }} <i class="bi bi-box-arrow-up-right small"></i>
                        </a>
                    </div>
                @endif
                @if($contact->linkedin_url)
                    <div>
                        <span class="rm-stat-label d-block mb-1">LinkedIn</span>
                        <a href="{{ $contact->linkedin_url }}" target="_blank" class="text-decoration-none">
                            Profile Link <i class="bi bi-box-arrow-up-right small"></i>
                        </a>
                    </div>
                @endif
                <div>
                    <span class="rm-stat-label d-block mb-1">Location</span>
                    <span class="text-dark">{{ $contact->city ?: 'Not specified' }}</span>
                </div>
                <div>
                    <span class="rm-stat-label d-block mb-1">Assigned Categories</span>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        @forelse($contact->categories as $cat)
                            <span class="badge bg-light text-dark border">{{ $cat->name }}</span>
                        @empty
                            <span class="text-muted small">None</span>
                        @endforelse
                    </div>
                </div>
                <div>
                    <span class="rm-stat-label d-block mb-1">Status</span>
                    <span class="rm-badge bg-light text-dark border">{{ $contact->status }}</span>
                </div>
                @if($contact->notes)
                    <div class="border-top pt-2">
                        <span class="rm-stat-label d-block mb-1">Internal Notes</span>
                        <p class="text-muted small mb-0">{{ $contact->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Campaign & Activity History -->
    <div class="col-12 col-lg-7">
        <div class="rm-card mb-4">
            <h3 class="rm-card-title mb-3">Outreach Campaign History</h3>
            @if($contact->campaignRecipients->isEmpty())
                <p class="text-muted small my-3">No emails have been sent to this contact yet.</p>
            @else
                <div class="table-responsive">
                    <table class="rm-table">
                        <thead>
                            <tr>
                                <th>Campaign</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contact->campaignRecipients as $cr)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $cr->campaign->name ?? 'Campaign' }}</div>
                                        <div class="text-muted small text-truncate" style="max-width: 250px;">{{ $cr->rendered_subject }}</div>
                                    </td>
                                    <td>
                                        @if($cr->status === 'sent')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Sent</span>
                                        @elseif($cr->status === 'failed')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Failed</span>
                                        @else
                                            <span class="badge bg-light text-muted border">{{ ucfirst($cr->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ $cr->sent_at ? $cr->sent_at->format('M d, Y') : 'Pending' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
