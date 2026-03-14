<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập — Hệ thống Quản lý Nhà thuốc GPP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --navy: #0B1929;
            --navy-mid: #112240;
            --navy-light: #1B3A60;
            --teal: #00C9A7;
            --teal-dim: #00A88A;
            --white: #FFFFFF;
            --gray-100: #EEF2F7;
            --gray-400: #94A3B8;
            --gray-600: #64748B;
            --gray-800: #1E293B;
            --danger: #F43F5E;
            --gold: #F4B942;
        }

        html,
        body {
            height: 100%;
            font-family: 'DM Sans', sans-serif;
            overflow: hidden;
        }

        /* FULL BACKGROUND */
        .bg {
            position: fixed;
            inset: 0;
            background: var(--navy);
            z-index: 0;
        }

        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            animation: drift 14s ease-in-out infinite;
        }

        .blob-1 {
            width: 520px;
            height: 520px;
            background: var(--teal);
            opacity: .12;
            top: -160px;
            right: -100px;
        }

        .blob-2 {
            width: 400px;
            height: 400px;
            background: #4F6AF5;
            opacity: .11;
            bottom: -80px;
            left: -100px;
            animation-delay: -5s;
        }

        .blob-3 {
            width: 280px;
            height: 280px;
            background: var(--gold);
            opacity: .07;
            top: 40%;
            left: 30%;
            animation-delay: -9s;
        }

        .blob-4 {
            width: 220px;
            height: 220px;
            background: #A855F7;
            opacity: .08;
            bottom: 20%;
            right: 18%;
            animation-delay: -3s;
        }

        @keyframes drift {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(30px, -40px) scale(1.06);
            }

            66% {
                transform: translate(-20px, 25px) scale(0.94);
            }
        }

        .grid-overlay {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, .035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .035) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        /* BACKGROUND INFO — chìm phía sau */
        .bg-info {
            position: fixed;
            inset: 0;
            z-index: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 44px 56px;
            pointer-events: none;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            opacity: .9;
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--teal), var(--teal-dim));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
            box-shadow: 0 6px 20px rgba(0, 201, 167, .3);
        }

        .brand-name {
            font-family: 'DM Serif Display', serif;
            font-size: 18px;
            color: white;
            line-height: 1.2;
        }

        .brand-sub {
            font-size: 10.5px;
            color: var(--teal);
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 500;
        }

        .bg-headline {
            position: absolute;
            left: 56px;
            top: 50%;
            transform: translateY(-50%);
            max-width: 420px;
            opacity: .42;
        }

        .bg-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(0, 201, 167, .15);
            border: 1px solid rgba(0, 201, 167, .3);
            border-radius: 100px;
            padding: 5px 14px;
            font-size: 11px;
            color: var(--teal);
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .bg-eyebrow::before {
            content: '';
            width: 6px;
            height: 6px;
            background: var(--teal);
            border-radius: 50%;
        }

        .bg-title {
            font-family: 'DM Serif Display', serif;
            font-size: clamp(36px, 4vw, 56px);
            color: white;
            line-height: 1.12;
            margin-bottom: 16px;
        }

        .bg-title em {
            font-style: italic;
            color: var(--teal);
        }

        .bg-desc {
            font-size: 15px;
            color: var(--gray-400);
            line-height: 1.7;
        }

        .bg-stats {
            display: flex;
            gap: 36px;
            opacity: .35;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .stat-num {
            font-family: 'DM Serif Display', serif;
            font-size: 26px;
            color: white;
            line-height: 1;
        }

        .stat-label {
            font-size: 11px;
            color: var(--gray-400);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 500;
        }

        .stat-div {
            width: 1px;
            background: rgba(255, 255, 255, .15);
        }

        .bg-chips {
            position: absolute;
            right: 56px;
            bottom: 44px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            max-width: 340px;
            justify-content: flex-end;
            opacity: .28;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 8px;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .12);
            font-size: 12px;
            color: rgba(255, 255, 255, .8);
        }

        .chip-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--teal);
        }

        /* FORM CARD — glassmorphism giữa màn hình */
        .page {
            position: fixed;
            inset: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .form-card {
            width: 100%;
            max-width: 430px;
            background: rgba(10, 22, 42, .82);
            backdrop-filter: blur(24px) saturate(1.6);
            -webkit-backdrop-filter: blur(24px) saturate(1.6);
            border: 1px solid rgba(0, 201, 167, .18);
            border-top: 1px solid rgba(0, 201, 167, .3);
            border-radius: 24px;
            padding: 44px 44px 36px;
            box-shadow: 0 40px 90px rgba(0, 0, 0, .6), 0 0 60px rgba(0, 201, 167, .06), 0 0 0 1px rgba(0, 201, 167, .08) inset;
            animation: slideUp .5s cubic-bezier(.22, 1, .36, 1) both;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(28px) scale(.97);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .form-header {
            margin-bottom: 30px;
        }

        .form-greeting {
            font-size: 12px;
            color: rgba(255, 255, 255, .4);
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 500;
            margin-bottom: 6px;
        }

        .form-title {
            font-family: 'DM Serif Display', serif;
            font-size: 32px;
            color: white;
            line-height: 1.1;
            margin-bottom: 8px;
        }

        .form-subtitle {
            font-size: 14px;
            color: rgba(255, 255, 255, .4);
            line-height: 1.6;
        }

        .field-group {
            margin-bottom: 18px;
        }

        .field-label {
            display: block;
            font-size: 11.5px;
            font-weight: 600;
            color: rgba(255, 255, 255, .45);
            letter-spacing: .8px;
            text-transform: uppercase;
            margin-bottom: 8px;
            transition: color .2s;
        }

        .field-group:focus-within .field-label {
            color: var(--teal);
        }

        .field-wrap {
            position: relative;
        }

        .field-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, .28);
            transition: color .2s;
            pointer-events: none;
        }

        .field-wrap:focus-within .field-icon {
            color: var(--teal);
        }

        .field-input {
            width: 100%;
            height: 50px;
            padding: 0 46px 0 44px;
            background: rgba(255, 255, 255, .05);
            border: 1.5px solid rgba(255, 255, 255, .12);
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            color: white;
            outline: none;
            transition: all .2s;
        }

        .field-input::placeholder {
            color: rgba(255, 255, 255, .28);
        }

        .field-input:hover {
            border-color: rgba(0, 201, 167, .35);
            background: rgba(0, 201, 167, .04);
        }

        .field-input:focus {
            border-color: var(--teal);
            background: rgba(0, 201, 167, .07);
            box-shadow: 0 0 0 3px rgba(0, 201, 167, .18);
        }

        .field-input.is-invalid {
            border-color: var(--danger);
            box-shadow: 0 0 0 3px rgba(244, 63, 94, .15);
        }

        .field-input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 100px #112240 inset;
            -webkit-text-fill-color: white;
            caret-color: white;
        }

        .pwd-toggle {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, .3);
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            transition: color .2s;
        }

        .pwd-toggle:hover {
            color: rgba(255, 255, 255, .7);
        }

        .alert-error {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 11px 14px;
            background: rgba(244, 63, 94, .12);
            border: 1px solid rgba(244, 63, 94, .3);
            border-radius: 10px;
            margin-bottom: 18px;
            font-size: 13.5px;
            color: #FDA4AF;
            animation: shake .4s both;
        }

        @keyframes shake {

            10%,
            90% {
                transform: translateX(-2px);
            }

            20%,
            80% {
                transform: translateX(3px);
            }

            30%,
            50%,
            70% {
                transform: translateX(-3px);
            }

            40%,
            60% {
                transform: translateX(3px);
            }
        }

        .field-error {
            margin-top: 5px;
            font-size: 12px;
            color: #FDA4AF;
        }

        .form-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }

        .checkbox-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .checkbox-wrap input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--teal);
            cursor: pointer;
        }

        .checkbox-label {
            font-size: 13.5px;
            color: rgba(255, 255, 255, .4);
            user-select: none;
        }

        .btn-submit {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, #00C9A7 0%, #00A88A 100%);
            color: #0B1929;
            border: none;
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all .25s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 6px 28px rgba(0, 201, 167, .4), 0 1px 0 rgba(255, 255, 255, .15) inset;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, .15), transparent);
            opacity: 0;
            transition: opacity .3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 36px rgba(0, 201, 167, .5);
        }

        .btn-submit:hover::before {
            opacity: 1;
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit.loading .btn-text {
            opacity: 0;
        }

        .btn-submit.loading .btn-spinner {
            display: block;
        }

        .btn-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2.5px solid rgba(11, 25, 41, .3);
            border-top-color: var(--navy);
            border-radius: 50%;
            animation: spin .7s linear infinite;
            position: absolute;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .form-footer {
            margin-top: 22px;
            text-align: center;
            font-size: 11.5px;
            color: rgba(255, 255, 255, .22);
        }

        @media (max-width:600px) {
            .form-card {
                padding: 32px 24px 28px;
                border-radius: 20px;
            }

            .bg-headline,
            .bg-chips,
            .bg-stats {
                display: none;
            }
        }
    </style>
</head>

<body>

    {{-- FULL BACKGROUND --}}
    <div class="bg">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
        <div class="blob blob-4"></div>
        <div class="grid-overlay"></div>
    </div>

    {{-- BACKGROUND INFO chìm --}}
    <div class="bg-info">
        <div class="brand">
            <div class="brand-icon">💊</div>
            <div>
                <div class="brand-name">PharmaCare GPP</div>
                <div class="brand-sub">Pharmacy Management</div>
            </div>
        </div>

        <div class="bg-headline">
            <div class="bg-eyebrow">Chuẩn GPP Bộ Y Tế</div>
            <h1 class="bg-title">Quản lý nhà thuốc<br><em>thông minh &amp; chuẩn mực</em></h1>
            <p class="bg-desc">Hệ thống quản lý toàn diện: tồn kho FEFO/FIFO, hóa đơn, công nợ, báo cáo — thiết kế riêng
                cho nhà thuốc đạt chuẩn GPP.</p>
        </div>

        <div class="bg-stats">
            <div class="stat-item"><span class="stat-num">FEFO</span><span class="stat-label">Xuất hàng</span></div>
            <div class="stat-div"></div>
            <div class="stat-item"><span class="stat-num">4</span><span class="stat-label">Vai trò</span></div>
            <div class="stat-div"></div>
            <div class="stat-item"><span class="stat-num">GPP</span><span class="stat-label">Chuẩn BYT</span></div>
        </div>

        <div class="bg-chips">
            <span class="chip"><span class="chip-dot"></span>Quản lý lô &amp; HSD</span>
            <span class="chip"><span class="chip-dot"></span>Báo cáo doanh thu</span>
            <span class="chip"><span class="chip-dot"></span>POS bán hàng</span>
            <span class="chip"><span class="chip-dot"></span>Quản lý công nợ</span>
            <span class="chip"><span class="chip-dot"></span>Phân quyền RBAC</span>
            <span class="chip"><span class="chip-dot"></span>Xuất CSV / In phiếu</span>
        </div>
    </div>

    {{-- FORM CARD GIỮA MÀN HÌNH --}}
    <div class="page">
        <div class="form-card">
            <div class="form-header">
                <p class="form-greeting">Chào mừng trở lại</p>
                <h2 class="form-title">Đăng nhập</h2>
                <p class="form-subtitle">Nhập thông tin tài khoản để tiếp tục.</p>
            </div>

            @if ($errors->any())
                <div class="alert-error">
                    <span style="font-size:15px;flex-shrink:0;">⚠️</span>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @if (session('status'))
                <div class="alert-error"
                    style="background:rgba(34,197,94,.1);border-color:rgba(34,197,94,.3);color:#86EFAC;">
                    <span style="font-size:15px;flex-shrink:0;">✅</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <div class="field-group">
                    <label class="field-label" for="email">Email</label>
                    <div class="field-wrap">
                        <svg class="field-icon" viewBox="0 0 20 20" fill="currentColor" width="17" height="17">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                        </svg>
                        <input type="email" id="email" name="email"
                            class="field-input @error('email') is-invalid @enderror" value="{{ old('email') }}"
                            placeholder="email@nhàthuoc.vn" autocomplete="email" autofocus required>
                    </div>
                    @error('email')
                        <div class="field-error">⚠ {{ $message }}</div>
                    @enderror
                </div>

                <div class="field-group">
                    <label class="field-label" for="password">Mật khẩu</label>
                    <div class="field-wrap">
                        <svg class="field-icon" viewBox="0 0 20 20" fill="currentColor" width="17" height="17">
                            <path fill-rule="evenodd"
                                d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <input type="password" id="password" name="password"
                            class="field-input @error('password') is-invalid @enderror" placeholder="••••••••"
                            autocomplete="current-password" required>
                        <button type="button" class="pwd-toggle" onclick="togglePassword()">
                            <svg id="eyeOpen" viewBox="0 0 20 20" fill="currentColor" width="17" height="17">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                <path fill-rule="evenodd"
                                    d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            <svg id="eyeClosed" viewBox="0 0 20 20" fill="currentColor" width="17" height="17"
                                style="display:none;">
                                <path fill-rule="evenodd"
                                    d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z"
                                    clip-rule="evenodd" />
                                <path
                                    d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.064 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <div class="field-error">⚠ {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-meta">
                    <label class="checkbox-wrap">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span class="checkbox-label">Ghi nhớ đăng nhập</span>
                    </label>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <div class="btn-spinner"></div>
                    <span class="btn-text">
                        Đăng nhập
                        <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16" style="margin-left:2px;">
                            <path fill-rule="evenodd"
                                d="M3 10a1 1 0 011-1h8.586l-2.293-2.293a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H4a1 1 0 01-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                </button>
            </form>

            <div class="form-footer">© {{ date('Y') }} PharmaCare GPP · Hệ thống quản lý nhà thuốc</div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const open = document.getElementById('eyeOpen');
            const closed = document.getElementById('eyeClosed');
            if (input.type === 'password') {
                input.type = 'text'; open.style.display = 'none'; closed.style.display = 'block';
            } else {
                input.type = 'password'; open.style.display = 'block'; closed.style.display = 'none';
            }
        }
        document.getElementById('loginForm').addEventListener('submit', function () {
            const btn = document.getElementById('submitBtn');
            btn.classList.add('loading'); btn.disabled = true;
        });
    </script>
</body>

</html>