@extends('layouts.app')

@section('title', 'Reply Details')

@section('content')
<div class="rm-header">
    <div>
        <h1 class="rm-page-title">Reply from {{ $reply->sender_name ?: $reply->sender_email }}</h1>
        <p class="rm-page-subtitle">Received on {{ $reply->received_at->format('M d, Y H:i') }}</p>
    </div>
    <a href="{{ route('replies.index') }}" class="rm-btn rm-btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Replies
    </a>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="rm-card mb-4">
            <div class="border-bottom pb-3 mb-3">
                <div class="d-flex justify-content-between align-items-baseline mb-2">
                    <h3 class="fs-5 fw-bold text-dark mb-0">{{ $reply->subject }}</h3>
                    <span class="text-muted small">{{ $reply->received_at->format('M d, Y h:i A') }}</span>
                </div>
                <div class="text-muted small">From: <span class="text-dark fw-medium">{{ $reply->sender_name }} &lt;{{ $reply->sender_email }}&gt;</span></div>
            </div>

            <div class="p-3 bg-light rounded" style="font-size: 0.95rem; line-height: 1.7; min-height: 180px;">
                {{ $reply->snippet ?: 'No snippet available.' }}
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="rm-card">
            <h3 class="rm-card-title mb-3">Associated Details</h3>
            @if($reply->contact)
                <div class="mb-3">
                    <label class="rm-label">Contact</label>
                    <div class="fw-semibold text-dark">{{ $reply->contact->display_name }}</div>
                    <div class="text-muted small">{{ $reply->contact->job_title }} • {{ $reply->contact->company_name }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('replies.update', $reply) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="rm-label">Status</label>
                    <select name="status" class="rm-select">
                        <option value="reviewed" {{ $reply->status === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                        <option value="action_required" {{ $reply->status === 'action_required' ? 'selected' : '' }}>Action Required</option>
                        <option value="unread" {{ $reply->status === 'unread' ? 'selected' : '' }}>Mark as Unread</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="rm-label">Notes</label>
                    <textarea name="notes" rows="3" class="rm-textarea" placeholder="Internal reminder or note...">{{ $reply->notes }}</textarea>
                </div>
                <button type="submit" class="rm-btn rm-btn-primary w-100">Save Update</button>
            </form>
        </div>
    </div>
</div>
@endsection
