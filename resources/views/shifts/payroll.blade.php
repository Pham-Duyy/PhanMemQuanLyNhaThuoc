@extends('layouts.app')
@section('title', 'Bảng lương')
@section('page-title', 'Bảng lương nhân viên')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-x-circle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Bộ lọc: 2 form RIÊNG BIỆT, không lồng nhau ── --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3 px-4">
            <div class="d-flex gap-3 align-items-end flex-wrap">

                {{-- Form 1: XEM theo tháng (GET) --}}
                <form method="GET" action="{{ route('shifts.payroll') }}" class="d-flex gap-3 align-items-end">
                    <div>
                        <label class="form-label fw-semibold mb-1 small">Tháng</label>
                        <select name="month" class="form-select" style="width:110px;">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>Tháng {{ $m }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="form-label fw-semibold mb-1 small">Năm</label>
                        <select name="year" class="form-select" style="width:100px;">
                            @foreach([2024, 2025, 2026, 2027] as $y)
                                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>Xem
                    </button>
                </form>

                {{-- Form 2: TÍNH LẠI bảng lương (POST) — hoàn toàn tách riêng --}}
                <form id="form-generate" action="{{ route('shifts.generate-payroll') }}" method="POST">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <button type="submit" class="btn btn-warning fw-semibold"
                        onclick="return confirm('Tính lại bảng lương tháng {{ $month }}/{{ $year }}?\nDữ liệu cũ sẽ được ghi đè.')">
                        <i class="bi bi-calculator me-1"></i>Tính lại bảng lương
                    </button>
                </form>

            </div>
        </div>
    </div>

    {{-- ── Thẻ tổng kết ── --}}
    @if($payrolls->count() > 0)
        <div class="row g-3 mb-3">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fw-bold fs-2 text-primary">{{ $payrolls->count() }}</div>
                    <div class="text-muted small">Nhân viên</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fw-bold fs-2 text-success">
                        {{ number_format($payrolls->sum('net_salary') / 1_000_000, 1) }}M
                    </div>
                    <div class="text-muted small">Tổng thực lĩnh</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fw-bold fs-2 text-info">
                        {{ $payrolls->where('status', 'paid')->count() }}
                    </div>
                    <div class="text-muted small">Đã trả lương</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fw-bold fs-2 text-warning">
                        {{ $payrolls->where('status', 'draft')->count() }}
                    </div>
                    <div class="text-muted small">Chờ duyệt</div>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Bảng lương chi tiết ── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header py-3 px-4 border-0 bg-white d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold">💰 Bảng lương tháng {{ $month }}/{{ $year }}</h6>
            @if($payrolls->count() == 0)
                <span class="text-muted small">
                    Chưa có dữ liệu — nhấn <strong>"Tính lại bảng lương"</strong> để tạo.
                </span>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nhân viên</th>
                        <th class="text-center">Ca làm</th>
                        <th class="text-center">Giờ</th>
                        <th class="text-center">Vắng</th>
                        <th class="text-center">Trễ</th>
                        <th class="text-end">Lương cơ bản</th>
                        <th class="text-end">Lương ca</th>
                        <th class="text-end">Thưởng</th>
                        <th class="text-end fw-bold">Thực lĩnh</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $user)
                        @php $p = $payrolls->get($user->id); @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold small">{{ $user->name }}</div>
                                @if($user->position)
                                    <div class="text-muted" style="font-size:11px;">{{ $user->position }}</div>
                                @endif
                            </td>

                            @if($p)
                                <td class="text-center">{{ $p->total_shifts }}</td>
                                <td class="text-center">{{ $p->total_hours }}h</td>
                                <td class="text-center {{ $p->absent_days > 0 ? 'text-danger fw-bold' : '' }}">
                                    {{ $p->absent_days }}
                                </td>
                                <td class="text-center {{ $p->late_days > 0 ? 'text-warning fw-bold' : '' }}">
                                    {{ $p->late_days }}
                                </td>
                                <td class="text-end">
                                    {{ number_format($p->base_salary, 0, ',', '.') }}đ
                                </td>
                                <td class="text-end text-success fw-semibold">
                                    {{ number_format($p->shift_salary, 0, ',', '.') }}đ
                                </td>
                                <td class="text-end text-info">
                                    {{ number_format($p->bonus ?? 0, 0, ',', '.') }}đ
                                </td>
                                <td class="text-end fw-bold fs-6 text-success">
                                    {{ number_format($p->net_salary, 0, ',', '.') }}đ
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $p->status_color }}">{{ $p->status_label }}</span>
                                </td>
                                <td class="text-center">
                                    @if($p->status === 'draft')
                                        <form action="{{ route('shifts.confirm-payroll', $p) }}" method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="action" value="confirm">
                                            <button class="btn btn-sm btn-outline-primary py-0">Duyệt</button>
                                        </form>
                                    @elseif($p->status === 'confirmed')
                                        <form action="{{ route('shifts.confirm-payroll', $p) }}" method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="action" value="pay">
                                            <button class="btn btn-sm btn-success py-0">✅ Đã trả</button>
                                        </form>
                                    @else
                                        <span class="text-success small">💰 Đã trả</span>
                                    @endif
                                </td>
                            @else
                                {{-- Chưa tính bảng lương cho nhân viên này --}}
                                <td colspan="9" class="text-center text-muted small py-2">
                                    Chưa tính —
                                    <button type="button" class="btn btn-link btn-sm p-0 text-warning"
                                        onclick="document.getElementById('form-generate').submit()">
                                        Tính ngay
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-3 d-block mb-2 opacity-25"></i>
                                Không có nhân viên nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if($payrolls->count() > 0)
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="5" class="text-end">Tổng cộng:</td>
                            <td class="text-end">{{ number_format($payrolls->sum('base_salary'), 0, ',', '.') }}đ</td>
                            <td class="text-end text-success">{{ number_format($payrolls->sum('shift_salary'), 0, ',', '.') }}đ
                            </td>
                            <td class="text-end text-info">{{ number_format($payrolls->sum('bonus'), 0, ',', '.') }}đ</td>
                            <td class="text-end text-success fs-6">
                                {{ number_format($payrolls->sum('net_salary'), 0, ',', '.') }}đ</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection