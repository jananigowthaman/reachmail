@extends('layouts.app')

@section('title', 'Resume & File Management')

@section('content')
<div class="rm-header">
    <div>
        <h1 class="rm-page-title">Resume & Files</h1>
        <p class="rm-page-subtitle">Upload your resumes, cover letters, and portfolio documents to automatically attach to job campaigns.</p>
    </div>
    <button type="button" class="rm-btn rm-btn-primary" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
        <i class="bi bi-cloud-arrow-up"></i> Upload Document
    </button>
</div>

<!-- Primary Resumes Section -->
<div class="mb-5">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="rm-card-title fs-5">Resumes</h3>
        <span class="badge bg-light text-dark border">{{ $resumes->count() }} files</span>
    </div>

    <div class="row g-4">
        @forelse($resumes as $resume)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="rm-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-pdf fs-2 text-danger"></i>
                                <div>
                                    <h4 class="fw-semibold text-dark mb-0 fs-6">{{ $resume->display_name }}</h4>
                                    <span class="text-muted small">{{ $resume->formatted_size }} • {{ $resume->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                            @if($resume->is_default_resume)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Default</span>
                            @endif
                        </div>
                        <p class="text-muted small mt-2 mb-3">
                            {{ $resume->description ?: 'Ready to be attached to job applications.' }}
                        </p>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-3 border-top border-light">
                        <a href="{{ route('resumes.download', $resume) }}" class="rm-btn rm-btn-secondary py-1 px-2 text-decoration-none">
                            <i class="bi bi-download"></i> Download
                        </a>

                        <div class="d-flex gap-1 align-items-center">
                            @if(!$resume->is_default_resume)
                                <form method="POST" action="{{ route('resumes.setDefault', $resume) }}">
                                    @csrf
                                    <button type="submit" class="rm-btn rm-btn-ghost py-1 px-2 text-dark small" title="Set as Default">
                                        Make Default
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('resumes.destroy', $resume) }}" onsubmit="return confirm('Delete this resume?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rm-btn rm-btn-ghost py-1 px-2 text-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="rm-card text-center py-4">
                    <p class="text-muted mb-3">No resumes uploaded yet. Upload your primary resume in PDF or DOCX format.</p>
                    <button type="button" class="rm-btn rm-btn-primary" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                        Upload Your Resume
                    </button>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Supporting Documents & Cover Letters -->
<div>
    <h3 class="rm-card-title fs-5 mb-3">Cover Letters & Other Files</h3>
    <div class="row g-4">
        @forelse($coverLetters->merge($others) as $doc)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="rm-card h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-file-earmark-text fs-3 text-primary"></i>
                        <div>
                            <h4 class="fw-semibold text-dark mb-0 fs-6">{{ $doc->display_name }}</h4>
                            <span class="text-muted small">{{ ucfirst(str_replace('_', ' ', $doc->file_type)) }} • {{ $doc->formatted_size }}</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top border-light">
                        <a href="{{ route('resumes.download', $doc) }}" class="rm-btn rm-btn-secondary py-1 px-2 text-decoration-none">
                            <i class="bi bi-download"></i> Download
                        </a>
                        <form method="POST" action="{{ route('resumes.destroy', $doc) }}" onsubmit="return confirm('Delete document?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rm-btn rm-btn-ghost py-1 px-2 text-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-muted small">No secondary cover letters or certificates uploaded.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadFileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('resumes.store') }}" enctype="multipart/form-data" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Upload Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="rm-label">Document Title / Display Name <span class="text-danger">*</span></label>
                    <input type="text" name="display_name" class="rm-input" required placeholder="e.g. Senior Laravel Engineer Resume">
                </div>
                <div class="mb-3">
                    <label class="rm-label">Document Type <span class="text-danger">*</span></label>
                    <select name="file_type" class="rm-select">
                        <option value="resume">Resume / CV</option>
                        <option value="cover_letter">Cover Letter</option>
                        <option value="certificate">Certificate</option>
                        <option value="other">Other Document</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="rm-label">Select File (PDF or DOCX, max 10MB) <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="rm-input" required accept=".pdf, .docx, .doc">
                </div>
                <div class="mb-3">
                    <label class="rm-label">Description (Optional)</label>
                    <textarea name="description" rows="2" class="rm-textarea" placeholder="Notes on when to use this file..."></textarea>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_default_resume" value="1" id="defaultResumeCheck">
                    <label class="form-check-label small" for="defaultResumeCheck">
                        Set as my default resume for job application campaigns
                    </label>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="rm-btn rm-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="rm-btn rm-btn-primary">Upload Document</button>
            </div>
        </form>
    </div>
</div>
@endsection
