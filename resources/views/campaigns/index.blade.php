@extends('layouts.app')

@section('title', 'Campaigns')

@section('content')
<div class="rm-header">
    <div>
        <h1 class="rm-page-title">Campaigns</h1>
        <p class="rm-page-subtitle">Track, schedule, and review personalized outreach and job application batches.</p>
    </div>
    <a href="{{ route('campaigns.create') }}" class="rm-btn rm-btn-primary">
        <i class="bi bi-plus-lg"></i> New Campaign
    </a>
</div>

<div class="rm-card mb-4 p-2">
    <div class="d-flex align-items-center gap-2 overflow-x-auto">
        <a href="{{ route('campaigns.index') }}" class="rm-btn {{ !request('status') ? 'rm-btn-primary' : 'rm-btn-secondary' }} py-1 px-3">
            All Campaigns
        </a>
        <a href="{{ route('campaigns.index', ['status' => 'processing']) }}" class="rm-btn {{ request('status') === 'processing' ? 'rm-btn-primary' : 'rm-btn-secondary' }} py-1 px-3">
            Processing
        </a>
        <a href="{{ route('campaigns.index', ['status' => 'scheduled']) }}" class="rm-btn {{ request('status') === 'scheduled' ? 'rm-btn-primary' : 'rm-btn-secondary' }} py-1 px-3">
            Scheduled
        </a>
        <a href="{{ route('campaigns.index', ['status' => 'completed']) }}" class="rm-btn {{ request('status') === 'completed' ? 'rm-btn-primary' : 'rm-btn-secondary' }} py-1 px-3">
            Completed
        </a>
        <a href="{{ route('campaigns.index', ['status' => 'draft']) }}" class="rm-btn {{ request('status') === 'draft' ? 'rm-btn-primary' : 'rm-btn-secondary' }} py-1 px-3">
            Drafts
        </a>
    </div>
</div>

<div class="rm-table-wrapper">
    @if($campaigns->isEmpty())
        <div class="rm-empty-state">
            <div class="rm-empty-icon">
                <i class="bi bi-rocket-takeoff"></i>
            </div>
            <h4 class="rm-empty-title">No campaigns found</h4>
            <p class="rm-empty-text">Create your first outreach campaign by selecting target categories and email templates.</p>
            <a href="{{ route('campaigns.create') }}" class="rm-btn rm-btn-primary">
                <i class="bi bi-plus-lg"></i> Launch Campaign
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
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Date Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($campaigns as $camp)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark">
                                    <a href="{{ route('campaigns.show', $camp) }}" class="text-dark text-decoration-none">
                                        {{ $camp->name }}
                                    </a>
                                </div>
                                <div class="text-muted small">{{ $camp->template ? $camp->template->name : 'Custom body' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $camp->type }}</span>
                            </td>
                            <td>
                                <div>{{ $camp->total_recipients }} contacts</div>
                            </td>
                            <td>
                                @php
                                    $pct = $camp->total_recipients > 0 ? round(($camp->sent_count / $camp->total_recipients) * 100) : 0;
                                @endphp
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: {{ $pct }}%;"></div>
                                    </div>
                                    <span class="text-muted small">{{ $camp->sent_count }}/{{ $camp->total_recipients }}</span>
                                </div>
                            </td>
                            <td>
                                @if($camp->status === 'completed')
                                    <span class="rm-badge" style="background-color: #dcfce7; color: #15803d;">
                                        <span class="rm-badge-dot"></span> Completed
                                    </span>
                                @elseif($camp->status === 'processing')
                                    <span class="rm-badge" style="background-color: #fef3c7; color: #b45309;">
                                        <span class="rm-badge-dot"></span> Processing
                                    </span>
                                @elseif($camp->status === 'scheduled')
                                    <span class="rm-badge" style="background-color: #e0e7ff; color: #4338ca;">
                                        <span class="rm-badge-dot"></span> Scheduled
                                    </span>
                                @elseif($camp->status === 'cancelled')
                                    <span class="rm-badge" style="background-color: #fee2e2; color: #991b1b;">
                                        <span class="rm-badge-dot"></span> Cancelled
                                    </span>
                                @else
                                    <span class="rm-badge" style="background-color: #f1f5f9; color: #475569;">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted small">{{ $camp->created_at->format('M d, Y') }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('campaigns.show', $camp) }}" class="rm-btn rm-btn-secondary py-1 px-3 text-decoration-none">
                                    Manage
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($campaigns->hasPages())
            <div class="p-3 border-top">
                {{ $campaigns->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
