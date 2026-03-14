@extends('layouts.app')
@section('title', 'Khách hàng')
@section('page-title', 'Khách hàng')

@section('content')

    <div class="page-header">
        <div>
            <h4 class="mb-0"><i class="bi bi-people me-2 text-primary"></i>Khách hàng</h4>
            <small class="text-muted">Quản lý danh sách khách hàng và công nợ</small>
        </div>
        @if(auth()->user()->hasPermission('customer.create'))
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Thêm khách hàng
            </a>
        @endif
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f4fd;font-size:22px;">👥</div>
                <div>
                    <div class="stat-value">{{ $customers->total() }}</div>
                    <div class="stat-label">Tổng khách hàng</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fdf0f0;font-size:22px;">📒</div>
                <div>
                    <div class="stat-value text-danger" style="font-size:18px;">
                        {{ number_format($customers->sum('current_debt') / 1000000, 2) }}tr đ
                    </div>
                    <div class="stat-label">Tổng công nợ KH</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f8f0;font-size:22px;">✅</div>
                <div>
                    <div class="stat-value text-success">{{ $customers->where('current_debt', 0)->count() }}</div>
                    <div class="stat-label">Không có nợ</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Tìm tên, SĐT, mã KH..."
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="has_debt" class="form-select form-select-sm">
                        <option value="">-- Tất cả --</option>
                        <option value="1" {{ request('has_debt') ? 'selected' : '' }}>Đang có nợ</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-sm btn-primary flex-fill"><i class="bi bi-funnel me-1"></i>Lọc</button>
                    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary">
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
                        <th>Khách hàng</th>
                        <th>SĐT</th>
                        <th>Ghi chú y tế</th>
                        <th class="text-end">Nợ hiện tại</th>
                        <th class="text-end">Hạn mức</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $c)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $c->name }}</div>
                                <small class="text-muted"><code>{{ $c->code }}</code></small>
                            </td>
                            <td class="small">{{ $c->phone ?? '—' }}</td>
                            <td class="small text-muted" style="max-width:200px;">
                                {{ Str::limit($c->medical_note, 50) ?? '—' }}
                            </td>
                            <td class="text-end fw-bold {{ $c->current_debt > 0 ? 'text-danger' : 'text-muted' }}">
                                {{ $c->current_debt > 0 ? number_format($c->current_debt, 0, ',', '.') . 'đ' : '—' }}
                            </td>
                            <td class="text-end small text-muted">
                                {{ $c->debt_limit > 0 ? number_format($c->debt_limit, 0, ',', '.') . 'đ' : '∞' }}
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('customers.show', $c) }}" class="btn btn-sm btn-outline-primary"><i
                                            class="bi bi-eye"></i></a>
                                    @if(auth()->user()->hasPermission('customer.edit'))
                                        <a href="{{ route('customers.edit', $c) }}" class="btn btn-sm btn-outline-warning"><i
                                                class="bi bi-pencil"></i></a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Chưa có khách hàng nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center py-3 px-4">
                <small class="text-muted">{{ $customers->total() }} khách hàng</small>
                {{ $customers->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection