@extends('layouts.app')
@section('title', 'Quản lý ca làm việc')
@section('page-title', 'Quản lý ca làm việc')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i
                class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}<button type="button" class="btn-close"
                data-bs-dismiss="alert"></button></div>
    @endif

    <div class="page-header">
        <h5 class="mb-0"><i class="bi bi-clock me-2 text-primary"></i>Các ca làm việc</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addShiftModal">
            <i class="bi bi-plus-circle me-1"></i>Thêm ca mới
        </button>
    </div>

    <div class="row g-3">
        @forelse($shifts as $shift)
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm" style="border-left:5px solid {{ $shift->color }}!important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge rounded-pill px-3 py-2 fw-bold"
                                    style="background:{{ $shift->color }};font-size:14px;">
                                    {{ $shift->name }}
                                </span>
                            </div>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-secondary"
                                    onclick="editShift({{ $shift->id }},'{{ $shift->name }}','{{ substr($shift->start_time, 0, 5) }}','{{ substr($shift->end_time, 0, 5) }}','{{ number_format($shift->shift_wage, 0, ',', '.') }}','{{ $shift->color }}')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-3">
                            <i class="bi bi-clock text-muted"></i>
                            <span class="fw-semibold fs-5">{{ substr($shift->start_time, 0, 5) }} –
                                {{ substr($shift->end_time, 0, 5) }}</span>
                            <span class="badge bg-light text-muted border">{{ $shift->hours }}h</span>
                        </div>
                        <div class="mt-2 d-flex justify-content-between">
                            <span class="text-muted small">Lương/ca:</span>
                            <span class="fw-bold text-success">{{ number_format($shift->shift_wage, 0, ',', '.')}}đ</span>
                        </div>
                        <div class="mt-1 d-flex justify-content-between">
                            <span class="text-muted small">Đã dùng tháng này:</span>
                            <span class="fw-semibold">{{ $shift->total_used }} ca</span>
                        </div>
                        @if($shift->note)
                            <div class="mt-2 text-muted small border-top pt-2">{{ $shift->note }}</div>
                        @endif
                        <div class="mt-2">
                            <span
                                class="badge {{ $shift->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                {{ $shift->is_active ? '✅ Đang dùng' : '⏸ Tạm dừng' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm text-center py-5">
                    <i class="bi bi-clock-history fs-1 text-muted d-block mb-3"></i>
                    <p class="text-muted">Chưa có ca làm việc nào. Hãy tạo ca đầu tiên!</p>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-3 d-flex gap-2">
        <a href="{{ route('shifts.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-calendar-week me-1"></i>Xem lịch tuần
        </a>
        <a href="{{ route('shifts.attendance') }}" class="btn btn-outline-secondary">
            <i class="bi bi-person-check me-1"></i>Chấm công
        </a>
    </div>

    {{-- Modal thêm ca --}}
    <div class="modal fade" id="addShiftModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 px-4 pt-4" style="background:linear-gradient(135deg,#0B5E5A,#0EA5A0);">
                    <h5 class="modal-title text-white fw-bold"><i class="bi bi-plus-circle me-2"></i>Thêm ca mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('shifts.store-shift') }}" method="POST">
                    @csrf
                    <div class="modal-body px-4 py-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tên ca <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="VD: Ca Sáng" required>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Giờ bắt đầu</label>
                                <input type="time" name="start_time" class="form-control" value="07:00" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Giờ kết thúc</label>
                                <input type="time" name="end_time" class="form-control" value="12:00" required>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-8">
                                <label class="form-label fw-semibold">Lương/ca (đ)</label>
                                <input type="number" name="shift_wage" class="form-control" value="150000" min="0"
                                    step="10000">
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-semibold">Màu sắc</label>
                                <input type="color" name="color" class="form-control form-control-color w-100"
                                    value="#0EA5A0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Ghi chú</label>
                            <input type="text" name="note" class="form-control" placeholder="Tùy chọn...">
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary fw-bold px-5">
                            <i class="bi bi-check-circle-fill me-1"></i>Lưu ca
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal sửa ca --}}
    <div class="modal fade" id="editShiftModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 px-4 pt-4 bg-warning-subtle">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2"></i>Chỉnh sửa ca</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editShiftForm" method="POST">
                    @csrf @method('PATCH')
                    <div class="modal-body px-4 py-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tên ca</label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Giờ bắt đầu</label>
                                <input type="time" name="start_time" id="editStart" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Giờ kết thúc</label>
                                <input type="time" name="end_time" id="editEnd" class="form-control" required>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-8">
                                <label class="form-label fw-semibold">Lương/ca (đ)</label>
                                <input type="number" name="shift_wage" id="editWage" class="form-control" min="0"
                                    step="10000">
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-semibold">Màu sắc</label>
                                <input type="color" name="color" id="editColor"
                                    class="form-control form-control-color w-100">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-warning fw-bold px-5">Cập nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        function editShift(id, name, start, end, wage, color) {
            document.getElementById('editShiftForm').action = `/shifts/manage/${id}`;
            document.getElementById('editName').value = name;
            document.getElementById('editStart').value = start;
            document.getElementById('editEnd').value = end;
            document.getElementById('editWage').value = wage.replace(/\./g, '');
            document.getElementById('editColor').value = color;
            new bootstrap.Modal(document.getElementById('editShiftModal')).show();
        }
    </script>
@endpush