@extends('layouts.app')
@section('title', 'Báo cáo chấm công')
@section('page-title', 'Chấm công tháng')

@section('content')
    {{-- Bộ lọc --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3 px-4">
            <form method="GET" class="d-flex gap-3 align-items-end flex-wrap">
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
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Xem</button>
                <a href="{{ route('shifts.payroll', ['month' => $month, 'year' => $year]) }}" class="btn btn-success ms-auto">
                    <i class="bi bi-cash-stack me-1"></i>Tính bảng lương
                </a>
            </form>
        </div>
    </div>

    {{-- Tổng kết --}}
    <div class="row g-3 mb-3">
        @php
            $totalCompleted = $summary->sum('completed');
            $totalAbsent = $summary->sum('absent');
            $totalWage = $summary->sum('total_wage');
        @endphp
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fw-bold fs-2 text-primary">{{ $summary->sum('total_shifts') }}</div>
                <div class="text-muted small">Tổng ca đã xếp</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fw-bold fs-2 text-success">{{ $totalCompleted }}</div>
                <div class="text-muted small">Ca hoàn thành</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fw-bold fs-2 text-danger">{{ $totalAbsent }}</div>
                <div class="text-muted small">Tổng vắng</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fw-bold fs-2 text-success">{{ number_format($totalWage / 1000000, 1) }}M</div>
                <div class="text-muted small">Tổng lương ca</div>
            </div>
        </div>
    </div>

    {{-- Bảng chấm công từng người --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center border-0 bg-white">
            <h6 class="mb-0 fw-bold">📋 Chấm công tháng {{ $month }}/{{ $year }}</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:200px;">Nhân viên</th>
                        <th class="text-center">Tổng ca</th>
                        <th class="text-center">✅ Hoàn thành</th>
                        <th class="text-center">⏰ Đi muộn</th>
                        <th class="text-center">❌ Vắng</th>
                        <th class="text-center">⏱ Giờ làm</th>
                        <th class="text-end">💰 Lương ca</th>
                        <th class="text-center">Tỷ lệ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summary as $row)
                        @php
                            $rate = $row['total_shifts'] > 0
                                ? round($row['completed'] / $row['total_shifts'] * 100)
                                : 0;
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                                        style="width:34px;height:34px;min-width:34px;background:#0EA5A0;font-size:13px;">
                                        {{ strtoupper(mb_substr($row['user']->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold small">{{ $row['user']->name }}</div>
                                        @if($row['user']->position)
                                            <div class="text-muted" style="font-size:10px;">{{ $row['user']->position }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-center fw-semibold">{{ $row['total_shifts'] }}</td>
                            <td class="text-center text-success fw-bold">{{ $row['completed'] }}</td>
                            <td class="text-center text-warning fw-bold">{{ $row['late'] }}</td>
                            <td class="text-center text-danger fw-bold">{{ $row['absent'] }}</td>
                            <td class="text-center">{{ $row['total_hours'] }}h</td>
                            <td class="text-end fw-bold text-success">{{ number_format($row['total_wage'], 0, ',', '.')}}đ</td>
                            <td class="text-center" style="width:120px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-fill" style="height:8px;">
                                        <div class="progress-bar {{ $rate >= 80 ? 'bg-success' : ($rate >= 60 ? 'bg-warning' : 'bg-danger') }}"
                                            style="width:{{ $rate }}%;"></div>
                                    </div>
                                    <span class="small fw-bold" style="width:30px;">{{ $rate }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">Không có dữ liệu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection