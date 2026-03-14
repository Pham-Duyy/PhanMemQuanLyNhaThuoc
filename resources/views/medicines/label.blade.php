@extends('layouts.app')
@section('title', 'In nhãn — ' . $medicine->name)
@section('page-title', 'In nhãn thuốc')

@section('content')

<div class="page-header">
    <div>
        <h4 class="mb-0"><i class="bi bi-tag me-2 text-primary"></i>In nhãn thuốc</h4>
        <small class="text-muted">Thiết lập và in nhãn dán vào lọ / hộp thuốc</small>
    </div>
    <a href="{{ route('medicines.show', $medicine) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Quay lại
    </a>
</div>

<div class="row g-4">

    {{-- ── Cột trái: Form thiết lập ─────────────────────────────────────── --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header py-3 px-4">
                <h6 class="mb-0 fw-bold">⚙️ Thiết lập nhãn</h6>
            </div>
            <div class="card-body px-4 py-3">
                <form method="GET" action="{{ route('medicines.label.print', $medicine) }}"
                      target="_blank" id="labelForm">

                    {{-- Thông tin thuốc (readonly) --}}
                    <div class="p-3 rounded-3 mb-4" style="background:#f0f7ff;">
                        <div class="fw-bold">{{ $medicine->name }}</div>
                        <div class="text-muted small mt-1">
                            {{ $medicine->category?->name ?? '—' }} &nbsp;|&nbsp;
                            {{ $medicine->unit }} &nbsp;|&nbsp;
                            {{ $medicine->dosage_form ?? '' }}
                        </div>
                    </div>

                    {{-- Chọn lô --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lô hàng <span class="text-danger">*</span></label>
                        <select name="batch_id" class="form-select" onchange="updatePreview()">
                            <option value="">-- Không chọn lô cụ thể --</option>
                            @foreach($medicine->batches as $batch)
                            <option value="{{ $batch->id }}"
                                    data-expiry="{{ $batch->expiry_date->format('d/m/Y') }}"
                                    data-lot="{{ $batch->batch_number }}"
                                    {{ $selectedBatch?->id == $batch->id ? 'selected' : '' }}>
                                Lô {{ $batch->batch_number }} — HSD: {{ $batch->expiry_date->format('d/m/Y') }}
                                (Tồn: {{ number_format($batch->current_quantity) }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tên bệnh nhân --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên bệnh nhân</label>
                        <input type="text" name="patient_name" class="form-control"
                               value="{{ $patientName }}"
                               placeholder="VD: Nguyễn Văn A"
                               oninput="updatePreview()">
                    </div>

                    {{-- Cách dùng --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Liều dùng / Cách dùng</label>
                        <input type="text" name="dosage" class="form-control"
                               value="{{ $dosage }}"
                               placeholder="VD: Ngày 3 lần, mỗi lần 1 viên sau ăn"
                               oninput="updatePreview()">
                        {{-- Gợi ý nhanh --}}
                        <div class="mt-2 d-flex gap-1 flex-wrap">
                            @foreach(['Ngày 2 lần, mỗi lần 1 viên','Ngày 3 lần, mỗi lần 1 viên sau ăn','Sáng 1v - Tối 1v','Khi đau uống 1 viên','Uống 1 viên trước khi ngủ'] as $d)
                            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2"
                                    style="font-size:11px;"
                                    onclick="document.querySelector('[name=dosage]').value='{{ $d }}'; updatePreview();">
                                {{ $d }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Ghi chú --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ghi chú thêm</label>
                        <input type="text" name="note" class="form-control"
                               value="{{ $customNote }}"
                               placeholder="VD: Bảo quản nơi khô ráo, tránh ánh sáng"
                               oninput="updatePreview()">
                    </div>

                    <hr>

                    {{-- Kích thước nhãn --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kích thước nhãn</label>
                        <div class="d-flex gap-2">
                            @foreach(['small' => 'Nhỏ (5×3cm)', 'medium' => 'Vừa (7×4cm)', 'large' => 'Lớn (10×5cm)'] as $sz => $lbl)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="size"
                                       id="sz_{{ $sz }}" value="{{ $sz }}"
                                       {{ $sz === 'medium' ? 'checked' : '' }}
                                       onchange="updatePreview()">
                                <label class="form-check-label small" for="sz_{{ $sz }}">{{ $lbl }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Số lượng nhãn --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Số lượng nhãn in</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="number" name="quantity" class="form-control"
                                   value="{{ $quantity }}" min="1" max="100"
                                   style="width:100px;" oninput="updatePreview()">
                            <span class="text-muted small">nhãn (tối đa 100)</span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary fw-semibold w-100">
                        <i class="bi bi-printer me-1"></i>Xem trước & In nhãn
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Cột phải: Preview nhãn ───────────────────────────────────────── --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">👁️ Xem trước nhãn</h6>
                <span class="badge bg-info">Preview thời gian thực</span>
            </div>
            <div class="card-body px-4 py-4">

                {{-- Nhãn MEDIUM preview --}}
                <div id="labelPreview" class="mx-auto"
                     style="border:2px solid #333;border-radius:8px;padding:14px 18px;max-width:380px;background:#fff;font-family:'Times New Roman',serif;box-shadow:0 4px 16px rgba(0,0,0,.12);">

                    {{-- Tiêu đề nhà thuốc --}}
                    <div style="text-align:center;border-bottom:1.5px solid #333;padding-bottom:6px;margin-bottom:8px;">
                        <div style="font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:0.5px;">
                            {{ auth()->user()->pharmacy?->name ?? 'NHÀ THUỐC GPP' }}
                        </div>
                        <div style="font-size:10px;color:#555;">
                            {{ auth()->user()->pharmacy?->address ?? '' }}
                        </div>
                    </div>

                    {{-- Tên thuốc --}}
                    <div id="prev_name"
                         style="font-size:17px;font-weight:bold;text-align:center;text-transform:uppercase;margin-bottom:4px;color:#1a1a1a;">
                        {{ $medicine->name }}
                    </div>

                    {{-- Dạng bào chế & đơn vị --}}
                    <div style="text-align:center;font-size:11px;color:#555;margin-bottom:10px;">
                        {{ $medicine->dosage_form ?? '' }}
                        {{ $medicine->unit ? '(' . $medicine->unit . ')' : '' }}
                    </div>

                    {{-- Bệnh nhân --}}
                    <div id="prev_patient"
                         style="font-size:13px;margin-bottom:4px;">
                        <strong>Bệnh nhân:</strong>
                        <span>{{ $patientName ?: '.................................' }}</span>
                    </div>

                    {{-- Cách dùng --}}
                    <div id="prev_dosage"
                         style="font-size:13px;margin-bottom:4px;line-height:1.5;">
                        <strong>Cách dùng:</strong>
                        <span>{{ $dosage ?: '.................................' }}</span>
                    </div>

                    {{-- Lô & HSD --}}
                    <div style="display:flex;justify-content:space-between;margin-top:8px;padding-top:6px;border-top:1px dashed #aaa;font-size:11px;">
                        <span>
                            <strong>Lô:</strong>
                            <span id="prev_lot">{{ $selectedBatch?->batch_number ?? '—' }}</span>
                        </span>
                        <span>
                            <strong>HSD:</strong>
                            <span id="prev_expiry">{{ $selectedBatch?->expiry_date->format('d/m/Y') ?? '—' }}</span>
                        </span>
                    </div>

                    {{-- Ghi chú --}}
                    <div id="prev_note"
                         style="font-size:10px;color:#666;margin-top:6px;font-style:italic;">
                        {{ $customNote ?: '' }}
                    </div>

                    {{-- Footer --}}
                    <div style="text-align:center;margin-top:10px;font-size:9px;color:#999;border-top:1px solid #eee;padding-top:4px;">
                        Ngày {{ now()->format('d/m/Y') }} — Tuân thủ chuẩn GPP
                    </div>
                </div>

                <div class="mt-3 text-center text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    Preview hiển thị nhãn kích thước vừa. Nhãn thực tế sẽ khớp kích thước được chọn.
                </div>
            </div>
        </div>

        {{-- Hướng dẫn --}}
        <div class="card mt-3">
            <div class="card-body px-4 py-3">
                <div class="fw-semibold small mb-2">📌 Hướng dẫn in nhãn:</div>
                <ul class="text-muted small mb-0">
                    <li>Chuẩn bị giấy nhãn dán phù hợp kích thước đã chọn</li>
                    <li>Nhấn <strong>"Xem trước & In nhãn"</strong> → trình duyệt sẽ mở trang in</li>
                    <li>Trong hộp thoại in: tắt <em>Header/Footer</em>, chọn <em>Background graphics</em></li>
                    <li>Khuyến nghị: dùng máy in nhiệt Zebra ZD230 hoặc Brother QL</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function updatePreview() {
    const patientName = document.querySelector('[name=patient_name]').value;
    const dosage      = document.querySelector('[name=dosage]').value;
    const note        = document.querySelector('[name=note]').value;
    const batchSelect = document.querySelector('[name=batch_id]');
    const opt         = batchSelect?.options[batchSelect.selectedIndex];

    document.getElementById('prev_patient').innerHTML =
        '<strong>Bệnh nhân:</strong> ' + (patientName || '.................................');
    document.getElementById('prev_dosage').innerHTML =
        '<strong>Cách dùng:</strong> ' + (dosage || '.................................');
    document.getElementById('prev_note').textContent = note;

    if (opt && opt.value) {
        document.getElementById('prev_lot').textContent    = opt.dataset.lot    || '—';
        document.getElementById('prev_expiry').textContent = opt.dataset.expiry || '—';
    }
}
</script>
@endpush