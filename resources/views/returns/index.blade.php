@extends('layouts.app')
@section('title', 'Phiếu trả hàng')
@section('page-title', 'Quản lý trả hàng')

@section('content')

    <div class="page-header">
        <div>
            <h4 class="mb-0"><i class="bi bi-arrow-return-left me-2 text-warning"></i>Phiếu trả hàng</h4>
            <small class="text-muted">Quản lý hoàn trả và hoàn tiền cho khách hàng</small>
        </div>
        @can('invoice.create')
            <a href="{{ route('returns.create') }}" class="btn btn-warning fw-semibold">
                <i class="bi bi-plus-lg me-1"></i>Tạo phiếu trả
            </a>
        @endcan
    </div>

    {{-- Stats hôm nay --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-3">
            <div class="card h-100" style="border-left:4px solid #E67E22;">
                <div class="card-body py-3 px-4">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Hôm nay</div>
                    <div class="fw-bold fs-5 text-warning">{{ number_format($totalToday, 0, ',', '.') }}đ</div>
                    <div class="text-muted small">Đã hoàn tiền</div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="card h-100" style="border-left:4px solid #2E86C1;">
                <div class="card-body py-3 px-4">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Tháng này</div>
                    <div class="fw-bold fs-5 text-primary">
                        {{ \App\Models\ReturnInvoice::whereMonth('return_date', now()->month)->count() }}
                    </div>
                    <div class="text-muted small">Phiếu trả</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bộ lọc --}}
    <div class="card mb-3">
        <div class="card-body py-3 px-4">
            <form method="GET" class="d-flex gap-3 align-items-end flex-wrap">
                <div>
                    <label class="form-label small fw-semibold mb-1">Tìm kiếm</label>
                    <input type="text" name="q" class="form-control form-control-sm" value="{{ request('q') }}"
                        placeholder="Mã phiếu, mã HĐ, khách hàng..." style="width:220px;">
                </div>
                <div>
                    <label class="form-label small fw-semibold mb-1">Từ ngày</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                </div>
                <div>
                    <label class="form-label small fw-semibold mb-1">Đến ngày</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                </div>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search me-1"></i>Lọc
                </button>
                <a href="{{ route('returns.index') }}" class="btn btn-sm btn-outline-secondary">Xóa lọc</a>
            </form>
        </div>
    </div>

    {{-- Bảng --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Mã phiếu trả</th>
                        <th>Hóa đơn gốc</th>
                        <th>Khách hàng</th>
                        <th class="text-center">Ngày trả</th>
                        <th class="text-center">SP trả</th>
                        <th class="text-end">Hoàn tiền</th>
                        <th>Lý do</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $r)
                        <tr>
                            <td>
                                <code class="text-warning fw-bold">{{ $r->code }}</code>
                            </td>
                            <td>
                                <a href="{{ route('invoices.show', $r->invoice_id) }}"
                                    class="text-primary fw-semibold text-decoration-none">
                                    {{ $r->invoice->code ?? '—' }}
                                </a>
                            </td>
                            <td>{{ $r->customer?->name ?? 'Khách lẻ' }}</td>
                            <td class="text-center">{{ $r->return_date->format('d/m/Y') }}</td>
                            <td class="text-center">
                                <span class="badge bg-warning text-dark">{{ $r->items_count ?? $r->items->count() }} SP</span>
                            </td>
                            <td class="text-end fw-bold text-danger">
                                -{{ number_format($r->refund_amount, 0, ',', '.') }}đ
                            </td>
                            <td>
                                <span class="text-muted small"
                                    style="max-width:200px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ $r->reason }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('returns.show', $r) }}" class="btn btn-sm btn-outline-primary"
                                        title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('returns.print', $r) }}" target="_blank"
                                        class="btn btn-sm btn-outline-secondary" title="In phiếu">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-arrow-return-left fs-1 d-block mb-2"></i>
                                Chưa có phiếu trả hàng nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($returns->hasPages())
            <div class="card-footer py-3 px-4">
                {{ $returns->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@endsection