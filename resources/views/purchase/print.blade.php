<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu nhập hàng {{ $purchaseOrder->code }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', serif; font-size: 13px; color: #000; padding: 20px 30px; }

        /* ── Header ── */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
        .pharmacy-name { font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .pharmacy-sub  { font-size: 11px; color: #333; margin-top: 2px; }
        .gpp-badge { border: 1.5px solid #000; padding: 1px 7px; font-size: 10px;
                     font-weight: bold; letter-spacing: 1px; margin-left: 6px; vertical-align: middle; }
        .divider  { border: none; border-top: 1px solid #000; margin: 8px 0; }
        .divider2 { border: none; border-top: 2.5px double #000; margin: 8px 0; }

        /* ── Title ── */
        .doc-title { text-align: center; margin: 8px 0 4px; }
        .doc-title h2 { font-size: 19px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .doc-title .sub { font-size: 12px; color: #444; margin-top: 3px; }
        .status-badge { display:inline-block; padding: 2px 10px; border-radius: 3px;
                        font-weight: bold; font-size: 12px; }

        /* ── Meta grid ── */
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 3px 20px;
                     margin: 10px 0; font-size: 12.5px; }
        .meta-row  { display: flex; gap: 4px; }
        .meta-label { font-weight: bold; white-space: nowrap; min-width: 130px; }

        /* ── Table ── */
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 8px; }
        th { border: 1px solid #000; padding: 5px 6px; text-align: center;
             font-weight: bold; background: #F0F0F0; }
        td { border: 1px solid #000; padding: 5px 6px; vertical-align: top; }
        .tc { text-align: center; }
        .tr { text-align: right; }
        .med-name  { font-weight: bold; font-size: 12.5px; }
        .med-sub   { font-size: 10.5px; color: #333; margin-top: 2px; }
        .med-reg   { font-size: 10.5px; color: #000; font-style: italic; margin-top: 1px; }

        /* ── Totals ── */
        .total-wrap { margin-top: 8px; font-size: 13px; }
        .total-row  { display: flex; justify-content: flex-end; gap: 20px; margin: 3px 0; }
        .t-label { min-width: 160px; text-align: right; }
        .t-value { min-width: 130px; text-align: right; font-weight: bold; }
        .total-final { font-size: 14px; font-weight: bold; border-top: 1.5px solid #000;
                       padding-top: 5px; margin-top: 5px; }
        .words { margin-top: 5px; font-style: italic; font-size: 12px; }

        /* ── Signatures ── */
        .sig-section { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;
                       margin-top: 28px; text-align: center; font-size: 12px; }
        .sig-title { font-weight: bold; margin-bottom: 48px; }
        .sig-note  { font-style: italic; color: #555; font-size: 11px; margin-top: 4px; }

        /* ── Narc warning ── */
        .narc-warn { border: 2px solid #000; padding: 5px 10px; text-align: center;
                     font-weight: bold; font-size: 12px; letter-spacing: 0.5px; margin-bottom: 8px; }

        @media print {
            body { padding: 10mm 15mm; }
            .no-print { display: none !important; }
            @page { margin: 8mm; size: A4; }
        }
    </style>
</head>
<body>

@php
    $pharmacy = $purchaseOrder->pharmacy;
    $hasNarc  = $purchaseOrder->items->contains(fn($i) => $i->medicine?->is_narcotic);
    $hasPsyc  = $purchaseOrder->items->contains(fn($i) => $i->medicine?->is_psychotropic);

    /* Đọc số tiền bằng chữ */
    function docNhom3_p($n) {
        $dv = ['','một','hai','ba','bốn','năm','sáu','bảy','tám','chín'];
        $kq = ''; $tram = intdiv($n,100); $n%=100; $chuc=intdiv($n,10); $dvi=$n%10;
        if ($tram>0) $kq .= $dv[$tram].' trăm';
        if ($chuc>1) $kq .= ($kq?' ':'').$dv[$chuc].' mươi';
        elseif ($chuc==1) $kq .= ($kq?' ':'').'mười';
        elseif ($chuc==0&&$dvi>0&&$tram>0) $kq .= ' linh';
        if ($dvi>0) {
            if ($chuc>1&&$dvi==5) $kq .= ' lăm';
            elseif ($chuc>1&&$dvi==1) $kq .= ' mốt';
            else $kq .= ($kq?' ':'').$dv[$dvi];
        }
        return $kq;
    }
    function docSoTien_p($so) {
        $so=(int)abs($so); if($so==0) return 'không đồng';
        $ty=intdiv($so,1000000000); $so%=1000000000;
        $tr=intdiv($so,1000000);    $so%=1000000;
        $ng=intdiv($so,1000);       $le=$so%1000;
        $kq='';
        if($ty>0) $kq .= docNhom3_p($ty).' tỷ';
        if($tr>0) $kq .= ($kq?' ':'').docNhom3_p($tr).' triệu';
        if($ng>0) $kq .= ($kq?' ':'').docNhom3_p($ng).' nghìn';
        if($le>0) $kq .= ($kq?' ':'').docNhom3_p($le);
        return trim($kq).' đồng';
    }
@endphp

{{-- ── HEADER ── --}}
<div class="header">
    <div>
        <div class="pharmacy-name">
            {{ $pharmacy->name ?? 'NHÀ THUỐC GPP' }}
            <span class="gpp-badge">GPP</span>
        </div>
        <div class="pharmacy-sub">{{ $pharmacy->address ?? '' }}</div>
        <div class="pharmacy-sub">
            ĐT: {{ $pharmacy->phone ?? '' }}
            @if($pharmacy->license_number ?? false) &nbsp;·&nbsp; GP: {{ $pharmacy->license_number }} @endif
        </div>
        @if($pharmacy->tax_code ?? false)
        <div class="pharmacy-sub">MST: {{ $pharmacy->tax_code }}</div>
        @endif
    </div>
    <div style="text-align:right;font-size:11px;color:#555;">
        <div>Ngày in: {{ now()->format('H:i d/m/Y') }}</div>
        <div>Người in: {{ auth()->user()->name }}</div>
    </div>
</div>

<hr class="divider2">

{{-- Cảnh báo thuốc gây nghiện / HTT (BẮT BUỘC TT27/2021) --}}
@if($hasNarc)
<div class="narc-warn">⚠ PHIẾU NHẬP THUỐC GÂY NGHIỆN — LƯU HỒ SƠ BẮT BUỘC</div>
@elseif($hasPsyc)
<div class="narc-warn">⚠ PHIẾU NHẬP THUỐC HƯỚNG TÂM THẦN — LƯU HỒ SƠ BẮT BUỘC</div>
@endif

{{-- ── TIÊU ĐỀ ── --}}
<div class="doc-title">
    <h2>Phiếu nhập hàng</h2>
    <div class="sub">
        Mã phiếu: <strong>{{ $purchaseOrder->code }}</strong>
        &nbsp;·&nbsp; Trạng thái:
        <span class="status-badge" style="background:{{ match($purchaseOrder->status) {
            'received'  => '#D4EDDA', 'partial'   => '#FFF3CD',
            'approved'  => '#CCE5FF', 'pending'   => '#E2E3E5',
            'cancelled' => '#F8D7DA', default     => '#F5F5F5'
        } }};">{{ $purchaseOrder->status_label ?? $purchaseOrder->status }}</span>
    </div>
</div>

<hr class="divider">

{{-- ── THÔNG TIN PHIẾU ── --}}
<div class="meta-grid">
    <div class="meta-row">
        <span class="meta-label">Nhà cung cấp:</span>
        <span><strong>{{ $purchaseOrder->supplier->name }}</strong></span>
    </div>
    <div class="meta-row">
        <span class="meta-label">Ngày đặt hàng:</span>
        <span>{{ $purchaseOrder->order_date->format('d/m/Y') }}</span>
    </div>
    <div class="meta-row">
        <span class="meta-label">Địa chỉ NCC:</span>
        <span>{{ $purchaseOrder->supplier->address ?? '—' }}</span>
    </div>
    <div class="meta-row">
        <span class="meta-label">Ngày nhận hàng:</span>
        <span>{{ $purchaseOrder->received_date?->format('d/m/Y') ?? '—' }}</span>
    </div>
    <div class="meta-row">
        <span class="meta-label">ĐT nhà cung cấp:</span>
        <span>{{ $purchaseOrder->supplier->phone ?? '—' }}</span>
    </div>
    <div class="meta-row">
        <span class="meta-label">Người lập phiếu:</span>
        <span>{{ $purchaseOrder->createdBy->name ?? '—' }}</span>
    </div>
    <div class="meta-row">
        <span class="meta-label">MST nhà cung cấp:</span>
        <span>{{ $purchaseOrder->supplier->tax_code ?? '—' }}</span>
    </div>
    <div class="meta-row">
        <span class="meta-label">Người duyệt:</span>
        <span>{{ $purchaseOrder->approvedBy?->name ?? '—' }}</span>
    </div>
    @if($purchaseOrder->notes)
    <div class="meta-row" style="grid-column:1/-1;">
        <span class="meta-label">Ghi chú:</span>
        <span>{{ $purchaseOrder->notes }}</span>
    </div>
    @endif
</div>

<hr class="divider">

{{-- ── BẢNG HÀNG NHẬP — ĐẦY ĐỦ TRƯỜNG GPP ── --}}
<table>
    <thead>
        <tr>
            <th style="width:24px;">STT</th>
            <th>Tên thuốc — Hoạt chất — Hàm lượng<br>Dạng bào chế — Nước SX — Số ĐK</th>
            <th style="width:48px;">ĐVT</th>
            <th style="width:52px;">SL đặt</th>
            <th style="width:52px;">SL nhận</th>
            <th style="width:88px;">Đơn giá (đ)</th>
            <th style="width:96px;">Thành tiền (đ)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchaseOrder->items as $i => $item)
        @php $med = $item->medicine; @endphp
        <tr>
            <td class="tc">{{ $i + 1 }}</td>
            <td>
                {{-- Tên thương mại (BẮT BUỘC) --}}
                <div class="med-name">
                    {{ $med?->name }}
                    @if($med?->is_narcotic)   <span style="font-size:10px;">[GN]</span>
                    @elseif($med?->is_psychotropic) <span style="font-size:10px;">[HTT]</span>
                    @endif
                </div>

                {{-- Hoạt chất + Hàm lượng + Dạng bào chế (BẮT BUỘC — TT02/2018) --}}
                <div class="med-sub">
                    @if($med?->generic_name)Hoạt chất: {{ $med->generic_name }}@endif
                    @if($med?->concentration) · {{ $med->concentration }}@endif
                </div>
                @if($med?->dosage_form)
                <div class="med-sub">Dạng BC: {{ $med->dosage_form }}</div>
                @endif

                {{-- Nhà sản xuất + Nước SX (BẮT BUỘC — NĐ54) --}}
                <div class="med-sub">
                    @if($med?->manufacturer)NSX: {{ $med->manufacturer }}@endif
                    @if($med?->country_of_origin) · {{ $med->country_of_origin }}@endif
                </div>

                {{-- Số đăng ký lưu hành (BẮT BUỘC — NĐ54 Điều 95) --}}
                @if($med?->registration_number)
                <div class="med-reg">SĐK: {{ $med->registration_number }}</div>
                @else
                <div class="med-reg" style="color:#C0392B;">⚠ Chưa có số đăng ký</div>
                @endif

                {{-- Số lô + HSD --}}
                @if($item->batch_number)
                <div class="med-sub">
                    Lô: <strong>{{ $item->batch_number }}</strong>
                    @if($item->expiry_date)
                        · HSD: {{ \Carbon\Carbon::parse($item->expiry_date)->format('d/m/Y') }}
                    @endif
                </div>
                @endif

                {{-- Điều kiện bảo quản (TT02 khuyến nghị) --}}
                @if($med?->storage_instruction)
                <div class="med-sub">BQ: {{ $med->storage_instruction }}</div>
                @endif
            </td>
            <td class="tc">{{ $med?->unit }}</td>
            <td class="tc">{{ number_format($item->ordered_quantity) }}</td>
            <td class="tc {{ $item->received_quantity < $item->ordered_quantity ? 'font-weight:bold;' : '' }}">
                {{ $item->received_quantity > 0 ? number_format($item->received_quantity) : '—' }}
            </td>
            <td class="tr">{{ number_format($item->purchase_price, 0, ',', '.') }}</td>
            <td class="tr" style="font-weight:bold;">
                {{ number_format(($item->received_quantity ?: $item->ordered_quantity) * $item->purchase_price, 0, ',', '.') }}
            </td>
        </tr>
        @endforeach

        {{-- Dòng tổng --}}
        <tr style="background:#F5F5F5;">
            <td colspan="3" class="tc" style="font-weight:bold;font-style:italic;">
                Cộng ({{ $purchaseOrder->items->count() }} mặt hàng)
            </td>
            <td class="tc" style="font-weight:bold;">{{ number_format($purchaseOrder->items->sum('ordered_quantity')) }}</td>
            <td class="tc" style="font-weight:bold;">{{ number_format($purchaseOrder->items->sum('received_quantity')) }}</td>
            <td></td>
            <td class="tr" style="font-weight:bold;font-size:13.5px;">
                {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}
            </td>
        </tr>
    </tbody>
</table>

{{-- ── TỔNG TIỀN ── --}}
<div class="total-wrap">
    <div class="total-row">
        <span class="t-label">Tổng tiền hàng:</span>
        <span class="t-value">{{ number_format($purchaseOrder->total_amount,0,',','.') }} đ</span>
    </div>
    <div class="total-row">
        <span class="t-label">Đã thanh toán:</span>
        <span class="t-value" style="color:#27AE60;">{{ number_format($purchaseOrder->paid_amount,0,',','.') }} đ</span>
    </div>
    @if($purchaseOrder->total_amount - $purchaseOrder->paid_amount > 0)
    <div class="total-row total-final">
        <span class="t-label">Còn nợ nhà cung cấp:</span>
        <span class="t-value" style="color:#C0392B;">
            {{ number_format($purchaseOrder->total_amount - $purchaseOrder->paid_amount,0,',','.') }} đ
        </span>
    </div>
    @endif
    <div class="words">
        Bằng chữ: <em>{{ ucfirst(docSoTien_p($purchaseOrder->total_amount)) }}</em>
    </div>
</div>

<hr class="divider">

{{-- ── CHỮ KÝ (BẮT BUỘC — TT02/2018) ── --}}
<div class="sig-section">
    <div>
        <div class="sig-title">Người lập phiếu</div>
        <div>{{ $purchaseOrder->createdBy->name ?? '' }}</div>
        <div class="sig-note">(Ký, ghi rõ họ tên)</div>
    </div>
    <div>
        <div class="sig-title">Thủ kho</div>
        <div>&nbsp;</div>
        <div class="sig-note">(Ký, ghi rõ họ tên)</div>
    </div>
    <div>
        <div class="sig-title">Giám đốc / Dược sĩ phụ trách</div>
        <div>{{ $purchaseOrder->approvedBy?->name ?? '' }}</div>
        <div class="sig-note">(Ký, đóng dấu)</div>
    </div>
</div>

<hr class="divider" style="margin-top:28px;">
<div style="text-align:center;font-size:10px;color:#777;">
    Phiếu tạo tự động từ hệ thống quản lý nhà thuốc GPP
    &nbsp;·&nbsp; In lúc {{ now()->format('H:i d/m/Y') }}
</div>

{{-- Nút in --}}
<div class="no-print" style="text-align:center;margin-top:24px;display:flex;gap:12px;justify-content:center;">
    <button onclick="window.print()"
            style="padding:10px 28px;background:#1B4F72;color:#fff;border:none;
                   border-radius:8px;cursor:pointer;font-size:15px;font-weight:600;">
        🖨️ In phiếu
    </button>
    <button onclick="window.close()"
            style="padding:10px 20px;background:#6c757d;color:#fff;border:none;
                   border-radius:8px;cursor:pointer;font-size:14px;">
        Đóng
    </button>
</div>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
</body>
</html>