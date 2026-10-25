@extends('layouts.app')

@section('title', 'Edit Contact | ReachMail')

@section('content')
<div class="rm-header">
    <div>
        <h1 class="rm-page-title">Edit Contact</h1>
        <p class="rm-page-subtitle">Update details for {{ $contact->display_name }}.</p>
    </div>
    <a href="{{ route('contacts.index') }}" class="rm-btn rm-btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Contacts
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="rm-card">
            <form method="POST" action="{{ route('contacts.update', $contact) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="rm-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="rm-input @error('email') is-invalid @enderror" value="{{ old('email', $contact->email) }}" required placeholder="e.g. hr@company.com">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="rm-label">Full Name</label>
                        <input type="text" name="full_name" class="rm-input" value="{{ old('full_name', $contact->full_name) }}" placeholder="e.g. Sarah Jenkins">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="rm-label">Company Name</label>
                        <input type="text" name="company_name" class="rm-input" value="{{ old('company_name', $contact->company_name) }}" placeholder="e.g. Acme Technologies">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="rm-label">Job Title / Designation</label>
                        <input type="text" name="job_title" class="rm-input" value="{{ old('job_title', $contact->job_title) }}" placeholder="e.g. Technical Recruiter">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="rm-label">Company Website</label>
                        <input type="url" name="company_website" class="rm-input" value="{{ old('company_website', $contact->company_website) }}" placeholder="https://company.com">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="rm-label">LinkedIn Profile URL</label>
                        <input type="url" name="linkedin_url" class="rm-input" value="{{ old('linkedin_url', $contact->linkedin_url) }}" placeholder="https://linkedin.com/in/username">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="rm-label">City / Location</label>
                        <input type="text" name="city" class="rm-input" value="{{ old('city', $contact->city) }}" placeholder="e.g. Chennai">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="rm-label">Contact Status</label>
                        <select name="status" class="rm-select">
                            @foreach(['Ready to Contact', 'New', 'Contacted', 'Replied', 'Follow-up Due', 'Not Interested', 'Do Not Contact'] as $st)
                                <option value="{{ $st }}" {{ old('status', $contact->status) === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="rm-label">Assign to Categories</label>
                        <div class="d-flex flex-wrap gap-2 pt-1">
                            @foreach($categories as $category)
                                <label class="border rounded px-3 py-1 d-flex align-items-center gap-2 bg-light small cursor-pointer">
                                    <input type="checkbox" name="categories[]" value="{{ $category->id }}" class="form-check-input mt-0" {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }}>
                                    <span>{{ $category->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="rm-label">Internal Notes</label>
                        <textarea name="notes" rows="3" class="rm-textarea" placeholder="Notes regarding hiring requirements, context, or meeting dates...">{{ old('notes', $contact->notes) }}</textarea>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="do_not_contact" value="1" id="dncCheck" {{ $contact->do_not_contact ? 'checked' : '' }}>
                            <label class="form-check-label text-danger" for="dncCheck">
                                Mark as Do Not Contact (Suppress from all outbound campaigns)
                            </label>
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('contacts.index') }}" class="rm-btn rm-btn-secondary">Cancel</a>
                        <button type="submit" class="rm-btn rm-btn-primary">Update Contact</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
