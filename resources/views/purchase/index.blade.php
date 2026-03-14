@extends('layouts.app')
@section('title', 'Đơn nhập hàng')
@section('page-title', 'Đơn nhập hàng')

@section('content')

    <div class="page-header">
        <div>
            <h4 class="mb-0"><i class="bi bi-truck me-2 text-primary"></i>Đơn nhập hàng</h4>
            <small class="text-muted">Quản lý nhập hàng từ nhà cung cấp</small>
        </div>
        @if(auth()->user()->hasPermission('purchase.create'))
            <a href="{{ route('purchase.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Tạo đơn nhập hàng
            </a>
        @endif
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        @php
            $allOrders = \App\Models\PurchaseOrder::where('pharmacy_id', auth()->user()->pharmacy_id)->get();
            $stats = [
                ['label' => 'Chờ duyệt', 'value' => $allOrders->where('status', 'pending')->count(), 'color' => '#F39C12', 'bg' => '#fff8e1', 'icon' => '⏳'],
                ['label' => 'Đã duyệt', 'value' => $allOrders->where('status', 'approved')->count(), 'color' => '#2980B9', 'bg' => '#e8f0fe', 'icon' => '✅'],
                ['label' => 'Đã nhận', 'value' => $allOrders->where('status', 'received')->count(), 'color' => '#1E8449', 'bg' => '#e8f8f0', 'icon' => '📦'],
                ['label' => 'Đã hủy', 'value' => $allOrders->where('status', 'cancelled')->count(), 'color' => '#C0392B', 'bg' => '#fdf0f0', 'icon' => '❌'],
            ];
        @endphp
        @foreach($stats as $s)
            <div class="col-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:{{ $s['bg'] }};">
                        <span style="font-size:20px;">{{ $s['icon'] }}</span>
                    </div>
                    <div>
                        <div class="stat-value" style="color:{{ $s['color'] }}">{{ $s['value'] }}</div>
                        <div class="stat-label">{{ $s['label'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Bộ lọc --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Nhà cung cấp</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">-- Tất cả --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả --</option>
                        @foreach(['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'received' => 'Đã nhận', 'cancelled' => 'Đã hủy'] as $v => $l)
                            <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Từ ngày</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Đến ngày</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-funnel"></i> Lọc
                    </button>
                    <a href="{{ route('purchase.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Bảng --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Nhà cung cấp</th>
                        <th class="text-center">Ngày đặt</th>
                        <th class="text-center">Dự kiến nhận</th>
                        <th class="text-end">Tổng tiền</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center">Người tạo</th>
                        <th class="text-center" style="width:130px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $po)
                        <tr>
                            <td>
                                <a href="{{ route('purchase.show', $po) }}" class="fw-bold text-primary text-decoration-none">
                                    {{ $po->code }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $po->supplier->name }}</div>
                            </td>
                            <td class="text-center small">{{ $po->order_date->format('d/m/Y') }}</td>
                            <td class="text-center small">
                                {{ $po->expected_date?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="text-end money fw-bold">
                                {{ number_format($po->total_amount, 0, ',', '.') }}đ
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $po->status_color }}">
                                    {{ $po->status_label }}
                                </span>
                            </td>
                            <td class="text-center small text-muted">
                                {{ $po->createdBy?->name ?? '—' }}
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('purchase.show', $po) }}" class="btn btn-sm btn-outline-primary"
                                        title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($po->canApprove() && auth()->user()->hasPermission('purchase.approve'))
                                        <form action="{{ route('purchase.approve', $po) }}" method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-sm btn-success" title="Duyệt đơn"
                                                onclick="return confirm('Duyệt đơn {{ $po->code }}?')">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($po->canReceive() && auth()->user()->hasPermission('purchase.receive'))
                                        <a href="{{ route('purchase.receive.form', $po) }}" class="btn btn-sm btn-warning"
                                            title="Nhận hàng">
                                            <i class="bi bi-box-arrow-in-down"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                Chưa có đơn nhập hàng nào.
                                @if(auth()->user()->hasPermission('purchase.create'))
                                    <div class="mt-2">
                                        <a href="{{ route('purchase.create') }}" class="btn btn-primary btn-sm">
                                            Tạo đơn đầu tiên
                                        </a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center py-3 px-4">
                <small class="text-muted">
                    {{ $orders->firstItem() }}–{{ $orders->lastItem() }} / {{ $orders->total() }} đơn
                </small>
                {{ $orders->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@endsection