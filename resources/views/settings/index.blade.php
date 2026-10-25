@extends('layouts.app')

@section('title', 'Application Settings | ReachMail')

@section('content')
<div class="rm-header">
    <div>
        <h1 class="rm-page-title">Settings</h1>
        <p class="rm-page-subtitle">Configure your personal sender profile, Gmail OAuth integration, and sending boundaries.</p>
    </div>
</div>

<!-- Settings Navigation Pill Tabs -->
<div class="rm-card mb-4 p-2">
    <div class="d-flex align-items-center gap-2" role="tablist">
        <button type="button" class="rm-btn rm-btn-primary py-1 px-4 active" id="tab-sender-btn" data-bs-toggle="tab" data-bs-target="#tab-sender" role="tab" aria-controls="tab-sender" aria-selected="true">
            <i class="bi bi-person-gear"></i> Sender Profile &amp; Provider
        </button>
        <button type="button" class="rm-btn rm-btn-secondary py-1 px-4" id="tab-oauth-btn" data-bs-toggle="tab" data-bs-target="#tab-oauth" role="tab" aria-controls="tab-oauth" aria-selected="false">
            <i class="bi bi-shield-lock"></i> Google OAuth &amp; Setup Guide
        </button>
    </div>
</div>

<div class="tab-content" id="settingsTabContent">
    <!-- TAB 1: Sender Profile & Email Provider -->
    <div class="tab-pane fade show active" id="tab-sender" role="tabpanel" aria-labelledby="tab-sender-btn">
        <div class="row g-4">
            <!-- Left: Sender Profile -->
            <div class="col-12 col-lg-7">
                <div class="rm-card h-100">
                    <h3 class="rm-card-title mb-3">Sender Profile</h3>
                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="rm-label">Your Name <span class="text-danger">*</span></label>
                                <input type="text" name="sender_name" class="rm-input" required value="{{ $settings['sender_name'] }}" placeholder="e.g. Janani Gowthaman">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="rm-label">Sender Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="sender_email" class="rm-input" required value="{{ $settings['sender_email'] }}" placeholder="name@gmail.com">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="rm-label">Default Reply-To</label>
                                <input type="email" name="default_reply_to" class="rm-input" value="{{ $settings['default_reply_to'] }}" placeholder="Optional reply email">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="rm-label">Daily Sending Limit</label>
                                <input type="number" name="daily_sending_limit" class="rm-input" required value="{{ $settings['daily_sending_limit'] }}" min="10" max="500">
                            </div>
                            <div class="col-12">
                                <label class="rm-label">Default Email Signature</label>
                                <textarea name="signature" rows="4" class="rm-textarea">{{ $settings['signature'] }}</textarea>
                            </div>
                            <div class="col-12 d-flex justify-content-end pt-3 border-top">
                                <button type="submit" class="rm-btn rm-btn-primary">Save Sender Preferences</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right: Email Provider Status -->
            <div class="col-12 col-lg-5">
                <div class="rm-card h-100">
                    <div class="rm-card-header pb-2 border-bottom">
                        <h3 class="rm-card-title">Email Provider</h3>
                        @if($emailAccount && $emailAccount->connection_status === 'connected')
                            <span class="rm-badge" style="background-color: #dcfce7; color: #15803d;">
                                <span class="rm-badge-dot"></span> Connected
                            </span>
                        @endif
                    </div>

                    @if($emailAccount && $emailAccount->connection_status === 'connected')
                        <div class="py-3">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <i class="bi bi-google fs-2 text-danger"></i>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $emailAccount->email_address }}</div>
                                    <div class="text-muted small">Connected via Gmail API OAuth 2.0</div>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('settings.oauth.disconnect') }}" onsubmit="return confirm('Disconnect this Gmail account?');">
                                @csrf
                                <button type="submit" class="rm-btn rm-btn-danger py-1 px-3">
                                    Disconnect Account
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="py-3">
                            <p class="text-muted small mb-3">
                                Connect your Gmail account via secure OAuth 2.0 to send emails through your real address and sync incoming replies automatically.
                            </p>
                            <a href="{{ route('settings.oauth.connect') }}" class="rm-btn rm-btn-secondary w-100 justify-content-center">
                                <i class="bi bi-google text-danger"></i> Connect Gmail Account
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: Google OAuth Credentials (Left) & Step-by-Step Setup Guide (Right) -->
    <div class="tab-pane fade" id="tab-oauth" role="tabpanel" aria-labelledby="tab-oauth-btn">
        <div class="row g-4">
            <!-- Left: Google OAuth Form -->
            <div class="col-12 col-lg-6">
                <div class="rm-card h-100">
                    <h3 class="rm-card-title mb-2">Google OAuth Credentials</h3>
                    <p class="text-muted small mb-4">
                        Save your Google Cloud Console credentials directly here without modifying the <code>.env</code> file.
                    </p>

                    <form method="POST" action="{{ route('settings.google.update') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="rm-label">Google Client ID <span class="text-danger">*</span></label>
                            <input type="text" name="google_client_id" class="rm-input" required value="{{ $settings['google_client_id'] }}" placeholder="xxxxxx-xxxxxx.apps.googleusercontent.com">
                        </div>

                        <div class="mb-3">
                            <label class="rm-label">Google Client Secret <span class="text-danger">*</span></label>
                            <input type="password" name="google_client_secret" class="rm-input" required value="{{ $settings['google_client_secret'] }}" placeholder="GOCSPX-xxxxxx">
                        </div>

                        <div class="mb-4">
                            <label class="rm-label mb-2 d-block">Authorized Redirect URI</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="text" id="redirect_uri_val" class="rm-input bg-light flex-grow-1" readonly value="{{ $settings['google_redirect_uri'] }}">
                                <button type="button" class="rm-btn rm-btn-secondary px-3 flex-shrink-0" onclick="navigator.clipboard.writeText(document.getElementById('redirect_uri_val').value); ReachMail.toast('Redirect URI copied to clipboard!');">
                                    <i class="bi bi-clipboard"></i> Copy
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2" style="line-height: 1.5;">
                                Paste this exact URI into your Google Cloud Console under <strong>Authorized redirect URIs</strong>.
                            </small>
                        </div>

                        <div class="d-flex justify-content-end pt-3 border-top">
                            <button type="submit" class="rm-btn rm-btn-primary">Save Google Credentials</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right: Step-by-Step Setup Guide -->
            <div class="col-12 col-lg-6">
                <div class="rm-card h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h3 class="rm-card-title fs-6 mb-0">Step-by-Step Setup Guide</h3>
                        <span class="badge bg-light text-muted border small">5 Minutes</span>
                    </div>

                    <div class="d-flex flex-column gap-3 text-muted small" style="line-height: 1.6;">
                        <div class="d-flex gap-3 align-items-start">
                            <span class="badge bg-dark rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 24px; height: 24px;">1</span>
                            <div>
                                Go to <a href="https://console.cloud.google.com/apis/dashboard" target="_blank" class="fw-semibold text-dark text-decoration-none">Google Cloud Console <i class="bi bi-box-arrow-up-right small"></i></a> and create a project named <strong>ReachMail</strong>.
                            </div>
                        </div>

                        <div class="d-flex gap-3 align-items-start">
                            <span class="badge bg-dark rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 24px; height: 24px;">2</span>
                            <div>
                                Navigate to <strong>APIs &amp; Services &gt; Library</strong>. Search for <strong>Gmail API</strong> and click <strong>Enable</strong>.
                            </div>
                        </div>

                        <div class="d-flex gap-3 align-items-start">
                            <span class="badge bg-dark rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 24px; height: 24px;">3</span>
                            <div>
                                Open <strong>OAuth consent screen</strong>. Select <strong>External</strong>, set your email, and under <em>Test Users</em> add your email (<code>{{ $settings['sender_email'] }}</code>).
                            </div>
                        </div>

                        <div class="d-flex gap-3 align-items-start">
                            <span class="badge bg-dark rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 24px; height: 24px;">4</span>
                            <div>
                                Go to <strong>Credentials &gt; Create Credentials &gt; OAuth client ID</strong>. Select <em>Application type: Web application</em>.
                            </div>
                        </div>

                        <div class="d-flex gap-3 align-items-start">
                            <span class="badge bg-dark rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 24px; height: 24px;">5</span>
                            <div>
                                Under <strong>Authorized redirect URIs</strong>, paste the Redirect URI copied from the left panel.
                            </div>
                        </div>

                        <div class="d-flex gap-3 align-items-start">
                            <span class="badge bg-dark rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 24px; height: 24px;">6</span>
                            <div>
                                Copy the <strong>Client ID</strong> and <strong>Client Secret</strong> into the form on the left, save them, then switch to the first tab and click <strong>Connect Gmail Account</strong>.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Tab active pill toggle
    const senderTabBtn = document.getElementById('tab-sender-btn');
    const oauthTabBtn = document.getElementById('tab-oauth-btn');

    if (senderTabBtn && oauthTabBtn) {
        senderTabBtn.addEventListener('shown.bs.tab', () => {
            senderTabBtn.classList.add('rm-btn-primary');
            senderTabBtn.classList.remove('rm-btn-secondary');
            oauthTabBtn.classList.add('rm-btn-secondary');
            oauthTabBtn.classList.remove('rm-btn-primary');
        });

        oauthTabBtn.addEventListener('shown.bs.tab', () => {
            oauthTabBtn.classList.add('rm-btn-primary');
            oauthTabBtn.classList.remove('rm-btn-secondary');
            senderTabBtn.classList.add('rm-btn-secondary');
            senderTabBtn.classList.remove('rm-btn-primary');
        });
    }
</script>
@endpush
@endsection
