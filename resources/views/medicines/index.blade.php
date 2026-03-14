@extends('layouts.app')

@section('title', 'Danh mục thuốc')
@section('page-title', 'Danh mục thuốc')

@section('content')

    {{-- ── HEADER ──────────────────────────────────────────────────────────── --}}
    <div class="page-header">
        <h4><i class="bi bi-capsule me-2 text-primary"></i>Danh mục thuốc</h4>
        @if(auth()->user()->hasPermission('medicine.create'))
            <a href="{{ route('medicines.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Thêm thuốc mới
            </a>
        @endif
    </div>

    {{-- ── BỘ LỌC ───────────────────────────────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold text-muted mb-1">Tìm kiếm</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control"
                            placeholder="Tên thuốc, hoạt chất, mã, barcode..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Nhóm thuốc</label>
                    <select name="category_id" class="form-select">
                        <option value="">-- Tất cả nhóm --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang bán</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Ngừng bán</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-funnel"></i> Lọc
                    </button>
                    <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── BẢNG DANH SÁCH ───────────────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center py-3 px-4">
            <span class="fw-semibold">
                Tổng: <strong>{{ $medicines->total() }}</strong> loại thuốc
            </span>
            <small class="text-muted">Trang {{ $medicines->currentPage() }}/{{ $medicines->lastPage() }}</small>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:100px">Mã thuốc</th>
                        <th>Tên thuốc</th>
                        <th>Nhóm</th>
                        <th class="text-center" style="width:90px">Tồn kho</th>
                        <th class="text-end" style="width:120px">Giá bán</th>
                        <th class="text-center" style="width:110px">Đặc tính</th>
                        <th class="text-center" style="width:90px">Trạng thái</th>
                        <th class="text-center" style="width:80px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medicines as $medicine)
                        <tr class="{{ !$medicine->is_active ? 'table-secondary' : '' }}">

                            {{-- Mã --}}
                            <td><code class="text-primary fw-semibold">{{ $medicine->code }}</code></td>

                            {{-- Tên --}}
                            <td>
                                <div class="fw-semibold">{{ $medicine->name }}</div>
                                @if($medicine->generic_name)
                                    <small class="text-muted">{{ $medicine->generic_name }}</small>
                                @endif
                                @if($medicine->manufacturer)
                                    <small class="d-block text-muted" style="font-size:11px;">
                                        <i class="bi bi-building"></i> {{ $medicine->manufacturer }}
                                    </small>
                                @endif
                            </td>

                            {{-- Nhóm --}}
                            <td>
                                @if($medicine->category)
                                    <span class="badge bg-light text-dark border">{{ $medicine->category->name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Tồn kho --}}
                            <td class="text-center">
                                @php $stock = $medicine->total_stock; @endphp
                                @if($stock == 0)
                                    <span class="badge bg-danger">Hết hàng</span>
                                @elseif($medicine->is_low_stock)
                                    <span class="badge bg-warning text-dark" title="Tối thiểu: {{ $medicine->min_stock }}">
                                        {{ number_format($stock) }}
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                    </span>
                                @else
                                    <span class="fw-semibold text-success">{{ number_format($stock) }}</span>
                                @endif
                                <small class="d-block text-muted">{{ $medicine->unit }}</small>
                            </td>

                            {{-- Giá bán --}}
                            <td class="text-end">
                                <span class="fw-semibold money">
                                    {{ number_format($medicine->sell_price, 0, ',', '.') }}đ
                                </span>
                                <small class="d-block text-muted">/{{ $medicine->unit }}</small>
                            </td>

                            {{-- Đặc tính --}}
                            <td class="text-center">
                                <div class="d-flex flex-wrap gap-1 justify-content-center">
                                    @if($medicine->requires_prescription)
                                        <span class="badge bg-danger" title="Thuốc kê đơn">Kê đơn</span>
                                    @endif
                                    @if($medicine->is_narcotic)
                                        <span class="badge bg-dark" title="Gây nghiện">GN</span>
                                    @endif
                                    @if($medicine->is_antibiotic)
                                        <span class="badge bg-warning text-dark" title="Kháng sinh">KS</span>
                                    @endif
                                    @if(!$medicine->requires_prescription && !$medicine->is_narcotic && !$medicine->is_antibiotic)
                                        <span class="text-muted small">OTC</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Trạng thái --}}
                            <td class="text-center">
                                @if($medicine->is_active)
                                    <span class="badge bg-success">Đang bán</span>
                                @else
                                    <span class="badge bg-secondary">Ngừng bán</span>
                                @endif
                            </td>

                            {{-- Thao tác --}}
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle px-2"
                                        data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('medicines.show', $medicine) }}">
                                                <i class="bi bi-eye me-2 text-primary"></i>Chi tiết & Lô hàng
                                            </a>
                                        </li>
                                        @if(auth()->user()->hasPermission('medicine.edit'))
                                            <li>
                                                <a class="dropdown-item" href="{{ route('medicines.edit', $medicine) }}">
                                                    <i class="bi bi-pencil me-2 text-warning"></i>Chỉnh sửa
                                                </a>
                                            </li>
                                            <li>
                                                <form action="{{ route('medicines.toggle', $medicine) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="dropdown-item
                                                        {{ $medicine->is_active ? 'text-secondary' : 'text-success' }}">
                                                        <i
                                                            class="bi bi-{{ $medicine->is_active ? 'pause' : 'play' }}-circle me-2"></i>
                                                        {{ $medicine->is_active ? 'Ngừng bán' : 'Kích hoạt lại' }}
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                Không tìm thấy thuốc nào.
                                @if(!request('search') && !request('category_id'))
                                    <div class="mt-2">
                                        <a href="{{ route('medicines.create') }}" class="btn btn-primary btn-sm">
                                            <i class="bi bi-plus-lg me-1"></i> Thêm thuốc đầu tiên
                                        </a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($medicines->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center py-3 px-4">
                <small class="text-muted">
                    Hiển thị {{ $medicines->firstItem() }}–{{ $medicines->lastItem() }}
                    trong tổng {{ $medicines->total() }} thuốc
                </small>
                {{ $medicines->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@endsection