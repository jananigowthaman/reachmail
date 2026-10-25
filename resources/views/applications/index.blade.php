@extends('layouts.app')

@section('title', 'Job Applications Tracker')

@section('content')
<div class="rm-header">
    <div>
        <h1 class="rm-page-title">Job Applications</h1>
        <p class="rm-page-subtitle">Track submitted roles, interviews, resume versions used, and scheduled follow-ups.</p>
    </div>
    <a href="{{ route('applications.create') }}" class="rm-btn rm-btn-primary">
        <i class="bi bi-briefcase"></i> Log Application
    </a>
</div>

<!-- Status Filters -->
<div class="rm-card mb-4 p-2">
    <div class="d-flex align-items-center gap-2 overflow-x-auto">
        <a href="{{ route('applications.index') }}" class="rm-btn {{ !request('status') ? 'rm-btn-primary' : 'rm-btn-secondary' }} py-1 px-3">
            All Statuses
        </a>
        @foreach(['Applied', 'Interview', 'Response Received', 'Offer', 'Rejected'] as $st)
            <a href="{{ route('applications.index', ['status' => $st]) }}" class="rm-btn {{ request('status') === $st ? 'rm-btn-primary' : 'rm-btn-secondary' }} py-1 px-3">
                {{ $st }}
            </a>
        @endforeach
    </div>
</div>

<div class="rm-table-wrapper">
    @if($applications->isEmpty())
        <div class="rm-empty-state">
            <div class="rm-empty-icon">
                <i class="bi bi-briefcase"></i>
            </div>
            <h4 class="rm-empty-title">No job applications logged</h4>
            <p class="rm-empty-text">Track your applications manually or associate outreach campaigns with active opportunities.</p>
            <a href="{{ route('applications.create') }}" class="rm-btn rm-btn-primary">
                <i class="bi bi-plus-lg"></i> Log First Application
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="rm-table">
                <thead>
                    <tr>
                        <th>Company & Role</th>
                        <th>HR Contact</th>
                        <th>Resume Attached</th>
                        <th>Application Date</th>
                        <th>Status</th>
                        <th>Follow-up Due</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applications as $app)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark">{{ $app->company_name }}</div>
                                <div class="text-muted small">
                                    {{ $app->job_title }}
                                    @if($app->job_url)
                                        <a href="{{ $app->job_url }}" target="_blank" class="text-decoration-none ms-1"><i class="bi bi-box-arrow-up-right"></i></a>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($app->contact)
                                    <div>{{ $app->contact->display_name }}</div>
                                    <div class="text-muted small">{{ $app->contact->email }}</div>
                                @elseif($app->hr_contact_name || $app->hr_email)
                                    <div>{{ $app->hr_contact_name }}</div>
                                    <div class="text-muted small">{{ $app->hr_email }}</div>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                @if($app->resume)
                                    <span class="badge bg-light text-dark border"><i class="bi bi-paperclip"></i> {{ $app->resume->display_name }}</span>
                                @else
                                    <span class="text-muted small">None</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted small">{{ $app->application_date ? $app->application_date->format('M d, Y') : '—' }}</span>
                            </td>
                            <td>
                                @if($app->status === 'Offer')
                                    <span class="rm-badge" style="background-color: #dcfce7; color: #15803d;">
                                        <span class="rm-badge-dot"></span> Offer
                                    </span>
                                @elseif($app->status === 'Interview')
                                    <span class="rm-badge" style="background-color: #dbeafe; color: #1e40af;">
                                        <span class="rm-badge-dot"></span> Interview
                                    </span>
                                @elseif($app->status === 'Applied')
                                    <span class="rm-badge" style="background-color: #f1f5f9; color: #334155;">
                                        Applied
                                    </span>
                                @elseif($app->status === 'Rejected')
                                    <span class="rm-badge" style="background-color: #fee2e2; color: #991b1b;">
                                        Rejected
                                    </span>
                                @else
                                    <span class="rm-badge" style="background-color: #f1f5f9; color: #64748b;">
                                        {{ $app->status }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($app->next_follow_up_date)
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                        {{ $app->next_follow_up_date->format('M d') }}
                                    </span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('applications.edit', $app) }}" class="rm-btn rm-btn-ghost py-1 px-2" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('applications.destroy', $app) }}" onsubmit="return confirm('Remove application record?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rm-btn rm-btn-ghost py-1 px-2 text-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($applications->hasPages())
            <div class="p-3 border-top">
                {{ $applications->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
