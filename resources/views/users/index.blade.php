@extends('layouts.app')
@section('title', 'Quản lý người dùng')
@section('page-title', 'Quản lý người dùng')

@section('content')
    <div class="page-header">
        <div>
            <h4 class="mb-0"><i class="bi bi-people me-2 text-primary"></i>Quản lý người dùng</h4>
            <small class="text-muted">Tài khoản nhân viên trong nhà thuốc</small>
        </div>
        @if(auth()->user()->hasPermission('user.create'))
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Thêm nhân viên
            </a>
        @endif
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nhân viên</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th class="text-center">Vai trò</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center">Đăng nhập gần nhất</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="{{ !$user->is_active ? 'table-secondary text-muted' : '' }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                                        style="width:36px;height:36px;font-size:14px;
                                                background:{{ ['#2471A3', '#1E8449', '#D35400', '#7D3C98'][crc32($user->name) % 4] }};">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $user->name }}</div>
                                        @if($user->id === auth()->id())
                                            <span class="badge bg-primary" style="font-size:10px;">Bạn</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="small">{{ $user->email }}</td>
                            <td class="small text-muted">{{ $user->phone ?? '—' }}</td>
                            <td class="text-center">
                                @foreach($user->roles as $role)
                                    <span class="badge bg-light text-dark border">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td class="text-center">
                                @if($user->is_active)
                                    <span class="badge bg-success">Hoạt động</span>
                                @else
                                    <span class="badge bg-secondary">Vô hiệu hóa</span>
                                @endif
                            </td>
                            <td class="text-center small text-muted">
                                {{ $user->last_login_at?->diffForHumans() ?? 'Chưa đăng nhập' }}
                            </td>
                            <td class="text-center">
                                @if(auth()->user()->hasPermission('user.edit') && $user->id !== auth()->id())
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-warning"><i
                                                class="bi bi-pencil"></i></a>
                                        <form action="{{ route('users.destroy', $user) }}" method="POST"
                                            onsubmit="return confirm('Vô hiệu hóa tài khoản này?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-{{ $user->is_active ? 'danger' : 'success' }}"
                                                title="{{ $user->is_active ? 'Vô hiệu hóa' : 'Kích hoạt lại' }}">
                                                <i class="bi bi-{{ $user->is_active ? 'person-x' : 'person-check' }}"></i>
                                            </button>
                                        </form>
                                        <button class="btn btn-sm btn-outline-secondary" title="Đặt lại mật khẩu"
                                            onclick="openResetPwd({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                            <i class="bi bi-key"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info" title="Cài mã PIN chấm công"
                                            onclick="openSetPin({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                            <i class="bi bi-person-badge"></i> PIN
                                        </button>
                                    </div>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Chưa có nhân viên nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="card-footer py-3 px-4">{{ $users->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>

    {{-- Modal đặt lại mật khẩu --}}
    <div class="modal fade" id="resetPwdModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">🔑 Đặt lại mật khẩu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="resetPwdForm" method="POST">
                    @csrf
                    <div class="modal-body pt-3">
                        <p class="text-muted mb-3">Đặt lại mật khẩu cho: <strong id="resetUserName"></strong></p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mật khẩu mới <span class="text-danger">*</span></label>
                            <input type="password" name="new_password" class="form-control" placeholder="Tối thiểu 8 ký tự"
                                required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Xác nhận mật khẩu <span
                                    class="text-danger">*</span></label>
                            <input type="password" name="new_password_confirmation" class="form-control"
                                placeholder="Nhập lại mật khẩu mới" required minlength="8">
                        </div>
                        <div class="alert alert-warning py-2 small mb-0">
                            ⚠️ Người dùng sẽ cần dùng mật khẩu mới này để đăng nhập lần sau.
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-warning fw-bold">
                            <i class="bi bi-key me-1"></i>Đặt lại mật khẩu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function openResetPwd(userId, userName) {
                document.getElementById('resetUserName').textContent = userName;
                document.getElementById('resetPwdForm').action = `/users/${userId}/reset-password`;
                new bootstrap.Modal(document.getElementById('resetPwdModal')).show();
            }
        </script>
    @endpush

    {{-- ── Modal cài PIN chấm công ── --}}
    <div class="modal fade" id="setPinModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:360px;">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 px-4 pt-4 pb-3"
                    style="background:linear-gradient(135deg,#0B5E5A,#0EA5A0);">
                    <div>
                        <h6 class="modal-title text-white fw-bold mb-0">
                            <i class="bi bi-person-badge me-2"></i>Cài mã PIN chấm công
                        </h6>
                        <div class="text-white opacity-75 small mt-1" id="pinUserName"></div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="setPinForm" method="POST">
                    @csrf
                    <div class="modal-body px-4 py-4">
                        {{-- PIN hiện tại --}}
                        <div class="alert alert-light border py-2 px-3 small mb-3" id="currentPinInfo"
                            style="display:none;">
                            <i class="bi bi-info-circle me-1 text-info"></i>
                            Nhân viên này <strong>đã có PIN</strong>. Nhập PIN mới để thay thế.
                        </div>

                        <label class="form-label fw-semibold">Mã PIN mới <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="password" name="pin" id="pinInput"
                                class="form-control form-control-lg text-center fw-bold" maxlength="6" pattern="[0-9]{4,6}"
                                placeholder="4–6 chữ số" inputmode="numeric" autocomplete="off"
                                style="letter-spacing:10px;font-size:24px;">
                            <button type="button"
                                class="btn btn-sm btn-light position-absolute top-50 end-0 translate-middle-y me-2"
                                onclick="togglePinVis()" id="pinVisBtn" title="Hiện/ẩn PIN">
                                <i class="bi bi-eye" id="pinVisIcon"></i>
                            </button>
                        </div>
                        <div class="text-muted small mt-2 text-center">
                            Nhân viên dùng mã này để bấm chấm công tại quầy
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn fw-bold px-5 text-white"
                            style="background:linear-gradient(135deg,#0B5E5A,#0EA5A0);border-radius:8px;">
                            <i class="bi bi-check-circle-fill me-1"></i>Lưu PIN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function openSetPin(userId, userName) {
                document.getElementById('setPinForm').action = `/checkin/users/${userId}/set-pin`;
                document.getElementById('pinUserName').textContent = userName;
                document.getElementById('pinInput').value = '';
                document.getElementById('pinInput').type = 'password';
                document.getElementById('pinVisIcon').className = 'bi bi-eye';
                new bootstrap.Modal(document.getElementById('setPinModal')).show();
                setTimeout(() => document.getElementById('pinInput').focus(), 350);
            }

            function togglePinVis() {
                const inp = document.getElementById('pinInput');
                const icon = document.getElementById('pinVisIcon');
                if (inp.type === 'password') {
                    inp.type = 'text';
                    icon.className = 'bi bi-eye-slash';
                } else {
                    inp.type = 'password';
                    icon.className = 'bi bi-eye';
                }
            }

            // Chỉ cho nhập số
            document.getElementById('pinInput').addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '').slice(0, 6);
            });

            // Submit bằng AJAX để không reload trang
            document.getElementById('setPinForm').addEventListener('submit', async function (e) {
                e.preventDefault();
                const pin = document.getElementById('pinInput').value;
                if (pin.length < 4) {
                    alert('Mã PIN phải từ 4–6 chữ số!');
                    document.getElementById('pinInput').focus();
                    return;
                }

                const formData = new FormData(this);
                try {
                    const res = await fetch(this.action, { method: 'POST', body: formData });
                    const data = await res.json().catch(() => ({}));
                    if (res.ok) {
                        bootstrap.Modal.getInstance(document.getElementById('setPinModal')).hide();
                        // Toast thông báo
                        showToast('✅ Đã cài PIN thành công cho ' + document.getElementById('pinUserName').textContent);
                    } else {
                        alert(data.message || 'Lỗi khi cài PIN. Vui lòng thử lại.');
                    }
                } catch (err) {
                    alert('Lỗi kết nối: ' + err.message);
                }
            });

            function showToast(msg) {
                const t = document.createElement('div');
                t.textContent = msg;
                t.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#0EA5A0;color:#fff;' +
                    'padding:12px 20px;border-radius:12px;font-weight:600;font-size:14px;' +
                    'box-shadow:0 4px 16px rgba(0,0,0,.2);z-index:9999;transition:opacity .4s;';
                document.body.appendChild(t);
                setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 400); }, 2500);
            }
        </script>
    @endpush

@endsection