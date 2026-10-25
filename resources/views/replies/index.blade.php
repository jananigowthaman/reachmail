@extends('layouts.app')

@section('title', 'Replies & Inbox')

@section('content')
<div class="rm-header">
    <div>
        <h1 class="rm-page-title">Replies & Inbox</h1>
        <p class="rm-page-subtitle">Track inbound responses to your outreach and job applications.</p>
    </div>
    <form method="POST" action="{{ route('replies.sync') }}">
        @csrf
        <button type="submit" class="rm-btn rm-btn-secondary">
            <i class="bi bi-arrow-repeat"></i> Sync Replies Now
        </button>
    </form>
</div>

<div class="rm-table-wrapper">
    @if($replies->isEmpty())
        <div class="rm-empty-state">
            <div class="rm-empty-icon">
                <i class="bi bi-chat-left-dots"></i>
            </div>
            <h4 class="rm-empty-title">No incoming replies detected</h4>
            <p class="rm-empty-text">When contacts and HR professionals reply to your campaigns, responses will automatically appear here.</p>
            <form method="POST" action="{{ route('replies.sync') }}">
                @csrf
                <button type="submit" class="rm-btn rm-btn-primary">
                    <i class="bi bi-arrow-repeat"></i> Check Inbox Now
                </button>
            </form>
        </div>
    @else
        <div class="table-responsive">
            <table class="rm-table">
                <thead>
                    <tr>
                        <th>Sender / Contact</th>
                        <th>Subject & Snippet</th>
                        <th>Received</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($replies as $rep)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark">{{ $rep->sender_name ?: $rep->sender_email }}</div>
                                <div class="text-muted small">{{ $rep->contact ? $rep->contact->company_name : 'New Contact' }}</div>
                            </td>
                            <td>
                                <div class="fw-medium text-dark">{{ $rep->subject }}</div>
                                <div class="text-muted small text-truncate" style="max-width: 450px;">{{ $rep->snippet }}</div>
                            </td>
                            <td>
                                <span class="text-muted small">{{ $rep->received_at->diffForHumans() }}</span>
                            </td>
                            <td>
                                @if($rep->status === 'unread')
                                    <span class="badge bg-primary text-white">Unread</span>
                                @else
                                    <span class="badge bg-light text-muted border">Reviewed</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('replies.show', $rep) }}" class="rm-btn rm-btn-secondary py-1 px-3 text-decoration-none">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($replies->hasPages())
            <div class="p-3 border-top">
                {{ $replies->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
