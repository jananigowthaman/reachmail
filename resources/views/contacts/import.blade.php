@extends('layouts.app')

@section('title', 'Import Contacts Wizard')

@section('content')
<div class="rm-header">
    <div>
        <h1 class="rm-page-title">Contact Import Wizard</h1>
        <p class="rm-page-subtitle">Upload your CSV or Excel spreadsheets, map columns, and safely import contacts.</p>
    </div>
    <a href="{{ route('contacts.index') }}" class="rm-btn rm-btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Contacts
    </a>
</div>

@php
    $step = session('step', 1);
    $headers = session('import_headers', []);
    $previewRows = session('import_preview_rows', []);
    $totalRows = session('import_total_rows', 0);
    $fileName = session('import_original_filename', '');
@endphp

<!-- Wizard Stepper Indicator -->
<div class="rm-card mb-4 p-3">
    <div class="d-flex justify-content-between align-items-center position-relative">
        <div class="d-flex align-items-center gap-2">
            <span class="badge {{ $step >= 1 ? 'bg-dark' : 'bg-light text-muted border' }} rounded-circle p-2 px-3">1</span>
            <span class="fw-semibold {{ $step >= 1 ? 'text-dark' : 'text-muted' }}">Upload File</span>
        </div>
        <div class="flex-grow-1 border-top mx-3 border-light"></div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge {{ $step >= 2 ? 'bg-dark' : 'bg-light text-muted border' }} rounded-circle p-2 px-3">2</span>
            <span class="fw-semibold {{ $step >= 2 ? 'text-dark' : 'text-muted' }}">Map Columns & Configure</span>
        </div>
    </div>
</div>

@if($step == 1)
    <!-- Step 1: Upload File -->
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="rm-card text-center p-5">
                <form method="POST" action="{{ route('contacts.import.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <div class="rm-empty-icon mx-auto" style="width: 72px; height: 72px; font-size: 2rem;">
                            <i class="bi bi-cloud-arrow-up"></i>
                        </div>
                        <h4 class="fw-bold mb-2">Upload CSV or XLSX File</h4>
                        <p class="text-muted small max-w-md mx-auto">Select an exported spreadsheet with company names, emails, and job designations. Maximum file size is 10MB.</p>
                    </div>

                    <div class="mb-4 max-w-md mx-auto">
                        <input type="file" name="spreadsheet" class="form-control rm-input p-3" required accept=".csv, .xlsx, .xls, text/csv">
                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        <button type="submit" class="rm-btn rm-btn-primary px-4 py-2">
                            Upload and Inspect Columns <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@else
    <!-- Step 2: Preview & Column Mapping -->
    <div class="row g-4">
        <div class="col-12">
            <div class="rm-card">
                <div class="rm-card-header">
                    <div>
                        <h3 class="rm-card-title">File Preview: {{ $fileName }}</h3>
                        <p class="text-muted small mb-0">{{ $totalRows }} total records detected.</p>
                    </div>
                </div>

                <!-- Preview Table -->
                <div class="table-responsive mb-4 border rounded">
                    <table class="table table-sm table-striped mb-0" style="font-size: 0.82rem;">
                        <thead class="table-light">
                            <tr>
                                @foreach($headers as $h)
                                    <th>{{ $h }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($previewRows as $row)
                                <tr>
                                    @foreach($headers as $idx => $h)
                                        <td>{{ $row[$idx] ?? '—' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <form method="POST" action="{{ route('contacts.import.process') }}">
                    @csrf
                    <h4 class="fs-6 fw-bold mb-3 border-bottom pb-2">Column Mapping</h4>
                    <div class="row g-3 mb-4">
                        @foreach($headers as $idx => $header)
                            @php
                                $hLower = strtolower(trim($header));
                                $autoMap = '';
                                if (str_contains($hLower, 'email')) $autoMap = 'email';
                                elseif (str_contains($hLower, 'name') || str_contains($hLower, 'person')) $autoMap = 'full_name';
                                elseif (str_contains($hLower, 'company') || str_contains($hLower, 'organization')) $autoMap = 'company_name';
                                elseif (str_contains($hLower, 'designation') || str_contains($hLower, 'role') || str_contains($hLower, 'title')) $autoMap = 'job_title';
                                elseif (str_contains($hLower, 'website') || str_contains($hLower, 'url')) $autoMap = 'company_website';
                                elseif (str_contains($hLower, 'city') || str_contains($hLower, 'location')) $autoMap = 'city';
                            @endphp
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="rm-label text-truncate d-block" title="{{ $header }}">
                                    Column: <strong>"{{ $header }}"</strong>
                                </label>
                                <select name="mapping[{{ $idx }}]" class="rm-select">
                                    <option value="">-- Ignore column --</option>
                                    <option value="email" {{ $autoMap === 'email' ? 'selected' : '' }}>Email Address (Required)</option>
                                    <option value="full_name" {{ $autoMap === 'full_name' ? 'selected' : '' }}>Full Name</option>
                                    <option value="first_name">First Name</option>
                                    <option value="last_name">Last Name</option>
                                    <option value="company_name" {{ $autoMap === 'company_name' ? 'selected' : '' }}>Company Name</option>
                                    <option value="job_title" {{ $autoMap === 'job_title' ? 'selected' : '' }}>Job Title / Designation</option>
                                    <option value="company_website" {{ $autoMap === 'company_website' ? 'selected' : '' }}>Company Website</option>
                                    <option value="city" {{ $autoMap === 'city' ? 'selected' : '' }}>City / Location</option>
                                </select>
                            </div>
                        @endforeach
                    </div>

                    <h4 class="fs-6 fw-bold mb-3 border-bottom pb-2">Import Settings</h4>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <label class="rm-label">Target Category</label>
                            <select name="target_category_id" class="rm-select">
                                <option value="">None (Don't assign)</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="rm-label">Duplicate Handling</label>
                            <select name="duplicate_mode" class="rm-select">
                                <option value="skip">Skip duplicates (Keep existing records)</option>
                                <option value="update">Update existing records with new data</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="rm-label">Default Contact Status</label>
                            <select name="default_status" class="rm-select">
                                <option value="Ready to Contact">Ready to Contact</option>
                                <option value="New">New</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ route('contacts.import') }}" class="rm-btn rm-btn-secondary">Start Over</a>
                        <button type="submit" class="rm-btn rm-btn-primary px-4">
                            Run Safe Import <i class="bi bi-check2-circle ms-1"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
