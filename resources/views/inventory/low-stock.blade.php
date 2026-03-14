@extends('layouts.app')
@section('title', 'Thuốc sắp hết tồn')
@section('page-title', 'Cảnh báo tồn kho thấp')

@section('content')

    <div class="page-header">
        <div>
            <h4 class="mb-0"><i class="bi bi-arrow-down-circle me-2 text-warning"></i>Thuốc sắp hết tồn</h4>
            <small class="text-muted">Danh sách thuốc có tồn kho ≤ mức tối thiểu đã cài đặt</small>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()->hasPermission('purchase.create'))
                <a href="{{ route('purchase.create') }}" class="btn btn-primary">
                    <i class="bi bi-truck me-1"></i> Tạo đơn nhập hàng
                </a>
            @endif
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        @php
            $outOfStock = $medicines->filter(fn($m) => $m->total_stock == 0);
            $lowStock = $medicines->filter(fn($m) => $m->total_stock > 0 && $m->total_stock <= $m->min_stock);
        @endphp
        <div class="col-sm-6">
            <div class="card border-danger">
                <div class="card-body d-flex align-items-center gap-4 py-3 px-4">
                    <div style="font-size:40px;">🚫</div>
                    <div>
                        <div class="fs-2 fw-bold text-danger">{{ $outOfStock->count() }}</div>
                        <div class="fw-semibold">Thuốc hết hàng hoàn toàn</div>
                        <small class="text-muted">Cần nhập hàng ngay</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card border-warning">
                <div class="card-body d-flex align-items-center gap-4 py-3 px-4">
                    <div style="font-size:40px;">⚠️</div>
                    <div>
                        <div class="fs-2 fw-bold text-warning">{{ $lowStock->count() }}</div>
                        <div class="fw-semibold">Thuốc dưới mức tối thiểu</div>
                        <small class="text-muted">Nên lên kế hoạch nhập</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header py-3 px-4">
            <h6 class="mb-0 fw-bold">
                {{ $medicines->count() }} thuốc cần bổ sung tồn kho
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Tên thuốc</th>
                        <th>Nhóm</th>
                        <th class="text-center">Tồn hiện tại</th>
                        <th class="text-center">Mức tối thiểu</th>
                        <th class="text-center">Cần nhập thêm</th>
                        <th class="text-end">Giá bán</th>
                        <th class="text-center">Mức độ</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medicines->sortBy('total_stock') as $medicine)
                        <tr class="{{ $medicine->total_stock == 0 ? 'table-danger' : 'table-warning' }}">
                            <td>
                                <div class="fw-semibold">{{ $medicine->name }}</div>
                                <small class="text-muted">{{ $medicine->code }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $medicine->category?->name ?? '—' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold fs-5 {{ $medicine->total_stock == 0 ? 'text-danger' : 'text-warning' }}">
                                    {{ number_format($medicine->total_stock) }}
                                </span>
                                <small class="d-block text-muted">{{ $medicine->unit }}</small>
                            </td>
                            <td class="text-center text-muted fw-semibold">
                                {{ number_format($medicine->min_stock) }}
                                <small class="d-block">{{ $medicine->unit }}</small>
                            </td>
                            <td class="text-center">
                                @php $need = max(0, $medicine->min_stock * 2 - $medicine->total_stock); @endphp
                                <span class="badge bg-primary">
                                    +{{ number_format($need) }} {{ $medicine->unit }}
                                </span>
                                <small class="d-block text-muted">đề xuất</small>
                            </td>
                            <td class="text-end money fw-semibold">
                                {{ number_format($medicine->sell_price, 0, ',', '.') }}đ
                            </td>
                            <td class="text-center">
                                @if($medicine->total_stock == 0)
                                    <span class="badge bg-danger">🔴 Hết hàng</span>
                                @else
                                    <span class="badge bg-warning text-dark">🟡 Sắp hết</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('inventory.batches', $medicine) }}" class="btn btn-sm btn-outline-primary"
                                        title="Xem lô">
                                        <i class="bi bi-boxes"></i>
                                    </a>
                                    @if(auth()->user()->hasPermission('purchase.create'))
                                        <a href="{{ route('purchase.create') }}" class="btn btn-sm btn-primary"
                                            title="Tạo đơn nhập">
                                            <i class="bi bi-truck"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-check-circle text-success fs-1 d-block mb-2"></i>
                                Tất cả thuốc đều đủ tồn kho. Tuyệt vời!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection