<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'نظام إدارة المياه') - GEZR</title>

    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #1e2a3a;
            --sidebar-color: #c8d6e5;
            --sidebar-active: #3498db;
            --sidebar-hover: rgba(255,255,255,0.07);
            --topbar-height: 60px;
        }

        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            min-height: 100vh;
        }

        /* Sidebar */
        #sidebar {
            position: fixed;
            top: 0;
            right: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            color: var(--sidebar-color);
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        #sidebar .sidebar-brand {
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            padding: 0 1.25rem;
            background: rgba(0,0,0,0.2);
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        #sidebar .sidebar-brand i { font-size: 1.4rem; margin-left: 0.5rem; color: var(--sidebar-active); }

        #sidebar .nav-section {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255,255,255,0.3);
            padding: 1rem 1.25rem 0.3rem;
        }

        #sidebar .nav-link {
            color: var(--sidebar-color);
            padding: 0.6rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            border-radius: 0;
            transition: background 0.2s, color 0.2s;
            text-decoration: none;
            font-size: 0.9rem;
        }

        #sidebar .nav-link:hover { background: var(--sidebar-hover); color: #fff; }

        #sidebar .nav-link.active {
            background: var(--sidebar-active);
            color: #fff;
        }

        #sidebar .nav-link i { width: 20px; text-align: center; font-size: 1rem; }

        /* Topbar */
        #topbar {
            position: fixed;
            top: 0;
            right: var(--sidebar-width);
            left: 0;
            height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid #e0e4ea;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            z-index: 999;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }

        #topbar .page-title {
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
            flex: 1;
        }

        #topbar .user-menu .dropdown-toggle {
            background: none;
            border: none;
            color: #2c3e50;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* Main */
        #main {
            margin-right: var(--sidebar-width);
            padding-top: var(--topbar-height);
            min-height: 100vh;
        }

        .page-content { padding: 1.5rem; }

        /* Cards */
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-card .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .stat-card .stat-value { font-size: 1.6rem; font-weight: 700; color: #1e2a3a; line-height: 1; }
        .stat-card .stat-label { font-size: 0.8rem; color: #7f8c9a; margin-top: 0.2rem; }

        /* Table */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #f0f2f5;
            border-radius: 12px 12px 0 0 !important;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-header h5 { margin: 0; font-size: 0.95rem; font-weight: 600; color: #1e2a3a; }

        .table th {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #7f8c9a;
            font-weight: 600;
            background: #f8f9fb;
            border-bottom: 2px solid #e8ecf0;
        }

        .table td { vertical-align: middle; font-size: 0.875rem; }

        /* Badges */
        .badge-active   { background: #e8f5e9; color: #2e7d32; }
        .badge-inactive { background: #fce4ec; color: #c62828; }

        /* Buttons */
        .btn-sm { font-size: 0.78rem; }

        /* Alerts */
        .flash-container {
            position: fixed;
            top: calc(var(--topbar-height) + 1rem);
            left: 1rem;
            z-index: 9999;
            max-width: 380px;
        }

        /* Mobile */
        @media (max-width: 768px) {
            #sidebar { transform: translateX(100%); }
            #sidebar.show { transform: translateX(0); }
            #topbar { right: 0; }
            #main { margin-right: 0; }
        }
    </style>
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css">
    <style>
        .select2-container { width: 100% !important; }
        .select2-container--bootstrap-5 .select2-selection { font-size: 0.875rem; }
    </style>
    @stack('styles')
</head>
<body>

{{-- Sidebar --}}
<nav id="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <i class="bi bi-droplet-fill"></i>
        GEZR
    </a>

    <div class="py-2">
        <div class="nav-section">الرئيسية</div>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i> لوحة التحكم
        </a>
        <a href="{{ route('rapport.statistiques') }}" class="nav-link {{ request()->routeIs('rapport.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i> الإحصائيات
        </a>

        <div class="nav-section">الإدارة</div>
        <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> الفلاحين
        </a>
        <a href="{{ route('vannes.index') }}" class="nav-link {{ request()->routeIs('vannes.*') ? 'active' : '' }}">
            <i class="bi bi-tools"></i> الصنابير
        </a>

        <div class="nav-section">المعاملات</div>
        <a href="{{ route('paiements.index') }}" class="nav-link {{ request()->routeIs('paiements.*') ? 'active' : '' }}">
            <i class="bi bi-cash-coin"></i> مداخيل
        </a>
        <a href="{{ route('consommation.index') }}" class="nav-link {{ request()->routeIs('consommation.*') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> الاستهلاك
        </a>
        <a href="{{ route('planning.create') }}" class="nav-link {{ request()->routeIs('planning.*') ? 'active' : '' }}">
            <i class="bi bi-calendar3"></i> تخطيط
        </a>
        <a href="{{ route('activations.index') }}" class="nav-link {{ request()->routeIs('activations.*') ? 'active' : '' }}">
            <i class="bi bi-toggle-on"></i> التفعيلات
        </a>
        <a href="{{ route('nouvelle-activation.create') }}" class="nav-link {{ request()->routeIs('nouvelle-activation.*') ? 'active' : '' }}">
            <i class="bi bi-plus-circle"></i> تفعيل جديد
        </a>

        <div class="nav-section">الإعدادات</div>
        <a href="{{ route('prix-m3.index') }}" class="nav-link {{ request()->routeIs('prix-m3.*') ? 'active' : '' }}">
            <i class="bi bi-currency-dollar"></i> أسعار م³
        </a>
        <a href="{{ route('params.edit') }}" class="nav-link {{ request()->routeIs('params.*') ? 'active' : '' }}">
            <i class="bi bi-sliders"></i> المعاملات
        </a>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="bi bi-person-gear"></i> المستخدمون
        </a>
        @endif
    </div>
</nav>

{{-- Topbar --}}
<header id="topbar">
    <button class="btn btn-sm btn-light d-md-none me-2" onclick="document.getElementById('sidebar').classList.toggle('show')">
        <i class="bi bi-list"></i>
    </button>
    <span class="page-title">@yield('page-title', 'لوحة التحكم')</span>
    <div class="user-menu">
        <div class="dropdown">
            <button class="dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle"></i>
                {{ auth()->user()->username }}
            </button>
            <ul class="dropdown-menu dropdown-menu-start">
                <li><h6 class="dropdown-header">{{ auth()->user()->username }}</h6></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-1"></i> تسجيل الخروج
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

{{-- Flash messages --}}
<div class="flash-container">
    @foreach(['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $class)
        @if(session($key))
            <div class="alert alert-{{ $class }} alert-dismissible fade show shadow-sm" role="alert">
                {{ session($key) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach
</div>

{{-- Main content --}}
<main id="main">
    <div class="page-content">
        @yield('content')
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(function () {
        $('select').not('.no-select2').select2({
            theme: 'bootstrap-5',
            dir: 'rtl',
            width: '100%',
        });
    });
</script>
<script>
    // Auto-dismiss flash after 4s
    setTimeout(() => {
        document.querySelectorAll('.flash-container .alert').forEach(el => {
            bootstrap.Alert.getOrCreateInstance(el).close();
        });
    }, 4000);
</script>
@stack('scripts')
</body>
</html>
