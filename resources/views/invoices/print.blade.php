<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Hóa đơn {{ $invoice->code }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            width: 80mm;
            margin: 0 auto;
            padding: 8px;
            color: #000;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .separator {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .separator2 {
            border-top: 2px solid #000;
            margin: 6px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }

        .row .left {
            flex: 1;
        }

        .row .right {
            text-align: right;
            font-weight: bold;
        }

        .item-name {
            font-weight: bold;
            margin-top: 5px;
            font-size: 13px;
        }

        .item-sub {
            font-size: 11px;
            color: #333;
            margin-top: 1px;
        }

        .item-usage {
            font-size: 12px;
            color: #000;
            margin-top: 4px;
            background: #f0f0f0;
            border-left: 3px solid #000;
            padding: 4px 6px;
            border-radius: 0 4px 4px 0;
        }

        .item-usage .lbl {
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 1px;
        }

        .reminder-box {
            border: 1.5px solid #000;
            border-radius: 4px;
            padding: 6px 8px;
            margin: 8px 0 4px;
        }

        .reminder-box .rh {
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 4px;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .reminder-row {
            display: flex;
            gap: 5px;
            align-items: flex-start;
            margin: 4px 0;
            font-size: 12px;
        }

        .reminder-row .rnum {
            font-weight: bold;
            min-width: 18px;
            font-size: 13px;
        }

        .reminder-row .rcontent {
            flex: 1;
        }

        .reminder-row .rmed {
            font-weight: bold;
            font-size: 12px;
        }

        .reminder-row .rusage {
            font-size: 12px;
            color: #222;
            margin-top: 1px;
        }

        .item-price {
            display: flex;
            justify-content: space-between;
            margin-top: 2px;
        }

        .total-row {
            font-size: 15px;
            font-weight: bold;
            margin: 4px 0;
        }

        .rx-banner {
            border: 2px solid #000;
            text-align: center;
            padding: 3px;
            font-weight: bold;
            font-size: 12px;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            color: #444;
            margin-top: 8px;
        }

        .warn {
            font-size: 10px;
            color: #000;
            margin-top: 3px;
            font-style: italic;
        }

        @media print {
            body {
                width: 80mm;
            }

            .no-print {
                display: none !important;
            }

            @page {
                margin: 0;
                size: 80mm auto;
            }
        }
    </style>
</head>

<body>

    @php
        $pharmacy = auth()->user()->pharmacy;
        $hasRx = $invoice->items->contains(fn($i) => $i->medicine?->requires_prescription);
        $hasNarc = $invoice->items->contains(fn($i) => $i->medicine?->is_narcotic);
        $hasAnti = $invoice->items->contains(fn($i) => $i->medicine?->is_antibiotic);
    @endphp

    {{-- ── HEADER ── --}}
    <div class="center">
        <div class="bold" style="font-size:15px;">{{ $pharmacy->name ?? 'NHÀ THUỐC GPP' }}</div>
        @if($pharmacy->address ?? false)
            <div style="font-size:10px;margin-top:1px;">{{ $pharmacy->address }}</div>
        @endif
        <div style="font-size:10px;">ĐT: {{ $pharmacy->phone ?? '' }}</div>
        @if($pharmacy->license_number ?? false)
            <div style="font-size:10px;">GPP: {{ $pharmacy->license_number }}</div>
        @endif
    </div>

    <div class="separator2"></div>

    {{-- Banner thuốc kê đơn (BẮT BUỘC TT02/2018 Điều 9) --}}
    @if($hasNarc)
        <div class="rx-banner" style="border-width:3px;">⚠ THUỐC GÂY NGHIỆN — BÁN THEO ĐƠN</div>
    @elseif($hasRx)
        <div class="rx-banner">THUỐC KÊ ĐƠN — BÁN THEO ĐƠN BÁC SĨ</div>
    @endif

    <div class="center bold" style="font-size:14px;">HÓA ĐƠN BÁN HÀNG</div>
    <div class="separator"></div>

    {{-- ── THÔNG TIN HÓA ĐƠN ── --}}
    <div class="row"><span>Số HĐ:</span><span class="bold">{{ $invoice->code }}</span></div>
    <div class="row"><span>Ngày:</span><span>{{ $invoice->invoice_date->format('d/m/Y H:i') }}</span></div>
    <div class="row"><span>Khách hàng:</span><span>{{ $invoice->customer?->name ?? 'Khách lẻ' }}</span></div>
    @if($invoice->customer?->phone ?? false)
        <div class="row"><span>SĐT KH:</span><span>{{ $invoice->customer->phone }}</span></div>
    @endif
    <div class="row"><span>Nhân viên:</span><span>{{ $invoice->createdBy?->name ?? '—' }}</span></div>

    {{-- Thông tin đơn thuốc (BẮT BUỘC khi có Rx — TT02 Điều 9) --}}
    @if($invoice->prescription_code || $invoice->doctor_name)
        <div class="separator"></div>
        <div class="bold" style="font-size:11px;">THÔNG TIN ĐƠN THUỐC</div>
        @if($invoice->prescription_code)
            <div class="row"><span>Mã đơn thuốc:</span><span class="bold">{{ $invoice->prescription_code }}</span></div>
        @endif
        @if($invoice->doctor_name)
            <div class="row"><span>Bác sĩ kê đơn:</span><span>{{ $invoice->doctor_name }}</span></div>
        @endif
    @endif

    <div class="separator"></div>

    {{-- ── DANH SÁCH THUỐC ── --}}
    @foreach($invoice->items as $item)
        @php $med = $item->medicine; @endphp

        {{-- Tên thuốc (BẮT BUỘC) --}}
        <div class="item-name">
            {{ $med?->name }}
            @if($med?->is_narcotic) <span style="font-size:10px;">[GN]</span>
            @elseif($med?->is_psychotropic) <span style="font-size:10px;">[HTT]</span>
            @elseif($med?->requires_prescription) <span style="font-size:10px;">[Rx]</span>
            @endif
        </div>

        {{-- Hàm lượng + dạng bào chế (BẮT BUỘC — TT02/2018) --}}
        <div class="item-sub">
            @if($med?->concentration){{ $med->concentration }}@endif
            @if($med?->dosage_form) · {{ $med->dosage_form }}@endif
            @if($med?->manufacturer) · {{ $med->manufacturer }}@endif
        </div>

        {{-- Số đăng ký lưu hành (BẮT BUỘC khi có — NĐ54) --}}
        @if($med?->registration_number)
            <div class="item-sub">SĐK: {{ $med->registration_number }}</div>
        @endif

        {{-- Số lô + hạn dùng --}}
        <div class="item-sub">
            Lô: {{ $item->batch_number ?? '—' }}
            &nbsp; HSD: {{ $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('d/m/Y') : '—' }}
        </div>

        {{-- Số lượng × đơn giá = thành tiền --}}
        <div class="item-price">
            <span>{{ $item->quantity }} {{ $item->unit }} × {{ number_format($item->sell_price, 0, ',', '.') }}đ</span>
            <span class="bold">{{ number_format($item->total_amount, 0, ',', '.') }}đ</span>
        </div>

        {{-- Hướng dẫn sử dụng inline (ngắn gọn) --}}
        @php $usage = $item->usage_instruction ?: $med?->description; @endphp
        @if($usage)
            <div class="item-usage">
                <span class="lbl">↳ Cách dùng:</span>{{ $usage }}
            </div>
        @endif

    @endforeach

    <div class="separator"></div>

    {{-- ── TỔNG TIỀN ── --}}
    @if($invoice->discount_amount > 0)
        <div class="row"><span>Tiền hàng:</span><span>{{ number_format($invoice->subtotal, 0, ',', '.') }}đ</span></div>
        <div class="row" style="color:#333;"><span>Giảm
                giá:</span><span>-{{ number_format($invoice->discount_amount, 0, ',', '.') }}đ</span></div>
    @endif

    <div class="separator"></div>
    <div class="row total-row">
        <span>TỔNG CỘNG:</span>
        <span>{{ number_format($invoice->total_amount, 0, ',', '.') }}đ</span>
    </div>
    <div class="row"><span>Khách trả ({{ $invoice->payment_method_label ?? '' }}):</span>
        <span>{{ number_format($invoice->paid_amount, 0, ',', '.') }}đ</span>
    </div>
    @if($invoice->change_amount > 0)
        <div class="row"><span>Tiền thối:</span><span>{{ number_format($invoice->change_amount, 0, ',', '.') }}đ</span></div>
    @endif
    @if($invoice->debt_amount > 0)
        <div class="row" style="font-weight:bold;"><span>Còn nợ:</span>
            <span>{{ number_format($invoice->debt_amount, 0, ',', '.') }}đ</span>
        </div>
    @endif

    <div class="separator2"></div>

    {{-- ── BẢNG NHẮC UỐNG THUỐC (chỉ hiện khi có hướng dẫn) ── --}}
    @php
        $itemsWithUsage = $invoice->items->filter(
            fn($i) =>
            !empty($i->usage_instruction) || !empty($i->medicine?->description)
        );
    @endphp
    @if($itemsWithUsage->count() > 0)
        <div class="reminder-box">
            <div class="rh">📋 HƯỚNG DẪN SỬ DỤNG THUỐC</div>
            @foreach($itemsWithUsage as $i => $item)
                @php $usage = $item->usage_instruction ?: $item->medicine?->description; @endphp
                <div class="reminder-row">
                    <div class="rnum">{{ $i + 1 }}.</div>
                    <div class="rcontent">
                        <div class="rmed">{{ $item->medicine?->name }}</div>
                        <div class="rusage">{{ $usage }}</div>
                    </div>
                </div>
            @endforeach
            <div
                style="border-top:1px dashed #000;margin-top:6px;padding-top:5px;font-size:10px;font-style:italic;text-align:center;">
                ⚠ Uống đúng liều, đúng giờ. Nếu có phản ứng lạ, ngừng thuốc và liên hệ ngay.
            </div>
        </div>
    @endif

    {{-- Cảnh báo kháng sinh (TT02 khuyến nghị) --}}
    @if($hasAnti)
        <div class="warn">⚠ Dùng đủ liều kháng sinh theo chỉ định bác sĩ.</div>
    @endif
    @if($hasRx)
        <div class="warn">* Lưu đơn thuốc để tái khám khi cần thiết.</div>
    @endif

    {{-- ── FOOTER ── --}}
    <div class="footer">
        <p>Cảm ơn quý khách đã tin dùng!</p>
        <p>Thuốc đã mua không đổi trả khi không có lý do</p>
        <p>Lưu hóa đơn để đổi/trả trong vòng 24h</p>
        <p style="margin-top:4px;font-weight:bold;">——— HẾT ———</p>
        <p style="margin-top:3px;font-size:9px;">In lúc {{ now()->format('H:i d/m/Y') }}</p>
    </div>

    {{-- Nút in (ẩn khi print) --}}
    <div class="no-print" style="text-align:center;margin-top:20px;display:flex;gap:10px;justify-content:center;">
        <button onclick="window.print()" style="padding:10px 24px;background:#1B4F72;color:#fff;border:none;
                   border-radius:8px;cursor:pointer;font-size:14px;font-weight:600;">
            🖨️ In hóa đơn
        </button>
        <button onclick="window.close()" style="padding:10px 20px;background:#6c757d;color:#fff;border:none;
                   border-radius:8px;cursor:pointer;font-size:14px;">
            ✕ Đóng
        </button>
    </div>

    <script>
        window.addEventListener('load', () => { setTimeout(() => window.print(), 300); });
    </script>
</body>

</html>