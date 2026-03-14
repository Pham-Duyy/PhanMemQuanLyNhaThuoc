@extends('layouts.app')
@section('title', 'Lô hàng: ' . $medicine->name)
@section('page-title', 'Lô hàng theo thuốc')

@section('content')

    <div class="page-header">
        <div>
            <h4 class="mb-0">
                <i class="bi bi-boxes me-2 text-primary"></i>
                Lô hàng: <span class="text-primary">{{ $medicine->name }}</span>
            </h4>
            <small class="text-muted">
                <code>{{ $medicine->code }}</code> ·
                {{ $medicine->category?->name ?? 'Chưa phân nhóm' }} ·
                Tồn kho: <strong class="{{ $medicine->total_stock == 0 ? 'text-danger' : 'text-success' }}">
                    {{ number_format($medicine->total_stock) }} {{ $medicine->unit }}
                </strong>
            </small>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()->hasPermission('inventory.adjust'))
                <a href="{{ route('inventory.adjust.create') }}" class="btn btn-outline-warning">
                    <i class="bi bi-sliders me-1"></i> Điều chỉnh kho
                </a>
            @endif
            <a href="{{ route('medicines.show', $medicine) }}" class="btn btn-outline-primary">
                <i class="bi bi-capsule me-1"></i> Xem thuốc
            </a>
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    {{-- Stats row --}}
    <div class="row g-3 mb-4">
        @php
            $activeBatches = $batches->where('is_active', true)->where('is_expired', false);
            $expiredBatches = $batches->where('is_expired', true);
            $totalValue = $batches->sum(fn($b) => $b->current_quantity * $b->purchase_price);
        @endphp
        <div class="col-sm-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f4fd;font-size:22px;">📦</div>
                <div>
                    <div class="stat-value">{{ $batches->count() }}</div>
                    <div class="stat-label">Tổng số lô</div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f8f0;font-size:22px;">✅</div>
                <div>
                    <div class="stat-value text-success">{{ $activeBatches->count() }}</div>
                    <div class="stat-label">Lô còn hạn</div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fdf0f0;font-size:22px;">❌</div>
                <div>
                    <div class="stat-value text-danger">{{ $expiredBatches->count() }}</div>
                    <div class="stat-label">Lô hết hạn</div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff8e1;font-size:22px;">💰</div>
                <div>
                    <div class="stat-value" style="font-size:16px;color:#D35400;">
                        {{ number_format($totalValue / 1000, 0, ',', '.') }}k đ
                    </div>
                    <div class="stat-label">Giá trị tồn kho</div>
                </div>
            </div>
        </div>
    </div>

    {{-- FEFO Info Banner --}}
    <div class="alert alert-info d-flex align-items-center gap-3 mb-3" role="alert">
        <span style="font-size:24px;">ℹ️</span>
        <div>
            <strong>Thứ tự xuất hàng FEFO:</strong>
            Khi bán, hệ thống tự động lấy từ lô có
            <strong>hạn dùng gần nhất trước</strong> (cột #).
            Các lô hết hạn hoặc hết hàng sẽ bị bỏ qua.
        </div>
    </div>

    {{-- Bảng lô hàng --}}
    <div class="card">
        <div class="card-header py-3 px-4 d-flex justify-content-between">
            <h6 class="mb-0 fw-bold">🗃️ Danh sách lô hàng — sắp xếp theo FEFO</h6>
            <small class="text-muted">Lô số 1 sẽ được xuất trước</small>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width:40px" class="text-center">
                            <span title="Thứ tự xuất FEFO">#</span>
                        </th>
                        <th>Số lô</th>
                        <th>Nhà cung cấp</th>
                        <th class="text-center">NSX</th>
                        <th class="text-center">HSD</th>
                        <th class="text-center">Còn lại</th>
                        <th class="text-center">Ban đầu</th>
                        <th class="text-end">Giá nhập</th>
                        <th class="text-end">Giá trị</th>
                        <th class="text-center">Trạng thái</th>
                        @if(auth()->user()->hasPermission('inventory.adjust'))
                            <th class="text-center">Điều chỉnh</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php $fefoOrder = 1; @endphp
                    @forelse($batches as $batch)
                        <tr class="
                            {{ $batch->is_expired ? 'table-danger' : '' }}
                            {{ !$batch->is_expired && $batch->expiry_status === 'critical' && $batch->current_quantity > 0 ? 'table-warning' : '' }}
                            {{ $batch->current_quantity == 0 ? 'opacity-50' : '' }}
                        ">
                            {{-- Thứ tự FEFO --}}
                            <td class="text-center">
                                @if(!$batch->is_expired && $batch->current_quantity > 0 && $batch->is_active)
                                    <span class="badge rounded-pill"
                                        style="background:#e8f4fd;color:#1B6FA8;border:1px solid #1B6FA840;">
                                        {{ $fefoOrder++ }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Số lô --}}
                            <td>
                                <code class="fw-bold text-primary">{{ $batch->batch_number }}</code>
                            </td>

                            {{-- NCC --}}
                            <td class="small text-muted">
                                {{ $batch->supplier?->name ?? '—' }}
                            </td>

                            {{-- NSX --}}
                            <td class="text-center small">
                                {{ $batch->manufacture_date?->format('d/m/Y') ?? '—' }}
                            </td>

                            {{-- HSD --}}
                            <td class="text-center">
                                <span class="badge bg-{{ $batch->expiry_badge_color }}">
                                    {{ $batch->expiry_date->format('d/m/Y') }}
                                </span>
                                <small class="d-block {{ $batch->days_until_expiry < 0 ? 'text-danger' : 'text-muted' }}">
                                    @if($batch->days_until_expiry < 0)
                                        Quá {{ abs($batch->days_until_expiry) }} ngày
                                    @else
                                        Còn {{ $batch->days_until_expiry }} ngày
                                    @endif
                                </small>
                            </td>

                            {{-- Tồn còn lại --}}
                            <td class="text-center">
                                <strong class="{{ $batch->current_quantity == 0 ? 'text-muted' : 'text-success' }}">
                                    {{ number_format($batch->current_quantity) }}
                                </strong>
                                <small class="d-block text-muted">{{ $medicine->unit }}</small>
                            </td>

                            {{-- Số lượng ban đầu --}}
                            <td class="text-center text-muted small">
                                {{ number_format($batch->initial_quantity) }}
                                <div style="font-size:10px;">
                                    Đã xuất: {{ number_format($batch->initial_quantity - $batch->current_quantity) }}
                                </div>
                            </td>

                            {{-- Giá nhập --}}
                            <td class="text-end small">
                                {{ number_format($batch->purchase_price, 0, ',', '.') }}đ
                            </td>

                            {{-- Giá trị tồn --}}
                            <td class="text-end fw-semibold">
                                {{ number_format($batch->stock_value, 0, ',', '.') }}đ
                            </td>

                            {{-- Trạng thái --}}
                            <td class="text-center">
                                @if($batch->is_expired)
                                    <span class="badge bg-danger">Hết hạn</span>
                                @elseif($batch->current_quantity == 0)
                                    <span class="badge bg-secondary">Hết hàng</span>
                                @elseif($batch->expiry_status === 'critical')
                                    <span class="badge bg-warning text-dark">Sắp hết hạn</span>
                                @elseif($batch->expiry_status === 'warning')
                                    <span class="badge bg-info text-dark">Cảnh báo</span>
                                @else
                                    <span class="badge bg-success">Tốt</span>
                                @endif
                            </td>

                            {{-- Điều chỉnh --}}
                            @if(auth()->user()->hasPermission('inventory.adjust'))
                                <td class="text-center">
                                    <a href="{{ route('inventory.adjust.create', ['batch_id' => $batch->id]) }}"
                                        class="btn btn-sm btn-outline-warning" title="Điều chỉnh lô này">
                                        <i class="bi bi-sliders"></i>
                                    </a>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                Chưa có lô hàng nào. Tạo đơn nhập hàng để bắt đầu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if($batches->isNotEmpty())
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="5" class="text-end px-4">Tổng tồn kho:</td>
                            <td class="text-center text-success fw-bold">
                                {{ number_format($batches->sum('current_quantity')) }}
                                {{ $medicine->unit }}
                            </td>
                            <td class="text-center text-muted">
                                {{ number_format($batches->sum('initial_quantity')) }}
                            </td>
                            <td></td>
                            <td class="text-end money">
                                {{ number_format($totalValue, 0, ',', '.') }}đ
                            </td>
                            <td colspan="{{ auth()->user()->hasPermission('inventory.adjust') ? 2 : 1 }}"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

@endsection