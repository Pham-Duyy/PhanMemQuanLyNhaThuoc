@extends('layouts.app')
@section('title', 'Chỉnh sửa: ' . $user->name)
@section('page-title', 'Chỉnh sửa nhân viên')

@section('content')

    <div class="page-header">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" style="width:44px;height:44px;font-size:18px;
                        background:{{ ['#2471A3', '#1E8449', '#D35400', '#7D3C98'][crc32($user->name) % 4] }};">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h4 class="mb-0">{{ $user->name }}</h4>
                <small class="text-muted">{{ $user->email }}</small>
            </div>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Thông tin cá nhân --}}
                <div class="card mb-3">
                    <div class="card-header py-3 px-4">
                        <h6 class="mb-0 fw-bold">👤 Thông tin nhân viên</h6>
                    </div>
                    <div class="card-body px-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">
                                    Họ tên đầy đủ <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $user->name) }}">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control"
                                    value="{{ old('phone', $user->phone) }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    Email đăng nhập <span class="text-danger">*</span>
                                </label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email) }}">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Giới tính</label>
                                <select name="gender" class="form-select">
                                    <option value="">-- Chọn --</option>
                                    <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Nam
                                    </option>
                                    <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>
                                        Nữ</option>
                                    <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>
                                        Khác</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Chức vụ</label>
                                <input type="text" name="position" class="form-control"
                                    placeholder="VD: Dược sĩ, Quản lý nhà thuốc..."
                                    value="{{ old('position', $user->position) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Lương cơ bản (đ/tháng)</label>
                                <input type="number" name="base_salary" class="form-control" min="0" step="100000"
                                    placeholder="0" value="{{ old('base_salary', $user->base_salary ?? 0) }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Đổi mật khẩu (tuỳ chọn) --}}
                <div class="card mb-3">
                    <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">🔑 Đổi mật khẩu</h6>
                        <span class="badge bg-light text-muted border">Để trống nếu không đổi</span>
                    </div>
                    <div class="card-body px-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mật khẩu mới</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="passInput"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="Để trống = không thay đổi" autocomplete="new-password">
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="togglePass('passInput', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nhập lại mật khẩu mới</label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="passConfirm"
                                        class="form-control" placeholder="Nhập lại nếu đổi mật khẩu">
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="togglePass('passConfirm', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Vai trò --}}
                <div class="card mb-3">
                    <div class="card-header py-3 px-4">
                        <h6 class="mb-0 fw-bold">🔐 Vai trò & Phân quyền</h6>
                    </div>
                    <div class="card-body px-4">
                        @php
                            $currentRole = $user->roles->first();
                            $currentRoleId = old('role_id', $currentRole?->id);
                            $icons = [
                                'admin' => '🔑',
                                'manager' => '👑',
                                'pharmacist' => '💊',
                            ];
                            $allowedRoles = ['admin', 'manager', 'pharmacist'];
                            $filteredRoles = $roles->filter(fn($r) => in_array($r->name, $allowedRoles))->values();
                        @endphp

                        <div class="row g-3">
                            @foreach($filteredRoles as $role)
                                                @php $isSelected = ($currentRoleId == $role->id); @endphp
                                                <div class="col-sm-4">
                                                    <label for="role_{{ $role->id }}"
                                                        class="role-card d-block p-3 rounded-3 border text-center w-100" style="cursor:pointer;transition:all .18s;user-select:none;
                                                          {{ $isSelected
                                ? 'background:#e8f4fd;border:2px solid #2471A3;'
                                : 'background:#fff;border:1px solid #dee2e6;' }}">
                                                        <input type="radio" name="role_id" id="role_{{ $role->id }}" value="{{ $role->id }}"
                                                            class="d-none role-radio" {{ $isSelected ? 'checked' : '' }}>
                                                        <div style="font-size:30px;margin-bottom:6px;line-height:1;">
                                                            {{ $icons[$role->name] ?? '👤' }}
                                                        </div>
                                                        <div class="fw-bold small text-dark">{{ $role->display_name }}</div>
                                                        <small class="text-muted d-block mt-1" style="font-size:11px;line-height:1.4;">
                                                            {{ $role->description ?? '' }}
                                                        </small>
                                                    </label>
                                                </div>
                            @endforeach
                        </div>

                        @error('role_id')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Trạng thái tài khoản --}}
                <div class="card mb-3">
                    <div class="card-header py-3 px-4">
                        <h6 class="mb-0 fw-bold">⚙️ Trạng thái tài khoản</h6>
                    </div>
                    <div class="card-body px-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="isActive"
                                value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="isActive">
                                Tài khoản đang hoạt động
                            </label>
                            <div class="text-muted small mt-1">
                                Tắt để ngăn nhân viên này đăng nhập vào hệ thống
                                (dữ liệu lịch sử vẫn được giữ lại).
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Thống kê --}}
                <div class="card mb-3">
                    <div class="card-header py-3 px-4">
                        <h6 class="mb-0 fw-bold">📊 Hoạt động</h6>
                    </div>
                    <div class="card-body px-4 py-3">
                        <div class="row g-3 text-center">
                            <div class="col-4">
                                <div class="fw-bold fs-4 text-primary">
                                    {{ $user->invoices()->count() }}
                                </div>
                                <small class="text-muted">Hóa đơn đã tạo</small>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold fs-4 text-success">
                                    {{ $user->purchaseOrders()->count() }}
                                </div>
                                <small class="text-muted">Đơn nhập hàng</small>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold fs-4 text-muted">
                                    {{ $user->last_login_at?->diffForHumans() ?? 'Chưa đăng nhập' }}
                                </div>
                                <small class="text-muted">Đăng nhập gần nhất</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-warning flex-fill py-2 fw-semibold">
                        <i class="bi bi-check-lg me-2"></i>Lưu thay đổi
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary flex-fill py-2">
                        <i class="bi bi-x-lg me-2"></i>Hủy bỏ
                    </a>
                </div>

            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function togglePass(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            input.type = input.type === 'password' ? 'text' : 'password';
            icon.className = input.type === 'text' ? 'bi bi-eye-slash' : 'bi bi-eye';
        }

        // Role card highlight
        document.querySelectorAll('.role-radio').forEach(radio => {
            radio.addEventListener('change', function () {
                document.querySelectorAll('.role-card').forEach(card => {
                    card.style.background = '#fff';
                    card.style.border = '1px solid #dee2e6';
                });
                if (this.checked) {
                    const lbl = document.querySelector(`label[for="${this.id}"]`);
                    lbl.style.background = '#e8f4fd';
                    lbl.style.border = '2px solid #2471A3';
                }
            });
        });
    </script>
@endpush