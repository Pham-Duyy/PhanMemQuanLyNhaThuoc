@extends('layouts.app')
@section('title', 'Nhà cung cấp')
@section('page-title', 'Nhà cung cấp')

@section('content')

    <div class="page-header">
        <div>
            <h4 class="mb-0"><i class="bi bi-building me-2 text-primary"></i>Nhà cung cấp</h4>
            <small class="text-muted">Quản lý danh sách nhà cung cấp thuốc</small>
        </div>
        @if(auth()->user()->hasPermission('supplier.create'))
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Thêm nhà cung cấp
            </a>
        @endif
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f4fd;font-size:22px;">🏭</div>
                <div>
                    <div class="stat-value">{{ $suppliers->total() }}</div>
                    <div class="stat-label">Tổng nhà cung cấp</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fdf0f0;font-size:22px;">📒</div>
                <div>
                    <div class="stat-value text-danger" style="font-size:18px;">
                        {{ number_format($suppliers->sum('current_debt') / 1000000, 2) }}tr đ
                    </div>
                    <div class="stat-label">Tổng nợ NCC</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f8f0;font-size:22px;">✅</div>
                <div>
                    <div class="stat-value text-success">
                        {{ $suppliers->where('current_debt', 0)->count() }}
                    </div>
                    <div class="stat-label">Đã thanh toán đầy đủ</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bộ lọc --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-8">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Tìm theo tên, mã, SĐT..."
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-sm btn-primary flex-fill">
                        <i class="bi bi-funnel me-1"></i>Lọc
                    </button>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-outline-secondary">
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
                        <th>Tên nhà cung cấp</th>
                        <th>Mã số thuế</th>
                        <th>SĐT liên hệ</th>
                        <th>Người liên hệ</th>
                        <th class="text-end">Nợ hiện tại</th>
                        <th class="text-end">Hạn mức nợ</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $s)
                        @php $overLimit = $s->debt_limit > 0 && $s->current_debt > $s->debt_limit; @endphp
                        <tr class="{{ $overLimit ? 'table-danger' : '' }}">
                            <td>
                                <div class="fw-semibold">{{ $s->name }}</div>
                                <small class="text-muted"><code>{{ $s->code }}</code></small>
                            </td>
                            <td class="small text-muted">{{ $s->tax_code ?? '—' }}</td>
                            <td class="small">{{ $s->phone ?? '—' }}</td>
                            <td class="small">
                                <div>{{ $s->contact_person ?? '—' }}</div>
                                <span class="text-muted">{{ $s->contact_phone ?? '' }}</span>
                            </td>
                            <td class="text-end fw-bold {{ $s->current_debt > 0 ? 'text-danger' : 'text-muted' }}">
                                {{ $s->current_debt > 0 ? number_format($s->current_debt, 0, ',', '.') . 'đ' : '—' }}
                            </td>
                            <td class="text-end small text-muted">
                                {{ $s->debt_limit > 0 ? number_format($s->debt_limit, 0, ',', '.') . 'đ' : 'Không giới hạn' }}
                            </td>
                            <td class="text-center">
                                @if($overLimit)
                                    <span class="badge bg-danger">Vượt hạn mức</span>
                                @elseif($s->current_debt > 0)
                                    <span class="badge bg-warning text-dark">Đang nợ</span>
                                @else
                                    <span class="badge bg-success">Bình thường</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('suppliers.show', $s) }}" class="btn btn-sm btn-outline-primary"
                                        title="Chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(auth()->user()->hasPermission('supplier.edit'))
                                        <a href="{{ route('suppliers.edit', $s) }}" class="btn btn-sm btn-outline-warning"
                                            title="Chỉnh sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                Chưa có nhà cung cấp nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center py-3 px-4">
                <small class="text-muted">{{ $suppliers->total() }} nhà cung cấp</small>
                {{ $suppliers->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@endsection