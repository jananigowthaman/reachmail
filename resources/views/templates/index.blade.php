@extends('layouts.app')

@section('title', 'Email Templates')

@section('content')
<div class="rm-header">
    <div>
        <h1 class="rm-page-title">Email Templates</h1>
        <p class="rm-page-subtitle">Design reusable, personalised outreach and job application emails.</p>
    </div>
    <a href="{{ route('templates.create') }}" class="rm-btn rm-btn-primary">
        <i class="bi bi-plus-lg"></i> Create Template
    </a>
</div>

<!-- Category Filters -->
<div class="d-flex align-items-center gap-2 mb-4 overflow-x-auto pb-2">
    <a href="{{ route('templates.index') }}" class="rm-btn {{ !request('category') ? 'rm-btn-primary' : 'rm-btn-secondary' }} py-1 px-3">
        All
    </a>
    @foreach(['Job Application', 'HR Enquiry', 'Recruiter Introduction', 'Follow-up', 'Interview Follow-up', 'General'] as $cat)
        <a href="{{ route('templates.index', ['category' => $cat]) }}" class="rm-btn {{ request('category') === $cat ? 'rm-btn-primary' : 'rm-btn-secondary' }} py-1 px-3">
            {{ $cat }}
        </a>
    @endforeach
</div>

<div class="row g-4">
    @forelse($templates as $template)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="rm-card h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-light text-dark border small">{{ $template->category }}</span>
                        @if($template->is_favorite)
                            <i class="bi bi-star-fill text-warning"></i>
                        @endif
                    </div>
                    <h3 class="rm-card-title fs-5 mb-1">{{ $template->name }}</h3>
                    <p class="text-muted small fw-medium mb-3">Subject: "{{ $template->subject }}"</p>
                    <div class="text-muted small border-top pt-2" style="max-height: 80px; overflow: hidden; text-overflow: ellipsis;">
                        {{ strip_tags($template->body_html) }}
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3 mt-3 border-top border-light">
                    <div class="d-flex gap-1">
                        <a href="{{ route('templates.edit', $template) }}" class="rm-btn rm-btn-secondary py-1 px-2 text-decoration-none">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <form method="POST" action="{{ route('templates.duplicate', $template) }}">
                            @csrf
                            <button type="submit" class="rm-btn rm-btn-ghost py-1 px-2 text-dark" title="Duplicate">
                                <i class="bi bi-copy"></i>
                            </button>
                        </form>
                    </div>

                    <form method="POST" action="{{ route('templates.destroy', $template) }}" onsubmit="return confirm('Delete this template?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rm-btn rm-btn-ghost py-1 px-2 text-danger" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="rm-card">
                <div class="rm-empty-state">
                    <div class="rm-empty-icon">
                        <i class="bi bi-envelope-paper"></i>
                    </div>
                    <h4 class="rm-empty-title">No email templates created</h4>
                    <p class="rm-empty-text">Create professional templates using dynamic placeholders like <code>@{{first_name}}</code> and <code>@{{company_name}}</code>.</p>
                    <a href="{{ route('templates.create') }}" class="rm-btn rm-btn-primary">
                        <i class="bi bi-plus-lg"></i> Create First Template
                    </a>
                </div>
            </div>
        </div>
    @endforelse
</div>
@endsection
