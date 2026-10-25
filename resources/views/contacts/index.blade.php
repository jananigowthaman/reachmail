@extends('layouts.app')

@section('title', 'Contacts Directory')

@section('content')
<div class="rm-header">
    <div>
        <h1 class="rm-page-title">Contacts</h1>
        <p class="rm-page-subtitle">Manage HR contacts, recruiters, and companies for outreach.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('contacts.export') }}" class="rm-btn rm-btn-secondary">
            <i class="bi bi-download"></i> Export CSV
        </a>
        <a href="{{ route('contacts.import') }}" class="rm-btn rm-btn-secondary">
            <i class="bi bi-file-earmark-spreadsheet"></i> Import CSV/XLSX
        </a>
        <a href="{{ route('contacts.create') }}" class="rm-btn rm-btn-primary">
            <i class="bi bi-person-plus"></i> Add Contact
        </a>
    </div>
</div>

<!-- Search & Auto-Filters Bar -->
<div class="rm-card mb-4 p-3">
    <form method="GET" action="{{ route('contacts.index') }}" id="contacts-filter-form" class="row g-3 align-items-center">
        <div class="col-12 col-md-5">
            <div class="rm-search-box">
                <i class="bi bi-search rm-search-icon"></i>
                <input type="text" name="search" id="contacts_search_input" class="rm-input" placeholder="Search by name, email, company..." value="{{ request('search') }}" autocomplete="off">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="category_id" id="category_filter" class="rm-select">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }} ({{ $cat->contacts_count }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select name="status" id="status_filter" class="rm-select">
                <option value="">All Statuses</option>
                <option value="New" {{ request('status') == 'New' ? 'selected' : '' }}>New</option>
                <option value="Ready to Contact" {{ request('status') == 'Ready to Contact' ? 'selected' : '' }}>Ready to Contact</option>
                <option value="Contacted" {{ request('status') == 'Contacted' ? 'selected' : '' }}>Contacted</option>
                <option value="Replied" {{ request('status') == 'Replied' ? 'selected' : '' }}>Replied</option>
                <option value="Follow-up Due" {{ request('status') == 'Follow-up Due' ? 'selected' : '' }}>Follow-up Due</option>
                <option value="Do Not Contact" {{ request('status') == 'Do Not Contact' ? 'selected' : '' }}>Do Not Contact</option>
            </select>
        </div>
        @if(request()->anyFilled(['search', 'category_id', 'status']))
            <div class="col-12 col-md-1 d-flex justify-content-end">
                <a href="{{ route('contacts.index') }}" class="rm-btn rm-btn-ghost py-1 px-3" title="Clear Filters">
                    <i class="bi bi-x-circle me-1"></i> Clear
                </a>
            </div>
        @endif
    </form>
</div>

<!-- Contacts Table & Bulk Actions -->
<form method="POST" action="{{ route('contacts.bulkAction') }}" id="bulk-action-form">
    @csrf
    <div class="d-flex align-items-center justify-content-between mb-3 px-1">
        <div class="d-flex align-items-center gap-2">
            <select name="bulk_action" id="bulk_action_select" class="rm-select" style="width: auto; min-width: 170px;">
                <option value="">Bulk Actions</option>
                <option value="mark_dnc">Mark as Do Not Contact</option>
                <option value="assign_category">Assign to Category...</option>
                <option value="delete">Delete Selected</option>
            </select>
            <select name="bulk_category_id" id="bulk_category_select" class="rm-select d-none" style="width: auto; min-width: 170px;">
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="rm-btn rm-btn-secondary px-4">Apply</button>
        </div>
        <span class="text-muted small">Showing {{ $contacts->firstItem() ?? 0 }}-{{ $contacts->lastItem() ?? 0 }} of {{ $contacts->total() }} contacts</span>
    </div>

    <div class="rm-table-wrapper">
        @if($contacts->isEmpty())
            <div class="rm-empty-state">
                <div class="rm-empty-icon">
                    <i class="bi bi-people"></i>
                </div>
                <h4 class="rm-empty-title">No contacts found</h4>
                <p class="rm-empty-text">Import contacts via CSV/Excel or add a new HR professional manually to get started.</p>
                <div class="d-flex gap-2">
                    <a href="{{ route('contacts.create') }}" class="rm-btn rm-btn-primary">
                        <i class="bi bi-person-plus"></i> Add Contact
                    </a>
                    <a href="{{ route('contacts.import') }}" class="rm-btn rm-btn-secondary">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Import CSV
                    </a>
                </div>
            </div>
        @else
            <div class="table-responsive">
                <table class="rm-table">
                    <thead>
                        <tr>
                            <th width="40"><input type="checkbox" id="select-all-contacts" class="form-check-input"></th>
                            <th>Contact / Company</th>
                            <th>Email Address</th>
                            <th>Categories</th>
                            <th>Status</th>
                            <th>Last Contacted</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contacts as $contact)
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_contacts[]" value="{{ $contact->id }}" class="form-check-input contact-checkbox">
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">
                                        <a href="{{ route('contacts.show', $contact) }}" class="text-dark text-decoration-none">
                                            {{ $contact->display_name }}
                                        </a>
                                    </div>
                                    <div class="text-muted small">
                                        {{ $contact->job_title ?? 'Role not specified' }} • {{ $contact->company_name ?? 'Individual' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="text-dark">{{ $contact->email }}</span>
                                    @if($contact->do_not_contact)
                                        <span class="badge bg-danger-subtle text-danger ms-1 small">DNC</span>
                                    @endif
                                </td>
                                <td>
                                    @forelse($contact->categories as $c)
                                        <span class="badge bg-light text-dark border me-1">{{ $c->name }}</span>
                                    @empty
                                        <span class="text-muted small">—</span>
                                    @endforelse
                                </td>
                                <td>
                                    @if($contact->status === 'Ready to Contact')
                                        <span class="rm-badge" style="background-color: #dbeafe; color: #1e40af;">
                                            <span class="rm-badge-dot"></span> Ready
                                        </span>
                                    @elseif($contact->status === 'Contacted')
                                        <span class="rm-badge" style="background-color: #f1f5f9; color: #475569;">
                                            <span class="rm-badge-dot"></span> Contacted
                                        </span>
                                    @elseif($contact->status === 'Replied')
                                        <span class="rm-badge" style="background-color: #dcfce7; color: #166534;">
                                            <span class="rm-badge-dot"></span> Replied
                                        </span>
                                    @elseif($contact->status === 'Do Not Contact')
                                        <span class="rm-badge" style="background-color: #fee2e2; color: #991b1b;">
                                            <span class="rm-badge-dot"></span> DNC
                                        </span>
                                    @else
                                        <span class="rm-badge" style="background-color: #f1f5f9; color: #64748b;">
                                            {{ $contact->status }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted small">
                                        {{ $contact->last_contacted_at ? $contact->last_contacted_at->format('M d, Y') : 'Never' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('contacts.edit', $contact) }}" class="rm-btn rm-btn-ghost py-1 px-2 text-decoration-none" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('contacts.destroy', $contact) }}" onsubmit="return confirm('Remove contact {{ $contact->display_name }}?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rm-btn rm-btn-ghost py-1 px-2 text-danger" title="Delete">
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

            @if($contacts->hasPages())
                <!-- Pagination -->
                <div class="p-3 border-top">
                    {{ $contacts->links() }}
                </div>
            @endif
        @endif
    </div>
</form>

@push('scripts')
<script>
    // Auto-filter on dropdown selection & debounced search
    const filterForm = document.getElementById('contacts-filter-form');
    const categoryFilter = document.getElementById('category_filter');
    const statusFilter = document.getElementById('status_filter');
    const searchInput = document.getElementById('contacts_search_input');

    if (categoryFilter) {
        categoryFilter.addEventListener('change', () => filterForm.submit());
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', () => filterForm.submit());
    }

    if (searchInput) {
        let timer = null;
        searchInput.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                filterForm.submit();
            }, 550);
        });
    }

    document.getElementById('select-all-contacts')?.addEventListener('change', function(e) {
        document.querySelectorAll('.contact-checkbox').forEach(cb => cb.checked = e.target.checked);
    });

    document.getElementById('bulk_action_select')?.addEventListener('change', function(e) {
        const catSelect = document.getElementById('bulk_category_select');
        if (e.target.value === 'assign_category') {
            catSelect.classList.remove('d-none');
        } else {
            catSelect.classList.add('d-none');
        }
    });
</script>
@endpush
@endsection
