@extends('layouts.app')

@section('title', 'Edit Template - ' . $template->name)

@section('content')
<div class="rm-header">
    <div>
        <h1 class="rm-page-title">Edit Template</h1>
        <p class="rm-page-subtitle">Update email copy, variables, and default attachments.</p>
    </div>
    <a href="{{ route('templates.index') }}" class="rm-btn rm-btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Templates
    </a>
</div>

<form method="POST" action="{{ route('templates.update', $template) }}">
    @csrf
    @method('PUT')
    <div class="row g-4">
        <!-- Left Panel: Editor -->
        <div class="col-12 col-lg-7">
            <div class="rm-card">
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-8">
                        <label class="rm-label">Template Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="rm-input" required value="{{ old('name', $template->name) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="rm-label">Category</label>
                        <select name="category" class="rm-select">
                            @foreach(['Job Application', 'HR Enquiry', 'Recruiter Introduction', 'Follow-up', 'Interview Follow-up', 'General'] as $cat)
                                <option value="{{ $cat }}" {{ $template->category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="rm-label">Subject Line <span class="text-danger">*</span></label>
                        <input type="text" name="subject" id="template_subject" class="rm-input" required value="{{ old('subject', $template->subject) }}">
                    </div>
                </div>

                <!-- Personalisation Pill Variables -->
                <div class="mb-3">
                    <label class="rm-label d-block">Insert Personalisation Placeholder</label>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach(['first_name', 'last_name', 'full_name', 'company_name', 'job_title', 'city', 'my_name', 'my_email', 'company_website'] as $v)
                            <button type="button" class="rm-btn rm-btn-secondary py-1 px-3 small tag-pill" data-var="{{ '{'.'{\'' . $v . '\'}'.'}' }}">
                                + &#123;&#123;{{ $v }}&#125;&#125;
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Email Body HTML Editor -->
                <div class="mb-3">
                    <label class="rm-label">Email Body (HTML) <span class="text-danger">*</span></label>
                    <textarea name="body_html" id="template_body" rows="12" class="rm-textarea font-monospace" required>{{ old('body_html', $template->body_html) }}</textarea>
                </div>

                <!-- Default Attachment Option -->
                <div class="row g-3">
                    <div class="col-12 col-md-8">
                        <label class="rm-label">Default Attachment</label>
                        <select name="default_attachment_id" class="rm-select">
                            <option value="">No default attachment</option>
                            @foreach($attachments as $att)
                                <option value="{{ $att->id }}" {{ $template->default_attachment_id == $att->id ? 'selected' : '' }}>
                                    {{ $att->display_name }} ({{ $att->formatted_size }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_favorite" value="1" id="favCheck" {{ $template->is_favorite ? 'checked' : '' }}>
                            <label class="form-check-label small" for="favCheck">Mark as favourite</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-4 border-top mt-4">
                    <a href="{{ route('templates.index') }}" class="rm-btn rm-btn-secondary">Cancel</a>
                    <button type="submit" class="rm-btn rm-btn-primary">Update Template</button>
                </div>
            </div>
        </div>

        <!-- Right Panel: Live Preview -->
        <div class="col-12 col-lg-5">
            <div class="rm-card sticky-top" style="top: 2rem;">
                <div class="rm-card-header pb-2 border-bottom">
                    <h3 class="rm-card-title fs-6">Live Recipient Preview</h3>
                    <span class="badge bg-light text-muted border small">Sample View</span>
                </div>

                <div class="py-2 border-bottom">
                    <div class="small text-muted mb-1">To: <span class="text-dark fw-medium">Sarah Jenkins &lt;s.jenkins@acme.corp&gt;</span></div>
                    <div class="small text-muted">Subject: <span class="text-dark fw-semibold" id="preview_subject">Subject</span></div>
                </div>

                <div class="mt-3 p-3 bg-light rounded" id="preview_content" style="min-height: 250px; font-size: 0.9rem; line-height: 1.6; white-space: pre-line;">
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    const subjectInput = document.getElementById('template_subject');
    const bodyInput = document.getElementById('template_body');
    const previewSubject = document.getElementById('preview_subject');
    const previewContent = document.getElementById('preview_content');

    const sampleData = {
        'first_name': 'Sarah',
        'last_name': 'Jenkins',
        'full_name': 'Sarah Jenkins',
        'company_name': 'Acme Corp',
        'job_title': 'Software Engineer',
        'city': 'Chennai',
        'my_name': 'Alex Rivera',
        'my_email': 'alex@example.com',
        'company_website': 'https://acme.corp'
    };

    function updatePreview() {
        let subj = subjectInput.value || '';
        let body = bodyInput.value || '';

        for (const [k, v] of Object.entries(sampleData)) {
            subj = subj.split('{{' + k + '}}').join(v);
            body = body.split('{{' + k + '}}').join(v);
        }

        previewSubject.textContent = subj;
        previewContent.innerHTML = body;
    }

    subjectInput.addEventListener('input', updatePreview);
    bodyInput.addEventListener('input', updatePreview);

    document.querySelectorAll('.tag-pill').forEach(btn => {
        btn.addEventListener('click', () => {
            const val = btn.getAttribute('data-var');
            const start = bodyInput.selectionStart;
            const end = bodyInput.selectionEnd;
            bodyInput.value = bodyInput.value.substring(0, start) + val + bodyInput.value.substring(end);
            bodyInput.focus();
            bodyInput.selectionStart = bodyInput.selectionEnd = start + val.length;
            updatePreview();
        });
    });

    updatePreview();
</script>
@endpush
@endsection
