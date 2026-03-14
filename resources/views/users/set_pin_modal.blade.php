{{--
INCLUDE partial này vào users/index.blade.php hoặc users/edit.blade.php:
@include('users.set_pin_modal', ['users' => $users])
--}}

{{-- Nút SET PIN trong bảng nhân viên --}}
{{-- Thêm vào cột action của bảng users: --}}
{{--
<button class="btn btn-sm btn-outline-info" onclick="openSetPin({{ $user->id }}, '{{ $user->name }}')">
    <i class="bi bi-key"></i> PIN
</button>
--}}

{{-- Modal --}}
<div class="modal fade" id="setPinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4" style="background:linear-gradient(135deg,#0B5E5A,#0EA5A0);">
                <div>
                    <h6 class="modal-title text-white fw-bold">
                        <i class="bi bi-key me-2"></i>Cài mã PIN chấm công
                    </h6>
                    <div class="text-white small opacity-75" id="pinUserName"></div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="setPinForm" method="POST">
                @csrf
                <div class="modal-body px-4 py-4">
                    <label class="form-label fw-semibold">Mã PIN mới (4–6 số)</label>
                    <input type="text" name="pin" id="pinInput"
                        class="form-control form-control-lg text-center fw-bold fs-3 letter-spacing-4" maxlength="6"
                        pattern="[0-9]{4,6}" placeholder="• • • •" inputmode="numeric" autocomplete="off"
                        style="letter-spacing:8px;">
                    <div class="text-muted small mt-2 text-center">
                        Nhân viên dùng mã này để chấm công tại quầy
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn fw-bold px-5 text-white" style="background:#0EA5A0;">
                        <i class="bi bi-check-circle-fill me-1"></i>Lưu PIN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openSetPin(userId, userName) {
        document.getElementById('setPinForm').action = `/checkin/users/${userId}/set-pin`;
        document.getElementById('pinUserName').textContent = userName;
        document.getElementById('pinInput').value = '';
        new bootstrap.Modal(document.getElementById('setPinModal')).show();
        setTimeout(() => document.getElementById('pinInput').focus(), 300);
    }
</script>