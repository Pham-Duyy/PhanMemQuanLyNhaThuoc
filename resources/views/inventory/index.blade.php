@extends('layouts.app')
@section('title', 'Tồn kho')
@section('page-title', 'Tồn kho')

@section('content')

    <div class="page-header">
        <div>
            <h4 class="mb-0"><i class="bi bi-boxes me-2 text-primary"></i>Tổng quan tồn kho</h4>
            <small class="text-muted">Theo dõi tồn kho thực tế theo từng thuốc và lô hàng</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.expiring') }}" class="btn btn-outline-danger">
                <i class="bi bi-exclamation-triangle me-1"></i> Sắp hết hạn
            </a>
            <a href="{{ route('inventory.low-stock') }}" class="btn btn-outline-warning">
                <i class="bi bi-arrow-down-circle me-1"></i> Sắp hết tồn
            </a>
            @if(auth()->user()->hasPermission('inventory.adjust'))
                <a href="{{ route('inventory.adjust.create') }}" class="btn btn-primary">
                    <i class="bi bi-sliders me-1"></i> Điều chỉnh kho
                </a>
            @endif
        </div>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f4fd;font-size:22px;">📦</div>
                <div>
                    <div class="stat-value">{{ $medicines->total() }}</div>
                    <div class="stat-label">Loại thuốc đang bán</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f8f0;font-size:22px;">💰</div>
                <div>
                    <div class="stat-value text-success" style="font-size:16px;">
                        {{ number_format($totalValue / 1000000, 1) }}tr đ
                    </div>
                    <div class="stat-label">Tổng giá trị tồn kho</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fdf0f0;font-size:22px;">⚠️</div>
                <div>
                    @php
                        $expiringCount = \App\Models\Batch::whereHas('medicine', fn($q) =>
                            $q->where('pharmacy_id', auth()->user()->pharmacy_id))
                            ->expiringSoon(30)->count();
                    @endphp
                    <div class="stat-value text-danger">{{ $expiringCount }}</div>
                    <div class="stat-label">Lô sắp hết hạn (30 ngày)</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff8e1;font-size:22px;">🔻</div>
                <div>
                    @php
                        $lowCount = $medicines->filter(fn($m) => $m->total_stock <= $m->min_stock && $m->min_stock > 0)->count();
                    @endphp
                    <div class="stat-value text-warning">{{ $lowCount }}</div>
                    <div class="stat-label">Thuốc sắp hết tồn</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bộ lọc --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Tìm thuốc..."
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">-- Tất cả nhóm --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill">
                        <i class="bi bi-funnel me-1"></i>Lọc
                    </button>
                    <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Bảng tồn kho --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:100px">Mã thuốc</th>
                        <th>Tên thuốc</th>
                        <th>Nhóm</th>
                        <th class="text-center">Số lô</th>
                        <th class="text-center">Tồn kho</th>
                        <th class="text-center">Hạn gần nhất</th>
                        <th class="text-end">Giá trị tồn</th>
                        <th class="text-center">Tình trạng</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medicines as $medicine)
                        @php
                            $stock = $medicine->total_stock;
                            $activeBatches = $medicine->batches->where('is_active', true);
                            $batchCount = $activeBatches->count();
                            $stockValue = $activeBatches->sum(fn($b) => $b->current_quantity * $b->purchase_price);
                            $nearExpiry = $activeBatches->where('is_expired', false)
                                ->where('current_quantity', '>', 0)
                                ->sortBy('expiry_date')->first();
                        @endphp
                        <tr>
                            <td><code class="text-primary">{{ $medicine->code }}</code></td>

                            <td>
                                <div class="fw-semibold">{{ $medicine->name }}</div>
                                <small class="text-muted">{{ $medicine->generic_name }}</small>
                            </td>

                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $medicine->category?->name ?? '—' }}
                                </span>
                            </td>

                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $batchCount }} lô</span>
                            </td>

                            <td class="text-center">
                                @if($stock == 0)
                                    <span class="badge bg-danger">Hết hàng</span>
                                @elseif($medicine->is_low_stock)
                                    <span class="badge bg-warning text-dark fw-bold" title="Tối thiểu: {{ $medicine->min_stock }}">
                                        {{ number_format($stock) }}
                                        <i class="bi bi-exclamation-triangle-fill ms-1"></i>
                                    </span>
                                @else
                                    <span class="fw-bold text-success">{{ number_format($stock) }}</span>
                                @endif
                                <small class="d-block text-muted">{{ $medicine->unit }}</small>
                            </td>

                            <td class="text-center">
                                @if($nearExpiry)
                                    <span class="badge bg-{{ $nearExpiry->expiry_badge_color }}">
                                        {{ $nearExpiry->expiry_date->format('d/m/Y') }}
                                    </span>
                                    <small class="d-block text-muted">
                                        Còn {{ $nearExpiry->days_until_expiry }} ngày
                                    </small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="text-end">
                                <span class="fw-semibold">
                                    {{ number_format($stockValue / 1000, 0, ',', '.') }}k
                                </span>
                            </td>

                            <td class="text-center">
                                @if($stock == 0)
                                    <span class="badge bg-danger">Hết hàng</span>
                                @elseif($medicine->is_low_stock)
                                    <span class="badge bg-warning text-dark">Sắp hết</span>
                                @elseif($nearExpiry && $nearExpiry->expiry_status === 'critical')
                                    <span class="badge bg-danger">Sắp hết hạn</span>
                                @elseif($nearExpiry && $nearExpiry->expiry_status === 'warning')
                                    <span class="badge bg-warning text-dark">Cảnh báo HSD</span>
                                @else
                                    <span class="badge bg-success">Bình thường</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <a href="{{ route('inventory.batches', $medicine) }}" class="btn btn-sm btn-outline-primary"
                                    title="Xem lô hàng">
                                    <i class="bi bi-boxes me-1"></i>Xem lô
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                Không tìm thấy thuốc nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($medicines->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center py-3 px-4">
                <small class="text-muted">{{ $medicines->total() }} loại thuốc</small>
                {{ $medicines->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@endsection