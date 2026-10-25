@extends('layouts.app')

@section('title', $campaign->name)

@section('content')
<div class="rm-header">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h1 class="rm-page-title">{{ $campaign->name }}</h1>
            @if($campaign->status === 'completed')
                <span class="rm-badge" style="background-color: #dcfce7; color: #15803d;">
                    <span class="rm-badge-dot"></span> Completed
                </span>
            @elseif($campaign->status === 'processing')
                <span class="rm-badge" style="background-color: #fef3c7; color: #b45309;">
                    <span class="rm-badge-dot"></span> Processing
                </span>
            @else
                <span class="rm-badge" style="background-color: #f1f5f9; color: #475569;">
                    {{ ucfirst($campaign->status) }}
                </span>
            @endif
        </div>
        <p class="rm-page-subtitle">Created on {{ $campaign->created_at->format('M d, Y H:i') }} • Type: {{ $campaign->type }}</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if(in_array($campaign->status, ['draft', 'scheduled']))
            <form method="POST" action="{{ route('campaigns.launch', $campaign) }}" onsubmit="return confirm('Launch this campaign now?');">
                @csrf
                <button type="submit" class="rm-btn rm-btn-primary">
                    <i class="bi bi-send"></i> Launch Now
                </button>
            </form>
        @endif

        @if($campaign->status === 'processing')
            <form method="POST" action="{{ route('campaigns.cancel', $campaign) }}" onsubmit="return confirm('Halt remaining pending emails?');">
                @csrf
                <button type="submit" class="rm-btn rm-btn-danger">
                    <i class="bi bi-stop-circle"></i> Cancel Campaign
                </button>
            </form>
        @endif

        <a href="{{ route('campaigns.index') }}" class="rm-btn rm-btn-secondary">
            Back to List
        </a>
    </div>
</div>

<!-- Metrics Bar -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="rm-stat-tile">
            <span class="rm-stat-label">Total Recipients</span>
            <span class="rm-stat-value">{{ $campaign->total_recipients }}</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rm-stat-tile">
            <span class="rm-stat-label">Sent</span>
            <span class="rm-stat-value text-success">{{ $campaign->sent_count }}</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rm-stat-tile">
            <span class="rm-stat-label">Estimated Opens</span>
            <span class="rm-stat-value text-primary">{{ $campaign->opened_count }}</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rm-stat-tile">
            <span class="rm-stat-label">Replies</span>
            <span class="rm-stat-value text-info">{{ $campaign->replied_count }}</span>
        </div>
    </div>
</div>

<!-- Send Test Email Tool -->
<div class="rm-card mb-4 p-3 bg-light">
    <form method="POST" action="{{ route('campaigns.sendTest', $campaign) }}" class="d-flex align-items-center gap-2 flex-wrap">
        @csrf
        <i class="bi bi-shield-check fs-5 text-muted"></i>
        <span class="fw-semibold text-dark small">Send a single test message:</span>
        <input type="email" name="test_email" class="rm-input py-1" style="width: auto; min-width: 250px;" required placeholder="your.personal@email.com">
        <button type="submit" class="rm-btn rm-btn-secondary py-1 px-3">
            Send Test
        </button>
    </form>
</div>

<!-- Recipients Snapshot Table -->
<div class="rm-card">
    <div class="rm-card-header">
        <h3 class="rm-card-title">Recipient Messages</h3>
        <span class="badge bg-light text-muted border">{{ $campaign->recipients->count() }} personalized snapshots</span>
    </div>

    <div class="table-responsive">
        <table class="rm-table">
            <thead>
                <tr>
                    <th>Recipient</th>
                    <th>Rendered Subject</th>
                    <th>Sending Status</th>
                    <th>Sent Timestamp</th>
                </tr>
            </thead>
            <tbody>
                @foreach($campaign->recipients as $rec)
                    <tr>
                        <td>
                            <div class="fw-medium text-dark">{{ $rec->contact->display_name ?? 'Contact' }}</div>
                            <div class="text-muted small">{{ $rec->email }}</div>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 320px;" title="{{ $rec->rendered_subject }}">
                                {{ $rec->rendered_subject }}
                            </div>
                        </td>
                        <td>
                            @if($rec->status === 'sent')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Sent</span>
                            @elseif($rec->status === 'failed')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle" title="{{ $rec->error_message }}">Failed</span>
                            @elseif($rec->status === 'skipped')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Skipped</span>
                            @else
                                <span class="badge bg-light text-muted border">Pending</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-muted small">
                                {{ $rec->sent_at ? $rec->sent_at->format('M d, H:i') : '—' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
