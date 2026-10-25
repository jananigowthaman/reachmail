@extends('layouts.app')

@section('title', 'Analytics & Reports')

@section('content')
<div class="rm-header">
    <div>
        <h1 class="rm-page-title">Reports & Metrics</h1>
        <p class="rm-page-subtitle">Real performance metrics derived directly from actual email deliveries and responses.</p>
    </div>
    <a href="{{ route('reports.export') }}" class="rm-btn rm-btn-secondary">
        <i class="bi bi-download"></i> Export Reports CSV
    </a>
</div>

<!-- Key Performance Tiles -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="rm-stat-tile">
            <span class="rm-stat-label">Total Outbound Sent</span>
            <span class="rm-stat-value">{{ number_format($emailsSent) }}</span>
            <span class="text-muted small">Across {{ $totalCampaigns }} campaigns</span>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="rm-stat-tile">
            <span class="rm-stat-label">Estimated Open Rate</span>
            <span class="rm-stat-value text-primary">{{ $openRate }}%</span>
            <span class="text-muted small">{{ $estimatedOpens }} opened events</span>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="rm-stat-tile">
            <span class="rm-stat-label">Direct Reply Rate</span>
            <span class="rm-stat-value text-success">{{ $replyRate }}%</span>
            <span class="text-muted small">{{ $repliesReceived }} replies synchronized</span>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="rm-stat-tile">
            <span class="rm-stat-label">Interviews & Offers</span>
            <span class="rm-stat-value text-info">{{ $interviewsScheduled + $offersReceived }}</span>
            <span class="text-muted small">{{ $interviewsScheduled }} interviews, {{ $offersReceived }} offers</span>
        </div>
    </div>
</div>

<!-- Detailed Campaign Performance Table -->
<div class="rm-card">
    <div class="rm-card-header">
        <h3 class="rm-card-title">Campaign Efficiency Breakdown</h3>
    </div>

    <div class="table-responsive">
        <table class="rm-table">
            <thead>
                <tr>
                    <th>Campaign</th>
                    <th>Status</th>
                    <th>Recipients</th>
                    <th>Sent</th>
                    <th>Estimated Opens</th>
                    <th>Replies</th>
                </tr>
            </thead>
            <tbody>
                @forelse($campaigns as $camp)
                    <tr>
                        <td>
                            <div class="fw-semibold text-dark">{{ $camp->name }}</div>
                            <div class="text-muted small">{{ $camp->type }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ ucfirst($camp->status) }}</span>
                        </td>
                        <td>{{ $camp->total_recipients }}</td>
                        <td>{{ $camp->sent_count }}</td>
                        <td>
                            <span class="text-primary fw-medium">{{ $camp->opened_count }}</span>
                            @if($camp->sent_count > 0)
                                <small class="text-muted">({{ round(($camp->opened_count / $camp->sent_count) * 100) }}%)</small>
                            @endif
                        </td>
                        <td>
                            <span class="text-success fw-medium">{{ $camp->replied_count }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No campaign data available yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
