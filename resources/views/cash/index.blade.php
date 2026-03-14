@extends('layouts.app')
@section('title', 'Sổ quỹ')
@section('page-title', 'Sổ quỹ tiền mặt')

@section('content')

    <div class="page-header">
        <div>
            <h4 class="mb-0"><i class="bi bi-cash-coin me-2 text-primary"></i>Sổ quỹ tiền mặt</h4>
            <small class="text-muted">Theo dõi toàn bộ thu chi trong nhà thuốc</small>
        </div>
        @if(auth()->user()->hasPermission('cash.create'))
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCash">
                <i class="bi bi-plus-lg me-1"></i> Thu/Chi thủ công
            </button>
        @endif
    </div>

    {{-- Tổng thu/chi --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card" style="border-left:4px solid #1E8449;">
                <div class="card-body px-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div style="font-size:32px;">💚</div>
                        <div>
                            <div class="fs-4 fw-bold text-success">
                                {{ number_format($totalReceipt / 1000000, 2) }}tr đ
                            </div>
                            <div class="text-muted">Tổng thu kỳ này</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card" style="border-left:4px solid #C0392B;">
                <div class="card-body px-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div style="font-size:32px;">❤️</div>
                        <div>
                            <div class="fs-4 fw-bold text-danger">
                                {{ number_format($totalPayment / 1000000, 2) }}tr đ
                            </div>
                            <div class="text-muted">Tổng chi kỳ này</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card" style="border-left:4px solid #1B6FA8;">
                <div class="card-body px-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div style="font-size:32px;">💰</div>
                        <div>
                            @php $balance = $totalReceipt - $totalPayment; @endphp
                            <div class="fs-4 fw-bold {{ $balance >= 0 ? 'text-primary' : 'text-danger' }}">
                                {{ number_format($balance / 1000000, 2) }}tr đ
                            </div>
                            <div class="text-muted">Số dư thuần kỳ</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bộ lọc --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <select name="type" class="form-select form-select-sm">
                        <option value="">-- Tất cả --</option>
                        <option value="receipt" {{ request('type') === 'receipt' ? 'selected' : '' }}>Thu tiền</option>
                        <option value="payment" {{ request('type') === 'payment' ? 'selected' : '' }}>Chi tiền</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="from" class="form-control form-control-sm"
                        value="{{ request('from', today()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="to" class="form-control form-control-sm"
                        value="{{ request('to', today()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-sm btn-primary flex-fill">
                        <i class="bi bi-funnel me-1"></i>Lọc
                    </button>
                    <a href="{{ route('cash.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Bảng giao dịch --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="text-center">Loại</th>
                        <th>Mô tả</th>
                        <th>Danh mục</th>
                        <th class="text-center">Thời gian</th>
                        <th class="text-end">Số tiền</th>
                        <th>Nhân viên</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td class="text-center">
                                @if($tx->type === 'receipt')
                                    <span class="badge bg-success">
                                        <i class="bi bi-arrow-down-circle me-1"></i>Thu
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="bi bi-arrow-up-circle me-1"></i>Chi
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold small">{{ $tx->description }}</div>
                                @if($tx->reference_code)
                                    <small class="text-muted">
                                        <code>{{ $tx->reference_code }}</code>
                                    </small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $catLabels = [
                                        'sale' => ['🛒', 'Bán hàng'],
                                        'purchase' => ['🚚', 'Nhập hàng'],
                                        'debt_receipt' => ['📥', 'Thu nợ'],
                                        'debt_payment' => ['📤', 'Trả nợ'],
                                        'expense' => ['💸', 'Chi phí'],
                                        'other' => ['📝', 'Khác'],
                                    ];
                                    $cat = $catLabels[$tx->category] ?? ['❓', $tx->category];
                                @endphp
                                <span class="badge bg-light text-dark border small">
                                    {{ $cat[0] }} {{ $cat[1] }}
                                </span>
                            </td>
                            <td class="text-center small">
                                {{ $tx->transaction_date->format('H:i') }}<br>
                                <span class="text-muted">{{ $tx->transaction_date->format('d/m/Y') }}</span>
                            </td>
                            <td class="text-end fw-bold {{ $tx->type === 'receipt' ? 'text-success' : 'text-danger' }}">
                                {{ $tx->type === 'receipt' ? '+' : '-' }}{{ number_format($tx->amount, 0, ',', '.')}}đ
                            </td>
                            <td class="small text-muted">{{ $tx->createdBy?->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                Không có giao dịch nào trong kỳ này.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center py-3 px-4">
                <small class="text-muted">{{ $transactions->total() }} giao dịch</small>
                {{ $transactions->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    {{-- Modal thu/chi thủ công --}}
    <div class="modal fade" id="modalCash" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('cash.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">💰 Thu / Chi thủ công</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4 py-4">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Loại <span class="text-danger">*</span></label>
                                <select name="type" class="form-select" required id="cashType">
                                    <option value="receipt">💚 Thu tiền</option>
                                    <option value="payment">❤️ Chi tiền</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Danh mục</label>
                                <select name="category" class="form-select">
                                    <option value="other">📝 Khác</option>
                                    <option value="debt_receipt">📥 Thu nợ KH</option>
                                    <option value="debt_payment">📤 Trả nợ NCC</option>
                                    <option value="expense">💸 Chi phí vận hành</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Số tiền <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="amount" class="form-control" min="1000" step="1000" required
                                        placeholder="0">
                                    <span class="input-group-text">đ</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Mô tả <span class="text-danger">*</span></label>
                                <input type="text" name="description" class="form-control" required
                                    placeholder="Lý do thu/chi...">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Ghi chú thêm</label>
                                <textarea name="note" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary fw-semibold">
                            <i class="bi bi-check-lg me-1"></i>Xác nhận
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection