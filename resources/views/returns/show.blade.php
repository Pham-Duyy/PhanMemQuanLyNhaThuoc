@extends('layouts.app')
@section('title', 'Phiếu trả: ' . $return->code)
@section('page-title', 'Chi tiết phiếu trả hàng')

@section('content')

    <div class="page-header">
        <div class="d-flex align-items-center gap-3">
            <h4 class="mb-0"><i class="bi bi-arrow-return-left me-2 text-warning"></i>{{ $return->code }}</h4>
            <span class="badge bg-success fs-6">✅ Hoàn tất</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('returns.print', $return) }}" target="_blank" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-1"></i>In phiếu
            </a>
            <a href="{{ route('returns.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Quay lại
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header py-3 px-4">
                    <h6 class="mb-0 fw-bold">📋 Thông tin phiếu trả</h6>
                </div>
                <div class="card-body px-4 py-3">
                    @php $rows = [
                        ['Mã phiếu trả', '<code class="fw-bold text-warning">' . $return->code . '</code>'],
                        ['Hóa đơn gốc', '<a href="' . route('invoices.show', $return->invoice_id) . '" class="text-primary fw-semibold">' . $return->invoice->code . '</a>'],
                        ['Khách hàng', $return->customer?->name ?? 'Khách lẻ'],
                        ['Ngày trả', $return->return_date->format('d/m/Y')],
                        ['Người lập', $return->createdBy?->name ?? '—'],
                        ['Hình thức HT', $return->refund_method_label],
                    ]; @endphp
                    @foreach($rows as [$label, $value])
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted small">{{ $label }}</span>
                            <span class="fw-semibold small text-end">{!! $value !!}</span>
                        </div>
                    @endforeach
                    @if($return->reason)
                        <div class="mt-3 p-3 rounded-2" style="background:#fff8e1;">
                            <small class="text-muted d-block mb-1">Lý do trả:</small>
                            <span class="small fw-semibold">{{ $return->reason }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card" style="border-left:4px solid #E74C3C;">
                <div class="card-body px-4 py-3 text-center">
                    <div class="text-muted small mb-1">Tổng tiền hoàn trả</div>
                    <div class="fw-bold fs-3 text-danger">
                        {{ number_format($return->refund_amount, 0, ',', '.') }}đ
                    </div>
                    <div class="text-muted small">{{ $return->items->count() }} sản phẩm</div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">

                                    <div class="card    ">


                                            <div class="card-header py-3 px-4">

                                                <h6     class
                        ="mb-0 fw-bold">💊 Sản phẩm đã trả</h6>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr>
                            <th>#</th><th>Sản phẩm</th><th class="text-center">SL trả</th>
                            <th class="text-end">Đơn giá</th><th class="text-end">Hoàn tiền</th>
                        </tr></thead>
                        <tbody>
                            @foreach($return->items as $i => $item)
                                <tr>
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $item->medicine?->name }}</div>
                                        @if($item->batch)
                                            <small class="text-muted">Lô: {{ $item->batch->batch_number }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold">{{ number_format($item->quantity) }} {{ $item->unit }}</td>
                                    <td class="text-end">{{ number_format($item->unit_price, 0, ',', '.') }}đ</td>
                                    <td class="text-end fw-bold text-danger">{{ number_format($item->total_amount, 0, ',', '.') }}đ</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end fw-bold px-4">Tổng hoàn tiền:</td>
                                <td class="text-end fw-bold fs-5 text-danger">{{ number_format($return->refund_amount, 0, ',', '.') }}đ</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection