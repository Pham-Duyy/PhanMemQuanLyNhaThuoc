@extends('layouts.app')
@section('title', 'Công nợ chi tiết')
@section('page-title', 'Công nợ chi tiết')

@section('content')

    <div class="page-header">
        <div>
            <h4 class="mb-0"><i class="bi bi-journal-bookmark me-2 text-danger"></i>Báo cáo công nợ chi tiết</h4>
            <small class="text-muted">Lịch sử từng giao dịch phát sinh — truy xuất theo đối tác</small>
        </div>
        <div class="d-flex gap-2">
            @if($partner && $transactions->isNotEmpty())
                <a href="{{ request()->fullUrlWithQuery(['export' => '1']) }}" class="btn btn-outline-success">
                    <i class="bi bi-download me-1"></i>Xuất CSV
                </a>
            @endif
            <a href="{{ route('reports.debt') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Tổng hợp
            </a>
        </div>
    </div>

    {{-- ── Toggle NCC / KH ────────────────────────────────────────────────── --}}
    <div class="card mb-4">
        <div class="card-body py-3 px-4">
            <div class="d-flex align-items-center gap-4 flex-wrap">
                <div class="d-flex gap-2">
                    <a href="{{ route('reports.debt.detail', ['type' => 'supplier']) }}"
                        class="btn btn-sm {{ $type == 'supplier' ? 'btn-primary' : 'btn-outline-secondary' }} fw-semibold">
                        🏭 Nhà cung cấp
                    </a>
                    <a href="{{ route('reports.debt.detail', ['type' => 'customer']) }}"
                        class="btn btn-sm {{ $type == 'customer' ? 'btn-primary' : 'btn-outline-secondary' }} fw-semibold">
                        👤 Khách hàng
                    </a>
                </div>

                <form method="GET" class="d-flex gap-2 align-items-end flex-wrap flex-fill">
                    <input type="hidden" name="type" value="{{ $type }}">
                    <div style="min-width:220px;">
                        <label class="form-label small fw-semibold mb-1">
                            {{ $type == 'supplier' ? 'Nhà cung cấp' : 'Khách hàng' }}
                        </label>
                        <select name="partner_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Chọn đối tác --</option>
                            @foreach($partners as $p)
                                <option value="{{ $p->id }}" {{ $partnerId == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}
                                    @if($p->current_debt > 0)
                                        (Nợ: {{ number_format($p->current_debt / 1000, 0) }}k)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small fw-semibold mb-1">Từ ngày</label>
                        <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
                    </div>
                    <div>
                        <label class="form-label small fw-semibold mb-1">Đến ngày</label>
                        <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-search me-1"></i>Xem
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if($partner)

        {{-- ── Thông tin đối tác + Stats ──────────────────────────────────────── --}}
        <div class="row g-3 mb-4">
            <div class="col-lg-5">
                <div class="card h-100" style="border-left:4px solid #C0392B;">
                    <div class="card-body px-4 py-3">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center"
                                style="width:52px;height:52px;background:#fdf0ef;font-size:26px;">
                                {{ $type == 'supplier' ? '🏭' : '👤' }}
                            </div>
                            <div>
                                <div class="fw-bold fs-5">{{ $partner->name }}</div>
                                <div class="text-muted small">
                                    {{ $type == 'supplier' ? 'Nhà cung cấp' : 'Khách hàng' }}
                                    @if($partner->phone ?? false) · {{ $partner->phone }} @endif
                                </div>
                            </div>
                        </div>
                        <div class="p-3 rounded-3" style="background:#fdf0ef;">
                            <div class="text-muted small mb-1">Số dư công nợ hiện tại</div>
                            <div class="fw-bold fs-3 text-danger">
                                {{ number_format($summary['current_debt'] ?? 0, 0, ',', '.') }}đ
                            </div>
                            @if(($summary['current_debt'] ?? 0) == 0)
                                <div class="text-success small fw-semibold">✅ Đã thanh toán đầy đủ</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="row g-3 h-100">
                    <div class="col-sm-4">
                        <div class="card h-100" style="border-left:4px solid #E74C3C;">
                            <div class="card-body py-3 px-3 text-center">
                                <div class="text-muted small mb-1">📈 Tổng phát sinh Nợ</div>
                                <div class="fw-bold text-danger" style="font-size:18px;">
                                    {{ number_format($summary['total_debit'] ?? 0, 0, ',', '.') }}đ
                                </div>
                                <div class="text-muted small">(Tăng nợ)</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="card h-100" style="border-left:4px solid #27AE60;">
                            <div class="card-body py-3 px-3 text-center">
                                <div class="text-muted small mb-1">📉 Tổng phát sinh Có</div>
                                <div class="fw-bold text-success" style="font-size:18px;">
                                    {{ number_format($summary['total_credit'] ?? 0, 0, ',', '.') }}đ
                                </div>
                                <div class="text-muted small">(Thanh toán)</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="card h-100" style="border-left:4px solid #2E86C1;">
                            <div class="card-body py-3 px-3 text-center">
                                <div class="text-muted small mb-1">📋 Số giao dịch</div>
                                <div class="fw-bold text-primary" style="font-size:18px;">
                                    {{ $summary['tx_count'] ?? 0 }}
                                </div>
                                <div class="text-muted small">
                                    {{ \Carbon\Carbon::parse($from)->format('d/m') }} —
                                    {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Bảng lịch sử giao dịch ─────────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-list-ul me-2"></i>
                    Lịch sử giao dịch — {{ $transactions->total() }} bản ghi
                </h6>
                <span class="text-muted small">Sắp xếp: cũ → mới</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:140px;">Ngày GD</th>
                            <th style="width:120px;">Loại</th>
                            <th style="width:120px;">Danh mục</th>
                            <th>Mô tả</th>
                            <th style="width:120px;">Mã CT</th>
                            <th class="text-end" style="width:130px;">Phát sinh Nợ</th>
                            <th class="text-end" style="width:130px;">Phát sinh Có</th>
                            <th class="text-end" style="width:130px;">Số dư</th>
                            <th style="width:100px;">Người TH</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr class="{{ $tx->type === 'debit' ? 'table-danger-subtle' : 'table-success-subtle' }}"
                                style="{{ $tx->type === 'debit' ? 'background:#fff9f9;' : 'background:#f9fff9;' }}">
                                <td class="small text-nowrap">
                                    {{ \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y') }}
                                    <div style="font-size:10px;color:#aaa;">
                                        {{ \Carbon\Carbon::parse($tx->transaction_date)->format('H:i') }}
                                    </div>
                                </td>
                                <td>
                                    @if($tx->type === 'debit')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            📈 Phát sinh Nợ
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            📉 Thanh toán
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border small">
                                        {{ ucfirst(str_replace('_', ' ', $tx->category ?? '—')) }}
                                    </span>
                                </td>
                                <td class="small">{{ $tx->description }}</td>
                                <td>
                                    @if($tx->reference_code)
                                        <code class="small text-primary">{{ $tx->reference_code }}</code>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-danger">
                                    {{ $tx->type === 'debit' ? number_format($tx->amount, 0, ',', '.') . 'đ' : '—' }}
                                </td>
                                <td class="text-end fw-bold text-success">
                                    {{ $tx->type === 'credit' ? number_format($tx->amount, 0, ',', '.') . 'đ' : '—' }}
                                </td>
                                <td class="text-end fw-bold {{ $tx->balance_after > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($tx->balance_after, 0, ',', '.') }}đ
                                </td>
                                <td class="small text-muted">{{ $tx->createdBy?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Không có giao dịch nào trong kỳ này.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if($transactions->isNotEmpty())
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="5" class="text-end px-4">Tổng cộng trong kỳ:</td>
                                <td class="text-end text-danger">
                                    {{ number_format($summary['total_debit'] ?? 0, 0, ',', '.') }}đ
                                </td>
                                <td class="text-end text-success">
                                    {{ number_format($summary['total_credit'] ?? 0, 0, ',', '.') }}đ
                                </td>
                                <td class="text-end text-danger fs-6">
                                    {{ number_format($summary['current_debt'] ?? 0, 0, ',', '.') }}đ
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            @if($transactions->hasPages())
                <div class="card-footer py-3 px-4">
                    {{ $transactions->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>

    @else
        {{-- Chưa chọn đối tác --}}
        <div class="card">
            <div class="card-body text-center py-5">
                <div style="font-size:64px;">📒</div>
                <h5 class="fw-bold mt-3">Chọn đối tác để xem lịch sử công nợ</h5>
                <p class="text-muted">Chọn nhà cung cấp hoặc khách hàng ở bộ lọc phía trên.</p>
                <div class="mt-3 p-3 rounded-3 text-start d-inline-block" style="background:#f8fafc;max-width:460px;">
                    <div class="fw-semibold small mb-2">📌 Báo cáo này gồm:</div>
                    <ul class="small text-muted mb-0">
                        <li>Mỗi lần phát sinh nợ (mua hàng / bán nợ)</li>
                        <li>Mỗi lần thanh toán (trả nợ)</li>
                        <li>Số dư công nợ sau mỗi giao dịch</li>
                        <li>Người thực hiện và thời gian</li>
                        <li>Xuất CSV để in sao kê gửi đối tác</li>
                    </ul>
                </div>
            </div>
        </div>
    @endif

@endsection