<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In nhãn — {{ $medicine->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 8mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            background: #fff;
        }

        /* ── Kích thước nhãn ── */
        .label-small {
            width: 50mm;
            min-height: 30mm;
            font-size: 8pt;
        }

        .label-medium {
            width: 70mm;
            min-height: 40mm;
            font-size: 9pt;
        }

        .label-large {
            width: 100mm;
            min-height: 50mm;
            font-size: 10pt;
        }

        /* ── Container tất cả nhãn ── */
        .labels-container {
            display: flex;
            flex-wrap: wrap;
            gap: 4mm;
            padding: 2mm;
        }

        /* ── Từng nhãn ── */
        .label {
            border: 1.5px solid #222;
            border-radius: 4px;
            padding: 3mm 4mm;
            background: #fff;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
        }

        .label-header {
            text-align: center;
            border-bottom: 1px solid #444;
            padding-bottom: 2mm;
            margin-bottom: 2mm;
        }

        .pharmacy-name {
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            line-height: 1.3;
        }

        .pharmacy-address {
            font-size: 0.75em;
            color: #555;
            line-height: 1.2;
        }

        .medicine-name {
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 2mm 0 1mm;
            line-height: 1.2;
        }

        .medicine-form {
            text-align: center;
            font-size: 0.82em;
            color: #555;
            margin-bottom: 2.5mm;
        }

        .label-row {
            margin-bottom: 1.5mm;
            line-height: 1.4;
        }

        .label-row strong {
            font-weight: bold;
        }

        .label-footer {
            border-top: 0.5px dashed #999;
            margin-top: 2mm;
            padding-top: 2mm;
            display: flex;
            justify-content: space-between;
            font-size: 0.82em;
        }

        .label-note {
            margin-top: 1.5mm;
            font-size: 0.8em;
            color: #555;
            font-style: italic;
            line-height: 1.3;
        }

        .label-date {
            text-align: center;
            margin-top: 2mm;
            font-size: 0.72em;
            color: #888;
            border-top: 0.5px solid #eee;
            padding-top: 1.5mm;
        }

        /* ── Toolbar (chỉ hiện trên màn hình, ẩn khi in) ── */
        .print-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #1a2332;
            color: white;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 999;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .3);
        }

        .print-toolbar .info {
            font-size: 14px;
        }

        .print-toolbar .info strong {
            font-size: 16px;
        }

        .btn-print {
            background: #00C9A7;
            color: #0B1929;
            border: none;
            padding: 8px 24px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
        }

        .btn-back {
            background: transparent;
            color: #ccc;
            border: 1px solid #555;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
        }

        .labels-wrapper {
            padding-top: 60px;
            /* offset for toolbar */
        }

        @media print {
            .print-toolbar {
                display: none !important;
            }

            .labels-wrapper {
                padding-top: 0 !important;
            }

            body {
                background: white !important;
            }
        }
    </style>
</head>

<body>

    {{-- Toolbar --}}
    <div class="print-toolbar">
        <div class="info">
            <strong>{{ $medicine->name }}</strong>
            &nbsp;—&nbsp;
            In {{ $quantity }} nhãn
            @if($batch) · Lô {{ $batch->batch_number }} @endif
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('medicines.label', $medicine) }}" class="btn-back">← Sửa</a>
            <button onclick="window.print()" class="btn-print">🖨️ In ngay</button>
        </div>
    </div>

    {{-- Tất cả nhãn --}}
    <div class="labels-wrapper">
        <div class="labels-container">
            @for($i = 0; $i < $quantity; $i++)
                <div class="label label-{{ $labelSize }}">

                    {{-- Header nhà thuốc --}}
                    <div class="label-header">
                        <div class="pharmacy-name">{{ auth()->user()->pharmacy?->name ?? 'NHÀ THUỐC GPP' }}</div>
                        @if(auth()->user()->pharmacy?->address)
                            <div class="pharmacy-address">{{ auth()->user()->pharmacy->address }}</div>
                        @endif
                        @if(auth()->user()->pharmacy?->phone)
                            <div class="pharmacy-address">☎ {{ auth()->user()->pharmacy->phone }}</div>
                        @endif
                    </div>

                    {{-- Tên thuốc --}}
                    <div class="medicine-name">{{ $medicine->name }}</div>

                    @if($medicine->dosage_form || $medicine->unit)
                        <div class="medicine-form">
                            {{ $medicine->dosage_form ?? '' }}
                            {{ $medicine->unit ? '(' . $medicine->unit . ')' : '' }}
                        </div>
                    @endif

                    {{-- Barcode --}}
                    @if($medicine->barcode)
                        <div style="text-align: center; margin: 2mm 0; padding: 1.5mm 0; border-top: 0.5px solid #ddd; border-bottom: 0.5px solid #ddd;">
                            <div style="height: 18mm; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                {!! \App\Helpers\BarcodeHelper::generate($medicine->barcode) !!}
                            </div>
                            <div style="font-size: 0.7em; margin-top: 0.5mm; color: #333; letter-spacing: 2px;">
                                {{ $medicine->barcode }}
                            </div>
                        </div>
                    @endif

                    {{-- Bệnh nhân --}}
                    @if($patientName)
                        <div class="label-row">
                            <strong>Bệnh nhân:</strong> {{ $patientName }}
                        </div>
                    @else
                        <div class="label-row">
                            <strong>Bệnh nhân:</strong> .................................
                        </div>
                    @endif

                    {{-- Cách dùng --}}
                    @if($dosage)
                        <div class="label-row">
                            <strong>Cách dùng:</strong> {{ $dosage }}
                        </div>
                    @else
                        <div class="label-row">
                            <strong>Cách dùng:</strong> .................................
                        </div>
                    @endif

                    {{-- Lô & HSD --}}
                    @if($batch)
                        <div class="label-footer">
                            <span><strong>Lô:</strong> {{ $batch->batch_number }}</span>
                            <span><strong>HSD:</strong> {{ $batch->expiry_date->format('d/m/Y') }}</span>
                        </div>
                    @endif

                    {{-- Ghi chú --}}
                    @if($note)
                        <div class="label-note">⚠ {{ $note }}</div>
                    @endif

                    {{-- Ngày in --}}
                    <div class="label-date">
                        Ngày {{ now()->format('d/m/Y') }} · Chuẩn GPP
                    </div>

                </div>
            @endfor
        </div>
    </div>

    <script>
        // Tự động mở hộp thoại in sau 500ms
        setTimeout(() => window.print(), 500);
    </script>
</body>

</html>