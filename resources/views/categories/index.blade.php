@extends('layouts.app')

@section('title', 'Lists & Categories')

@section('content')
<div class="rm-header">
    <div>
        <h1 class="rm-page-title">Lists & Categories</h1>
        <p class="rm-page-subtitle">Group contacts into functional targeting lists for job applications and outreach.</p>
    </div>
    <button type="button" class="rm-btn rm-btn-primary" data-bs-toggle="modal" data-bs-target="#newCategoryModal">
        <i class="bi bi-plus-lg"></i> New Category
    </button>
</div>

<div class="row g-4">
    @forelse($categories as $cat)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="rm-card h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle d-inline-block" style="width: 10px; height: 10px; background-color: {{ $cat->color }};"></span>
                            <h3 class="rm-card-title mb-0" style="font-size: 0.95rem; font-weight: 600;">{{ $cat->name }}</h3>
                        </div>
                        <span class="badge bg-light text-dark border small" style="font-size: 0.72rem;">{{ $cat->contacts_count }} contacts</span>
                    </div>
                    <p class="text-muted small mb-3">
                        {{ $cat->description ?: 'No description provided.' }}
                    </p>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top border-light">
                    <a href="{{ route('contacts.index', ['category_id' => $cat->id]) }}" class="rm-btn rm-btn-ghost py-1 px-2 text-decoration-none">
                        View Contacts <i class="bi bi-arrow-right"></i>
                    </a>
                    <div class="d-flex gap-1">
                        <form method="POST" action="{{ route('categories.destroy', $cat) }}" onsubmit="return confirm('Delete this category? Contacts will not be deleted.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rm-btn rm-btn-ghost py-1 px-2 text-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="rm-card">
                <div class="rm-empty-state">
                    <div class="rm-empty-icon">
                        <i class="bi bi-tags"></i>
                    </div>
                    <h4 class="rm-empty-title">No categories created</h4>
                    <p class="rm-empty-text">Create categories such as "Chennai HR", "Laravel Developers", or "Recruiters" to organize outreach campaigns.</p>
                    <button type="button" class="rm-btn rm-btn-primary" data-bs-toggle="modal" data-bs-target="#newCategoryModal">
                        <i class="bi bi-plus-lg"></i> Create First Category
                    </button>
                </div>
            </div>
        </div>
    @endforelse
</div>

<!-- Modal for New Category -->
<div class="modal fade" id="newCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('categories.store') }}" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Create New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="rm-label">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="rm-input" required placeholder="e.g. Bangalore HR Leads">
                </div>
                <div class="mb-3">
                    <label class="rm-label">Description</label>
                    <textarea name="description" rows="2" class="rm-textarea" placeholder="Optional details regarding this list..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="rm-label">Color Label</label>
                    <input type="color" name="color" value="#0f766e" class="form-control form-control-color w-100" style="height: 42px;">
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="rm-btn rm-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="rm-btn rm-btn-primary">Create Category</button>
            </div>
        </form>
    </div>
</div>
@endsection
