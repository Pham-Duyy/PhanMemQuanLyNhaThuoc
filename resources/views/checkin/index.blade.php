<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chấm công — Nhà thuốc</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --teal: #0EA5A0;
            --teal-dark: #0B5E5A;
            --bg: #F0F4F8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: var(--bg);
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
        }

        /* TOPBAR */
        .topbar {
            background: linear-gradient(135deg, var(--teal-dark), var(--teal));
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 12px rgba(11, 94, 90, .3);
        }

        .topbar .brand {
            color: #fff;
            font-weight: 800;
            font-size: 18px;
        }

        .topbar .clock {
            color: #fff;
            font-size: 28px;
            font-weight: 900;
            font-variant-numeric: tabular-nums;
            letter-spacing: 1px;
        }

        .topbar .date {
            color: rgba(255, 255, 255, .8);
            font-size: 13px;
            text-align: right;
        }

        /* MAIN LAYOUT */
        .main {
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 20px;
            padding: 20px 24px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* STAFF LIST */
        .staff-grid {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .staff-card {
            background: #fff;
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .07);
            cursor: default;
            transition: all .15s;
            border: 2px solid transparent;
            opacity: 0.8;
        }

        .staff-card:hover {
            border-color: transparent;
            transform: none;
        }

        .staff-card.active {
            border-color: var(--teal);
            background: #F0FDF9;
        }

        .staff-card.checked-in {
            border-color: #16A34A;
            background: #F0FDF9;
        }

        .staff-card.checked-out {
            border-color: #94A3B8;
            background: #F8FAFC;
            opacity: .7;
        }

        .staff-card.no-pin {
            border-color: #FDE68A;
            background: #FFFBEB;
        }

        .staff-avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 17px;
            color: #fff;
            background: var(--teal);
            flex-shrink: 0;
        }

        .staff-name {
            font-weight: 700;
            font-size: 15px;
            color: #1E293B;
        }

        .staff-meta {
            font-size: 12px;
            color: #64748B;
            margin-top: 2px;
        }

        .staff-status {
            margin-left: auto;
            text-align: right;
            flex-shrink: 0;
        }

        .shift-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        /* PIN PAD */
        .pin-panel {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .1);
            overflow: hidden;
            position: sticky;
            top: 20px;
        }

        .pin-header {
            background: linear-gradient(135deg, var(--teal-dark), var(--teal));
            padding: 20px;
            color: #fff;
            text-align: center;
        }

        .pin-header .selected-name {
            font-size: 20px;
            font-weight: 800;
            margin-top: 8px;
        }

        .pin-header .selected-shift {
            font-size: 13px;
            opacity: .85;
            margin-top: 4px;
        }

        .pin-body {
            padding: 20px;
        }

        /* PIN DISPLAY */
        .pin-display {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 20px;
        }

        .pin-dot {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            border: 2px solid #E2E8F0;
            background: #F8FAFC;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 900;
            color: var(--teal);
            transition: all .15s;
        }

        .pin-dot.filled {
            background: var(--teal);
            border-color: var(--teal);
            color: #fff;
        }

        /* NUMPAD */
        .numpad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .num-btn {
            padding: 16px;
            border: none;
            border-radius: 12px;
            background: #F1F5F9;
            font-size: 20px;
            font-weight: 700;
            color: #1E293B;
            cursor: pointer;
            transition: all .12s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }

        .num-btn:hover {
            background: #E2E8F0;
            transform: scale(.97);
        }

        .num-btn:active {
            background: var(--teal);
            color: #fff;
            transform: scale(.93);
        }

        .num-btn .num-sub {
            font-size: 9px;
            color: #94A3B8;
            font-weight: 500;
            letter-spacing: .5px;
        }

        .num-btn.del-btn {
            background: #FEE2E2;
            color: #EF4444;
        }

        .num-btn.del-btn:hover {
            background: #FECACA;
        }

        .num-btn.ok-btn {
            background: #DCFCE7;
            color: #16A34A;
            font-size: 14px;
        }

        .num-btn.ok-btn:hover {
            background: #BBF7D0;
        }

        /* MODE TOGGLE */
        .mode-toggle {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .mode-btn {
            flex: 1;
            padding: 10px;
            border-radius: 10px;
            border: 2px solid #E2E8F0;
            background: #F8FAFC;
            font-size: 13px;
            font-weight: 700;
            color: #64748B;
            cursor: pointer;
            transition: all .15s;
            text-align: center;
        }

        .mode-btn.active-in {
            border-color: #16A34A;
            background: #F0FDF4;
            color: #16A34A;
        }

        .mode-btn.active-out {
            border-color: #EF4444;
            background: #FEF2F2;
            color: #EF4444;
        }

        /* TOAST */
        .toast-wrap {
            position: fixed;
            bottom: 28px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            min-width: 340px;
        }

        .toast-msg {
            padding: 14px 20px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 14px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, .15);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideUp .25s ease;
        }

        .toast-success {
            background: #fff;
            border-left: 5px solid #16A34A;
            color: #1E293B;
        }

        .toast-error {
            background: #fff;
            border-left: 5px solid #EF4444;
            color: #1E293B;
        }

        .toast-late {
            background: #fff;
            border-left: 5px solid #F59E0B;
            color: #1E293B;
        }

        .toast-info {
            background: #fff;
            border-left: 5px solid #0EA5A0;
            color: #1E293B;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* LIVE LIST */
        .live-list {
            max-height: 340px;
            overflow-y: auto;
        }

        .live-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-bottom: 1px solid #F1F5F9;
        }

        .live-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 800;
            color: #fff;
            background: #16A34A;
            flex-shrink: 0;
        }

        @media(max-width:768px) {
            .main {
                grid-template-columns: 1fr;
            }

            .pin-panel {
                position: static;
            }
        }
    </style>
