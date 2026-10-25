<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="ReachMail - Executive Personal Outreach & Career Campaign Workspace">
    <meta name="author" content="ReachMail">
    <title>@yield('title', 'Dashboard') | ReachMail</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2">
    <link rel="alternate icon" href="{{ asset('favicon.svg') }}?v=2">
    
    <!-- Claude.ai Exact Font Family (Plus Jakarta Sans + Playfair Display) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- ReachMail Luxury Design System -->
    <link href="{{ asset('css/reachmail.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>

    <!-- Toast Notification Container -->
    <div id="rm-toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;">
        @if(session('success'))
            <div class="toast rm-toast-success align-items-center mb-2 show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex align-items-center">
                    <div class="toast-body d-flex align-items-center gap-2 py-2 px-3 fw-medium" style="font-size: 0.88rem;">
                        <i class="bi bi-check2-circle fs-5 text-white"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="btn-close me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="toast rm-toast-error align-items-center mb-2 show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex align-items-center">
                    <div class="toast-body d-flex align-items-center gap-2 py-2 px-3 fw-medium" style="font-size: 0.88rem;">
                        <i class="bi bi-exclamation-octagon fs-5 text-white"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" class="btn-close me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif
    </div>

    <!-- Sidebar Navigation -->
    <aside class="rm-sidebar">
        <a href="{{ route('dashboard') }}" class="rm-brand">
            <div class="rm-brand-badge">
                <i class="bi bi-send-fill" style="font-size: 1.05rem;"></i>
            </div>
            <span>ReachMail</span>
        </a>

        <ul class="rm-nav">
            <li class="rm-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}">
                    <i class="bi bi-grid-1x2"></i>
                    <span>Overview</span>
                </a>
            </li>
            <li class="rm-nav-item {{ request()->routeIs('contacts.*') ? 'active' : '' }}">
                <a href="{{ route('contacts.index') }}">
                    <i class="bi bi-people"></i>
                    <span>Contacts</span>
                </a>
            </li>
            <li class="rm-nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <a href="{{ route('categories.index') }}">
                    <i class="bi bi-tags"></i>
                    <span>Lists & Categories</span>
                </a>
            </li>
            <li class="rm-nav-item {{ request()->routeIs('templates.*') ? 'active' : '' }}">
                <a href="{{ route('templates.index') }}">
                    <i class="bi bi-envelope-paper"></i>
                    <span>Email Templates</span>
                </a>
            </li>
            <li class="rm-nav-item {{ request()->routeIs('campaigns.*') ? 'active' : '' }}">
                <a href="{{ route('campaigns.index') }}">
                    <i class="bi bi-rocket-takeoff"></i>
                    <span>Campaigns</span>
                </a>
            </li>
            <li class="rm-nav-item {{ request()->routeIs('applications.*') ? 'active' : '' }}">
                <a href="{{ route('applications.index') }}">
                    <i class="bi bi-briefcase"></i>
                    <span>Job Applications</span>
                </a>
            </li>
            <li class="rm-nav-item {{ request()->routeIs('resumes.*') ? 'active' : '' }}">
                <a href="{{ route('resumes.index') }}">
                    <i class="bi bi-file-earmark-person"></i>
                    <span>Resume & Files</span>
                </a>
            </li>
            <li class="rm-nav-item {{ request()->routeIs('replies.*') ? 'active' : '' }}">
                <a href="{{ route('replies.index') }}">
                    <i class="bi bi-chat-left-dots"></i>
                    <span>Replies & Inbox</span>
                </a>
            </li>
            <li class="rm-nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <a href="{{ route('settings.index') }}">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>

        <!-- User Profile Card & Sign Out Capsule in Sidebar -->
        <div class="mt-auto pt-3 border-top border-light">
            <div class="d-flex align-items-center justify-content-between px-2 py-2 rounded-4 bg-light bg-opacity-50">
                <div class="overflow-hidden pe-2">
                    <div class="fw-semibold text-dark text-truncate" style="font-size: 0.82rem;">{{ auth()->user()->name ?? 'Owner' }}</div>
                    <div class="text-muted text-truncate" style="font-size: 0.72rem;">{{ auth()->user()->email ?? 'admin@reachmail.local' }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="m-0 flex-shrink-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-link text-muted p-1" title="Sign Out">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="rm-main">
        <!-- Mobile Header Bar -->
        <div class="d-lg-none d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="rm-btn rm-btn-secondary p-2" id="rm-sidebar-toggle">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <span class="fw-bold fs-5">ReachMail</span>
            </div>
            <a href="{{ route('campaigns.create') }}" class="rm-btn rm-btn-primary py-1 px-3">
                <i class="bi bi-plus-lg"></i> Campaign
            </a>
        </div>

        @yield('content')
    </main>

    <!-- Bootstrap 5.3 JavaScript Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/reachmail.js') }}"></script>
    @stack('scripts')
</body>
</html>
