@extends('layouts.app')
@section('title', 'Hồ sơ cá nhân')
@section('page-title', 'Hồ sơ cá nhân')

@section('content')

    <div class="row g-4 justify-content-center">

        {{-- ── Cột trái: Avatar + thông tin tóm tắt ── --}}
        <div class="col-lg-4">

            {{-- Avatar card --}}
            <div class="card text-center mb-3">
                <div class="card-body py-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 fw-bold text-white"
                        style="width:80px;height:80px;font-size:32px;
                                background:{{ $user->avatar_color ?? '#4A90D9' }};">
                        {{ $user->avatar_initial ?? strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h5 class="mb-1 fw-bold">{{ $user->name }}</h5>
                    <p class="text-muted mb-2">{{ $user->email }}</p>
                    <span class="badge"
                        style="background:{{ $user->roles->first()?->color ?? '#6c757d' }};font-size:13px;padding:6px 14px;">
                        {{ $user->roles->first()?->display_name ?? $user->roles->first()?->name ?? 'Chưa có vai trò' }}
                    </span>
                </div>
                <div class="card-footer py-3">
                    <div class="row text-center g-0">
                        <div class="col border-end">
                            <div class="fw-bold text-primary">{{ $user->invoices()->count() }}</div>
                            <div class="small text-muted">Hóa đơn</div>
                        </div>
                        <div class="col border-end">
                            <div class="fw-bold text-warning">{{ $user->purchaseOrders()->count() }}</div>
                            <div class="small text-muted">Đơn nhập</div>
                        </div>
                        <div class="col">
                            <div class="fw-bold text-success">
                                {{ $user->last_login_at ? $user->last_login_at->format('d/m') : '—' }}
                            </div>
                            <div class="small text-muted">Đăng nhập</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Thông tin nhà thuốc --}}
            <div class="card">
                <div class="card-header py-3 px-4">
                    <h6 class="mb-0 fw-bold">🏥 Nhà thuốc</h6>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item px-4 py-2">
                        <small class="text-muted d-block">Tên nhà thuốc</small>
                        <span class="fw-semibold">{{ $user->pharmacy?->name ?? '—' }}</span>
                    </div>
                    <div class="list-group-item px-4 py-2">
                        <small class="text-muted d-block">Địa chỉ</small>
                        <span>{{ $user->pharmacy?->address ?? '—' }}</span>
                    </div>
                    <div class="list-group-item px-4 py-2">
                        <small class="text-muted d-block">Giấy phép GPP</small>
                        <span class="badge bg-success">{{ $user->pharmacy?->license_number ?? 'Đã cấp phép' }}</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── Cột phải: Form sửa + đổi mật khẩu ── --}}
        <div class="col-lg-8">

            {{-- Cập nhật thông tin --}}
            <div class="card mb-4">
                <div class="card-header py-3 px-4">
                    <h6 class="mb-0 fw-bold">👤 Thông tin cá nhân</h6>
                </div>
                <div class="card-body px-4 py-4">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Vai trò</label>
                                <input type="text" class="form-control" readonly
                                    value="{{ $user->roles->first()?->display_name ?? $user->roles->first()?->name }}"
                                    style="background:#f8fafc;">
                                <small class="text-muted">Chỉ quản lý mới có thể thay đổi vai trò.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Trạng thái tài khoản</label>
                                <input type="text" class="form-control" readonly
                                    value="{{ $user->is_active ? 'Đang hoạt động' : 'Bị khóa' }}"
                                    style="background:#f8fafc;color:{{ $user->is_active ? '#27AE60' : '#C0392B' }};">
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-circle me-2"></i>Lưu thông tin
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Đổi mật khẩu --}}
            <div class="card">
                <div class="card-header py-3 px-4">
                    <h6 class="mb-0 fw-bold">🔒 Đổi mật khẩu</h6>
                </div>
                <div class="card-body px-4 py-4">
                    <form action="{{ route('profile.password') }}" method="POST" id="passwordForm">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">

                            {{-- Mật khẩu hiện tại --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    Mật khẩu hiện tại <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" name="current_password" id="currentPwd"
                                        class="form-control @error('current_password') is-invalid @enderror"
                                        placeholder="Nhập mật khẩu đang dùng">
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="togglePwd('currentPwd', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @error('current_password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Mật khẩu mới --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Mật khẩu mới <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" name="password" id="newPwd"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="Tối thiểu 8 ký tự" oninput="checkStrength(this.value)">
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="togglePwd('newPwd', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Password strength bar --}}
                                <div class="mt-2">
                                    <div class="progress" style="height:4px;border-radius:2px;">
                                        <div id="strengthBar" class="progress-bar" style="width:0%;transition:.3s;"></div>
                                    </div>
                                    <small id="strengthLabel" class="text-muted"></small>
                                </div>
                            </div>

                            {{-- Xác nhận mật khẩu --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Xác nhận mật khẩu mới <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="confirmPwd" class="form-control"
                                        placeholder="Nhập lại mật khẩu mới" oninput="checkMatch()">
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="togglePwd('confirmPwd', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <small id="matchLabel" class="text-muted"></small>
                            </div>

                        </div>

                        <div class="alert alert-light border mt-3 mb-0 py-2 px-3" style="font-size:13px;">
                            <strong>Yêu cầu mật khẩu:</strong>
                            Tối thiểu 8 ký tự. Nên kết hợp chữ hoa, chữ thường và số.
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-warning fw-bold px-4" id="submitPwdBtn">
                                <i class="bi bi-shield-lock me-2"></i>Đổi mật khẩu
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // Toggle hiển thị/ẩn mật khẩu
        function togglePwd(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }

        // Đánh giá độ mạnh mật khẩu
        function checkStrength(val) {
            const bar = document.getElementById('strengthBar');
            const label = document.getElementById('strengthLabel');
            let score = 0;
            if (val.length >= 8) score++;
            if (val.length >= 12) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            const levels = [
                { pct: '20%', color: '#e74c3c', text: 'Rất yếu' },
                { pct: '40%', color: '#e67e22', text: 'Yếu' },
                { pct: '60%', color: '#f1c40f', text: 'Trung bình' },
                { pct: '80%', color: '#2ecc71', text: 'Mạnh' },
                { pct: '100%', color: '#27AE60', text: '💪 Rất mạnh' },
            ];
            const lvl = levels[Math.min(score, 4)];
            bar.style.width = lvl.pct;
            bar.style.background = lvl.color;
            label.textContent = val ? lvl.text : '';
            label.style.color = lvl.color;
            checkMatch();
        }

        // Kiểm tra 2 mật khẩu có khớp không
        function checkMatch() {
            const pwd = document.getElementById('newPwd').value;
            const confirm = document.getElementById('confirmPwd').value;
            const label = document.getElementById('matchLabel');
            if (!confirm) { label.textContent = ''; return; }
            if (pwd === confirm) {
                label.textContent = '✅ Mật khẩu khớp';
                label.style.color = '#27AE60';
            } else {
                label.textContent = '❌ Mật khẩu chưa khớp';
                label.style.color = '#e74c3c';
            }
        }
    </script>
@endpush