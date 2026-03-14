@extends('layouts.app')
@section('title', 'Thêm nhân viên')
@section('page-title', 'Thêm nhân viên')

@section('content')

    <div class="page-header">
        <div>
            <h4 class="mb-0"><i class="bi bi-person-plus me-2 text-primary"></i>Thêm nhân viên mới</h4>
            <small class="text-muted">Tạo tài khoản đăng nhập cho nhân viên nhà thuốc</small>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

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
                                    value="{{ old('name') }}" placeholder="Nguyễn Thị Dược">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}"
                                    placeholder="09xx xxx xxx">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    Email đăng nhập <span class="text-danger">*</span>
                                </label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" placeholder="nhanvien@nhathuoc.com">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Mật khẩu <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" name="password" id="passInput"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="Tối thiểu 8 ký tự">
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="togglePass('passInput', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Nhập lại mật khẩu <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="passConfirm"
                                        class="form-control" placeholder="Nhập lại mật khẩu">
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="togglePass('passConfirm', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Phân quyền --}}
                <div class="card mb-3">
                    <div class="card-header py-3 px-4">
                        <h6 class="mb-0 fw-bold">🔐 Vai trò & Phân quyền</h6>
                    </div>
                    <div class="card-body px-4">
                        <label class="form-label fw-semibold">
                            Vai trò <span class="text-danger">*</span>
                        </label>
                        @php
                            $icons = ['admin' => '🔑', 'manager' => '👑', 'pharmacist' => '💊'];
                            $allowedRoles = ['admin', 'manager', 'pharmacist'];
                            $filteredRoles = $roles->filter(fn($r) => in_array($r->name, $allowedRoles))->values();
                            $oldRoleId = old('role_id');
                        @endphp
                        <div class="row g-3">
                            @foreach($filteredRoles as $role)
                                                @php $isSelected = ($oldRoleId == $role->id); @endphp
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

                        <div class="alert alert-info small mt-3 mb-0 py-2 px-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Phân quyền chi tiết được cấu hình sẵn theo từng vai trò.
                            Liên hệ quản trị viên hệ thống để thay đổi.
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary flex-fill py-2 fw-semibold">
                        <i class="bi bi-person-plus me-2"></i>Tạo tài khoản nhân viên
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
        // Toggle show/hide password
        function togglePass(inputId, btn) {
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