</head>

<body>

    <div class="topbar">
        <div>
            <div class="brand">🏥 Chấm công nhà thuốc</div>
            <div style="color:rgba(255,255,255,.75);font-size:12px;margin-top:2px;">
                {{ now()->format('l, d/m/Y') }}
            </div>
        </div>
        <div class="text-end">
            <div class="clock" id="liveClock">{{ now()->format('H:i:s') }}</div>
            <div class="date">{{ now()->format('d/m/Y') }}</div>
        </div>
        @auth
            <a href="{{ url('/dashboard') }}" class="btn btn-sm ms-4"
                style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.4);">
                <i class="bi bi-grid me-1"></i>Dashboard
            </a>
        @endauth
    </div>

    <div class="main">

        {{-- ── Cột trái: Danh sách nhân viên (read-only) ────────────────────────── --}}
        <div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-people-fill me-2 text-primary"></i>
                    Danh sách nhân viên hôm nay
                    <span class="badge bg-primary ms-1">{{ $todayStaff->count() }}</span>
                </h6>
                <a href="{{ route('checkin.history') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-clock-history me-1"></i>Lịch sử
                </a>
            </div>

            <div class="staff-grid">
                @forelse($todayStaff as $item)
                    @php
                        $log = $item['log'];
                        $status = $log ? $log->status : 'none';
                        $cardCls = match ($status) {
                            'checked_in' => 'checked-in',
                            'checked_out' => 'checked-out',
                            default => ($item['has_pin'] ? '' : 'no-pin'),
                        };
                    @endphp
                    <div class="staff-card {{ $cardCls }}" id="card-{{ $item['user']->id }}"
                        data-uid="{{ $item['user']->id }}" data-name="{{ e($item['user']->name) }}"
                        data-shift="{{ e($item['shift']?->name ?? 'Ca hôm nay') }}"
                        data-color="{{ $item['shift']?->color ?? '#0EA5A0' }}"
                        data-time="{{ $item['shift'] ? substr($item['shift']->start_time, 0, 5) . ' - ' . substr($item['shift']->end_time, 0, 5) : '' }}"
                        data-haspin="{{ $item['has_pin'] ? '1' : '0' }}">

                        <div class="staff-avatar" style="background:{{ $item['shift']?->color ?? '#64748B' }};">
                            {{ strtoupper(mb_substr($item['user']->name, 0, 1)) }}
                        </div>

                        <div class="flex-fill">
                            <div class="staff-name">{{ $item['user']->name }}</div>
                            <div class="staff-meta">
                                @if($item['shift'])
                                    <span class="shift-badge"
                                        style="background:{{ $item['shift']->color }}20;color:{{ $item['shift']->color }};border:1px solid {{ $item['shift']->color }};">
                                        {{ $item['shift']->name }}
                                        ·
                                        {{ substr($item['shift']->start_time, 0, 5) }}–{{ substr($item['shift']->end_time, 0, 5) }}
                                    </span>
                                @endif
                                @if(!$item['has_pin'])
                                    <span class="badge bg-warning text-dark ms-1" style="font-size:10px;">⚠ Chưa có PIN</span>
                                @endif
                            </div>
                        </div>

                        <div class="staff-status">
                            @if($status === 'checked_in')
                                <div class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    ✅ {{ $log->checkin_time ? substr($log->checkin_time, 0, 5) : '' }}
                                </div>
                                @if($log->late_minutes > 0)
                                    <div class="text-warning small mt-1">⚠ Muộn {{ $log->late_minutes }}p</div>
                                @endif
                            @elseif($status === 'checked_out')
                                <div class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                    👋 {{ $log->checkout_time ? substr($log->checkout_time, 0, 5) : '' }}
                                </div>
                                <div class="text-muted small mt-1">{{ $log->actual_hours }}h</div>
                            @else
                                <div class="badge bg-light text-muted border px-2 py-1">Chưa đến</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                        Không có nhân viên nào có ca hôm nay
                    </div>
                @endforelse
            </div>

            {{-- Đang làm việc --}}
            @if($checkedIn->count() > 0)
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-success-subtle border-0 py-2 px-3">
                        <span class="fw-bold text-success small">
                            🟢 Đang làm việc ({{ $checkedIn->count() }} người)
                        </span>
                    </div>
                    <div class="live-list">
                        @foreach($checkedIn as $log)
                            <div class="live-item">
                                <div class="live-avatar">{{ strtoupper(mb_substr($log->user->name, 0, 1)) }}</div>
                                <div class="flex-fill">
                                    <div class="fw-semibold small">{{ $log->user->name }}</div>
                                    <div class="text-muted" style="font-size:11px;">
                                        Vào lúc {{ substr($log->checkin_time, 0, 5) }}
                                    </div>
                                </div>
                                @if($log->late_minutes > 0)
                                    <span class="badge bg-warning text-dark small">Muộn {{ $log->late_minutes }}p</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- ── Cột phải: PIN Pad ───────────────────────────────────────── --}}
        <div>
            <div class="pin-panel">
                <div class="pin-header" id="pinHeader">
                    <div><i class="bi bi-fingerprint" style="font-size:32px;opacity:.8;"></i></div>
                    <div class="selected-name" id="selectedName">Chọn nhân viên</div>
                    <div class="selected-shift" id="selectedShift">← Bấm vào tên bên trái</div>
                </div>

                <div class="pin-body">
                    {{-- Cảnh báo chưa có PIN --}}
                    <div id="noPinBar" style="display:none;background:#FEF2F2;border:1px solid #FECACA;
                     border-radius:10px;padding:10px 14px;margin-bottom:12px;font-size:13px;">
                        <div class="fw-bold text-danger mb-1">⚠ Nhân viên này chưa có mã PIN</div>
                        <div class="text-muted small">
                            Quản lý vào
                            <a href="{{ url('/users') }}" target="_blank" style="color:#0EA5A0;font-weight:600;">Quản lý
                                nhân viên</a>
                            → bấm nút <strong>🔑 PIN</strong> để cài mã PIN.
                        </div>
                    </div>

                    {{-- Check-in / Check-out toggle --}}
                    <div id="numpadWrap">
                        <div class="mode-toggle">
                            <div class="mode-btn active-in" id="modeIn" onclick="setMode('in')">
                                <i class="bi bi-box-arrow-in-right me-1"></i>CHECK-IN
                            </div>
                            <div class="mode-btn" id="modeOut" onclick="setMode('out')">
                                <i class="bi bi-box-arrow-right me-1"></i>CHECK-OUT
                            </div>
                        </div>

                        {{-- PIN hiển thị --}}
                        <div class="pin-display" id="pinDisplay">
                            <div class="pin-dot" id="dot0">·</div>
                            <div class="pin-dot" id="dot1">·</div>
                            <div class="pin-dot" id="dot2">·</div>
                            <div class="pin-dot" id="dot3">·</div>
                        </div>

                        {{-- Numpad --}}
                        <div class="numpad">
                            @foreach([[1, ''], [2, 'ABC'], [3, 'DEF'], [4, 'GHI'], [5, 'JKL'], [6, 'MNO'], [7, 'PQRS'], [8, 'TUV'], [9, 'WXYZ']] as [$n, $s])
                                <button class="num-btn" onclick="pressNum('{{ $n }}')">
                                    {{ $n }}
                                    @if($s)<span class="num-sub">{{ $s }}</span>@endif
                                </button>
                            @endforeach
                            <button class="num-btn del-btn" onclick="delNum()">
                                <i class="bi bi-backspace"></i>
                            </button>
                            <button class="num-btn" onclick="pressNum('0')">0</button>
                            <button class="num-btn ok-btn fw-bold" onclick="submitPin()" id="okBtn">
                                <i class="bi bi-check-lg" style="font-size:22px;"></i>
                                <span style="font-size:11px;">XÁC NHẬN</span>
                            </button>
                        </div>

                        <div class="text-center mt-3 text-muted small" id="pinHint">
                            Nhập mã PIN 4–6 số của bạn
                        </div>
                    </div>{{-- /numpadWrap --}}
                </div>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div class="toast-wrap" id="toastWrap" style="display:none;">
        <div class="toast-msg" id="toastMsg"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ── State ────────────────────────────────────────────────────────────
        let selectedUserId = null;
        let pin = '';
        let mode = 'in'; // 'in' | 'out'
        let selfCheckIn = false; // true = check in for self, false = check in for others
        const pharmacyId = {{ $pharmacyId }};
        @auth
            const currentUserId = {{ auth()->id() }};
            const currentUserName = "{{ auth()->user()->name }}";
        @endauth

        // ── Self Check-in toggle ─────────────────────────────────────────────
        function enableSelfCheckinMode() {
            selfCheckIn = true;
            selectedUserId = currentUserId;
            pin = '';
            updateDots();
            
            // Update header
            document.getElementById('selectedName').textContent = currentUserName;
            document.getElementById('selectedShift').textContent = 'Chấm công cho bản thân';
            document.getElementById('pinHeader').style.background = 'linear-gradient(135deg, #0EA5A066, #0EA5A0)';
            
            // Remove highlight from staff cards
            document.querySelectorAll('.staff-card').forEach(c => c.classList.remove('active'));
            
            // Show PIN pad and instructions
            const noPinBar = document.getElementById('noPinBar');
            const numpadEl = document.getElementById('numpadWrap');
            
            // Check if current user has PIN
            const selfCard = document.querySelector(`[data-uid="${currentUserId}"]`);
            const hasSelfPin = selfCard ? selfCard.dataset.haspin === '1' : true; // Assume has PIN if not in list
            
            if (!hasSelfPin) {
                document.getElementById('pinHint').innerHTML =
                    '<span style="color:#DC2626;font-weight:700;">⚠ Bạn chưa có mã PIN</span><br>' +
                    '<span style="font-size:11px;">Liên hệ quản lý để <b>cài PIN</b> trước</span>';
                if (noPinBar) noPinBar.style.display = '';
                if (numpadEl) numpadEl.style.opacity = '0.35';
            } else {
                document.getElementById('pinHint').textContent = 'Nhập mã PIN 4–6 số của bạn';
                if (noPinBar) noPinBar.style.display = 'none';
                if (numpadEl) numpadEl.style.opacity = '1';
            }
            
            setMode('in');
        }

        function toggleSelfCheckin() {
            // Self-checkin is now mandatory — this function is disabled
            // Kept for backward compatibility but does nothing
            showToast('⚠️ Chế độ chấm công: Mỗi người phải chấm công cho bản thân', 'info');
        }

        // ── Staff card click (dùng event delegation) ─────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            // Staff cards are now READ-ONLY (display only)
            // All check-ins must be done via self-checkin
            document.querySelectorAll('.staff-card').forEach(function (card) {
                card.style.cursor = 'default';
                card.style.pointerEvents = 'none';
            });
            
            // Auto-enable self-checkin on page load
            @auth
                enableSelfCheckinMode();
            @endauth
        });

        // ── Clock ────────────────────────────────────────────────────────────
        function updateClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('liveClock').textContent = h + ':' + m + ':' + s;
        }
        setInterval(updateClock, 1000);

        // ── Select staff ─────────────────────────────────────────────────────
        function selectStaff(id, name, shiftName, color, timeRange, hasPin) {
            selectedUserId = id;
            pin = '';
            updateDots();

            // Update header
            document.getElementById('selectedName').textContent = name;
            document.getElementById('selectedShift').textContent = shiftName + (timeRange ? ' · ' + timeRange : '');
            document.getElementById('pinHeader').style.background = `linear-gradient(135deg, ${color}cc, ${color})`;

            // Highlight card
            document.querySelectorAll('.staff-card').forEach(c => c.classList.remove('active'));
            const card = document.getElementById('card-' + id);
            if (card) card.classList.add('active');

            // Nếu chưa có PIN — hiện cảnh báo + disable numpad
            const noPinBar = document.getElementById('noPinBar');
            const numpadEl = document.getElementById('numpadWrap');
            if (!hasPin) {
                document.getElementById('pinHint').innerHTML =
                    '<span style="color:#DC2626;font-weight:700;">⚠ Nhân viên chưa có mã PIN</span><br>' +
                    '<span style="font-size:11px;">Quản lý cần vào <b>Nhân viên → Cài PIN</b> trước</span>';
                if (noPinBar) noPinBar.style.display = '';
                if (numpadEl) numpadEl.style.opacity = '0.35';
                // Vẫn cho submit để hiện thông báo rõ ràng
            } else {
                document.getElementById('pinHint').textContent = 'Nhập mã PIN 4–6 số của bạn';
                if (noPinBar) noPinBar.style.display = 'none';
                if (numpadEl) numpadEl.style.opacity = '1';
            }

            // Auto detect mode
            if (card && card.classList.contains('checked-in')) {
                setMode('out');
            } else {
                setMode('in');
            }
        }

        // ── Mode toggle ──────────────────────────────────────────────────────
        function setMode(m) {
            mode = m;
            document.getElementById('modeIn').className = 'mode-btn' + (m === 'in' ? ' active-in' : '');
            document.getElementById('modeOut').className = 'mode-btn' + (m === 'out' ? ' active-out' : '');
            pin = ''; updateDots();
        }

        // ── PIN pad ──────────────────────────────────────────────────────────
        function pressNum(n) {
            if (pin.length >= 6) return;
            pin += n;
            updateDots();
            if (pin.length >= 4) {
                document.getElementById('okBtn').style.background = '#DCFCE7';
            }
        }

        function delNum() {
            pin = pin.slice(0, -1);
            updateDots();
        }

        function updateDots() {
            for (let i = 0; i < 4; i++) {
                const dot = document.getElementById('dot' + i);
                if (i < pin.length) {
                    dot.textContent = '●';
                    dot.classList.add('filled');
                } else {
                    dot.textContent = '·';
                    dot.classList.remove('filled');
                }
            }
        }

        // ── Submit PIN ───────────────────────────────────────────────────────
        async function submitPin() {
            // For self check-in, currentUserId should be set
            const userToCheckIn = selfCheckIn ? currentUserId : selectedUserId;
            
            if (!userToCheckIn) { 
                showToast('Vui lòng ' + (selfCheckIn ? 'loại bỏ "Chấm công cho bản thân" hoặc' : '') + ' chọn nhân viên trước!', 'error'); 
                return; 
            }
            if (pin.length < 4) { showToast('Mã PIN tối thiểu 4 số!', 'error'); return; }

            const url = mode === 'in' ? '/checkin/check-in' : '/checkin/check-out';
            const body = { user_id: userToCheckIn, pin, pharmacy_id: pharmacyId };
            const csrfEl = document.querySelector('meta[name="csrf-token"]');

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfEl.content },
                    body: JSON.stringify(body),
                });
                const data = await res.json();

                if (data.success) {
                    const type = data.late_minutes > 0 ? 'late' : 'success';
                    showToast(data.message, type);
                    pin = ''; updateDots();
                    // Reload sau 2s để cập nhật danh sách
                    setTimeout(() => location.reload(), 2200);
                } else {
                    showToast(data.message, 'error');
                    pin = ''; updateDots();
                    // Rung nhẹ PIN display
                    document.getElementById('pinDisplay').style.animation = 'none';
                    setTimeout(() => document.getElementById('pinDisplay').style.animation = '', 100);
                }
            } catch (e) {
                showToast('Lỗi kết nối!', 'error');
            }
        }

        // ── Toast ─────────────────────────────────────────────────────────────
        let toastTimer;
        function showToast(msg, type = 'success') {
            clearTimeout(toastTimer);
            const wrap = document.getElementById('toastWrap');
            const el = document.getElementById('toastMsg');
            el.className = 'toast-msg toast-' + type;
            let icon = '✅ ';
            if (type === 'late') icon = '⚠️ ';
            else if (type === 'error') icon = '❌ ';
            else if (type === 'info') icon = 'ℹ️ ';
            el.innerHTML = icon + msg;
            wrap.style.display = 'block';
            toastTimer = setTimeout(() => { wrap.style.display = 'none'; }, 3500);
        }

        // Keyboard support
        document.addEventListener('keydown', e => {
            if (e.key >= '0' && e.key <= '9') pressNum(e.key);
            else if (e.key === 'Backspace') delNum();
            else if (e.key === 'Enter') submitPin();
        });
    </script>
</body>

</html>