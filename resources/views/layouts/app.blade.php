<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — Lumiora</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --accent:#6366f1; }
        * { font-family:'Inter',sans-serif; }
        body { background:#f1f5f9; margin:0; }

        /* Sidebar */
        #sidebar {
            width:240px; min-height:100vh; background:#0f172a;
            position:fixed; top:0; left:0; z-index:200;
            display:flex; flex-direction:column;
        }
        .sidebar-brand {
            padding:20px; display:flex; align-items:center; gap:10px;
            border-bottom:1px solid rgba(255,255,255,.07);
        }
        .brand-icon {
            width:36px; height:36px; border-radius:10px;
            background:linear-gradient(135deg,#6366f1,#8b5cf6);
            display:flex; align-items:center; justify-content:center;
            color:#fff; font-size:18px;
        }
        .brand-name { color:#fff; font-weight:700; font-size:1.05rem; }
        .brand-sub  { color:rgba(255,255,255,.3); font-size:.65rem; }

        .nav-label {
            font-size:.65rem; font-weight:600; letter-spacing:1.2px;
            text-transform:uppercase; color:rgba(255,255,255,.3);
            padding:16px 20px 5px;
        }
        .s-link {
            display:flex; align-items:center; gap:10px;
            padding:9px 16px; margin:1px 8px; border-radius:8px;
            color:rgba(255,255,255,.6); text-decoration:none;
            font-size:.875rem; font-weight:500; transition:all .15s;
        }
        .s-link:hover { background:rgba(255,255,255,.08); color:#fff; }
        .s-link.active {
            background:rgba(99,102,241,.25); color:#fff;
            border-left:3px solid #6366f1; padding-left:13px;
        }
        .s-link i { width:18px; }
        .s-badge {
            margin-left:auto; background:#ef4444; color:#fff;
            font-size:.65rem; font-weight:700; padding:2px 6px;
            border-radius:99px;
        }
        .sidebar-footer {
            margin-top:auto; padding:10px 8px;
            border-top:1px solid rgba(255,255,255,.07);
        }

        /* Main */
        #main { margin-left:240px; min-height:100vh; display:flex; flex-direction:column; }

        /* Topbar */
        #topbar {
            background:#fff; border-bottom:1px solid #e2e8f0;
            padding:0 24px; height:62px;
            display:flex; align-items:center; justify-content:space-between;
            position:sticky; top:0; z-index:100;
        }
        .topbar-title { font-weight:700; font-size:1rem; color:#0f172a; }
        .topbar-sub   { font-size:.75rem; color:#94a3b8; }
        .user-pill {
            display:flex; align-items:center; gap:8px; padding:6px 12px;
            border-radius:99px; border:1px solid #e2e8f0; background:#f8fafc;
            text-decoration:none; color:#334155; font-size:.85rem; font-weight:500;
        }
        .user-pill:hover { background:#f1f5f9; color:#0f172a; }
        .u-avatar {
            width:28px; height:28px; border-radius:50%;
            background:linear-gradient(135deg,#6366f1,#8b5cf6);
            display:flex; align-items:center; justify-content:center;
            color:#fff; font-size:.75rem; font-weight:700;
        }

        /* Content */
        #content { padding:24px; flex:1; }

        /* Stat cards */
        .stat-card {
            border:none; border-radius:14px; padding:20px;
            transition:transform .2s, box-shadow .2s; cursor:default;
        }
        .stat-card:hover { transform:translateY(-3px); box-shadow:0 12px 28px rgba(0,0,0,.15)!important; }
        .stat-icon {
            width:46px; height:46px; border-radius:12px;
            background:rgba(255,255,255,.2); display:flex;
            align-items:center; justify-content:center;
            font-size:1.3rem; color:#fff;
        }
        .stat-val { font-size:2rem; font-weight:700; color:#fff; line-height:1.1; }
        .stat-lbl { font-size:.8rem; color:rgba(255,255,255,.8); font-weight:500; }
        .stat-sub { font-size:.72rem; color:rgba(255,255,255,.55); margin-top:6px; }
        .g-blue   { background:linear-gradient(135deg,#3b82f6,#1d4ed8); }
        .g-violet { background:linear-gradient(135deg,#8b5cf6,#6d28d9); }
        .g-green  { background:linear-gradient(135deg,#10b981,#059669); }
        .g-red    { background:linear-gradient(135deg,#ef4444,#b91c1c); }
        .g-orange { background:linear-gradient(135deg,#f59e0b,#d97706); }
        .g-teal   { background:linear-gradient(135deg,#14b8a6,#0f766e); }

        /* Table card */
        .t-card { border:none; border-radius:14px; box-shadow:0 1px 4px rgba(0,0,0,.06); }
        .t-card .card-header {
            background:#fff; border-bottom:1px solid #f1f5f9;
            border-radius:14px 14px 0 0!important;
            padding:15px 20px; font-weight:600; font-size:.9rem; color:#0f172a;
        }
        .table thead th {
            background:#f8fafc; color:#475569; font-size:.75rem;
            font-weight:600; text-transform:uppercase; letter-spacing:.5px;
            border:none; padding:10px 16px;
        }
        .table tbody td {
            padding:11px 16px; vertical-align:middle;
            font-size:.875rem; color:#334155; border-color:#f1f5f9;
        }
        .table tbody tr:hover { background:#f8fafc; }

        /* Badges */
        .bs-borrowed  { background:#dbeafe; color:#1d4ed8; }
        .bs-returned  { background:#dcfce7; color:#15803d; }
        .bs-overdue   { background:#fee2e2; color:#b91c1c; }
        .bs-pending   { background:#fef9c3; color:#854d0e; }
        .bs-available { background:#dcfce7; color:#15803d; }
        .bs-cancelled { background:#f1f5f9; color:#64748b; }

        /* Form */
        .form-control,.form-select {
            border-color:#e2e8f0; border-radius:8px;
            font-size:.875rem; padding:9px 12px;
        }
        .form-control:focus,.form-select:focus {
            border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.1);
        }
        .form-label { font-weight:500; font-size:.85rem; color:#374151; margin-bottom:5px; }

        /* Buttons */
        .btn { border-radius:8px; font-weight:500; font-size:.875rem; }
        .btn-primary { background:#6366f1; border-color:#6366f1; }
        .btn-primary:hover { background:#4f46e5; border-color:#4f46e5; }
        .btn-sm { padding:5px 11px; font-size:.8rem; }

        /* Alerts */
        .alert { border:none; border-radius:10px; font-size:.875rem; }
        .alert-success { background:#dcfce7; color:#15803d; }
        .alert-danger  { background:#fee2e2; color:#b91c1c; }

        /* Empty state */
        .empty-state { text-align:center; padding:52px 24px; color:#94a3b8; }
        .empty-state i { font-size:3rem; opacity:.35; display:block; margin-bottom:12px; }

        /* Search box */
        .srch-wrap { position:relative; }
        .srch-wrap .bi { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#94a3b8; }
        .srch-wrap input { padding-left:32px; }

        /* Print */
        @media print {
            #sidebar,#topbar,.no-print { display:none!important; }
            #main { margin-left:0!important; }
        }

        @media(max-width:768px) {
            #sidebar { transform:translateX(-100%); }
            #main { margin-left:0; }
        }
    </style>
</head>
<body>

<aside id="sidebar">
    <div class="sidebar-brand">
        <img src="/images/logo-only.png" alt="Lumiora Logo" style="width: 40px; height: 40px;">
        <div>
            <div class="brand-name"><img src="/images/lumiora-only.png" alt="Lumiora Logo" style="width: 110px; height: 25px;"></div>
            <!-- <div class="brand-sub">Library System</div> -->
        </div>
    </div>

    <nav class="flex-fill" style="overflow-y:auto">
        <div class="nav-label">Main</div>
        <a href="{{ route('dashboard') }}" class="s-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="nav-label">Catalog</div>
        <a href="{{ route('books.index') }}" class="s-link {{ request()->routeIs('books.*') ? 'active' : '' }}">
            <i class="bi bi-book"></i> Books
        </a>
        <a href="{{ route('categories.index') }}" class="s-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i> Categories
        </a>

        <div class="nav-label">Circulation</div>
        <a href="{{ route('members.index') }}" class="s-link {{ request()->routeIs('members.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Members
        </a>
        <a href="{{ route('borrowings.index') }}" class="s-link {{ request()->routeIs('borrowings.*') ? 'active' : '' }}">
            <i class="bi bi-arrow-left-right"></i> Borrowings
            @php $overdueCount = \App\Models\Borrowing::where('status','overdue')->count(); @endphp
            @if($overdueCount > 0)
                <span class="s-badge">{{ $overdueCount }}</span>
            @endif
        </a>
        <a href="{{ route('reservations.index') }}" class="s-link {{ request()->routeIs('reservations.*') ? 'active' : '' }}">
            <i class="bi bi-bookmark"></i> Reservations
        </a>

        <div class="nav-label">Reports</div>
        <a href="{{ route('reports.index') }}" class="s-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart"></i> Reports
        </a>
    </nav>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="s-link w-100 text-start border-0" style="background:none;cursor:pointer">
                <i class="bi bi-box-arrow-left"></i> Logout
            </button>
        </form>
    </div>
</aside>

<div id="main">
    <header id="topbar">
        <div>
            <div class="topbar-title">@yield('title','Dashboard')</div>
            <div class="topbar-sub">@yield('subtitle','Lumiora Management System')</div>
        </div>
        <div class="d-flex align-items-center gap-3">
            {{-- Global search --}}
            <div class="srch-wrap d-none d-md-block" style="position:relative">
                <i class="bi bi-search" style="font-size:.85rem"></i>
                <input id="gSearch" type="text" class="form-control form-control-sm"
                       placeholder="Search books or members..." style="width:230px" autocomplete="off">
                <div id="gResults" style="display:none;position:absolute;top:calc(100% + 6px);left:0;
                     width:320px;background:#fff;border:1px solid #e2e8f0;border-radius:12px;
                     box-shadow:0 8px 24px rgba(0,0,0,.1);z-index:999;max-height:320px;overflow-y:auto"></div>
            </div>

            <div class="dropdown">
                <a class="user-pill dropdown-toggle" data-bs-toggle="dropdown">
                    <div class="u-avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
                    {{ auth()->user()->name }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius:12px;font-size:.85rem;min-width:200px">
                    <li class="px-3 py-2">
                        <div style="font-weight:600;font-size:.85rem">{{ auth()->user()->name }}</div>
                        <div class="text-muted" style="font-size:.75rem">{{ auth()->user()->email }}</div>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item text-danger py-2">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <main id="content">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-check-circle-fill flex-shrink-0"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Global live search
const gSearch  = document.getElementById('gSearch');
const gResults = document.getElementById('gResults');
let st;
if (gSearch) {
    gSearch.addEventListener('input', function () {
        clearTimeout(st);
        const q = this.value.trim();
        if (q.length < 2) { gResults.style.display = 'none'; return; }
        st = setTimeout(() => {
            fetch(`/search?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.length) {
                        gResults.innerHTML = '<div class="p-3 text-center text-muted small">No results found.</div>';
                    } else {
                        gResults.innerHTML = data.map(i => `
                            <a href="${i.url}" class="d-flex align-items-center gap-3 p-3 text-decoration-none"
                               style="border-bottom:1px solid #f1f5f9;color:#334155;transition:background .1s"
                               onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                                <span class="badge" style="background:#ede9fe;color:#6d28d9;font-size:.7rem;white-space:nowrap">${i.type}</span>
                                <div>
                                    <div style="font-weight:500;font-size:.875rem">${i.title}</div>
                                    <div style="font-size:.75rem;color:#94a3b8">${i.subtitle}</div>
                                </div>
                            </a>`).join('');
                    }
                    gResults.style.display = 'block';
                });
        }, 280);
    });
    document.addEventListener('click', e => { if (!gSearch.contains(e.target)) gResults.style.display = 'none'; });
}
</script>
@yield('scripts')
</body>
</html>
