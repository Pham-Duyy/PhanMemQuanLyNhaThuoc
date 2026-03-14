@extends('layouts.app')
@section('title', $supplier->name)
@section('page-title','Chi tiết nhà cung cấp')

@section('content')

<div class="page-header">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <h4 class="mb-0"><i class="bi bi-building me-2 text-primary"></i>{{ $supplier->name }}</h4>
        @if($supplier->current_debt > 0)
            <span class="badge bg-danger fs-6">💸 Đang nợ: {{ number_format($supplier->current_debt,0,',','.')}}đ</span>
        @else
            <span class="badge bg-success fs-6">✅ Không có công nợ</span>
        @endif
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if($supplier->current_debt > 0 && auth()->user()->hasPermission('purchase.create'))
        <button class="btn btn-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#payDebtModal">
            <i class="bi bi-cash-coin me-1"></i>Thanh toán nợ
        </button>
        @endif
        @if(auth()->user()->hasPermission('supplier.edit'))
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning">
            <i class="bi bi-pencil me-1"></i>Chỉnh sửa
        </a>
        @endif
        @if(auth()->user()->hasPermission('purchase.create'))
        <a href="{{ route('purchase.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-primary">
            <i class="bi bi-truck me-1"></i>Tạo đơn nhập
        </a>
        @endif
        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Quay lại
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-3">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header py-3 px-4"><h6 class="mb-0 fw-bold">📋 Thông tin nhà cung cấp</h6></div>
            <div class="card-body px-4 py-3">
                @foreach([
                    ['Mã NCC',        '<code>'.$supplier->code.'</code>'],
                    ['Mã số thuế',    $supplier->tax_code ?? '—'],
                    ['Điện thoại',    $supplier->phone ?? '—'],
                    ['Email',         $supplier->email ?? '—'],
                    ['Người liên hệ', $supplier->contact_person ?? '—'],
                    ['SĐT liên hệ',   $supplier->contact_phone ?? '—'],
                    ['Địa chỉ',       $supplier->address ?? '—'],
                    ['Kỳ hạn TT',     ($supplier->payment_term_days ?? 0).' ngày'],
                ] as [$label, $value])
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted small">{{ $label }}</span>
                    <span class="text-end small fw-semibold">{!! $value !!}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card {{ $supplier->current_debt > 0 ? 'border-danger' : 'border-success' }}" style="border-width:2px!important;">
            <div class="card-header py-3 px-4 {{ $supplier->current_debt > 0 ? 'bg-danger-subtle' : 'bg-success-subtle' }}">
                <h6 class="mb-0 fw-bold {{ $supplier->current_debt > 0 ? 'text-danger' : 'text-success' }}">
                    💰 Tình trạng công nợ
                </h6>
            </div>
            <div class="card-body px-4 py-3">
                <div class="text-center py-2">
                    <div class="fw-bold {{ $supplier->current_debt > 0 ? 'text-danger' : 'text-success' }}" style="font-size:32px;letter-spacing:-1px;">
                        {{ number_format($supplier->current_debt, 0, ',', '.') }}đ
                    </div>
                    <div class="text-muted small mt-1">
                        {{ $supplier->current_debt > 0 ? 'Cần thanh toán cho nhà cung cấp' : 'Đã thanh toán đầy đủ ✅' }}
                    </div>
                </div>
                @if($supplier->debt_limit > 0)
                <div class="mt-3">
                    @php $pct = min(100, $supplier->current_debt / $supplier->debt_limit * 100); @endphp
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Hạn mức: {{ number_format($supplier->debt_limit,0,',','.')}}đ</span>
                        <span class="{{ $pct>=100?'text-danger fw-bold':'text-muted' }}">{{ number_format($pct,0) }}%</span>
                    </div>
                    <div class="progress" style="height:10px;border-radius:6px;">
                        <div class="progress-bar {{ $pct>=100?'bg-danger':($pct>=80?'bg-warning':'bg-success') }}"
                             style="width:{{ $pct }}%;"></div>
                    </div>
                </div>
                @endif
                @if($supplier->current_debt > 0 && auth()->user()->hasPermission('purchase.create'))
                <div class="mt-3">
                    <button class="btn btn-danger w-100 fw-bold"
                            data-bs-toggle="modal" data-bs-target="#payDebtModal">
                        <i class="bi bi-cash-coin me-1"></i>Thanh toán nợ ngay
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">📦 Đơn nhập hàng gần đây</h6>
                <a href="{{ route('purchase.index', ['supplier_id'=>$supplier->id]) }}"
                   class="btn btn-sm btn-outline-primary py-0 px-2">Xem tất cả</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã đơn</th>
                            <th class="text-center">Ngày</th>
                            <th class="text-end">Tổng tiền</th>
                            <th class="text-center">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supplier->purchaseOrders as $po)
                        <tr>
                            <td>
                                <a href="{{ route('purchase.show', $po) }}"
                                   class="fw-semibold text-primary text-decoration-none">{{ $po->code }}</a>
                            </td>
                            <td class="text-center small">{{ $po->order_date->format('d/m/Y') }}</td>
                            <td class="text-end fw-semibold">{{ number_format($po->total_amount,0,',','.')}}đ</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $po->status_color }}">{{ $po->status_label }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-4 text-muted">Chưa có đơn nhập hàng</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">📒 Lịch sử công nợ</h6>
                <a href="{{ route('reports.debt.detail', ['type'=>'supplier','partner_id'=>$supplier->id]) }}"
                   class="btn btn-sm btn-outline-secondary py-0 px-2">
                    <i class="bi bi-journal-bookmark me-1"></i>Báo cáo chi tiết
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mô tả</th>
                            <th class="text-center" style="width:110px;">Ngày</th>
                            <th class="text-center" style="width:110px;">Loại</th>
                            <th class="text-end" style="width:130px;">Số tiền</th>
                            <th class="text-end" style="width:130px;">Số dư sau</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($debtHistory as $tx)
                        <tr style="{{ $tx->type==='credit' ? 'background:#f0fdf4;' : '' }}">
                            <td class="small">
                                {{ $tx->description }}
                                @if($tx->reference_code)
                                <br><code class="text-muted" style="font-size:10px;">{{ $tx->reference_code }}</code>
                                @endif
                            </td>
                            <td class="text-center small">
                                {{ \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y') }}
                            </td>
                            <td class="text-center">
                                @if($tx->type === 'debit')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    📈 Phát sinh nợ
                                </span>
                                @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    💸 Đã thanh toán
                                </span>
                                @endif
                            </td>
                            <td class="text-end fw-bold {{ $tx->type==='debit' ? 'text-danger' : 'text-success' }}">
                                {{ $tx->type==='debit' ? '+' : '-' }}{{ number_format($tx->amount,0,',','.')}}đ
                            </td>
                            <td class="text-end small {{ $tx->balance_after > 0 ? 'text-danger' : 'text-muted' }}">
                                {{ number_format($tx->balance_after,0,',','.')}}đ
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>Chưa có lịch sử công nợ
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($debtHistory->count() > 0)
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3" class="text-end">Số dư nợ hiện tại:</td>
                            <td colspan="2" class="text-end {{ $supplier->current_debt > 0 ? 'text-danger' : 'text-success' }} fs-6 pe-3">
                                {{ number_format($supplier->current_debt,0,',','.')}}đ
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════ --}}
{{-- MODAL THANH TOÁN NỢ --}}
{{-- ════════════════════════════════════════════════════════════ --}}
@if($supplier->current_debt > 0)
<div class="modal fade" id="payDebtModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px;overflow:hidden;">

            <div class="modal-header border-0 px-4 pt-4 pb-3"
                 style="background:linear-gradient(135deg,#dc2626,#ef4444);">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-1">
                        <i class="bi bi-cash-coin me-2"></i>Thanh toán nợ nhà cung cấp
                    </h5>
                    <div class="text-white small" style="opacity:.85;">{{ $supplier->name }}</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            {{-- Tổng nợ hiện tại --}}
            <div class="px-4 py-3 d-flex justify-content-between align-items-center"
                 style="background:#fef2f2;border-bottom:1px solid #fecaca;">
                <div>
                    <div class="text-muted small mb-1">Tổng nợ cần thanh toán</div>
                    <div class="fw-bold text-danger" style="font-size:28px;letter-spacing:-1px;">
                        {{ number_format($supplier->current_debt,0,',','.')}}đ
                    </div>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm fw-bold"
                        onclick="document.getElementById('payAmount').value={{ (int) $supplier->current_debt }};calcRemain();">
                    <i class="bi bi-check-all me-1"></i>Trả hết nợ
                </button>
            </div>

            <form action="{{ route('suppliers.pay-debt', $supplier) }}" method="POST">
                @csrf
                <div class="modal-body px-4 py-4">

                    {{-- Số tiền --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Số tiền thanh toán <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-lg">
                            <input type="number" name="amount" id="payAmount"
                                   class="form-control fw-bold text-end"
                                   placeholder="0" min="1"
                                   max="{{ (int) $supplier->current_debt }}"
                                   step="any" required
                                   oninput="calcRemain()"
                                   style="font-size:22px;">
                            <span class="input-group-text fw-bold text-muted">đ</span>
                        </div>
                        <div class="d-flex gap-2 mt-2 flex-wrap">
                            @foreach([500000,1000000,2000000,5000000] as $p)
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    onclick="addAmt({{ $p }})">
                                +{{ number_format($p/1000) }}k
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Số dư còn lại --}}
                    <div class="p-3 rounded-3 mb-3" id="remainBox"
                         style="background:#f0fdf4;border:1px solid #86efac;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Số nợ còn lại sau khi thanh toán:</span>
                            <span class="fw-bold text-success fs-5" id="remainAmt">
                                {{ number_format($supplier->current_debt,0,',','.')}}đ
                            </span>
                        </div>
                    </div>

                    {{-- Phương thức --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phương thức <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            @foreach(['cash'=>['💵','Tiền mặt'],'card'=>['💳','Thẻ/ATM'],'transfer'=>['🏦','Chuyển khoản']] as $v=>[$ico,$lbl])
                            <div class="col-4">
                                <label class="d-block text-center p-2 rounded-3 border"
                                       id="pm_{{ $v }}"
                                       style="cursor:pointer;transition:all .15s;">
                                    <input type="radio" name="payment_method" value="{{ $v }}"
                                           {{ $v==='transfer'?'checked':'' }}
                                           class="d-none" onchange="highlightPM()">
                                    <div style="font-size:24px;">{{ $ico }}</div>
                                    <div class="small fw-semibold mt-1">{{ $lbl }}</div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="row g-3">
                        {{-- Ngày TT --}}
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Ngày thanh toán</label>
                            <input type="date" name="payment_date" class="form-control"
                                   value="{{ now()->format('Y-m-d') }}">
                        </div>
                        {{-- Ghi chú --}}
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Ghi chú</label>
                            <input type="text" name="note" class="form-control"
                                   placeholder="VD: TT đơn tháng 3...">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                    <button type="button" class="btn btn-light px-4 fw-semibold"
                            data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger fw-bold px-5 flex-fill"
                            id="paySubmitBtn">
                        <i class="bi bi-check-circle-fill me-2"></i>Xác nhận thanh toán
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
const currentDebt = {{ (int) $supplier->current_debt }};

function calcRemain(){
    const amt    = parseFloat(document.getElementById('payAmount').value) || 0;
    const remain = Math.max(0, currentDebt - amt);
    const box    = document.getElementById('remainBox');
    const el     = document.getElementById('remainAmt');
    const btn    = document.getElementById('paySubmitBtn');

    if(amt <= 0 || amt > currentDebt){
        box.style.cssText  = 'background:#fef2f2;border:1px solid #fca5a5;padding:12px;border-radius:12px;';
        el.className       = 'fw-bold text-danger fs-5';
        el.textContent     = amt > currentDebt ? '⚠ Vượt quá số nợ!' : '—';
        btn.disabled       = true;
    } else if(remain === 0){
        box.style.cssText  = 'background:#ecfdf5;border:1px solid #6ee7b7;padding:12px;border-radius:12px;';
        el.className       = 'fw-bold text-success fs-5';
        el.textContent     = '0đ — Hết nợ! ✅';
        btn.disabled       = false;
    } else {
        box.style.cssText  = 'background:#f0fdf4;border:1px solid #86efac;padding:12px;border-radius:12px;';
        el.className       = 'fw-bold text-success fs-5';
        el.textContent     = new Intl.NumberFormat('vi-VN').format(Math.round(remain)) + 'đ';
        btn.disabled       = false;
    }
}

function addAmt(a){
    const inp = document.getElementById('payAmount');
    inp.value = Math.min(currentDebt, (parseFloat(inp.value)||0) + a);
    calcRemain();
}

function highlightPM(){
    ['cash','card','transfer'].forEach(v => {
        const lbl   = document.getElementById('pm_' + v);
        const radio = lbl.querySelector('input');
        if(radio.checked){
            lbl.style.cssText = 'cursor:pointer;transition:all .15s;background:#eff6ff;border-color:#3b82f6!important;color:#1d4ed8;';
        } else {
            lbl.style.cssText = 'cursor:pointer;transition:all .15s;';
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    highlightPM();
});
</script>
@endpush