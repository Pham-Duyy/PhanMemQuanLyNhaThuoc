@extends('layouts.app')
@section('title', 'Cảnh báo hết hạn sử dụng')
@section('page-title', 'Cảnh báo HSD')

@section('content')

    <div class="page-header">
        <div>
            <h4 class="mb-0">
                <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i>Cảnh báo hết hạn sử dụng
            </h4>
            <small class="text-muted">Kiểm soát lô hàng theo chuẩn GPP — Thông tư 02/2018/TT-BYT</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ request()->fullUrlWithQuery(['export' => '1']) }}" class="btn btn-outline-success">
                <i class="bi bi-download me-1"></i> Xuất CSV
            </a>
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Quay lại kho
            </a>
        </div>
    </div>

    {{-- ── BỘ LỌC THỜI GIAN ──────────────────────────────────────────────── --}}
    <div class="card mb-4">
        <div class="card-body py-3 px-4">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <span class="fw-semibold text-muted small text-nowrap">
                    <i class="bi bi-funnel me-1"></i>Hiển thị lô hết hạn trong:
                </span>
                @foreach([7 => '7 ngày', 15 => '15 ngày', 30 => '30 ngày', 60 => '60 ngày', 90 => '90 ngày'] as $d => $lbl)
                    <a href="{{ route('inventory.expiring', ['days' => $d]) }}"
                        class="btn btn-sm {{ $days == $d ? 'btn-danger' : 'btn-outline-secondary' }} fw-semibold">
                        {{ $lbl }}
                    </a>
                @endforeach

                <div class="vr mx-1" style="height:28px;"></div>

                <span class="text-muted small">
                    <i class="bi bi-clock me-1"></i>
                    Cập nhật: {{ now()->format('H:i d/m/Y') }}
                </span>
            </div>
        </div>
    </div>

    {{-- ── STATS CARDS ──────────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">

        {{-- Khẩn cấp --}}
        <div class="col-lg-3 col-sm-6">
            <div class="card h-100" style="border-left:4px solid #C0392B;">
                <div class="card-body py-3 px-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small text-uppercase fw-semibold mb-1">🔴 Khẩn cấp</div>
                            <div class="fs-2 fw-bold text-danger">{{ $stats['critical'] }}</div>
                            <div class="text-muted small">Lô hết hạn ≤ 7 ngày</div>
                        </div>
                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                            style="width:48px;height:48px;background:#fdf0ef;font-size:22px;">🚨</div>
                    </div>
                    @if($stats['critical'] > 0)
                        <div class="mt-2 p-2 rounded-2" style="background:#fdf0ef;">
                            <small class="text-danger fw-semibold">⚡ Cần xử lý NGAY hôm nay!</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Cảnh báo --}}
        <div class="col-lg-3 col-sm-6">
            <div class="card h-100" style="border-left:4px solid #E67E22;">
                <div class="card-body py-3 px-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small text-uppercase fw-semibold mb-1">🟡 Cảnh báo</div>
                            <div class="fs-2 fw-bold text-warning">{{ $stats['warning'] }}</div>
                            <div class="text-muted small">Lô hết hạn 8–30 ngày</div>
                        </div>
                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                            style="width:48px;height:48px;background:#fef9ec;font-size:22px;">⚠️</div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">Ưu tiên bán trước (FEFO)</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Theo dõi --}}
        <div class="col-lg-3 col-sm-6">
            <div class="card h-100" style="border-left:4px solid #2E86C1;">
                <div class="card-body py-3 px-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small text-uppercase fw-semibold mb-1">🔵 Theo dõi</div>
                            <div class="fs-2 fw-bold text-info">{{ $stats['caution'] }}</div>
                            <div class="text-muted small">Lô hết hạn > 30 ngày</div>
                        </div>
                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                            style="width:48px;height:48px;background:#eaf4fb;font-size:22px;">📋</div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">Kiểm tra định kỳ hàng tuần</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Giá trị tồn --}}
        <div class="col-lg-3 col-sm-6">
            <div class="card h-100" style="border-left:4px solid #8E44AD;">
                <div class="card-body py-3 px-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small text-uppercase fw-semibold mb-1">💰 Giá trị tồn</div>
                            <div class="fs-4 fw-bold text-purple" style="color:#8E44AD;">
                                {{ number_format($stats['total_value'], 0, ',', '.') }}đ
                            </div>
                            <div class="text-muted small">{{ number_format($stats['total_qty']) }} đơn vị</div>
                        </div>
                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                            style="width:48px;height:48px;background:#f5eef8;font-size:22px;">💊</div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">Tổng giá trị trong {{ $days }} ngày</small>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── GỢI Ý XỬ LÝ (GPP) ──────────────────────────────────────────────── --}}
    @if($stats['critical'] > 0 || $stats['warning'] > 0)
        <div class="card mb-4 border-warning">
            <div class="card-header py-3 px-4" style="background:#fffbf0;border-bottom:1px solid #fde68a;">
                <h6 class="mb-0 fw-bold text-warning">
                    <i class="bi bi-lightbulb me-2"></i>Gợi ý xử lý theo chuẩn GPP
                </h6>
            </div>
            <div class="card-body px-4 py-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-flex gap-3 align-items-start p-3 rounded-3" style="background:#fdf0ef;">
                            <span style="font-size:28px;">🗑️</span>
                            <div>
                                <div class="fw-bold text-danger mb-1">Hủy lô hàng</div>
                                <div class="text-muted small" style="line-height:1.5;">
                                    Lô hết hạn hoặc hỏng không thể bán. Lập biên bản hủy,
                                    điều chỉnh tồn kho về 0, ghi lý do "Hủy thuốc hết hạn".
                                </div>
                                <a href="{{ route('inventory.adjust.create') }}?type=destroy"
                                    class="btn btn-sm btn-outline-danger mt-2">
                                    <i class="bi bi-sliders me-1"></i>Điều chỉnh tồn kho
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-3 align-items-start p-3 rounded-3" style="background:#fff8e1;">
                            <span style="font-size:28px;">↩️</span>
                            <div>
                                <div class="fw-bold text-warning mb-1">Trả lại nhà cung cấp</div>
                                <div class="text-muted small" style="line-height:1.5;">
                                    Thuốc còn hạn nhưng sắp hết — thương lượng trả NCC
                                    trước khi hết hạn để thu hồi vốn.
                                </div>
                                <a href="{{ route('purchase.index') }}" class="btn btn-sm btn-outline-warning mt-2">
                                    <i class="bi bi-truck me-1"></i>Xem đơn nhập hàng
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-3 align-items-start p-3 rounded-3" style="background:#e8f8f0;">
                            <span style="font-size:28px;">🏷️</span>
                            <div>
                                <div class="fw-bold text-success mb-1">Đẩy hàng / Xả kho</div>
                                <div class="text-muted small" style="line-height:1.5;">
                                    Ưu tiên bán lô sắp hết hạn trước (FEFO). Hệ thống
                                    tự động xuất lô gần hết hạn nhất khi bán hàng.
                                </div>
                                <a href="{{ route('invoices.create') }}" class="btn btn-sm btn-outline-success mt-2">
                                    <i class="bi bi-receipt me-1"></i>Tạo hóa đơn bán
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ── BẢNG CHI TIẾT LÔ ──────────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-table me-2"></i>
                Danh sách lô — {{ $batches->total() }} lô trong {{ $days }} ngày tới
            </h6>
            <span class="text-muted small">Sắp xếp theo ngày hết hạn gần nhất</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Tên thuốc</th>
                        <th>Danh mục</th>
                        <th class="text-center">Số lô</th>
                        <th class="text-center">Hạn dùng</th>
                        <th class="text-center">Còn lại</th>
                        <th class="text-center">Tồn kho</th>
                        <th class="text-end">Giá trị tồn</th>
                        <th class="text-center">Mức độ</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                        @php
                            $d = $batch->days_until_expiry;
                            $rowClass = $d <= 7 ? 'table-danger'
                                : ($d <= 30 ? 'table-warning' : '');
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td>
                                <div class="fw-semibold">{{ $batch->medicine?->name }}</div>
                                @if($batch->supplier?->name)
                                    <small class="text-muted">
                                        <i class="bi bi-building me-1"></i>{{ $batch->supplier->name }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $batch->medicine?->category?->name ?? '—' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <code class="text-primary fw-bold">{{ $batch->batch_number }}</code>
                            </td>
                            <td class="text-center fw-semibold">
                                {{ $batch->expiry_date->format('d/m/Y') }}
                                @if($d <= 7)
                                    <div><small class="text-danger">⚡ Sắp hết!</small></div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span
                                    class="badge fs-6 bg-{{ $d <= 7 ? 'danger' : ($d <= 30 ? 'warning text-dark' : 'info text-dark') }}">
                                    {{ $d }} ngày
                                </span>
                            </td>
                            <td class="text-center fw-bold">
                                {{ number_format($batch->current_quantity) }}
                                <small class="text-muted fw-normal">{{ $batch->medicine?->unit }}</small>
                            </td>
                            <td class="text-end fw-semibold">
                                @php $val = $batch->current_quantity * $batch->purchase_price; @endphp
                                <span class="{{ $val > 1000000 ? 'text-danger' : 'text-muted' }}">
                                    {{ number_format($val, 0, ',', '.') }}đ
                                </span>
                            </td>
                            <td class="text-center">
                                @if($d <= 7)
                                    <span class="badge bg-danger">🔴 Khẩn cấp</span>
                                @elseif($d <= 30)
                                    <span class="badge bg-warning text-dark">🟡 Cảnh báo</span>
                                @else
                                    <span class="badge bg-info text-dark">🔵 Theo dõi</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    {{-- Xem chi tiết lô --}}
                                    <a href="{{ route('inventory.batches', $batch->medicine_id) }}"
                                        class="btn btn-sm btn-outline-primary" title="Xem tất cả lô của thuốc này">
                                        <i class="bi bi-boxes"></i>
                                    </a>
                                    {{-- Điều chỉnh tồn --}}
                                    <a href="{{ route('inventory.adjust.create') }}?batch_id={{ $batch->id }}"
                                        class="btn btn-sm btn-outline-warning" title="Điều chỉnh tồn kho lô này">
                                        <i class="bi bi-sliders"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div style="font-size:48px;">✅</div>
                                <div class="fw-bold text-success mt-2">Tuyệt vời!</div>
                                <div class="text-muted">Không có lô nào hết hạn trong {{ $days }} ngày tới.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if($batches->isNotEmpty())
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="6" class="text-end fw-bold px-4">Tổng giá trị trang này:</td>
                            <td class="text-end fw-bold text-danger">
                                {{ number_format($batches->sum(fn($b) => $b->current_quantity * $b->purchase_price), 0, ',', '.') }}đ
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        @if($batches->hasPages())
            <div class="card-footer py-3 px-4">
                {{ $batches->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@endsection