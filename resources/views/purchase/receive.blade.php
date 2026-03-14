@extends('layouts.app')
@section('title', 'Nhận hàng: ' . $purchaseOrder->code)
@section('page-title', 'Nhận hàng')

@section('content')

    <div class="page-header">
        <div>
            <h4 class="mb-0">
                <i class="bi bi-box-arrow-in-down me-2 text-warning"></i>
                Nhận hàng — {{ $purchaseOrder->code }}
            </h4>
            <small class="text-muted">
                Từ: <strong>{{ $purchaseOrder->supplier->name }}</strong>
                &nbsp;·&nbsp; Điền số lô & hạn dùng để tạo batch trong kho
            </small>
        </div>
        <a href="{{ route('purchase.show', $purchaseOrder) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    {{-- Cảnh báo quan trọng --}}
    <div class="alert alert-warning d-flex gap-3 align-items-start mb-4">
        <i class="bi bi-exclamation-triangle-fill fs-4 flex-shrink-0 mt-1"></i>
        <div>
            <strong>Lưu ý khi nhận hàng (GPP):</strong>
            Kiểm tra kỹ <strong>số lô, ngày sản xuất, hạn dùng</strong> trên thùng/hộp thuốc
            trước khi nhập vào hệ thống. Thông tin này không thể sửa sau khi lưu và là cơ sở
            để hệ thống thực hiện <strong>FEFO</strong> khi bán hàng.
        </div>
    </div>

    <form action="{{ route('purchase.receive', $purchaseOrder) }}" method="POST" id="receiveForm">
        @csrf

        <div class="card">
            <div class="card-header py-3 px-4">
                <h6 class="mb-0 fw-bold">
                    📦 Điền thông tin lô hàng
                    <span class="badge bg-primary ms-1">{{ $purchaseOrder->items->count() }} loại thuốc</span>
                </h6>
            </div>
            <div class="card-body p-0">
                @foreach($purchaseOrder->items as $i => $item)
                    <div class="p-4 {{ !$loop->last ? 'border-bottom' : '' }}">

                        {{-- Thông tin thuốc --}}
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                style="width:44px;height:44px;background:#e8f4fd;font-size:22px;">💊</div>
                            <div class="flex-grow-1">
                                <div class="fw-bold fs-6">{{ $item->medicine->name }}</div>
                                <div class="text-muted small">
                                    <code>{{ $item->medicine->code }}</code>
                                    &nbsp;·&nbsp; Đơn vị: {{ $item->unit }}
                                    &nbsp;·&nbsp; Đã đặt:
                                    <strong>{{ number_format($item->ordered_quantity) }} {{ $item->unit }}</strong>
                                    &nbsp;·&nbsp; Giá nhập:
                                    <strong>{{ number_format($item->purchase_price, 0, ',', '.') }}đ</strong>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-primary">
                                    {{ number_format($item->total_amount, 0, ',', '.') }}đ
                                </div>
                                <small class="text-muted">Thành tiền dự tính</small>
                            </div>
                        </div>

                        <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">
                                    Số lô (Batch) <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="items[{{ $i }}][batch_number]" class="form-control"
                                    placeholder="VD: AMX-2025-001" required>
                                <small class="text-muted">Ghi trên hộp/thùng thuốc</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">
                                    Ngày sản xuất
                                </label>
                                <input type="date" name="items[{{ $i }}][manufacture_date]" class="form-control"
                                    max="{{ today()->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">
                                    Hạn dùng <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="items[{{ $i }}][expiry_date]" class="form-control expiry-input"
                                    min="{{ today()->addDay()->format('Y-m-d') }}" required>
                                <small class="expiry-warning text-danger" style="display:none;">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Hạn dùng còn rất ngắn!
                                </small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">
                                    Số lượng thực nhận <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="items[{{ $i }}][received_quantity]" class="form-control"
                                        value="{{ $item->ordered_quantity }}" min="1" required>
                                    <span class="input-group-text">{{ $item->unit }}</span>
                                </div>
                                @if($item->ordered_quantity > 0)
                                    <small class="text-muted">Đặt: {{ $item->ordered_quantity }}</small>
                                @endif
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center py-3 px-4">
                <div>
                    <div class="fw-bold">Tổng giá trị đơn hàng:
                        <span class="text-primary">
                            {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}đ
                        </span>
                    </div>
                    <small class="text-muted">
                        Sau khi xác nhận, tồn kho sẽ được cập nhật ngay lập tức.
                    </small>
                </div>
                <button type="submit" class="btn btn-warning fw-bold px-4"
                    onclick="return confirm('Xác nhận nhận hàng?\n\nHệ thống sẽ:\n✅ Tạo lô hàng mới trong kho\n✅ Cập nhật tồn kho\n✅ Ghi công nợ nhà cung cấp')">
                    <i class="bi bi-box-arrow-in-down me-2"></i>
                    Xác nhận nhận hàng & Nhập kho
                </button>
            </div>
        </div>

    </form>

@endsection

@push('scripts')
    <script>
        // Cảnh báo nếu hạn dùng < 90 ngày
        document.querySelectorAll('.expiry-input').forEach(input => {
            input.addEventListener('change', function () {
                const days = (new Date(this.value) - new Date()) / 86400000;
                const warn = this.closest('.col-md-3').querySelector('.expiry-warning');
                if (days < 90 && days > 0) {
                    warn.textContent = `⚠️ Hạn dùng chỉ còn ${Math.round(days)} ngày!`;
                    warn.style.display = '';
                    this.classList.add('border-warning');
                } else {
                    warn.style.display = 'none';
                    this.classList.remove('border-warning');
                }
            });
        });
    </script>
@endpush