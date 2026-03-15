@extends('layouts.app')
@section('title', 'Tạo đơn nhập hàng')
@section('page-title', 'Tạo đơn nhập hàng')

@push('styles')
<style>
/* ── Layout ─────────────────────────────────────────── */
.po-grid { display: grid; grid-template-columns: 360px 1fr; gap: 20px; align-items: start; }
@media(max-width:1100px){ .po-grid { grid-template-columns: 1fr; } }

/* ── Sticky left col ────────────────────────────────── */
.left-sticky { position: sticky; top: 80px; }
@media(max-width:1100px){ .left-sticky { position: static; } }

/* ── Cards ──────────────────────────────────────────── */
.po-card { background:#fff; border-radius:14px; border:1px solid #E2E8F0;
           box-shadow:0 1px 6px rgba(0,0,0,.06); overflow:hidden; }
.po-card-header { padding:14px 20px; border-bottom:1px solid #F1F5F9;
                  display:flex; align-items:center; justify-content:space-between; }
.po-card-header h6 { margin:0; font-weight:700; font-size:14px; }
.po-card-body { padding:20px; }

/* ── Items table ────────────────────────────────────── */
#itemsTable { width:100%; border-collapse:collapse; font-size:13px; }
#itemsTable thead th {
    background:#F8FAFC; padding:10px 10px; font-weight:700; font-size:12px;
    text-transform:uppercase; letter-spacing:.4px; color:#64748B;
    border-bottom:2px solid #E2E8F0; white-space:nowrap;
}
.item-row td { padding:8px 8px; border-bottom:1px solid #F1F5F9; vertical-align:middle; }
.item-row:last-child td { border-bottom:none; }
.item-row:hover { background:#FAFBFC; }

/* ── Form controls in table ─────────────────────────── */
.item-row .form-select,
.item-row .form-control { font-size:13px; border-radius:8px; border-color:#E2E8F0; }
.item-row .form-select:focus,
.item-row .form-control:focus { border-color:#0EA5A0; box-shadow:0 0 0 3px rgba(14,165,160,.12); }

/* ── Supplier badge ──────────────────────────────────── */
.sup-info { background:linear-gradient(135deg,#F0FDF9,#E6FFFA);
            border:1px solid #99F6E4; border-radius:10px; padding:12px 14px; font-size:13px; }
.sup-info .debt-ok  { color:#16A34A; font-weight:700; }
.sup-info .debt-warn{ color:#DC2626; font-weight:700; }

/* ── Summary card ────────────────────────────────────── */
.summary-row { display:flex; justify-content:space-between; align-items:center;
               padding:9px 0; border-bottom:1px solid #F1F5F9; }
.summary-row:last-of-type { border-bottom:none; }
.total-big { font-size:28px; font-weight:800; color:#0B5E5A; }

/* ── Submit button ───────────────────────────────────── */
.btn-submit { width:100%; padding:14px; border:none; border-radius:12px; cursor:pointer;
              font-size:15px; font-weight:700; color:#fff;
              background:linear-gradient(135deg,#0B5E5A,#0EA5A0);
              transition:opacity .2s; }
.btn-submit:hover { opacity:.9; }
.btn-submit:disabled { opacity:.6; cursor:not-allowed; }

/* ── Add row button ──────────────────────────────────── */
.btn-add-row { display:flex; align-items:center; gap:8px; width:100%; padding:10px 14px;
               background:#F8FAFC; border:2px dashed #CBD5E1; border-radius:10px;
               color:#64748B; font-size:13px; font-weight:600; cursor:pointer;
               transition:all .2s; }
.btn-add-row:hover { border-color:#0EA5A0; color:#0EA5A0; background:#F0FDFB; }

/* ── Delete button ───────────────────────────────────── */
.btn-del { width:28px; height:28px; border-radius:50%; border:none; background:#FEE2E2;
           color:#DC2626; font-size:16px; font-weight:700; cursor:pointer; line-height:1;
           transition:background .15s; display:flex; align-items:center; justify-content:center; }
.btn-del:hover { background:#FCA5A5; }

/* ── Warn bar ────────────────────────────────────────── */
.warn-bar { background:#FFF7ED; border:1px solid #FDBA74; border-radius:8px;
            padding:10px 14px; font-size:13px; color:#C2410C; margin-bottom:12px; }

/* ── Page header ─────────────────────────────────────── */
.page-header { display:flex; justify-content:space-between; align-items:center; }
</style>
@endpush

@section('content')

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3">
    <strong>Vui lòng kiểm tra lại:</strong>
    <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="page-header mb-3">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-plus-circle me-2 text-primary"></i>Tạo đơn nhập hàng</h4>
        <small class="text-muted">Đặt hàng từ nhà cung cấp — tuân thủ GPP/TT02-2018</small>
    </div>
    <a href="{{ route('purchase.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Quay lại
    </a>
</div>

<form action="{{ route('purchase.store') }}" method="POST" id="poForm">
@csrf

<div class="po-grid">

{{-- ══════════════════════════════════════════════
     CỘT TRÁI — Thông tin + Tổng kết
══════════════════════════════════════════════ --}}
<div class="left-sticky">

    {{-- Thông tin đơn hàng --}}
    <div class="po-card mb-3">
        <div class="po-card-header">
            <h6>📋 Thông tin đơn hàng</h6>
        </div>
        <div class="po-card-body">

            {{-- Nhà cung cấp --}}
            <div class="mb-3">
                <label class="form-label fw-semibold small">Nhà cung cấp <span class="text-danger">*</span></label>
                <select name="supplier_id" id="supplierSelect"
                        class="form-select @error('supplier_id') is-invalid @enderror" required>
                    <option value="">-- Chọn nhà cung cấp --</option>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}"
                            data-phone="{{ $s->contact_phone ?? $s->phone ?? '' }}"
                            data-debt="{{ $s->current_debt }}"
                            data-limit="{{ $s->debt_limit }}"
                            data-term="{{ $s->payment_term_days ?? 30 }}"
                            {{ old('supplier_id') == $s->id ? 'selected' : '' }}>
                        {{ $s->name }}
                    </option>
                    @endforeach
                </select>
                @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Supplier info box --}}
            <div id="supInfoBox" style="display:none;" class="sup-info mb-3">
                <div class="d-flex justify-content-between">
                    <span class="fw-bold" id="supName"></span>
                    <span id="supPhone" class="text-muted small"></span>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <span class="small text-muted">Công nợ hiện tại:</span>
                    <span id="supDebt" class="fw-bold"></span>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <span class="small text-muted">Hạn mức nợ:</span>
                    <span id="supLimit" class="small"></span>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <span class="small text-muted">Thời hạn thanh toán:</span>
                    <span id="supTerm" class="small fw-semibold"></span>
                </div>
                <div class="mt-2" id="supWarn" style="display:none;">
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle w-100 py-1">
                        ⚠ Vượt hạn mức tín dụng — cần thanh toán trước
                    </span>
                </div>
            </div>

            {{-- Số HĐ nhà cung cấp --}}
            <div class="mb-3">
                <label class="form-label fw-semibold small">Số hóa đơn NCC
                    <span class="text-muted fw-normal">(để đối chiếu thanh tra)</span>
                </label>
                <input type="text" name="supplier_invoice_no" class="form-control"
                       placeholder="VD: HD-2024-001234"
                       value="{{ old('supplier_invoice_no') }}">
            </div>

            {{-- Ngày đặt / nhận --}}
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label fw-semibold small">Ngày đặt hàng</label>
                    <input type="date" name="order_date" class="form-control"
                           value="{{ old('order_date', today()->format('Y-m-d')) }}">
                </div>
                <div class="col-6">
                    <label class="form-label fw-semibold small">Dự kiến nhận</label>
                    <input type="date" name="expected_date" class="form-control"
                           value="{{ old('expected_date', now()->addDays(3)->format('Y-m-d')) }}"
                           min="{{ today()->format('Y-m-d') }}">
                </div>
            </div>

            {{-- Phương thức thanh toán --}}
            <div class="mb-3">
                <label class="form-label fw-semibold small">Phương thức thanh toán</label>
                <div class="d-flex gap-2">
                    @foreach(['cash'=>'💵 Tiền mặt','transfer'=>'🏦 Chuyển khoản','debt'=>'📋 Ghi nợ NCC'] as $val=>$lbl)
                    <label class="flex-fill border rounded-3 py-2 text-center"
                           style="cursor:pointer;font-size:12px;font-weight:600;
                                  {{ old('payment_method','transfer')===$val ? 'background:#F0FDF9;border-color:#0EA5A0!important;color:#0EA5A0;' : 'color:#64748B;' }}">
                        <input type="radio" name="payment_method" value="{{ $val }}" class="d-none pm-radio"
                               {{ old('payment_method','transfer')===$val ? 'checked' : '' }}>
                        {{ $lbl }}
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Ghi chú --}}
            <div class="mb-0">
                <label class="form-label fw-semibold small">Ghi chú đơn hàng</label>
                <textarea name="note" class="form-control" rows="2"
                          placeholder="Ghi chú cho nhà cung cấp...">{{ old('note') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Tổng kết --}}
    <div class="po-card">
        <div class="po-card-header">
            <h6>🧾 Tổng kết đơn hàng</h6>
        </div>
        <div class="po-card-body">
            <div class="summary-row">
                <span class="text-muted small">Số mặt hàng</span>
                <strong id="sumRows">0</strong>
            </div>
            <div class="summary-row">
                <span class="text-muted small">Tổng số lượng</span>
                <strong id="sumQty">0</strong>
            </div>
            <div class="summary-row">
                <span class="text-muted small">Tổng tiền hàng</span>
                <strong id="sumSubtotal" class="text-primary">0đ</strong>
            </div>
            <div class="summary-row">
                <span class="text-muted small">Chiết khấu tổng</span>
                <div class="d-flex align-items-center gap-1">
                    <input type="number" name="discount_percent" id="discountPct"
                           class="form-control form-control-sm text-end" min="0" max="100"
                           step="0.5" placeholder="0" value="{{ old('discount_percent',0) }}"
                           style="width:60px;">
                    <span class="text-muted small">%</span>
                    <span id="sumDiscount" class="text-danger small fw-bold ms-1">-0đ</span>
                </div>
            </div>
            <div class="py-3 text-center border-top mt-2">
                <div class="text-muted small mb-1">Tổng cộng</div>
                <div class="total-big" id="sumTotal">0đ</div>
            </div>

            <button type="submit" class="btn-submit mt-2" id="btnSubmit">
                <i class="bi bi-send me-2"></i>Gửi đơn nhập hàng
            </button>
            <div class="text-center mt-2 text-muted small">
                Đơn sẽ ở trạng thái <strong>Chờ duyệt</strong>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     CỘT PHẢI — Danh sách thuốc
══════════════════════════════════════════════ --}}
<div>
    <div class="po-card">
        <div class="po-card-header">
            <h6>💊 Danh sách thuốc cần nhập</h6>
            <div class="d-flex gap-2 align-items-center">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" id="badgeCount">0 mặt hàng</span>
                <button type="button" class="btn btn-sm btn-outline-success px-3"
                        data-bs-toggle="modal" data-bs-target="#quickMedModal"
                        title="Thêm thuốc mới chưa có trong danh sách">
                    <i class="bi bi-plus-square me-1"></i>Thuốc mới
                </button>
                <button type="button" class="btn btn-sm btn-primary px-3" id="btnAddRow">
                    <i class="bi bi-plus-lg me-1"></i>Thêm dòng
                </button>
            </div>
        </div>

        {{-- Cảnh báo GPP --}}
        <div class="px-4 pt-3" id="narcWarn" style="display:none;">
            <div class="warn-bar">
                ⚠ Đơn hàng có <strong>thuốc gây nghiện / hướng tâm thần</strong> — cần lưu hồ sơ riêng theo TT27/2021.
            </div>
        </div>

        <div class="table-responsive">
            <table id="itemsTable">
                <thead>
                    <tr>
                        <th style="width:30px;">#</th>
                        <th style="min-width:220px;">Tên thuốc</th>
                        <th style="width:90px;">ĐVT</th>
                        <th style="width:100px;">SL đặt</th>
                        <th style="width:130px;">Giá nhập (đ)</th>z
                        <th style="width:80px;">CK (%)</th>
                        <th style="width:130px;" class="text-end">Thành tiền</th>
                        <th style="width:140px;">Lô / HSD</th>
                        <th style="width:36px;"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody"></tbody>
                <tfoot>
                    <tr style="background:#F8FAFC;">
                        <td colspan="6" class="text-end fw-bold px-3 py-3">Tổng cộng:</td>
                        <td class="text-end fw-bold text-primary px-2" id="tableTotal" style="font-size:15px;">0đ</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="px-4 py-3 border-top">
            <button type="button" class="btn-add-row" id="btnAddRow2">
                <i class="bi bi-plus-circle me-2"></i>Thêm dòng thuốc
            </button>
        </div>

        <div class="px-4 pb-3">
            <div class="alert alert-info py-2 mb-0 small">
                <i class="bi bi-info-circle me-1"></i>
                <strong>Số lô & hạn dùng</strong> có thể điền ngay hoặc điền sau khi nhận hàng thực tế.
                Để trống nếu chưa biết.
            </div>
        </div>
    </div>
</div>

</div>{{-- /.po-grid --}}
</form>

{{-- ── ROW TEMPLATE (ngoài form, đúng cách) ── --}}
<template id="rowTpl">
<tr class="item-row" data-idx="__IDX__">
    <td class="text-center text-muted fw-bold row-no" style="font-size:12px;">__NO__</td>
    <td>
        <select name="items[__IDX__][medicine_id]" class="form-select form-select-sm med-sel" required>
            <option value="">-- Chọn thuốc --</option>
            @foreach($medicines as $m)
            <option value="{{ $m->id }}"
                    data-unit="{{ $m->unit }}"
                    data-price="{{ $m->sell_price }}"
                    data-narc="{{ $m->is_narcotic ? 1 : 0 }}"
                    data-psyc="{{ $m->is_psychotropic ? 1 : 0 }}"
                    data-rx="{{ $m->requires_prescription ? 1 : 0 }}"
                    data-anti="{{ $m->is_antibiotic ? 1 : 0 }}"
                    data-reg="{{ $m->registration_number ?? '' }}"
                    data-generic="{{ $m->generic_name ?? '' }}"
                    data-conc="{{ $m->concentration ?? '' }}">
                {{ $m->name }}{{ $m->concentration ? ' '.$m->concentration : '' }}{{ $m->is_narcotic ? ' [GN]' : ($m->is_psychotropic ? ' [HTT]' : ($m->requires_prescription ? ' [Rx]' : '')) }}
            </option>
            @endforeach
        </select>
        <div class="med-info mt-1" style="display:none;font-size:11px;color:#64748B;line-height:1.6;">
            <span class="med-info-text"></span>
        </div>
    </td>
    <td>
        <input type="text" name="items[__IDX__][unit]" class="form-control form-control-sm item-unit"
               placeholder="Viên" required>
    </td>
    <td>
        <input type="number" name="items[__IDX__][quantity]" class="form-control form-control-sm item-qty"
               min="1" value="1" required>
    </td>
    <td>
        <input type="number" name="items[__IDX__][purchase_price]" class="form-control form-control-sm item-price"
               min="0" step="500" placeholder="0" required>
    </td>
    <td>
        <input type="number" name="items[__IDX__][discount_percent]" class="form-control form-control-sm item-ck"
               min="0" max="100" step="0.5" placeholder="0" value="0">
    </td>
    <td class="text-end fw-bold item-total pe-2" style="font-size:13px;color:#0B5E5A;">0đ</td>
    <td>
        <div style="min-width:120px;">
            <input type="text" name="items[__IDX__][batch_number]"
                   class="form-control form-control-sm mb-1" placeholder="Số lô" style="font-size:11px;">
            <input type="date" name="items[__IDX__][expiry_date]"
                   class="form-control form-control-sm" style="font-size:11px;"
                   min="{{ now()->addDays(30)->format('Y-m-d') }}">
        </div>
    </td>
    <td>
        <button type="button" class="btn-del" title="Xóa dòng">×</button>
    </td>
</tr>
</template>

{{-- ════════════════════════════════════════════════════════════════════
     MODAL: THÊM THUỐC MỚI NHANH
════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="quickMedModal" tabindex="-1" aria-labelledby="quickMedLabel">
<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
<div class="modal-content border-0 shadow-lg" style="border-radius:20px;overflow:hidden;">

    <div class="modal-header border-0 px-4 py-3"
         style="background:linear-gradient(135deg,#0B5E5A,#0EA5A0);">
        <div>
            <h5 class="modal-title text-white fw-bold mb-0" id="quickMedLabel">
                <i class="bi bi-plus-circle me-2"></i>Thêm thuốc mới vào danh sách
            </h5>
            <div class="text-white small opacity-75 mt-1">
                Thuốc sẽ được lưu vào danh mục và tự động thêm vào đơn hàng
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body px-4 py-4">
        <div id="qmAlert" class="alert alert-danger d-none py-2 mb-3"></div>
        <div id="qmSuccess" class="alert alert-success d-none py-2 mb-3"></div>

        <div class="d-flex gap-2 flex-wrap mb-4">
            <label class="d-flex align-items-center gap-2 border rounded-3 px-3 py-2" style="cursor:pointer;font-size:13px;font-weight:600;">
                <input type="checkbox" id="qm_rx" class="form-check-input mt-0">
                <span>🟣 Thuốc kê đơn</span>
            </label>
            <label class="d-flex align-items-center gap-2 border rounded-3 px-3 py-2" style="cursor:pointer;font-size:13px;font-weight:600;">
                <input type="checkbox" id="qm_narc" class="form-check-input mt-0">
                <span>🔴 Gây nghiện</span>
            </label>
            <label class="d-flex align-items-center gap-2 border rounded-3 px-3 py-2" style="cursor:pointer;font-size:13px;font-weight:600;">
                <input type="checkbox" id="qm_psyc" class="form-check-input mt-0">
                <span>🟠 Hướng tâm thần</span>
            </label>
            <label class="d-flex align-items-center gap-2 border rounded-3 px-3 py-2" style="cursor:pointer;font-size:13px;font-weight:600;">
                <input type="checkbox" id="qm_anti" class="form-check-input mt-0">
                <span>🟡 Kháng sinh</span>
            </label>
        </div>

        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Tên thương mại <span class="text-danger">*</span></label>
                <input type="text" id="qm_name" class="form-control form-control-lg"
                       placeholder="VD: Panadol Extra, Augmentin 625mg...">
            </div>
            <div class="col-md-7">
                <label class="form-label fw-semibold">Hoạt chất (generic name)
                    <span class="text-muted fw-normal small">— BẮT BUỘC theo TT02</span>
                </label>
                <input type="text" id="qm_generic" class="form-control"
                       placeholder="VD: Paracetamol, Amoxicillin + Clavulanic acid">
            </div>
            <div class="col-md-5">
                <label class="form-label fw-semibold">Hàm lượng / Nồng độ
                    <span class="text-muted fw-normal small">— BẮT BUỘC</span>
                </label>
                <input type="text" id="qm_conc" class="form-control" placeholder="VD: 500mg, 250mg/5ml">
            </div>
            <div class="col-md-7">
                <label class="form-label fw-semibold">Dạng bào chế
                    <span class="text-muted fw-normal small">— BẮT BUỘC theo TT02</span>
                </label>
                <select id="qm_form" class="form-select">
                    <option value="">-- Chọn dạng --</option>
                    @foreach(['Viên nén','Viên nén bao phim','Viên nang cứng','Viên nang mềm',
                              'Viên sủi','Viên ngậm','Siro','Dung dịch uống','Hỗn dịch uống',
                              'Dung dịch tiêm','Bột pha tiêm','Dung dịch nhỏ mắt',
                              'Kem bôi da','Gel bôi da','Thuốc đặt','Thuốc hít','Thuốc xịt','Khác'] as $f)
                    <option>{{ $f }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-semibold">Đơn vị tính <span class="text-danger">*</span></label>
                <select id="qm_unit" class="form-select">
                    @foreach(['Viên','Viên nang','Gói','Chai','Lọ','Tuýp','Ống','Hộp','Vỉ','Cái','Ml'] as $u)
                    <option>{{ $u }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-7">
                <label class="form-label fw-semibold">Nhà sản xuất</label>
                <input type="text" id="qm_mfr" class="form-control" placeholder="VD: GSK, Sanofi, Pymepharco">
            </div>
            <div class="col-md-5">
                <label class="form-label fw-semibold">Nước sản xuất</label>
                <select id="qm_country" class="form-select">
                    <option value="">-- Chọn --</option>
                    @foreach(['Việt Nam','Pháp','Đức','Mỹ','Anh','Ý','Tây Ban Nha','Ấn Độ','Hàn Quốc','Nhật Bản','Trung Quốc','Thụy Sĩ','Bỉ','Đan Mạch','Khác'] as $c)
                    <option>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Số đăng ký lưu hành
                    <span class="text-muted fw-normal small">— quan trọng GPP</span>
                </label>
                <input type="text" id="qm_reg" class="form-control" placeholder="VD: VD-12345-10">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nhóm thuốc</label>
                <select id="qm_cat" class="form-select">
                    <option value="">-- Chọn nhóm --</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Giá bán lẻ dự kiến (đ)</label>
                <input type="number" id="qm_price" class="form-control" min="0" step="500" placeholder="0">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Điều kiện bảo quản</label>
                <select id="qm_storage" class="form-select">
                    <option value="">-- Chọn --</option>
                    @foreach(['Nhiệt độ phòng (dưới 30°C)','Nơi khô thoáng, tránh ánh sáng',
                              'Bảo quản lạnh 2–8°C','Đông lạnh dưới -18°C',
                              'Tránh ánh sáng trực tiếp','Nhiệt độ phòng, độ ẩm < 75%'] as $st)
                    <option>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12" id="narcWarnQm" style="display:none;">
                <div class="alert alert-warning py-2 mb-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Thuốc gây nghiện / hướng tâm thần</strong> — phải lưu sổ theo dõi
                    riêng và báo cáo Sở Y tế theo TT27/2021.
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Hủy</button>
        <button type="button" class="btn fw-bold px-5 text-white" id="btnSaveMed"
                style="background:linear-gradient(135deg,#0B5E5A,#0EA5A0);border-radius:10px;">
            <i class="bi bi-plus-circle me-2"></i>Tạo thuốc & thêm vào đơn
        </button>
    </div>
</div>
</div>
</div>

@endsection

@push('scripts')
<script>
(function(){
'use strict';

const suppliers = @json($suppliers->keyBy('id'));
let rowCount    = 0;

// ── Format tiền ───────────────────────────────────────────────────────
function fmtMoney(n){ return new Intl.NumberFormat('vi-VN').format(Math.round(n||0)) + 'đ'; }

// ── Supplier select ───────────────────────────────────────────────────
document.getElementById('supplierSelect').addEventListener('change', function(){
    const s = suppliers[this.value];
    if(!s){ document.getElementById('supInfoBox').style.display='none'; return; }

    document.getElementById('supInfoBox').style.display = '';
    document.getElementById('supName').textContent  = s.name;
    document.getElementById('supPhone').textContent = s.contact_phone || s.phone || '';
    document.getElementById('supTerm').textContent  = (s.payment_term_days || 30) + ' ngày';

    const debt  = parseFloat(s.current_debt) || 0;
    const limit = parseFloat(s.debt_limit)   || 0;
    const debtEl  = document.getElementById('supDebt');
    const limitEl = document.getElementById('supLimit');
    const warnEl  = document.getElementById('supWarn');

    debtEl.textContent  = fmtMoney(debt);
    debtEl.className    = debt > 0 ? 'debt-warn' : 'debt-ok';
    limitEl.textContent = limit > 0 ? fmtMoney(limit) : 'Không giới hạn';
    warnEl.style.display = (limit > 0 && debt >= limit * 0.9) ? '' : 'none';
});

// ── Payment method radio UX ───────────────────────────────────────────
document.querySelectorAll('.pm-radio').forEach(r => {
    r.addEventListener('change', () => {
        document.querySelectorAll('label:has(.pm-radio)').forEach(l => {
            l.style.background = ''; l.style.borderColor = ''; l.style.color = '#64748B';
        });
        const lbl = r.closest('label');
        lbl.style.background  = '#F0FDF9';
        lbl.style.borderColor = '#0EA5A0';
        lbl.style.color       = '#0EA5A0';
    });
});

// ── Add row ───────────────────────────────────────────────────────────
function addRow(){
    const idx  = rowCount++;
    const noNow = document.querySelectorAll('.item-row').length + 1;
    const tpl  = document.getElementById('rowTpl');
    const html = tpl.innerHTML
        .replaceAll('__IDX__', idx)
        .replaceAll('__NO__', noNow);
    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', html);
    reNumber();
    recalcAll();
}

document.getElementById('btnAddRow').addEventListener('click', addRow);
document.getElementById('btnAddRow2').addEventListener('click', addRow);
window.addPurchaseRow = addRow;

// Dòng mặc định
addRow();

// ── Remove row ────────────────────────────────────────────────────────
document.getElementById('itemsBody').addEventListener('click', function(e){
    const btn = e.target.closest('.btn-del');
    if(!btn) return;
    if(document.querySelectorAll('.item-row').length <= 1){
        alert('Đơn hàng cần ít nhất 1 loại thuốc.'); return;
    }
    btn.closest('tr').remove();
    reNumber();
    recalcAll();
});

// ── Medicine select change ────────────────────────────────────────────
document.getElementById('itemsBody').addEventListener('change', function(e){
    if(!e.target.classList.contains('med-sel')) return;
    const row = e.target.closest('tr');
    const opt = e.target.selectedOptions[0];
    if(!opt || !opt.value){ clearMedInfo(row); recalcAll(); return; }

    row.querySelector('.item-unit').value = opt.dataset.unit || '';

    const estPrice = Math.round((parseFloat(opt.dataset.price)||0) * 0.65 / 500) * 500;
    const priceEl  = row.querySelector('.item-price');
    if(!priceEl.value || priceEl.value === '0') priceEl.value = estPrice || '';

    const infoBox  = row.querySelector('.med-info');
    const infoText = row.querySelector('.med-info-text');
    let parts = [];
    if(opt.dataset.generic) parts.push('Hoạt chất: ' + opt.dataset.generic);
    if(opt.dataset.conc)    parts.push(opt.dataset.conc);
    if(opt.dataset.reg)     parts.push('SĐK: ' + opt.dataset.reg);
    const flags = [];
    if(opt.dataset.narc==='1') flags.push('🔴 Gây nghiện');
    if(opt.dataset.psyc==='1') flags.push('🟠 Hướng TT');
    if(opt.dataset.rx  ==='1') flags.push('🟣 Kê đơn');
    if(opt.dataset.anti==='1') flags.push('🟡 Kháng sinh');
    if(flags.length) parts.push(flags.join(' '));

    infoText.textContent = parts.join(' · ');
    infoBox.style.display = parts.length ? '' : 'none';

    checkNarcWarn();
    recalcRow(row);
    recalcAll();
});

function clearMedInfo(row){
    row.querySelector('.item-unit').value = '';
    row.querySelector('.item-price').value = '';
    row.querySelector('.med-info').style.display = 'none';
    row.querySelector('.item-total').textContent = '0đ';
}

// ── Input events ──────────────────────────────────────────────────────
document.getElementById('itemsBody').addEventListener('input', function(e){
    const cl = e.target.classList;
    if(cl.contains('item-qty') || cl.contains('item-price') || cl.contains('item-ck')){
        recalcRow(e.target.closest('tr'));
        recalcAll();
    }
});

document.getElementById('discountPct').addEventListener('input', recalcAll);

// ── Recalc row ────────────────────────────────────────────────────────
function recalcRow(row){
    const qty    = parseFloat(row.querySelector('.item-qty').value)   || 0;
    const price  = parseFloat(row.querySelector('.item-price').value) || 0;
    const ck     = parseFloat(row.querySelector('.item-ck').value)    || 0;
    const total  = qty * price * (1 - ck / 100);
    row.querySelector('.item-total').textContent = fmtMoney(total);
    return total;
}

// ── Recalc all ────────────────────────────────────────────────────────
function recalcAll(){
    let subtotal = 0, totalQty = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        subtotal += recalcRow(row);
        totalQty += parseFloat(row.querySelector('.item-qty').value) || 0;
    });

    const rows    = document.querySelectorAll('.item-row').length;
    const discPct = parseFloat(document.getElementById('discountPct').value) || 0;
    const disc    = subtotal * discPct / 100;
    const total   = subtotal - disc;

    document.getElementById('sumRows').textContent     = rows;
    document.getElementById('sumQty').textContent      = totalQty;
    document.getElementById('sumSubtotal').textContent = fmtMoney(subtotal);
    document.getElementById('sumDiscount').textContent = '-' + fmtMoney(disc);
    document.getElementById('sumTotal').textContent    = fmtMoney(total);
    document.getElementById('tableTotal').textContent  = fmtMoney(subtotal);
    document.getElementById('badgeCount').textContent  = rows + ' mặt hàng';
}

// ── Renumber ──────────────────────────────────────────────────────────
function reNumber(){
    document.querySelectorAll('.item-row').forEach((r, i) => {
        r.querySelector('.row-no').textContent = i + 1;
    });
}

// ── Narc warning ──────────────────────────────────────────────────────
function checkNarcWarn(){
    let has = false;
    document.querySelectorAll('.med-sel').forEach(sel => {
        const opt = sel.selectedOptions[0];
        if(opt && (opt.dataset.narc==='1' || opt.dataset.psyc==='1')) has = true;
    });
    document.getElementById('narcWarn').style.display = has ? '' : 'none';
}

// ── Form submit validation ────────────────────────────────────────────
document.getElementById('poForm').addEventListener('submit', function(e){
    const rows  = document.querySelectorAll('.item-row');
    let valid   = true;
    rows.forEach(r => {
        if(!r.querySelector('.med-sel').value) valid = false;
    });
    if(!valid){
        e.preventDefault();
        alert('Vui lòng chọn thuốc cho tất cả các dòng hoặc xóa dòng trống.');
        return;
    }
    document.getElementById('btnSubmit').textContent = '⏳ Đang gửi...';
    document.getElementById('btnSubmit').disabled = true;
});

})();
</script>

<script>
// ── Quick create medicine ─────────────────────────────────────────────
(function(){
    const CSRF   = document.querySelector('meta[name="csrf-token"]').content;
    const QMURL  = '{{ route("purchase.quick-create-medicine") }}';

    function openQuickMed(){
        bootstrap.Modal.getOrCreateInstance(
            document.getElementById('quickMedModal'),
            { backdrop: true, keyboard: true }
        ).show();
    }

    // Nút "Thuốc mới" trong header bảng
    document.querySelector('[data-bs-target="#quickMedModal"]')
        ?.addEventListener('click', openQuickMed);

    // Toggle cảnh báo gây nghiện trong modal
    ['qm_narc','qm_psyc'].forEach(id => {
        document.getElementById(id).addEventListener('change', () => {
            const show = document.getElementById('qm_narc').checked ||
                         document.getElementById('qm_psyc').checked;
            document.getElementById('narcWarnQm').style.display = show ? '' : 'none';
        });
    });

    document.getElementById('btnSaveMed').addEventListener('click', async () => {
        const btn     = document.getElementById('btnSaveMed');
        const name    = document.getElementById('qm_name').value.trim();
        const alertEl = document.getElementById('qmAlert');
        const okEl    = document.getElementById('qmSuccess');

        alertEl.classList.add('d-none');
        okEl.classList.add('d-none');

        if(!name){
            alertEl.textContent = 'Vui lòng nhập tên thuốc.';
            alertEl.classList.remove('d-none');
            return;
        }

        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang tạo...';

        try {
            const res = await fetch(QMURL, {
                method:  'POST',
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({
                    name:                  name,
                    generic_name:          document.getElementById('qm_generic').value,
                    concentration:         document.getElementById('qm_conc').value,
                    dosage_form:           document.getElementById('qm_form').value,
                    unit:                  document.getElementById('qm_unit').value,
                    manufacturer:          document.getElementById('qm_mfr').value,
                    country_of_origin:     document.getElementById('qm_country').value,
                    registration_number:   document.getElementById('qm_reg').value,
                    category_id:           document.getElementById('qm_cat').value || null,
                    sell_price:            document.getElementById('qm_price').value || 0,
                    storage_instruction:   document.getElementById('qm_storage').value,
                    requires_prescription: document.getElementById('qm_rx').checked   ? 1 : 0,
                    is_narcotic:           document.getElementById('qm_narc').checked  ? 1 : 0,
                    is_psychotropic:       document.getElementById('qm_psyc').checked  ? 1 : 0,
                    is_antibiotic:         document.getElementById('qm_anti').checked  ? 1 : 0,
                }),
            });
            const data = await res.json();

            if(data.success){
                const med   = data.medicine;
                const label = med.name
                    + (med.concentration ? ' ' + med.concentration : '')
                    + (med.is_narcotic ? ' [GN]' : med.is_psychotropic ? ' [HTT]' : med.requires_prescription ? ' [Rx]' : '');

                // Thêm option vào tất cả select thuốc đang có
                document.querySelectorAll('.med-sel').forEach(sel => {
                    const opt = new Option(label, med.id);
                    opt.dataset.unit    = med.unit;
                    opt.dataset.price   = med.sell_price;
                    opt.dataset.narc    = med.is_narcotic       ? '1':'0';
                    opt.dataset.psyc    = med.is_psychotropic   ? '1':'0';
                    opt.dataset.rx      = med.requires_prescription ? '1':'0';
                    opt.dataset.anti    = med.is_antibiotic     ? '1':'0';
                    opt.dataset.reg     = med.registration_number || '';
                    opt.dataset.generic = med.generic_name      || '';
                    opt.dataset.conc    = med.concentration     || '';
                    sel.appendChild(opt);
                });

                // Chọn vào dòng trống hoặc thêm dòng mới
                let targetSel = null;
                document.querySelectorAll('.med-sel').forEach(sel => {
                    if(!sel.value && !targetSel) targetSel = sel;
                });
                if(!targetSel){
                    window.addPurchaseRow();
                    const all = document.querySelectorAll('.med-sel');
                    targetSel = all[all.length - 1];
                }
                targetSel.value = med.id;
                targetSel.dispatchEvent(new Event('change', { bubbles:true }));

                okEl.innerHTML = '✅ Đã tạo <strong>' + med.name + '</strong> (Mã: ' + med.code + ') và thêm vào đơn!';
                okEl.classList.remove('d-none');

                setTimeout(() => {
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('quickMedModal')).hide();
                    resetQmForm();
                }, 1500);
            } else {
                const errs = data.errors ? Object.values(data.errors).flat().join('<br>') : 'Có lỗi xảy ra.';
                alertEl.innerHTML = errs;
                alertEl.classList.remove('d-none');
            }
        } catch(err) {
            alertEl.textContent = 'Lỗi kết nối: ' + err.message;
            alertEl.classList.remove('d-none');
        } finally {
            btn.disabled  = false;
            btn.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Tạo thuốc & thêm vào đơn';
        }
    });

    function resetQmForm(){
        ['qm_name','qm_generic','qm_conc','qm_mfr','qm_reg','qm_price'].forEach(id =>
            document.getElementById(id).value = ''
        );
        ['qm_form','qm_unit','qm_country','qm_cat','qm_storage'].forEach(id =>
            document.getElementById(id).selectedIndex = 0
        );
        ['qm_rx','qm_narc','qm_psyc','qm_anti'].forEach(id =>
            document.getElementById(id).checked = false
        );
        document.getElementById('narcWarnQm').style.display = 'none';
        document.getElementById('qmAlert').classList.add('d-none');
        document.getElementById('qmSuccess').classList.add('d-none');
    }

    // Cleanup backdrop Bootstrap khi modal đóng
    document.getElementById('quickMedModal').addEventListener('hidden.bs.modal', function(){
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    });
})();
</script>
@endpush