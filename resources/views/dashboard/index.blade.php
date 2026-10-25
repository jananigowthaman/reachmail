@extends('layouts.app')

@section('title', 'Overview Dashboard')

@section('content')
<div class="rm-header">
    <div>
        <h1 class="rm-page-title">Overview</h1>
        <p class="rm-page-subtitle">Real-time status of your outreach campaigns, contacts, and job applications.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('contacts.import') }}" class="rm-btn rm-btn-secondary">
            <i class="bi bi-file-earmark-spreadsheet"></i> Import Contacts
        </a>
        <a href="{{ route('campaigns.create') }}" class="rm-btn rm-btn-primary">
            <i class="bi bi-plus-lg"></i> New Campaign
        </a>
    </div>
</div>

<!-- Metrics 4x2 Grid -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="rm-stat-tile py-3 px-4">
            <span class="rm-stat-label" style="font-size: 0.72rem; letter-spacing: 0.05em;">Total Contacts</span>
            <div class="mt-1">
                <span class="rm-stat-value" style="font-size: 1.75rem;">{{ number_format($metrics['total_contacts']) }}</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="rm-stat-tile py-3 px-4">
            <span class="rm-stat-label" style="font-size: 0.72rem; letter-spacing: 0.05em;">Emails Sent</span>
            <div class="mt-1">
                <span class="rm-stat-value" style="font-size: 1.75rem;">{{ number_format($metrics['emails_sent']) }}</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="rm-stat-tile py-3 px-4">
            <span class="rm-stat-label" style="font-size: 0.72rem; letter-spacing: 0.05em;">Replies Received</span>
            <div class="mt-1">
                <span class="rm-stat-value" style="font-size: 1.75rem;">{{ number_format($metrics['replies_received']) }}</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="rm-stat-tile py-3 px-4">
            <span class="rm-stat-label" style="font-size: 0.72rem; letter-spacing: 0.05em;">Applications Submitted</span>
            <div class="mt-1">
                <span class="rm-stat-value" style="font-size: 1.75rem;">{{ number_format($metrics['job_applications']) }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Dashboard Main Columns -->
<div class="row g-4">
    <!-- Left Column: Recent Campaigns & Activity -->
    <div class="col-12 col-lg-8">
        <!-- Recent Campaigns -->
        <div class="rm-card mb-4">
            <div class="rm-card-header">
                <h3 class="rm-card-title">Recent Campaigns</h3>
                <a href="{{ route('campaigns.index') }}" class="rm-btn rm-btn-ghost py-1 px-2 text-decoration-none">
                    View all <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            @if($recentCampaigns->isEmpty())
                <div class="rm-empty-state py-4 my-2 text-center d-flex flex-column align-items-center justify-content-center">
                    <div class="rm-empty-icon mb-3" style="width: 48px; height: 48px; font-size: 1.35rem;">
                        <i class="bi bi-rocket-takeoff"></i>
                    </div>
                    <h4 class="rm-empty-title fs-6 fw-bold mb-1">No campaigns yet</h4>
                    <p class="rm-empty-text text-muted small mb-3" style="max-width: 380px;">Import your target contacts, choose an email template, and launch your first personal outreach campaign.</p>
                    <a href="{{ route('campaigns.create') }}" class="rm-btn rm-btn-primary py-2 px-4">
                        <i class="bi bi-plus-lg"></i> Create Campaign
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="rm-table">
                        <thead>
                            <tr>
                                <th>Campaign Name</th>
                                <th>Type</th>
                                <th>Recipients</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentCampaigns as $c)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $c->name }}</div>
                                        <div class="text-muted small">{{ $c->template ? $c->template->name : 'Custom body' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $c->type }}</span>
                                    </td>
                                    <td>
                                        <div>{{ $c->total_recipients }} recipients</div>
                                        <div class="text-muted small">{{ $c->sent_count }} sent</div>
                                    </td>
                                    <td>
                                        @if($c->status === 'completed')
                                            <span class="rm-badge" style="background-color: #dcfce7; color: #15803d;">
                                                <span class="rm-badge-dot"></span> Completed
                                            </span>
                                        @elseif($c->status === 'scheduled')
                                            <span class="rm-badge" style="background-color: #e0e7ff; color: #4338ca;">
                                                <span class="rm-badge-dot"></span> Scheduled
                                            </span>
                                        @elseif($c->status === 'processing')
                                            <span class="rm-badge" style="background-color: #fef3c7; color: #b45309;">
                                                <span class="rm-badge-dot"></span> Processing
                                            </span>
                                        @else
                                            <span class="rm-badge" style="background-color: #f1f5f9; color: #475569;">
                                                <span class="rm-badge-dot"></span> {{ ucfirst($c->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('campaigns.show', $c) }}" class="rm-btn rm-btn-secondary py-1 px-2 text-decoration-none">
                                            Details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Recent Activity Feed -->
        <div class="rm-card">
            <div class="rm-card-header">
                <h3 class="rm-card-title">Recent Activity</h3>
            </div>
            @if($recentActivity->isEmpty())
                <p class="text-muted small my-3">No activity recorded yet. Once you import contacts, create templates, or send emails, events will appear here.</p>
            @else
                <div class="d-flex flex-column gap-3">
                    @foreach($recentActivity as $act)
                        <div class="d-flex align-items-start gap-3 pb-3 border-bottom border-light">
                            <div class="p-2 rounded-circle bg-light text-muted">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-medium text-dark">{{ $act->description }}</div>
                                <div class="text-muted small">{{ $act->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Right Column: Quick Actions & Upcoming Reminders -->
    <div class="col-12 col-lg-4">
        <!-- Quick Actions Panel -->
        <div class="rm-card mb-4">
            <h3 class="rm-card-title mb-3">Quick Actions</h3>
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('contacts.create') }}" class="rm-btn rm-btn-secondary justify-content-start">
                    <i class="bi bi-person-plus text-muted"></i> Add Single Contact
                </a>
                <a href="{{ route('contacts.import') }}" class="rm-btn rm-btn-secondary justify-content-start">
                    <i class="bi bi-file-earmark-spreadsheet text-muted"></i> Import CSV or Excel
                </a>
                <a href="{{ route('templates.create') }}" class="rm-btn rm-btn-secondary justify-content-start">
                    <i class="bi bi-envelope-plus text-muted"></i> Create Email Template
                </a>
                <a href="{{ route('applications.create') }}" class="rm-btn rm-btn-secondary justify-content-start">
                    <i class="bi bi-briefcase text-muted"></i> Log Job Application
                </a>
                <a href="{{ route('resumes.index') }}" class="rm-btn rm-btn-secondary justify-content-start">
                    <i class="bi bi-upload text-muted"></i> Manage Resumes & Files
                </a>
            </div>
        </div>

        <!-- Upcoming Schedule & Follow-ups -->
        <div class="rm-card">
            <div class="rm-card-header">
                <h3 class="rm-card-title">Pending Follow-ups</h3>
                <span class="badge bg-light text-muted border">{{ $upcomingFollowUps->count() }}</span>
            </div>
            @if($upcomingFollowUps->isEmpty())
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-calendar-check fs-2 text-muted mb-2 d-block"></i>
                    <span class="small">No pending follow-ups scheduled.</span>
                </div>
            @else
                <div class="d-flex flex-column gap-3">
                    @foreach($upcomingFollowUps as $reminder)
                        <div class="p-3 rounded border border-light bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold text-dark">{{ $reminder->title }}</span>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle small">
                                    {{ \Carbon\Carbon::parse($reminder->reminder_date)->format('M d') }}
                                </span>
                            </div>
                            @if($reminder->contact)
                                <div class="text-muted small">
                                    <i class="bi bi-person"></i> {{ $reminder->contact->display_name }} ({{ $reminder->contact->company_name ?? 'Individual' }})
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
