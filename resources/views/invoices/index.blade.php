@extends('layouts.app')
@section('title', 'Hóa đơn bán hàng')
@section('page-title', 'Hóa đơn bán hàng')

@section('content')

    <div class="page-header">
        <div>
            <h4 class="mb-0"><i class="bi bi-receipt me-2 text-primary"></i>Hóa đơn bán hàng</h4>
            <small class="text-muted">Lịch sử tất cả giao dịch bán hàng</small>
        </div>
        @if(auth()->user()->hasPermission('invoice.create'))
            <a href="{{ route('invoices.pos') }}" class="btn btn-success">
                <i class="bi bi-cart3 me-1"></i> Bán hàng mới (POS)
            </a>
        @endif
    </div>

    {{-- Bộ lọc --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Tìm mã hóa đơn</label>
                    <input type="text" name="search" class="form-control" placeholder="VD: INV-20250101-0001"
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả --</option>
                        @foreach(['completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'] as $v => $l)
                            <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Từ ngày</label>
                    <input type="date" name="from" class="form-control"
                        value="{{ request('from', today()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Đến ngày</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to', today()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-funnel"></i> Lọc
                    </button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Mã hóa đơn</th>
                        <th>Khách hàng</th>
                        <th class="text-center">Thời gian</th>
                        <th class="text-end">Tổng tiền</th>
                        <th class="text-end">Đã thu</th>
                        <th class="text-end">Còn nợ</th>
                        <th class="text-center">Thanh toán</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center" style="width:100px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td>
                                <a href="{{ route('invoices.show', $inv) }}" class="fw-bold text-primary text-decoration-none">
                                    {{ $inv->code }}
                                </a>
                            </td>
                            <td>{{ $inv->customer?->name ?? 'Khách lẻ' }}</td>
                            <td class="text-center small text-muted">
                                {{ $inv->invoice_date->format('d/m/Y H:i') }}
                                <small class="d-block">{{ $inv->createdBy?->name }}</small>
                            </td>
                            <td class="text-end money fw-bold">
                                {{ number_format($inv->total_amount, 0, ',', '.') }}đ
                            </td>
                            <td class="text-end text-success fw-bold">
                                {{ number_format($inv->paid_amount, 0, ',', '.') }}đ
                            </td>
                            <td class="text-end {{ $inv->debt_amount > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                {{ $inv->debt_amount > 0 ? number_format($inv->debt_amount, 0, ',', '.') . 'đ' : '—' }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">
                                    {{ $inv->payment_method_label }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $inv->status_color }}">
                                    {{ $inv->status_label }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('invoices.show', $inv) }}" class="btn btn-sm btn-outline-primary"
                                        title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('invoices.print', $inv) }}" target="_blank"
                                        class="btn btn-sm btn-outline-secondary" title="In hóa đơn">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                Không có hóa đơn nào trong khoảng thời gian này.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center py-3 px-4">
                <small class="text-muted">
                    {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} / {{ $invoices->total() }} hóa đơn
                </small>
                {{ $invoices->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@endsection