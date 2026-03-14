@extends('layouts.app')

@section('title', $medicine->name)
@section('page-title', 'Chi tiết thuốc')

@section('content')

    <div class="page-header">
        <h4>
            <i class="bi bi-capsule me-2 text-primary"></i>
            {{ $medicine->name }}
            @if(!$medicine->is_active)
                <span class="badge bg-secondary ms-2">Ngừng bán</span>
            @endif
        </h4>
        <div class="d-flex gap-2">
            @if(auth()->user()->hasPermission('medicine.edit'))
                <a href="{{ route('medicines.edit', $medicine) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i> Chỉnh sửa
                </a>
            @endif
            <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row g-3">

        {{-- ── CỘT TRÁI: Thông tin thuốc ────────────────────────────────────── --}}
        <div class="col-lg-4">

            {{-- Thông tin cơ bản --}}
            <div class="card mb-3">
                <div class="card-header py-3 px-4">
                    <h6 class="mb-0 fw-bold">📋 Thông tin thuốc</h6>
                </div>
                <div class="card-body px-4 py-3">
                    @php
                        $rows = [
                            ['Mã thuốc', '<code class="text-primary fw-bold">' . $medicine->code . '</code>'],
                            ['Nhóm thuốc', $medicine->category?->name ?? '—'],
                            ['Hoạt chất', $medicine->generic_name ?? '—'],
                            ['Nhà sản xuất', $medicine->manufacturer ?? '—'],
                            ['Barcode', $medicine->barcode ?? '—'],
                            ['Số đăng ký', $medicine->registration_number ?? '—'],
                        ];
                    @endphp
                    @foreach($rows as [$label, $value])
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted small">{{ $label }}</span>
                            <span class="fw-semibold text-end" style="max-width:60%;">
                                {!! $value !!}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Tồn kho tổng quan --}}
            <div class="card mb-3">
                <div class="card-header py-3 px-4">
                    <h6 class="mb-0 fw-bold">📦 Tồn kho & Giá</h6>
                </div>
                <div class="card-body px-4 py-3">
                    @php $totalStock = $medicine->total_stock; @endphp

                    <div class="text-center py-3">
                        <div
                            class="display-5 fw-bold
                            {{ $totalStock == 0 ? 'text-danger' : ($medicine->is_low_stock ? 'text-warning' : 'text-success') }}">
                            {{ number_format($totalStock) }}
                        </div>
                        <div class="text-muted">{{ $medicine->unit }} tồn kho</div>
                        @if($medicine->min_stock > 0)
                            <small class="text-muted">Tối thiểu: {{ $medicine->min_stock }} {{ $medicine->unit }}</small>
                        @endif
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Giá bán lẻ</span>
                        <strong class="money">{{ number_format($medicine->sell_price, 0, ',', '.') }}đ</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Đơn vị đóng gói</span>
                        <span>{{ $medicine->package_unit ?? '—' }}
                            @if($medicine->units_per_package > 1)
                                ({{ $medicine->units_per_package }} {{ $medicine->unit }})
                            @endif
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Hạn sử dụng gần nhất</span>
                        <strong>{{ $medicine->nearest_expiry_date ?? '—' }}</strong>
                    </div>
                </div>
            </div>

            {{-- Phân loại --}}
            <div class="card mb-3">
                <div class="card-header py-3 px-4">
                    <h6 class="mb-0 fw-bold">⚕️ Phân loại GPP</h6>
                </div>
                <div class="card-body px-4">
                    <div class="d-flex flex-wrap gap-2">
                        @if($medicine->requires_prescription)
                            <span class="badge bg-danger fs-6 py-2 px-3">
                                <i class="bi bi-prescription2 me-1"></i>Thuốc kê đơn
                            </span>
                        @endif
                        @if($medicine->is_antibiotic)
                            <span class="badge bg-warning text-dark fs-6 py-2 px-3">
                                <i class="bi bi-shield-exclamation me-1"></i>Kháng sinh
                            </span>
                        @endif
                        @if($medicine->is_narcotic)
                            <span class="badge bg-dark fs-6 py-2 px-3">
                                <i class="bi bi-exclamation-octagon me-1"></i>Gây nghiện
                            </span>
                        @endif
                        @if(!$medicine->requires_prescription && !$medicine->is_antibiotic && !$medicine->is_narcotic)
                            <span class="badge bg-success fs-6 py-2 px-3">
                                <i class="bi bi-check-circle me-1"></i>OTC - Không kê đơn
                            </span>
                        @endif
                    </div>

                    @if($medicine->storage_instruction)
                        <div class="alert alert-light border mt-3 mb-0 py-2 px-3 small">
                            <i class="bi bi-info-circle text-primary me-1"></i>
                            <strong>Bảo quản:</strong> {{ $medicine->storage_instruction }}
                        </div>
                    @endif
                </div>
            </div>

            @if($medicine->description)
                <div class="card">
                    <div class="card-header py-3 px-4">
                        <h6 class="mb-0 fw-bold">📝 Mô tả</h6>
                    </div>
                    <div class="card-body px-4">
                        <p class="mb-0 small text-muted" style="white-space:pre-line;">{{ $medicine->description }}</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- ── CỘT PHẢI: Bảng lô hàng FEFO ─────────────────────────────────── --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center py-3 px-4">
                    <h6 class="mb-0 fw-bold">
                        🗃️ Danh sách lô hàng
                        <span class="badge bg-primary ms-1">{{ $medicine->batches->count() }} lô</span>
                    </h6>
                    <div class="d-flex gap-2">
                        @if(auth()->user()->hasPermission('inventory.adjust'))
                            <a href="{{ route('inventory.adjust.create', ['batch_id' => $medicine->batches->first()?->id]) }}"
                                class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-sliders me-1"></i> Điều chỉnh kho
                            </a>
                        @endif
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th style="width:30px">#</th>
                                <th>Số lô</th>
                                <th>Nhà cung cấp</th>
                                <th class="text-center">Sản xuất</th>
                                <th class="text-center">Hạn dùng</th>
                                <th class="text-end">Tồn kho</th>
                                <th class="text-end">Giá nhập</th>
                                <th class="text-center">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($medicine->batches as $i => $batch)
                                <tr class="
                                    {{ $batch->is_expired ? 'table-danger' : '' }}
                                    {{ !$batch->is_expired && $batch->expiry_status === 'critical' ? 'table-warning' : '' }}
                                ">
                                    {{-- Thứ tự xuất FEFO --}}
                                    <td class="text-muted small">
                                        @if(!$batch->is_expired && $batch->current_quantity > 0)
                                            <span class="badge bg-light text-dark border">{{ $i + 1 }}</span>
                                        @endif
                                    </td>

                                    {{-- Số lô --}}
                                    <td>
                                        <code class="fw-bold">{{ $batch->batch_number }}</code>
                                    </td>

                                    {{-- NCC --}}
                                    <td class="small text-muted">
                                        {{ $batch->supplier?->name ?? '—' }}
                                    </td>

                                    {{-- Ngày SX --}}
                                    <td class="text-center small">
                                        {{ $batch->manufacture_date?->format('d/m/Y') ?? '—' }}
                                    </td>

                                    {{-- Hạn dùng --}}
                                    <td class="text-center">
                                        <span class="badge bg-{{ $batch->expiry_badge_color }}">
                                            {{ $batch->expiry_date->format('d/m/Y') }}
                                        </span>
                                        <small class="d-block text-muted">
                                            @if($batch->days_until_expiry < 0)
                                                Đã hết {{ abs($batch->days_until_expiry) }} ngày
                                            @else
                                                Còn {{ $batch->days_until_expiry }} ngày
                                            @endif
                                        </small>
                                    </td>

                                    {{-- Tồn kho --}}
                                    <td class="text-end">
                                        <strong class="{{ $batch->current_quantity == 0 ? 'text-muted' : '' }}">
                                            {{ number_format($batch->current_quantity) }}
                                        </strong>
                                        <small class="text-muted d-block">
                                            / {{ number_format($batch->initial_quantity) }} ban đầu
                                        </small>
                                    </td>

                                    {{-- Giá nhập --}}
                                    <td class="text-end small">
                                        {{ number_format($batch->purchase_price, 0, ',', '.') }}đ
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
                                            <span class="badge bg-success">Còn hạn</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                        Chưa có lô hàng nào. Tạo đơn nhập hàng để bắt đầu.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        @if($medicine->batches->isNotEmpty())
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="5" class="text-end fw-bold px-4">Tổng tồn kho:</td>
                                    <td class="text-end fw-bold text-success">
                                        {{ number_format($medicine->total_stock) }} {{ $medicine->unit }}
                                    </td>
                                    <td class="text-end fw-bold">
                                        Giá trị:
                                        {{ number_format($medicine->batches->sum(fn($b) => $b->stock_value), 0, ',', '.') }}đ
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                {{-- Ghi chú FEFO --}}
                @if($medicine->batches->where('current_quantity', '>', 0)->where('is_expired', false)->count() > 1)
                    <div class="card-footer py-2 px-4">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1 text-primary"></i>
                            <strong>FEFO:</strong> Khi bán hàng, hệ thống tự động xuất từ lô
                            <strong>hạn dùng gần nhất trước</strong> (lô số 1 → 2 → ...).
                            Đây là yêu cầu bắt buộc theo chuẩn GPP.
                        </small>
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection