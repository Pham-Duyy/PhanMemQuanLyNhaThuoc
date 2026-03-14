@extends('layouts.app')
@section('title', 'Lô sắp hết hạn')
@section('page-title', 'Cảnh báo hết hạn')

@section('content')

    <div class="page-header">
        <div>
            <h4 class="mb-0"><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Lô sắp hết hạn</h4>
            <small class="text-muted">Các lô hàng cần xử lý trong thời gian tới</small>
        </div>
        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại kho
        </a>
    </div>

    {{-- Bộ lọc ngày --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="d-flex gap-3 align-items-center">
                <span class="fw-semibold text-muted small">Hiển thị lô hết hạn trong:</span>
                @foreach([7 => '7 ngày', 15 => '15 ngày', 30 => '30 ngày', 60 => '60 ngày', 90 => '90 ngày'] as $d => $lbl)
                    <a href="{{ route('inventory.expiring', ['days' => $d]) }}"
                        class="btn btn-sm {{ $days == $d ? 'btn-danger' : 'btn-outline-secondary' }}">
                        {{ $lbl }}
                    </a>
                @endforeach
            </form>
        </div>
    </div>

    {{-- Stats theo mức độ --}}
    @php
        $critical = $batches->filter(fn($b) => $b->days_until_expiry <= 7);
        $warning = $batches->filter(fn($b) => $b->days_until_expiry > 7 && $b->days_until_expiry <= 30);
        $caution = $batches->filter(fn($b) => $b->days_until_expiry > 30);
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card border-danger">
                <div class="card-body text-center py-3">
                    <div class="fs-2 fw-bold text-danger">{{ $critical->count() }}</div>
                    <div class="fw-semibold text-danger">🔴 Khẩn cấp (≤ 7 ngày)</div>
                    <small class="text-muted">Cần xử lý ngay</small>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-warning">
                <div class="card-body text-center py-3">
                    <div class="fs-2 fw-bold text-warning">{{ $warning->count() }}</div>
                    <div class="fw-semibold text-warning">🟡 Cảnh báo (8–30 ngày)</div>
                    <small class="text-muted">Ưu tiên bán trước</small>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-info">
                <div class="card-body text-center py-3">
                    <div class="fs-2 fw-bold text-info">{{ $caution->count() }}</div>
                    <div class="fw-semibold text-info">🔵 Theo dõi (> 30 ngày)</div>
                    <small class="text-muted">Theo dõi định kỳ</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Bảng --}}
    <div class="card">
        <div class="card-header py-3 px-4">
            <h6 class="mb-0 fw-bold">
                Tổng {{ $batches->total() }} lô trong {{ $days }} ngày tới
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Tên thuốc</th>
                        <th>Số lô</th>
                        <th class="text-center">Hạn dùng</th>
                        <th class="text-center">Còn lại</th>
                        <th class="text-center">Tồn kho</th>
                        <th class="text-end">Giá trị</th>
                        <th class="text-center">Mức độ</th>
                        <th class="text-center">Xử lý</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                        <tr
                            class="{{ $batch->days_until_expiry <= 7 ? 'table-danger' : ($batch->days_until_expiry <= 30 ? 'table-warning' : '') }}">
                            <td>
                                <div class="fw-semibold">{{ $batch->medicine?->name }}</div>
                                <small class="text-muted">{{ $batch->medicine?->category?->name }}</small>
                            </td>
                            <td><code class="text-primary">{{ $batch->batch_number }}</code></td>
                            <td class="text-center">
                                <strong>{{ $batch->expiry_date->format('d/m/Y') }}</strong>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $batch->expiry_badge_color }} fs-6">
                                    {{ $batch->days_until_expiry }} ngày
                                </span>
                            </td>
                            <td class="text-center fw-semibold">
                                {{ number_format($batch->current_quantity) }}
                                {{ $batch->medicine?->unit }}
                            </td>
                            <td class="text-end">
                                {{ number_format($batch->stock_value, 0, ',', '.') }}đ
                            </td>
                            <td class="text-center">
                                @if($batch->days_until_expiry <= 7)
                                    <span class="badge bg-danger">🔴 Khẩn cấp</span>
                                @elseif($batch->days_until_expiry <= 30)
                                    <span class="badge bg-warning text-dark">🟡 Cảnh báo</span>
                                @else
                                    <span class="badge bg-info text-dark">🔵 Theo dõi</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('inventory.batches', $batch->medicine_id) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-boxes"></i> Xem lô
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-check-circle text-success fs-1 d-block mb-2"></i>
                                Không có lô nào hết hạn trong {{ $days }} ngày tới. Tuyệt vời!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($batches->hasPages())
            <div class="card-footer py-3 px-4">
                {{ $batches->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@endsection