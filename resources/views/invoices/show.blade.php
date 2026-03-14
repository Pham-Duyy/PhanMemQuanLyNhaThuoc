@extends('layouts.app')
@section('title', 'Hóa đơn: ' . $invoice->code)
@section('page-title', 'Chi tiết hóa đơn')

@section('content')

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <h4 class="mb-0">
            <i class="bi bi-receipt me-2 text-primary"></i>{{ $invoice->code }}
        </h4>
        <span class="badge bg-{{ $invoice->status_color }} fs-6 py-2 px-3">
            {{ $invoice->status_label }}
        </span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('invoices.print', $invoice) }}" target="_blank"
           class="btn btn-outline-secondary">
            <i class="bi bi-printer me-1"></i> In hóa đơn
        </a>
        @if($invoice->canCancel() && auth()->user()->hasPermission('invoice.cancel'))
        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
            <i class="bi bi-x-circle me-1"></i> Hủy hóa đơn
        </button>
        @endif
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại
        </a>
    </div>
</div>

<div class="row g-3">

    {{-- Thông tin hóa đơn --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header py-3 px-4">
                <h6 class="mb-0 fw-bold">📋 Thông tin hóa đơn</h6>
            </div>
            <div class="card-body px-4 py-3">
                @php $rows = [
                    ['Mã hóa đơn',   '<code class="fw-bold text-primary">' . $invoice->code . '</code>'],
                    ['Thời gian',     $invoice->invoice_date->format('d/m/Y H:i:s')],
                    ['Khách hàng',    $invoice->customer?->name ?? 'Khách lẻ'],
                    ['Nhân viên',     $invoice->createdBy?->name ?? '—'],
                    ['Thanh toán',    $invoice->payment_method_label],
                    ['Mã đơn thuốc', $invoice->prescription_code ?? '—'],
                ]; @endphp
                @foreach($rows as [$l, $v])
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted small">{{ $l }}</span>
                    <span class="fw-semibold small text-end">{!! $v !!}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header py-3 px-4">
                <h6 class="mb-0 fw-bold">💰 Thanh toán</h6>
            </div>
            <div class="card-body px-4 py-3">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Tổng tiền hàng</span>
                    <strong>{{ number_format($invoice->subtotal, 0, ',', '.') }}đ</strong>
                </div>
                @if($invoice->discount_amount > 0)
                <div class="d-flex justify-content-between py-2 border-bottom text-success">
                    <span>Giảm giá</span>
                    <strong>-{{ number_format($invoice->discount_amount, 0, ',', '.') }}đ</strong>
                </div>
                @endif
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="fw-bold">Tổng cộng</span>
                    <strong class="text-primary">{{ number_format($invoice->total_amount, 0, ',', '.') }}đ</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Khách trả</span>
                    <strong class="text-success">{{ number_format($invoice->paid_amount, 0, ',', '.') }}đ</strong>
                </div>
                @if($invoice->change_amount > 0)
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Tiền thối</span>
                    <strong>{{ number_format($invoice->change_amount, 0, ',', '.') }}đ</strong>
                </div>
                @endif
                @if($invoice->debt_amount > 0)
                <div class="d-flex justify-content-between py-2">
                    <span class="text-danger">Còn nợ</span>
                    <strong class="text-danger">{{ number_format($invoice->debt_amount, 0, ',', '.') }}đ</strong>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Chi tiết sản phẩm --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header py-3 px-4">
                <h6 class="mb-0 fw-bold">
                    💊 Chi tiết sản phẩm
                    <span class="badge bg-primary ms-1">{{ $invoice->items->count() }} dòng</span>
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tên thuốc</th>
                            <th class="text-center">Số lô</th>
                            <th class="text-center">Hạn dùng</th>
                            <th class="text-center">SL</th>
                            <th class="text-end">Đơn giá</th>
                            <th class="text-end">Thành tiền</th>
                            <th class="text-end">Lãi gộp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $i => $item)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $item->medicine?->name }}</div>
                                <small class="text-muted">{{ $item->medicine?->unit }}</small>
                            </td>
                            <td class="text-center"><code>{{ $item->batch_number ?? '—' }}</code></td>
                            <td class="text-center small">
                                {{ $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('d/m/Y') : '—' }}
                            </td>
                            <td class="text-center fw-bold">{{ $item->quantity }}</td>
                            <td class="text-end">{{ number_format($item->sell_price, 0, ',', '.') }}đ</td>
                            <td class="text-end money fw-bold">
                                {{ number_format($item->total_amount, 0, ',', '.') }}đ
                            </td>
                            <td class="text-end text-success small">
                                +{{ number_format($item->gross_profit, 0, ',', '.') }}đ
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="6" class="text-end fw-bold px-4">Tổng:</td>
                            <td class="text-end fw-bold text-primary">
                                {{ number_format($invoice->total_amount, 0, ',', '.') }}đ
                            </td>
                            <td class="text-end text-success fw-bold small">
                                +{{ number_format($invoice->gross_profit, 0, ',', '.') }}đ
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if($invoice->status === 'cancelled')
        <div class="alert alert-danger mt-3">
            <i class="bi bi-x-octagon me-2"></i>
            <strong>Hóa đơn đã bị hủy</strong>
            @if($invoice->cancel_reason)
                — Lý do: {{ $invoice->cancel_reason }}
            @endif
            <div class="text-muted small mt-1">
                Hủy lúc {{ $invoice->cancelled_at?->format('d/m/Y H:i') }}
                bởi {{ $invoice->cancelledBy?->name ?? '—' }}
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Modal hủy hóa đơn --}}
@if($invoice->canCancel())
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('invoices.cancel', $invoice) }}" method="POST">
                @csrf @method('PATCH')
                <div class="modal-header" style="background:#fdf0f0;border-bottom:2px solid #e74c3c30;">
                    <h5 class="modal-title fw-bold text-danger">
                        ⚠️ Hủy hóa đơn {{ $invoice->code }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <div class="alert alert-warning small">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Hủy hóa đơn sẽ <strong>hoàn trả tồn kho</strong> về đúng lô hàng gốc.
                        Hành động này <strong>không thể hoàn tác</strong>.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Lý do hủy <span class="text-danger">*</span>
                        </label>
                        <textarea name="cancel_reason" class="form-control" rows="3"
                                  placeholder="Nhập lý do hủy hóa đơn..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-danger fw-semibold">
                        <i class="bi bi-x-circle me-1"></i>Xác nhận hủy hóa đơn
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection