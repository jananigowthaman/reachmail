<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sign in to ReachMail - Personal Career Outreach & Campaign Management Platform">
    <meta name="author" content="ReachMail">
    <title>Sign In | ReachMail</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2">
    <link rel="alternate icon" href="{{ asset('favicon.svg') }}?v=2">

    <!-- Claude.ai Font Family (Plus Jakarta Sans + Playfair Display) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- ReachMail Luxury Design System -->
    <link href="{{ asset('css/reachmail.css') }}" rel="stylesheet">

    <style>
        body, html {
            height: 100%;
            background-color: #faf9f5;
        }
        .luxury-hero-panel {
            background: linear-gradient(to bottom, rgba(20, 18, 16, 0.40) 0%, rgba(20, 18, 16, 0.60) 100%), url('{{ asset('images/login-bg.jpg') }}?v=3') center center / cover no-repeat;
            position: relative;
            overflow: hidden;
            border-radius: 28px;
            margin: 1.25rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3.5rem;
            color: #ffffff;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
        }
        .hero-pattern-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: radial-gradient(rgba(217, 119, 87, 0.15) 1px, transparent 1px);
            background-size: 32px 32px;
            opacity: 0.65;
            pointer-events: none;
        }
        .hero-glow {
            position: absolute;
            width: 450px;
            height: 450px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(217, 119, 87, 0.22) 0%, rgba(217, 119, 87, 0) 70%);
            top: -100px;
            right: -100px;
            filter: blur(40px);
            pointer-events: none;
        }
        .login-card-wrapper {
            max-width: 420px;
            width: 100%;
            margin: auto;
            padding: 2rem 1rem;
        }
        .login-card-wrapper .rm-input {
            border-radius: 9999px !important;
            padding-left: 1.45rem !important;
            padding-right: 1.45rem !important;
        }
        .login-card-wrapper .position-relative .rm-input {
            padding-right: 3rem !important;
        }
        .editorial-quote {
            font-family: var(--rm-font-serif);
            font-size: 1.85rem;
            font-weight: 500;
            line-height: 1.35;
            letter-spacing: -0.01em;
            color: #f5f3ec;
        }
        .luxury-mockup-badge {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 18px;
            padding: 1.25rem 1.5rem;
            max-width: 440px;
        }
    </style>
</head>
<body>

<div class="container-fluid h-100 p-0">
    <div class="row g-0 h-100">
        <!-- Left Luxury Showcase Banner (Editorial Presentation) -->
        <div class="col-12 col-lg-7 d-none d-lg-flex flex-column">
            <div class="luxury-hero-panel flex-grow-1">
                <div class="hero-pattern-overlay"></div>
                <div class="hero-glow"></div>

                <div class="position-relative z-1">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="rm-brand-badge" style="width: 42px; height: 42px;">
                            <i class="bi bi-send-fill fs-5"></i>
                        </div>
                        <span class="fs-4 fw-bold tracking-tight text-white">ReachMail</span>
                    </div>

                    <div class="editorial-quote my-4">
                        “Thoughtful, deliberate career outreach delivered directly to the right decision makers.”
                    </div>
                    <p class="text-white-50" style="max-width: 520px; font-size: 1.05rem; line-height: 1.6;">
                        Manage targeted HR contacts, compose bespoke email templates with instant live previews, and monitor real replies from one unified private workspace.
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Login Panel -->
        <div class="col-12 col-lg-5 d-flex align-items-center justify-content-center p-4">
            <div class="login-card-wrapper">
                <div class="text-center mb-4">
                    <div class="d-inline-flex d-lg-none rm-brand-badge mb-3" style="width: 48px; height: 48px;">
                        <i class="bi bi-send-fill fs-4"></i>
                    </div>
                    <h2 class="fw-bold tracking-tight mb-2" style="font-size: 1.85rem;">Sign In to ReachMail</h2>
                    <p class="text-muted small">Access your personal outreach workspace & campaign suite.</p>
                </div>

                @if(session('success'))
                    <div id="login-alert-success" class="alert alert-success border-0 rounded-4 py-2 px-3 small d-flex align-items-center gap-2 mb-4" style="transition: opacity 0.5s ease;">
                        <i class="bi bi-check2-circle fs-5"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger border-0 rounded-4 py-2 px-3 small d-flex align-items-center gap-2 mb-4">
                        <i class="bi bi-exclamation-triangle fs-5"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="rm-label" for="email">Email Address</label>
                        <input type="email" name="email" id="email" class="rm-input @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus placeholder="name@company.com">
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="rm-label mb-0" for="password">Password</label>
                        </div>
                        <div class="position-relative">
                            <input type="password" name="password" id="password" class="rm-input pe-5" required placeholder="Enter password">
                            <button type="button" id="togglePasswordBtn" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3" style="z-index: 5;" aria-label="Toggle password visibility">
                                <i class="bi bi-eye" id="togglePasswordIcon" style="font-size: 1.15rem;"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                            <label class="form-check-label text-muted small" for="remember">Keep me logged in</label>
                        </div>
                    </div>

                    <button type="submit" class="rm-btn rm-btn-primary w-100 py-3 fs-6">
                        Sign In to ReachMail <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

    <script>
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        if (toggleBtn && passwordInput && toggleIcon) {
            toggleBtn.addEventListener('click', () => {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                toggleIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        }

        // Auto-dismiss signed out alert after exactly 10 seconds
        const successAlert = document.getElementById('login-alert-success');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.opacity = '0';
                setTimeout(() => successAlert.remove(), 500);
            }, 10000);
        }
    </script>
</body>
</html>
