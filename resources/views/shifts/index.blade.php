@extends('layouts.app')
@section('title', 'Lịch phân ca tuần')
@section('page-title', 'Lịch phân ca')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i
                class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}<button type="button" class="btn-close"
    data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><i
                class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}<button type="button" class="btn-close"
    data-bs-dismiss="alert"></button></div>@endif

    {{-- Toolbar --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3 px-4">
            <div class="d-flex flex-wrap gap-3 align-items-center">
                {{-- Điều hướng tuần --}}
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('shifts.index', ['week' => $weekStart->copy()->subWeek()->format('Y-m-d')]) }}"
                        class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-left"></i></a>
                    <span class="fw-bold px-2" style="min-width:200px;text-align:center;">
                        {{ $weekStart->format('d/m') }} – {{ $weekEnd->format('d/m/Y') }}
                    </span>
                    <a href="{{ route('shifts.index', ['week' => $weekStart->copy()->addWeek()->format('Y-m-d')]) }}"
                        class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-right"></i></a>
                    <a href="{{ route('shifts.index') }}" class="btn btn-sm btn-outline-primary ms-1">Tuần này</a>
                </div>

                {{-- Thống kê hôm nay --}}
                <div class="d-flex gap-3 ms-auto">
                    <div class="text-center px-3 border-start">
                        <div class="fw-bold text-primary fs-5">{{ $todayStats->sum() }}</div>
                        <div class="text-muted" style="font-size:11px;">Ca hôm nay</div>
                    </div>
                    <div class="text-center px-3 border-start">
                        <div class="fw-bold text-success fs-5">{{ $todayStats->get('completed', 0) }}</div>
                        <div class="text-muted" style="font-size:11px;">Hoàn thành</div>
                    </div>
                    <div class="text-center px-3 border-start">
                        <div class="fw-bold text-danger fs-5">{{ $todayStats->get('absent', 0) }}</div>
                        <div class="text-muted" style="font-size:11px;">Vắng</div>
                    </div>
                </div>

                {{-- Xếp tự động --}}
                <button class="btn btn-teal fw-semibold" data-bs-toggle="modal" data-bs-target="#autoModal"
                    style="background:#0EA5A0;color:#fff;">
                    <i class="bi bi-magic me-1"></i>Xếp tự động
                </button>
            </div>
        </div>
    </div>

    {{-- Bảng lịch tuần --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0" style="min-width:900px;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="width:160px;padding:12px 16px;" class="border-end">
                            <div class="text-muted small fw-bold text-uppercase">Nhân viên</div>
                        </th>
                        @for($d = 0; $d < 7; $d++)
                            @php $day = $weekStart->copy()->addDays($d);
                            $isToday = $day->isToday(); @endphp
                            <th class="text-center {{ $isToday ? 'bg-primary-subtle' : '' }}" style="padding:12px 8px;">
                                <div class="fw-bold {{ $isToday ? 'text-primary' : '' }}" style="font-size:13px;">
                                    {{ ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'][$d] }}
                                </div>
                                <div class="small {{ $isToday ? 'text-primary fw-bold' : 'text-muted' }}">
                                    {{ $day->format('d/m') }}
                                </div>
                                @if($isToday)
                                <div class="badge bg-primary mt-1" style="font-size:9px;">Hôm nay</div>@endif
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $user)
                        <tr class="align-middle">
                            <td class="border-end px-3 py-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                                        style="width:34px;height:34px;min-width:34px;background:#0EA5A0;font-size:13px;">
                                        {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold" style="font-size:13px;">{{ $user->name }}</div>
                                        @if($user->position)
                                            <div class="text-muted" style="font-size:10px;">{{ $user->position }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            @for($d = 0; $d < 7; $d++)
                                @php
                                    $day = $weekStart->copy()->addDays($d);
                                    $key = $user->id . '_' . $day->format('Y-m-d');
                                    $dayAssignments = $assignments->get($key, collect());
                                    $isToday = $day->isToday();
                                @endphp
                                <td class="text-center py-2 {{ $isToday ? 'bg-primary-subtle' : '' }}"
                                    style="vertical-align:top;min-width:100px;" ondragover="event.preventDefault()"
                                    ondrop="dropAssign(event, {{ $user->id }}, '{{ $day->format('Y-m-d') }}')">

                                    {{-- Ca đã xếp --}}
                                    @foreach($dayAssignments as $a)
                                        <div class="shift-badge mb-1 d-flex align-items-center justify-content-between px-2 py-1 rounded-2"
                                            style="background:{{ $a->shift->color }}20;border:1px solid {{ $a->shift->color }};font-size:11px;cursor:pointer;"
                                            onclick="updateStatus({{ $a->id }}, '{{ $a->status }}')">
                                            <span class="fw-bold" style="color:{{ $a->shift->color }}">{{ $a->shift->name }}</span>
                                            <span class="ms-1">
                                                @if($a->status === 'completed')✅
                                                @elseif($a->status === 'absent')❌
                                                @elseif($a->status === 'late')⏰
                                                    @elseif($a->status==='confirmed')👍
                                                @else ·
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach

                                    {{-- Nút thêm ca --}}
                                    @if(auth()->user()->hasPermission('user.edit'))
                                        <button class="btn btn-sm w-100 mt-1 add-shift-btn"
                                            style="border:1px dashed #CBD5E1;background:transparent;color:#94A3B8;font-size:11px;padding:3px;"
                                            onclick="openAssign({{ $user->id }}, '{{ $user->name }}', '{{ $day->format('Y-m-d') }}', '{{ $day->format('d/m') }}')">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-2 d-block mb-2"></i>Chưa có nhân viên nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Legend --}}
    <div class="d-flex gap-3 mt-3 flex-wrap">
        @foreach($shifts as $s)
            <span class="badge px-3 py-2 rounded-pill"
                style="background:{{ $s->color }}20;border:1px solid {{ $s->color }};color:{{ $s->color }};font-size:12px;">
                {{ $s->name }} {{ substr($s->start_time, 0, 5) }}–{{ substr($s->end_time, 0, 5) }}
            </span>
        @endforeach
        <a href="{{ route('shifts.manage') }}" class="btn btn-sm btn-outline-secondary ms-auto">
            <i class="bi bi-gear me-1"></i>Quản lý ca
        </a>
    </div>

    {{-- Modal xếp ca --}}
    <div class="modal fade" id="assignModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 px-4 pt-4" style="background:linear-gradient(135deg,#0B5E5A,#0EA5A0);">
                    <div>
                        <h6 class="modal-title text-white fw-bold mb-0">Xếp ca làm việc</h6>
                        <div class="text-white small opacity-75" id="assignSubtitle"></div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('shifts.assign') }}" method="POST">
                    @csrf
                    <input type="hidden" name="user_id" id="assignUserId">
                    <input type="hidden" name="work_date" id="assignDate">
                    <div class="modal-body px-4 py-4">
                        <label class="form-label fw-semibold mb-3">Chọn ca <span class="text-danger">*</span></label>
                        <div class="d-flex flex-column gap-2" id="shiftOptions">
                            @foreach($shifts as $s)
                                <label class="shift-option d-flex align-items-center gap-3 p-3 rounded-3 border"
                                    style="cursor:pointer;transition:all .15s;">
                                    <input type="radio" name="shift_id" value="{{ $s->id }}" class="d-none" required>
                                    <div class="rounded-circle"
                                        style="width:14px;height:14px;background:{{ $s->color }};flex-shrink:0;"></div>
                                    <div class="flex-fill">
                                        <div class="fw-bold">{{ $s->name }}</div>
                                        <div class="small text-muted">{{ substr($s->start_time, 0, 5) }} –
                                            {{ substr($s->end_time, 0, 5) }} · {{ $s->hours }}h</div>
                                    </div>
                                    <div class="text-success fw-bold small">{{ number_format($s->shift_wage, 0, ',', '.')}}đ</div>
                                </label>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <label class="form-label fw-semibold">Ghi chú</label>
                            <input type="text" name="note" class="form-control" placeholder="Tùy chọn...">
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary fw-bold px-5">
                            <i class="bi bi-check-circle-fill me-1"></i>Xếp ca
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal cập nhật trạng thái ca --}}
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 px-4 pt-4 bg-light">
                    <h6 class="modal-title fw-bold">Cập nhật trạng thái ca</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="statusForm" method="POST">
                    @csrf @method('PATCH')
                    <div class="modal-body px-4">
                        <div class="d-flex flex-column gap-2">
                            @foreach(['scheduled' => ['secondary', '·', 'Đã xếp'], 'confirmed' => ['primary', '👍', 'Xác nhận'], 'completed' => ['success', '✅', 'Hoàn thành'], 'late' => ['warning', '⏰', 'Đi muộn'], 'absent' => ['danger', '❌', 'Vắng mặt']] as $val => [$color, $ico, $lbl])
                                <label class="d-flex align-items-center gap-3 p-2 rounded-3 border" style="cursor:pointer;">
                                    <input type="radio" name="status" value="{{ $val }}" class="form-check-input mt-0">
                                    <span class="badge bg-{{ $color }} px-2">{{ $ico }} {{ $lbl }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                        <button type="button" class="btn btn-light flex-fill" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary fw-bold flex-fill">Cập nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal xếp tự động --}}
    <div class="modal fade" id="autoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 px-4 pt-4" style="background:linear-gradient(135deg,#7C3AED,#A78BFA);">
                    <h6 class="modal-title text-white fw-bold"><i class="bi bi-magic me-2"></i>Xếp ca tự động</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('shifts.auto-assign') }}" method="POST">
                    @csrf
                    <div class="modal-body px-4 py-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tuần cần xếp</label>
                            <input type="date" name="week" class="form-control" value="{{ $weekStart->format('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold mb-2">Kiểu xếp ca</label>
                            <div class="d-flex flex-column gap-2">
                                <label class="d-flex gap-3 p-3 border rounded-3" style="cursor:pointer;">
                                    <input type="radio" name="pattern" value="rotate" checked class="form-check-input mt-0">
                                    <div>
                                        <div class="fw-semibold">🔄 Xoay ca</div>
                                        <div class="text-muted small">Mỗi ngày xoay ca tiếp theo, đảm bảo công bằng</div>
                                    </div>
                                </label>
                                <label class="d-flex gap-3 p-3 border rounded-3" style="cursor:pointer;">
                                    <input type="radio" name="pattern" value="fixed" class="form-check-input mt-0">
                                    <div>
                                        <div class="fw-semibold">📌 Cố định</div>
                                        <div class="text-muted small">Mỗi nhân viên luôn làm cùng 1 ca cả tuần</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="alert alert-info py-2 small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Chỉ xếp những ô chưa có ca. Ca đã xếp sẽ không bị thay đổi.
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn fw-bold px-5 text-white" style="background:#7C3AED;">
                            <i class="bi bi-magic me-1"></i>Xếp tự động
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openAssign(userId, userName, date, dateLabel) {
            document.getElementById('assignUserId').value = userId;
            document.getElementById('assignDate').value = date;
            document.getElementById('assignSubtitle').textContent = userName + ' — ' + dateLabel;
            // Reset selections
            document.querySelectorAll('.shift-option').forEach(el => {
                el.style.background = '';
                el.style.borderColor = '';
                el.querySelector('input').checked = false;
            });
            new bootstrap.Modal(document.getElementById('assignModal')).show();
        }

        // Highlight selected shift option
        document.addEventListener('change', e => {
            if (e.target.name === 'shift_id') {
                document.querySelectorAll('.shift-option').forEach(el => {
                    el.style.background = '';
                    el.style.borderColor = '#E2E8F0';
                });
                e.target.closest('.shift-option').style.background = '#F0FDF9';
                e.target.closest('.shift-option').style.borderColor = '#0EA5A0';
            }
        });

        function updateStatus(assignmentId, currentStatus) {
            const form = document.getElementById('statusForm');
            form.action = `/shifts/${assignmentId}/status`;
            const radios = form.querySelectorAll('input[name=status]');
            radios.forEach(r => r.checked = (r.value === currentStatus));
            new bootstrap.Modal(document.getElementById('statusModal')).show();
        }
    </script>
@endpush