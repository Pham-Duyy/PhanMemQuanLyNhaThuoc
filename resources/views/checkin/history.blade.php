@extends('layouts.app')
@section('title','Lịch sử chấm công')
@section('page-title','Lịch sử chấm công')

@section('content')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3 px-4">
        <form method="GET" class="d-flex gap-3 align-items-end flex-wrap">
            <div>
                <label class="form-label fw-semibold mb-1 small">Ngày</label>
                <input type="date" name="date" class="form-control" value="{{ $date }}">
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Xem</button>
            <a href="{{ route('checkin.index') }}" class="btn btn-teal ms-auto"
               style="background:#0EA5A0;color:#fff;">
                <i class="bi bi-qr-code me-1"></i>Màn hình chấm công
            </a>
        </form>
    </div>
</div>

{{-- Tổng kết ngày --}}
<div class="row g-3 mb-3">
    @php
        $onTime  = $logs->where('late_minutes', 0)->where('status','!=','none')->count();
        $late    = $logs->where('late_minutes', '>', 0)->count();
        $working = $logs->where('status','checked_in')->count();
        $done    = $logs->where('status','checked_out')->count();
    @endphp
    @foreach([
        ['primary', $logs->count(), 'Tổng chấm công'],
        ['success', $onTime,  '✅ Đúng giờ'],
        ['warning', $late,    '⚠ Đi muộn'],
        ['info',    $working, '🟢 Đang làm'],
    ] as [$color,$val,$lbl])
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-2 text-{{ $color }}">{{ $val }}</div>
            <div class="text-muted small">{{ $lbl }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header py-3 px-4 border-0 bg-white d-flex justify-content-between">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-clock-history me-2"></i>
            Chấm công ngày {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nhân viên</th>
                    <th class="text-center">Ca làm</th>
                    <th class="text-center">Giờ vào</th>
                    <th class="text-center">Giờ ra</th>
                    <th class="text-center">Thực tế</th>
                    <th class="text-center">Đi muộn</th>
                    <th class="text-center">Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr style="{{ $log->late_minutes > 0 ? 'background:#FFFBEB;' : ($log->status==='checked_out' ? 'background:#F0FDF4;' : '') }}">
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                                 style="width:34px;height:34px;background:#0EA5A0;font-size:13px;flex-shrink:0;">
                                {{ strtoupper(mb_substr($log->user->name,0,1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold small">{{ $log->user->name }}</div>
                                @if($log->user->position)
                                <div class="text-muted" style="font-size:10px;">{{ $log->user->position }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        @if($log->assignment?->shift)
                        <span class="badge rounded-pill px-2"
                              style="background:{{ $log->assignment->shift->color }}20;
                                     color:{{ $log->assignment->shift->color }};
                                     border:1px solid {{ $log->assignment->shift->color }};">
                            {{ $log->assignment->shift->name }}
                        </span>
                        @else <span class="text-muted small">—</span> @endif
                    </td>
                    <td class="text-center fw-bold text-primary">
                        {{ $log->checkin_time ? substr($log->checkin_time,0,5) : '—' }}
                    </td>
                    <td class="text-center fw-bold text-secondary">
                        {{ $log->checkout_time ? substr($log->checkout_time,0,5) : '—' }}
                    </td>
                    <td class="text-center fw-semibold">
                        {{ $log->actual_hours ? $log->actual_hours.'h' : '—' }}
                    </td>
                    <td class="text-center">
                        @if($log->late_minutes > 0)
                        <span class="badge bg-warning text-dark">⚠ {{ $log->late_minutes }} phút</span>
                        @else
                        <span class="text-success small fw-bold">✅ Đúng giờ</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-{{ match($log->status) {
                            'checked_in'  => 'success',
                            'checked_out' => 'secondary',
                            default       => 'warning'
                        } }}">{{ $log->status_label }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>Không có dữ liệu chấm công
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection