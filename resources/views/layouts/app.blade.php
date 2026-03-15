<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Nhà Thuốc GPP</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --topbar-height: 60px;
            --primary: #1B4F72;
            --primary-light: #2471A3;
            --sidebar-bg: #1a2332;
            --sidebar-hover: #2d3f55;
            --sidebar-active: #2471A3;
            --sidebar-text: #c8d6e5;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            margin: 0;
        }

        /* ── SIDEBAR ───────────────────────────────────────────────────── */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
            transition: width .25s ease;
        }

        .sidebar-logo {
            padding: 18px 20px;
            border-bottom: 1px solid #2d3f55;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .sidebar-logo .logo-icon {
            width: 38px;
            height: 38px;
            background: var(--sidebar-active);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .sidebar-logo .logo-text {
            color: #fff;
            font-weight: 700;
            font-size: 15px;
            line-height: 1.2;
        }

        .sidebar-logo .logo-sub {
            color: var(--sidebar-text);
            font-size: 11px;
        }

        .sidebar-section {
            padding: 20px 16px 6px;
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: 1px;
            color: #556a7e;
            text-transform: uppercase;
        }

        .sidebar .nav-item {
            padding: 2px 10px;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            color: var(--sidebar-text);
            font-size: 14px;
            text-decoration: none;
            transition: all .15s;
            white-space: nowrap;
        }

        .sidebar .nav-link i {
            font-size: 17px;
            flex-shrink: 0;
            width: 22px;
            text-align: center;
        }

        .sidebar .nav-link:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }

        .sidebar .nav-link.active {
            background: var(--sidebar-active);
            color: #fff;
            font-weight: 600;
        }

        /* Badge số trên menu */
        .sidebar .nav-link .badge-count {
            margin-left: auto;
            background: #e74c3c;
            color: #fff;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
        }

        /* ── TOPBAR ────────────────────────────────────────────────────── */
        .topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid #e0e6ed;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 999;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
        }

        .topbar .page-title {
            font-size: 17px;
            font-weight: 600;
            color: #1a2332;
        }

        .topbar .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .topbar .btn-topbar {
            background: none;
            border: none;
            color: #556a7e;
            font-size: 20px;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 8px;
            transition: background .15s;
        }

        .topbar .btn-topbar:hover {
            background: #f0f2f5;
            color: #1a2332;
        }

        .topbar .user-menu .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #1a2332;
        }

        .topbar .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--sidebar-active);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
        }

        .topbar .user-name {
            font-size: 14px;
            font-weight: 500;
        }

        .topbar .user-role {
            font-size: 11px;
            color: #888;
        }

        /* ── MAIN CONTENT ──────────────────────────────────────────────── */
        .main-content {
            margin-left: var(--sidebar-width);
            padding-top: var(--topbar-height);
            min-height: 100vh;
        }

        .content-area {
            padding: 24px;
        }

        /* ── CARDS ─────────────────────────────────────────────────────── */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 8px rgba(0, 0, 0, .07);
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #f0f2f5;
            border-radius: 12px 12px 0 0 !important;
        }

        /* Stat cards */
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px 22px;
            box-shadow: 0 1px 8px rgba(0, 0, 0, .07);
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .stat-card .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .stat-card .stat-value {
            font-size: 22px;
            font-weight: 700;
            color: #1a2332;
            line-height: 1.2;
        }

        .stat-card .stat-label {
            font-size: 12.5px;
            color: #888;
            margin-top: 2px;
        }

        /* ── TABLES ─────────────────────────────────────────────────────── */
        .table {
            font-size: 13.5px;
        }

        .table thead th {
            background: #f8fafc;
            color: #556a7e;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 2px solid #e0e6ed;
            padding: 10px 14px;
        }

        .table tbody td {
            padding: 10px 14px;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        /* ── MISC ───────────────────────────────────────────────────────── */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .page-header h4 {
            margin: 0;
            font-weight: 700;
            color: #1a2332;
        }

        .badge {
            font-size: 11.5px;
            font-weight: 500;
            padding: 4px 9px;
        }

        /* Scrollbar sidebar */
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #2d3f55;
            border-radius: 4px;
        }

        /* Alert flash */
        .alert-flash {
            position: fixed;
            top: 70px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 420px;
            animation: slideIn .3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Format tiền tệ */
        .money {
            font-weight: 600;
            color: #1B4F72;
        }

        .money-negative {
            font-weight: 600;
            color: #e74c3c;
        }
    </style>

    @stack('styles')
</head>

<body>

    {{-- ── SIDEBAR ──────────────────────────────────────────────────────────── --}}
    <nav class="sidebar">
        {{-- Logo --}}
        <a href="{{ route('dashboard') }}" class="sidebar-logo">
            <div class="logo-icon">💊</div>
            <div>
                <div class="logo-text">Nhà Thuốc GPP</div>
                <div class="logo-sub">{{ auth()->user()->pharmacy->name ?? '' }}</div>
            </div>
        </a>

        {{-- Menu chính --}}
        <div class="sidebar-section">Tổng quan</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                    href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
        </ul>

        @if(auth()->user()->hasPermission('medicine.view'))
            <div class="sidebar-section">Danh mục</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('medicines.*') ? 'active' : '' }}"
                        href="{{ route('medicines.index') }}">
                        <i class="bi bi-capsule"></i> Danh mục thuốc
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"
                        href="{{ route('categories.index') }}">
                        <i class="bi bi-tags"></i> Nhóm thuốc
                    </a>
                </li>
                @if(auth()->user()->hasPermission('inventory.view'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}"
                            href="{{ route('inventory.index') }}">
                            <i class="bi bi-boxes"></i> Tồn kho
                            @if(($sidebarBadges['expiring'] ?? 0) + ($sidebarBadges['expired_with_stock'] ?? 0) > 0)
                                <span class="badge bg-danger ms-auto badge-count">
                                    {{ ($sidebarBadges['expiring'] ?? 0) + ($sidebarBadges['expired_with_stock'] ?? 0) }}
                                </span>
                            @elseif(($sidebarBadges['low_stock'] ?? 0) > 0)
                                <span class="badge bg-warning text-dark ms-auto badge-count">
                                    {{ $sidebarBadges['low_stock'] }}
                                </span>
                            @endif
                        </a>
                    </li>
                @endif
            </ul>
        @endif

        @if(auth()->user()->hasPermission('purchase.view'))
            <div class="sidebar-section">Nhập hàng</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('purchase.*') ? 'active' : '' }}"
                        href="{{ route('purchase.index') }}">
                        <i class="bi bi-truck"></i> Đơn nhập hàng
                        @if(($sidebarBadges['pending_orders'] ?? 0) > 0)
                            <span class="badge bg-primary ms-auto badge-count">
                                {{ $sidebarBadges['pending_orders'] }}
                            </span>
                        @endif
                    </a>
                </li>
                @if(auth()->user()->hasPermission('supplier.view'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"
                            href="{{ route('suppliers.index') }}">
                            <i class="bi bi-building"></i> Nhà cung cấp
                        </a>
                    </li>
                @endif
            </ul>
        @endif

        @if(auth()->user()->hasPermission('invoice.view'))
            <div class="sidebar-section">Bán hàng</div>
            <ul class="nav flex-column">
                @if(auth()->user()->hasPermission('invoice.create'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('invoices.pos') ? 'active' : '' }}"
                            href="{{ route('invoices.pos') }}">
                            <i class="bi bi-cart3"></i> Bán hàng (POS)
                        </a>
                    </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('invoices.index') ? 'active' : '' }}"
                        href="{{ route('invoices.index') }}">
                        <i class="bi bi-receipt"></i> Hóa đơn
                    </a>
                </li>
                @if(auth()->user()->hasPermission('customer.view'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}"
                            href="{{ route('customers.index') }}">
                            <i class="bi bi-people"></i> Khách hàng
                        </a>
                    </li>
                @endif
            </ul>
        @endif

        @if(auth()->user()->hasPermission('cash.view'))
            <div class="sidebar-section">Tài chính</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('cash.*') ? 'active' : '' }}" href="{{ route('cash.index') }}">
                        <i class="bi bi-cash-stack"></i> Sổ quỹ
                    </a>
                </li>
            </ul>
        @endif

        @if(auth()->user()->hasPermission('report.view'))
            <div class="sidebar-section">Báo cáo</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('reports.revenue') ? 'active' : '' }}"
                        href="{{ route('reports.revenue') }}">
                        <i class="bi bi-graph-up-arrow"></i> Doanh thu
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('reports.inventory') ? 'active' : '' }}"
                        href="{{ route('reports.inventory') }}">
                        <i class="bi bi-clipboard-data"></i> Báo cáo NXT
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('reports.debt') ? 'active' : '' }}"
                        href="{{ route('reports.debt') }}">
                        <i class="bi bi-credit-card"></i> Công nợ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('reports.ledger') ? 'active' : '' }}"
                        href="{{ route('reports.ledger') }}">
                        <i class="bi bi-journal-text"></i> Sổ xuất nhập tồn
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('reports.debt.detail') ? 'active' : '' }}"
                        href="{{ route('reports.debt.detail') }}">
                        <i class="bi bi-journal-bookmark"></i> Công nợ chi tiết
                    </a>
                </li>
            </ul>
        @endif

        {{-- Trả hàng --}}
        @if(auth()->user()->hasPermission('invoice.view'))
            <div class="sidebar-section">Trả hàng</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('returns.*') ? 'active' : '' }}"
                        href="{{ route('returns.index') }}">
                        <i class="bi bi-arrow-return-left"></i> Phiếu trả hàng
                    </a>
                </li>
            </ul>
        @endif

        @if(auth()->user()->hasPermission('user.view'))
            <div class="sidebar-section">Hệ thống</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('activity.*') ? 'active' : '' }}"
                        href="{{ route('activity.index') }}">
                        <i class="bi bi-clock-history"></i> Lịch sử hoạt động
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                        href="{{ route('users.index') }}">
                        <i class="bi bi-people-fill"></i> Nhân viên
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('shifts.index', 'shifts.manage') ? 'active' : '' }}"
                        href="{{ route('shifts.index') }}">
                        <i class="bi bi-calendar-week"></i> Lịch phân ca
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('shifts.attendance') ? 'active' : '' }}"
                        href="{{ route('shifts.attendance') }}">
                        <i class="bi bi-person-check"></i> Chấm công
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('shifts.payroll') ? 'active' : '' }}"
                        href="{{ route('shifts.payroll') }}">
                        <i class="bi bi-cash-stack"></i> Bảng lương
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('shifts.my-schedule') ? 'active' : '' }}"
                        href="{{ route('shifts.my-schedule') }}">
                        <i class="bi bi-calendar-heart"></i> Lịch của tôi
                    </a>
                </li>
            </ul>
        @endif

        {{-- CHẤM CÔNG PIN (ai cũng có thể dùng - không cần quyền user.view) --}}
        <div class="sidebar-section">Nhân viên</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('checkin.*') ? 'active' : '' }}"
                    href="{{ route('checkin.index') }}">
                    <i class="bi bi-qr-code-scan"></i> Chấm công PIN
                </a>
            </li>
        </ul>

        {{-- Logout --}}
        <div style="padding: 16px 10px 24px;">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent" style="color:#e74c3c;">
                    <i class="bi bi-box-arrow-left"></i> Đăng xuất
                </button>
            </form>
        </div>
    </nav>

    {{-- ── TOPBAR ────────────────────────────────────────────────────────────── --}}
    <div class="topbar">
        <div class="page-title">@yield('page-title', 'Dashboard')</div>

        {{-- ── GLOBAL SEARCH ────────────────────────────────────────── --}}
        <div class="position-relative mx-3" style="flex:1;max-width:360px;">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0" style="border-radius:8px 0 0 8px;">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" id="globalSearchInput" class="form-control bg-light border-start-0"
                    placeholder="Tìm thuốc, hóa đơn, khách hàng..." autocomplete="off"
                    style="border-radius:0 8px 8px 0;">
            </div>
            <div id="globalSearchResults" class="position-absolute bg-white border rounded-3 shadow-lg w-100 mt-1"
                style="z-index:9999;display:none;max-height:400px;overflow-y:auto;top:100%;">
            </div>
        </div>

        <div class="topbar-right">
            {{-- Nút tạo hóa đơn nhanh --}}
            @if(auth()->user()->hasPermission('invoice.create'))
                <a href="{{ route('invoices.pos') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                    <i class="bi bi-plus-lg"></i> Bán hàng
                </a>
            @endif

            {{-- Cảnh báo — dùng $sidebarBadges từ SidebarBadgesComposer (cache 5 phút) --}}
            <div class="dropdown">
                <button class="btn-topbar position-relative" data-bs-toggle="dropdown" title="Cảnh báo hệ thống">
                    <i
                        class="bi bi-bell{{ ($sidebarBadges['total_alerts'] ?? 0) > 0 ? '-fill text-warning' : '' }}"></i>
                    @if(($sidebarBadges['total_alerts'] ?? 0) > 0)
                        <span class="position-absolute badge rounded-pill bg-danger"
                            style="font-size:9px;padding:2px 5px;top:2px;right:2px;">
                            {{ $sidebarBadges['total_alerts'] > 99 ? '99+' : $sidebarBadges['total_alerts'] }}
                        </span>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width:320px;">
                    <li>
                        <h6 class="dropdown-header d-flex justify-content-between">
                            <span>🔔 Cảnh báo hệ thống</span>
                            @if(($sidebarBadges['total_alerts'] ?? 0) == 0)
                                <span class="badge bg-success">Ổn định</span>
                            @else
                                <span class="badge bg-danger">{{ $sidebarBadges['total_alerts'] }} mục</span>
                            @endif
                        </h6>
                    </li>
                    <li>
                        <hr class="dropdown-divider my-1">
                    </li>

                    @if(($sidebarBadges['expired_with_stock'] ?? 0) > 0)
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2"
                                href="{{ route('inventory.expiring') }}">
                                <span class="badge bg-danger" style="min-width:28px;">
                                    {{ $sidebarBadges['expired_with_stock'] }}
                                </span>
                                <div>
                                    <div class="fw-semibold small text-danger">Lô đã hết hạn còn tồn!</div>
                                    <div style="font-size:11px;" class="text-muted">Cần xử lý ngay — xuất hủy hoặc trả NCC
                                    </div>
                                </div>
                            </a>
                        </li>
                    @endif

                    @if(($sidebarBadges['expiring'] ?? 0) > 0)
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2"
                                href="{{ route('inventory.expiring') }}">
                                <span class="badge bg-warning text-dark" style="min-width:28px;">
                                    {{ $sidebarBadges['expiring'] }}
                                </span>
                                <div>
                                    <div class="fw-semibold small">Lô sắp hết hạn (≤30 ngày)</div>
                                    <div style="font-size:11px;" class="text-muted">Ưu tiên bán trước theo FEFO</div>
                                </div>
                            </a>
                        </li>
                    @endif

                    @if(($sidebarBadges['low_stock'] ?? 0) > 0)
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2"
                                href="{{ route('inventory.low-stock') }}">
                                <span class="badge bg-warning text-dark" style="min-width:28px;">
                                    {{ $sidebarBadges['low_stock'] }}
                                </span>
                                <div>
                                    <div class="fw-semibold small">Thuốc dưới mức tối thiểu</div>
                                    <div style="font-size:11px;" class="text-muted">Cần lên đơn nhập hàng</div>
                                </div>
                            </a>
                        </li>
                    @endif

                    @if(($sidebarBadges['pending_orders'] ?? 0) > 0)
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2"
                                href="{{ route('purchase.index', ['status' => 'pending']) }}">
                                <span class="badge bg-primary" style="min-width:28px;">
                                    {{ $sidebarBadges['pending_orders'] }}
                                </span>
                                <div>
                                    <div class="fw-semibold small">Đơn nhập chờ duyệt</div>
                                    <div style="font-size:11px;" class="text-muted">Cần phê duyệt trước khi nhận hàng</div>
                                </div>
                            </a>
                        </li>
                    @endif

                    @if(($sidebarBadges['over_debt_customers'] ?? 0) > 0)
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2"
                                href="{{ route('reports.debt', ['type' => 'customer']) }}">
                                <span class="badge bg-danger" style="min-width:28px;">
                                    {{ $sidebarBadges['over_debt_customers'] }}
                                </span>
                                <div>
                                    <div class="fw-semibold small">Khách vượt hạn mức nợ</div>
                                    <div style="font-size:11px;" class="text-muted">Cần thu hồi công nợ</div>
                                </div>
                            </a>
                        </li>
                    @endif

                    @if(($sidebarBadges['total_alerts'] ?? 0) == 0)
                        <li>
                            <span class="dropdown-item text-center text-muted py-3" style="font-size:13px;">
                                ✅ Không có cảnh báo nào
                            </span>
                        </li>
                    @endif

                    <li>
                        <hr class="dropdown-divider my-1">
                    </li>
                    <li>
                        <div class="dropdown-item d-flex justify-content-between align-items-center py-2">
                            <small class="text-muted">
                                HĐ hôm nay: <strong>{{ $sidebarBadges['today_invoices'] ?? 0 }}</strong>
                            </small>
                            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-primary py-0 px-2"
                                style="font-size:11px;">Dashboard</a>
                        </div>
                    </li>
                </ul>
            </div>

            {{-- User menu --}}
            <div class="user-menu dropdown">
                <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                    <div class="user-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="d-none d-md-block">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role">{{ auth()->user()->primary_role_name }}</div>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <h6 class="dropdown-header">{{ auth()->user()->email }}</h6>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.show') }}">
                            <i class="bi bi-person-circle me-2"></i>Hồ sơ cá nhân
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.show') }}#password">
                            <i class="bi bi-shield-lock me-2"></i>Đổi mật khẩu
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-left me-2"></i>Đăng xuất
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- ── MAIN ──────────────────────────────────────────────────────────────── --}}
    <div class="main-content">
        <div class="content-area">

            {{-- Flash messages --}}
            {{-- Flash alerts — được inject bởi AppServiceProvider View Composer --}}
            @if(!empty($flashAlerts))
                <div class="mb-3">
                    @foreach($flashAlerts as $alert)
                        <div class="alert alert-{{ $alert['type'] }} alert-dismissible alert-flash d-flex align-items-center gap-2"
                            role="alert" style="border-radius:10px;">
                            <span>{{ $alert['icon'] }}</span>
                            <span>{{ $alert['message'] }}</span>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Fallback: vẫn hỗ trợ session trực tiếp trong quá trình chuyển đổi --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible alert-flash d-flex align-items-center gap-2" role="alert">
                        <span>✅</span><span>{{ session('success') }}</span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible alert-flash d-flex align-items-center gap-2" role="alert">
                        <span>❌</span><span>{{ session('error') }}</span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible alert-flash d-flex align-items-center gap-2" role="alert">
                        <span>⚠️</span><span>{{ session('warning') }}</span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            @endif

            @yield('content')
        </div>
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        // Auto-dismiss flash messages sau 4 giây
        document.querySelectorAll('.alert-flash').forEach(el => {
            setTimeout(() => {
                const alert = bootstrap.Alert.getOrCreateInstance(el);
                alert.close();
            }, 4000);
        });

        // Format tiền tệ helper (dùng trong JS)
        function formatMoney(amount) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency', currency: 'VND'
            }).format(amount);
        }
    </script>

    @stack('scripts')

    <script>
        // ── Global Search ────────────────────────────────────────────────────
        (function () {
            const input = document.getElementById('globalSearchInput');
            const results = document.getElementById('globalSearchResults');
            if (!input) return;

            let timer = null;

            input.addEventListener('input', function () {
                clearTimeout(timer);
                const q = this.value.trim();
                if (q.length < 2) { results.style.display = 'none'; return; }
                timer = setTimeout(() => fetchResults(q), 300);
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') { results.style.display = 'none'; this.blur(); }
            });

            document.addEventListener('click', function (e) {
                if (!input.contains(e.target) && !results.contains(e.target)) {
                    results.style.display = 'none';
                }
            });

            async function fetchResults(q) {
                try {
                    const res = await fetch(`/api/search?q=${encodeURIComponent(q)}`);
                    const data = await res.json();
                    renderResults(data.results, q);
                } catch (e) {
                    console.error('Search error:', e);
                }
            }

            const typeColors = {
                medicine: '#0d6efd', invoice: '#198754', purchase: '#fd7e14',
                customer: '#6f42c1', batch: '#dc3545'
            };
            const typeLabels = {
                medicine: 'Thuốc', invoice: 'Hóa đơn', purchase: 'Đơn nhập',
                customer: 'Khách hàng', batch: 'Lô hàng'
            };

            function renderResults(items, q) {
                if (!items.length) {
                    results.innerHTML = '<div class="p-3 text-muted text-center small">Không tìm thấy kết quả cho "' + q + '"</div>';
                    results.style.display = 'block';
                    return;
                }

                // Group by type
                const groups = {};
                items.forEach(item => {
                    if (!groups[item.type]) groups[item.type] = [];
                    groups[item.type].push(item);
                });

                let html = '';
                for (const [type, groupItems] of Object.entries(groups)) {
                    html += `<div class="px-3 py-1" style="background:#f8f9fa;border-bottom:1px solid #eee;">
                <small class="text-muted fw-semibold" style="font-size:10px;letter-spacing:1px;text-transform:uppercase;">
                    ${groupItems[0].icon} ${typeLabels[type] || type}
                </small>
            </div>`;
                    groupItems.forEach(item => {
                        html += `<a href="${item.url}" class="d-flex align-items-start gap-3 px-3 py-2 text-decoration-none text-dark search-result-item"
                           style="border-bottom:1px solid #f5f5f5;">
                    <div class="flex-fill min-w-0">
                        <div class="fw-semibold small text-truncate">${highlight(item.title, q)}</div>
                        <div class="text-muted" style="font-size:11px;">${item.sub}</div>
                    </div>
                    ${item.badge ? `<span class="badge bg-light text-dark border ms-auto flex-shrink-0">${item.badge}</span>` : ''}
                </a>`;
                    });
                }
                html += `<div class="p-2 text-center" style="border-top:1px solid #eee;">
            <small class="text-muted">${items.length} kết quả</small>
        </div>`;

                results.innerHTML = html;
                results.style.display = 'block';

                // Hover style
                results.querySelectorAll('.search-result-item').forEach(el => {
                    el.addEventListener('mouseenter', () => el.style.background = '#f0f7ff');
                    el.addEventListener('mouseleave', () => el.style.background = '');
                });
            }

            function highlight(text, q) {
                if (!q) return text;
                const re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                return text.replace(re, '<mark style="background:#fff3cd;padding:0 2px;border-radius:2px;">$1</mark>');
            }
        })();
    </script>
</body>

</html>