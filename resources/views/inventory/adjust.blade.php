@extends('layouts.app')
@section('title','Điều chỉnh tồn kho')
@section('page-title','Điều chỉnh tồn kho')

@section('content')

<div class="page-header">
    <div>
        <h4 class="mb-0"><i class="bi bi-sliders me-2 text-warning"></i>Điều chỉnh tồn kho</h4>
        <small class="text-muted">Kiểm kê và cập nhật số lượng thực tế — bắt buộc ghi lý do theo chuẩn GPP</small>
    </div>
    <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Quay lại
    </a>
</div>

<div class="alert alert-warning d-flex gap-3 align-items-start">
    <span style="font-size:22px;">⚠️</span>
    <div>
        <strong>Lưu ý GPP:</strong> Mọi điều chỉnh tồn kho đều được ghi nhật ký
        (<em>StockAdjustment</em>) và không thể xóa. Chỉ điều chỉnh khi có lý do hợp lệ:
        kiểm kê định kỳ, thuốc hỏng/vỡ, trả lại nhà cung cấp, hoặc nhập sai số lượng.
    </div>
</div>

<div class="row g-3 justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header py-3 px-4">
                <h6 class="mb-0 fw-bold">📋 Thông tin điều chỉnh</h6>
            </div>
            <div class="card-body px-4 py-4">
                <form action="{{ route('inventory.adjust.store') }}" method="POST" id="adjustForm">
                @csrf

                {{-- BƯỚC 1: Chọn lô hàng --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        <span class="badge bg-warning text-dark me-1">1</span>
                        Tìm và chọn lô hàng cần điều chỉnh <span class="text-danger">*</span>
                    </label>

                    @if($batch)
                        <input type="hidden" name="batch_id" value="{{ $batch->id }}">
                        <div class="p-3 rounded-3" style="background:#e8f4fd;border:2px solid #2471A3;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-bold text-primary fs-6">{{ $batch->medicine->name }}</div>
                                    <div class="text-muted small mt-1">
                                        <span class="me-3">📦 Số lô: <code class="text-dark">{{ $batch->batch_number }}</code></span>
                                        <span class="me-3">📅 HSD: {{ $batch->expiry_date->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                                <div class="text-end ms-3 flex-shrink-0">
                                    <div class="fw-bold fs-4 text-success">{{ number_format($batch->current_quantity) }}</div>
                                    <div class="text-muted small">tồn hiện tại ({{ $batch->medicine->unit }})</div>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('inventory.adjust.create') }}" class="btn btn-sm btn-outline-secondary mt-2">
                            <i class="bi bi-arrow-repeat me-1"></i>Chọn lô khác
                        </a>
                    @else
                        {{-- Tìm thuốc --}}
                        <div id="step1">
                            <div class="position-relative">
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="bi bi-search text-muted" id="searchIcon"></i>
                                    </span>
                                    <input type="text" id="medicineSearch" class="form-control form-control-lg"
                                           placeholder="🔍 Nhập tên thuốc hoặc mã thuốc..." autocomplete="off">
                                    <button type="button" class="btn btn-outline-secondary" id="clearSearch"
                                            style="display:none;" onclick="resetSearch()">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                <div id="medicineResults"
                                     class="position-absolute w-100 bg-white border rounded-3 shadow-sm mt-1"
                                     style="display:none;z-index:200;max-height:280px;overflow-y:auto;"></div>
                            </div>
                            <small class="text-muted">Tìm ít nhất 2 ký tự</small>
                        </div>

                        {{-- Danh sách lô (hiện sau khi chọn thuốc) --}}
                        <div id="step2" style="display:none;" class="mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-semibold mb-0">
                                    <span class="badge bg-primary me-1">2</span> Chọn lô cụ thể
                                </label>
                                <button type="button" class="btn btn-sm btn-link text-muted p-0"
                                        onclick="backToMedicineSearch()">
                                    <i class="bi bi-arrow-left me-1"></i>Chọn thuốc khác
                                </button>
                            </div>
                            <div id="batchList"></div>
                        </div>

                        {{-- Card lô đã chọn --}}
                        <div id="selectedBatchCard" style="display:none;" class="mt-3 p-3 rounded-3"></div>
                        <input type="hidden" name="batch_id" id="batchIdInput" required>
                    @endif
                </div>

                {{-- BƯỚC 2: Số lượng --}}
                <div class="mb-4" id="qtySection" @unless($batch) style="display:none;" @endunless>
                    <label class="form-label fw-semibold">
                        <span class="badge bg-warning text-dark me-1">{{ $batch ? '2' : '3' }}</span>
                        Số lượng thực tế (sau kiểm kê) <span class="text-danger">*</span>
                    </label>
                    <div class="row g-2 align-items-center">
                        <div class="col-5">
                            <div class="input-group">
                                <input type="number" name="quantity_after" id="qtyAfter"
                                       class="form-control form-control-lg text-center fw-bold"
                                       value="{{ old('quantity_after', $batch?->current_quantity ?? '') }}"
                                       min="0" required>
                                <span class="input-group-text" id="unitLabel">
                                    {{ $batch?->medicine->unit ?? 'đv' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-7">
                            <div id="diffBadge" class="p-2 rounded-3 text-center fw-semibold"
                                 style="background:#f0f2f5;color:#666;">
                                Nhập số lượng để xem chênh lệch
                            </div>
                        </div>
                    </div>
                    <small class="text-muted">
                        Tồn hệ thống: <strong id="currentQtyLabel">{{ $batch?->current_quantity ?? 0 }}</strong>
                        <span id="currentUnitLabel">{{ $batch?->medicine->unit ?? '' }}</span>
                    </small>
                </div>

                {{-- Loại điều chỉnh --}}
                <div class="mb-4" id="typeSection" @unless($batch) style="display:none;" @endunless>
                    <label class="form-label fw-semibold">
                        <span class="badge bg-warning text-dark me-1">{{ $batch ? '3' : '4' }}</span>
                        Loại điều chỉnh <span class="text-danger">*</span>
                    </label>
                    <div class="row g-2">
                        @php
                            $types = [
                                'count'      => ['🔢', 'Kiểm kê định kỳ',  'Kết quả đếm thực tế'],
                                'destroy'    => ['🗑️', 'Hủy thuốc',        'Thuốc hỏng, vỡ, hết hạn'],
                                'return'     => ['↩️', 'Trả NCC',          'Trả lại nhà cung cấp'],
                                'correction' => ['✏️', 'Sửa sai số liệu',  'Nhập sai khi nhận hàng'],
                                'other'      => ['📝', 'Lý do khác',       'Vui lòng ghi rõ bên dưới'],
                            ];
                        @endphp
                        @foreach($types as $val => [$icon, $label, $desc])
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="type"
                                   id="type_{{ $val }}" value="{{ $val }}"
                                   {{ old('type', 'count') === $val ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary w-100 text-start py-2 px-3"
                                   for="type_{{ $val }}">
                                <span class="me-2">{{ $icon }}</span>
                                <span class="fw-semibold">{{ $label }}</span>
                                <div class="text-muted small mt-1">{{ $desc }}</div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Lý do + submit --}}
                <div id="reasonSection" @unless($batch) style="display:none;" @endunless>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lý do điều chỉnh <span class="text-danger">*</span></label>
                        <input type="text" name="reason" class="form-control"
                               value="{{ old('reason') }}"
                               placeholder="VD: Kiểm kê tháng 01/2025, phát hiện chênh lệch..." required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Ghi chú thêm</label>
                        <textarea name="note" class="form-control" rows="2"
                                  placeholder="Thông tin bổ sung (không bắt buộc)">{{ old('note') }}</textarea>
                    </div>
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-warning fw-bold flex-fill py-2">
                            <i class="bi bi-check-circle me-2"></i>Xác nhận điều chỉnh
                        </button>
                        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary flex-fill py-2">
                            <i class="bi bi-x-lg me-2"></i>Hủy bỏ
                        </a>
                    </div>
                </div>

                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let searchTimer;
let currentQty = {{ $batch?->current_quantity ?? 0 }};

@unless($batch)
const medicineSearch  = document.getElementById('medicineSearch');
const medicineResults = document.getElementById('medicineResults');

medicineSearch.addEventListener('input', function() {
    const q = this.value.trim();
    document.getElementById('clearSearch').style.display = q ? 'block' : 'none';
    if (q.length < 2) { medicineResults.style.display = 'none'; return; }
    clearTimeout(searchTimer);
    searchTimer = setTimeout(async () => {
        try {
            const res  = await fetch(`/api/medicines/search?q=${encodeURIComponent(q)}`);
            const data = await res.json();
            const list = data.medicines || [];
            if (!list.length) {
                medicineResults.innerHTML = '<div class="p-3 text-muted text-center">Không tìm thấy</div>';
            } else {
                medicineResults.innerHTML = list.map(m => `
                    <div class="px-3 py-2 border-bottom"
                         style="cursor:pointer;"
                         onclick="selectMedicine(${m.id},'${(m.name||"").replace(/'/g,"&#39;")}','${m.unit}')"
                         onmouseenter="this.style.background='#f0f7ff'"
                         onmouseleave="this.style.background=''">
                        <div class="fw-semibold">${m.name}</div>
                        <small class="text-muted">${m.code||''} · Tồn: <strong class="${m.total_stock<=0?'text-danger':'text-success'}">${m.total_stock}</strong> ${m.unit}</small>
                    </div>`).join('');
            }
            medicineResults.style.display = 'block';
        } catch(e) {}
    }, 300);
});

async function selectMedicine(id, name, unit) {
    medicineResults.style.display = 'none';
    medicineSearch.value = name;
    document.getElementById('step2').style.display = 'block';
    document.getElementById('batchList').innerHTML =
        '<div class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Đang tải...</div>';
    try {
        const res  = await fetch(`/api/medicines/${id}/batches`);
        const data = await res.json();
        renderBatches(data.batches || [], unit);
    } catch(e) {
        document.getElementById('batchList').innerHTML = '<div class="alert alert-danger">Lỗi tải lô</div>';
    }
}

function renderBatches(batches, unit) {
    if (!batches.length) {
        document.getElementById('batchList').innerHTML =
            '<div class="alert alert-warning py-2">Chưa có lô nào trong kho</div>';
        return;
    }
    document.getElementById('batchList').innerHTML = batches.map(b => {
        const ec = b.days_until_expiry <= 0  ? 'border-danger'
                 : b.days_until_expiry <= 30 ? 'border-warning'
                 : 'border-secondary';
        const eb = b.days_until_expiry <= 0  ? '<span class="badge bg-danger">Đã hết hạn</span>'
                 : b.days_until_expiry <= 30 ? `<span class="badge bg-warning text-dark">Còn ${b.days_until_expiry} ngày</span>`
                 : `<span class="badge bg-success">Còn ${b.days_until_expiry} ngày</span>`;
        const mn = (b.medicine_name||'').replace(/'/g,"&#39;");
        return `
        <div class="border rounded-3 p-3 mb-2 ${ec}" style="cursor:pointer;transition:.15s;"
             onclick="selectBatch(${b.id},${b.current_quantity},'${b.batch_number}','${b.expiry_date_formatted}','${unit}','${mn}')"
             onmouseenter="this.style.boxShadow='0 0 0 2px #2471A3'"
             onmouseleave="this.style.boxShadow=''">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="fw-bold">Lô: <code>${b.batch_number}</code></span>
                    &nbsp; HSD: ${b.expiry_date_formatted} &nbsp; ${eb}
                    ${b.supplier_name ? `<div class="text-muted small">NCC: ${b.supplier_name}</div>` : ''}
                </div>
                <div class="text-end">
                    <span class="fw-bold fs-5 ${b.current_quantity<=0?'text-danger':'text-success'}">${b.current_quantity}</span>
                    <div class="text-muted small">${unit}</div>
                </div>
            </div>
        </div>`;
    }).join('');
}

function selectBatch(batchId, qty, batchNo, expiryFmt, unit, medName) {
    currentQty = qty;
    document.getElementById('batchIdInput').value = batchId;
    const card = document.getElementById('selectedBatchCard');
    card.style.cssText = 'display:block;background:#e8f4fd;border:2px solid #2471A3;border-radius:8px;';
    card.innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="fw-bold text-primary">✅ ${medName}</div>
                <div class="text-muted small mt-1">📦 Lô: <code class="text-dark">${batchNo}</code> &nbsp;·&nbsp; 📅 HSD: ${expiryFmt}</div>
            </div>
            <div class="text-end ms-3">
                <div class="fw-bold fs-4 text-success">${qty}</div>
                <div class="text-muted small">${unit}</div>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="resetBatch()">
            <i class="bi bi-arrow-repeat me-1"></i>Chọn lô khác
        </button>`;
    document.getElementById('step2').style.display = 'none';
    document.getElementById('unitLabel').textContent = unit;
    document.getElementById('currentQtyLabel').textContent = qty;
    document.getElementById('currentUnitLabel').textContent = unit;
    document.getElementById('qtyAfter').value = qty;
    document.getElementById('qtySection').style.display = 'block';
    document.getElementById('typeSection').style.display = 'block';
    document.getElementById('reasonSection').style.display = 'block';
    document.getElementById('qtyAfter').dispatchEvent(new Event('input'));
    document.getElementById('qtySection').scrollIntoView({behavior:'smooth',block:'nearest'});
}

function resetBatch() {
    document.getElementById('batchIdInput').value = '';
    document.getElementById('selectedBatchCard').style.display = 'none';
    document.getElementById('step2').style.display = 'block';
    ['qtySection','typeSection','reasonSection'].forEach(id =>
        document.getElementById(id).style.display = 'none');
}

function backToMedicineSearch() {
    document.getElementById('step2').style.display = 'none';
    document.getElementById('selectedBatchCard').style.display = 'none';
    document.getElementById('batchIdInput').value = '';
    medicineSearch.value = '';
    document.getElementById('clearSearch').style.display = 'none';
    ['qtySection','typeSection','reasonSection'].forEach(id =>
        document.getElementById(id).style.display = 'none');
}

function resetSearch() {
    medicineSearch.value = '';
    medicineResults.style.display = 'none';
    document.getElementById('clearSearch').style.display = 'none';
    backToMedicineSearch();
}

document.addEventListener('click', e => {
    if (!medicineSearch.contains(e.target)) medicineResults.style.display = 'none';
});
@endunless

// Chênh lệch realtime
document.getElementById('qtyAfter').addEventListener('input', function() {
    const after = parseInt(this.value) || 0;
    const diff  = after - currentQty;
    const badge = document.getElementById('diffBadge');
    const unit  = document.getElementById('unitLabel').textContent.trim();
    if (diff === 0) {
        badge.style.cssText = 'background:#f0f2f5;color:#666;';
        badge.textContent = '↔ Không thay đổi';
    } else if (diff > 0) {
        badge.style.cssText = 'background:#d4edda;color:#1E8449;';
        badge.innerHTML = `<i class="bi bi-arrow-up-circle me-1"></i>Tăng <strong>+${diff}</strong> ${unit}`;
    } else {
        badge.style.cssText = 'background:#f8d7da;color:#C0392B;';
        badge.innerHTML = `<i class="bi bi-arrow-down-circle me-1"></i>Giảm <strong>${diff}</strong> ${unit}`;
    }
});
@if($batch)
document.getElementById('qtyAfter').dispatchEvent(new Event('input'));
@endif
</script>
@endpush