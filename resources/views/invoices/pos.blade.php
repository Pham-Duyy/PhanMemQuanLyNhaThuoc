@php
    // Fallback nếu controller không truyền biến (phòng trường hợp cache cũ)
    if (!isset($shiftStats)) {
        $pharmacyId = auth()->user()->pharmacy_id;
        $shiftStats = [
            'revenue' => \App\Models\Invoice::where('pharmacy_id', $pharmacyId)
                ->where('status', 'completed')
                ->whereDate('invoice_date', today())
                ->sum('total_amount'),
            'invoice_count' => \App\Models\Invoice::where('pharmacy_id', $pharmacyId)
                ->where('status', 'completed')
                ->whereDate('invoice_date', today())
                ->count(),
            'cashier' => auth()->user()->name,
        ];
    }
    if (!isset($recentInvoices)) {
        $recentInvoices = \App\Models\Invoice::where('pharmacy_id', auth()->user()->pharmacy_id)
            ->with('customer:id,name')
            ->where('status', 'completed')
            ->whereDate('invoice_date', today())
            ->latest('invoice_date')
            ->limit(8)
            ->get(['id', 'code', 'total_amount', 'payment_method', 'invoice_date', 'customer_id']);
    }
@endphp
{{-- POS: Full-screen — KHÔNG dùng layout có sidebar --}}
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS — {{ auth()->user()->pharmacy?->name }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --bg: #F0F4F8;
            --sur: #FFFFFF;
            --sur2: #F7F9FC;
            --bdr: #E2E8F0;
            --teal: #0EA5A0;
            --tdim: rgba(14, 165, 160, .10);
            --blue: #2563EB;
            --txt: #1E293B;
            --muted: #64748B;
            --danger: #EF4444;
            --success: #16A34A;
            --warn: #D97706;
            --r: 10px;
            --shadow: 0 1px 3px rgba(0, 0, 0, .08), 0 1px 2px rgba(0, 0, 0, .04);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, .10);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        html,
        body {
            height: 100vh;
            overflow: hidden;
            background: var(--bg);
            color: var(--txt);
            font-family: 'Segoe UI', system-ui, sans-serif;
            font-size: 14px
        }

        /* TOPBAR */
        .topbar {
            height: 56px;
            background: linear-gradient(135deg, #0B5E5A 0%, #0EA5A0 100%);
            border-bottom: none;
            display: flex;
            align-items: center;
            padding: 0 20px;
            gap: 16px;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(11, 94, 90, .3)
        }

        .topbar .brand {
            font-weight: 800;
            font-size: 16px;
            color: #fff;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: -.3px
        }

        .topbar .si {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 12px;
            color: rgba(255, 255, 255, .75);
            margin-left: auto
        }

        .topbar .ss {
            display: flex;
            flex-direction: column;
            align-items: flex-end
        }

        .topbar .ss strong {
            color: #fff;
            font-size: 14px;
            font-weight: 700
        }

        .clock {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #fff;
            font-variant-numeric: tabular-nums
        }

        .btn-exit {
            background: rgba(255, 255, 255, .15);
            border: 1px solid rgba(255, 255, 255, .3);
            color: #fff;
            padding: 6px 14px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all .15s;
            white-space: nowrap;
            backdrop-filter: blur(4px)
        }

        .btn-exit:hover {
            background: rgba(239, 68, 68, .85);
            border-color: transparent;
            color: #fff
        }

        /* LAYOUT */
        .main {
            display: grid;
            grid-template-columns: 1fr 360px 290px;
            height: calc(100vh - 56px);
            overflow: hidden;
            gap: 0
        }

        /* COL LEFT */
        .col-left {
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--bdr);
            overflow: hidden;
            background: var(--bg)
        }

        .srch-wrap {
            background: var(--sur);
            padding: 14px 16px;
            border-bottom: 1px solid var(--bdr);
            position: relative;
            flex-shrink: 0;
            box-shadow: var(--shadow)
        }

        .srch-box {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #F8FAFC;
            border: 2px solid var(--bdr);
            border-radius: 12px;
            padding: 0 16px;
            transition: all .2s
        }

        .srch-box:focus-within {
            border-color: var(--teal);
            background: #fff;
            box-shadow: 0 0 0 3px var(--tdim)
        }

        .srch-box input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: var(--txt);
            font-size: 15px;
            padding: 12px 0;
            caret-color: var(--teal)
        }

        .srch-box input::placeholder {
            color: #94A3B8
        }

        .srch-box .sicon {
            color: #94A3B8;
            font-size: 18px
        }

        .bc-ind {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            color: var(--teal);
            background: var(--tdim);
            padding: 3px 8px;
            border-radius: 4px;
            border: 1px solid var(--teal);
            opacity: 0;
            transition: opacity .2s;
            white-space: nowrap
        }

        .bc-ind.on {
            opacity: 1
        }

        .kbd {
            background: #F1F5F9;
            border: 1px solid #CBD5E1;
            border-radius: 5px;
            padding: 2px 7px;
            font-size: 10px;
            color: #64748B;
            font-family: monospace;
            font-weight: 600
        }

        .srch-dd {
            position: absolute;
            top: calc(100% - 4px);
            left: 16px;
            right: 16px;
            z-index: 999;
            background: #fff;
            border: 1px solid var(--bdr);
            border-top: none;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 16px 40px rgba(0, 0, 0, .12);
            max-height: 340px;
            overflow-y: auto;
            display: none
        }

        .si-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 16px;
            cursor: pointer;
            border-bottom: 1px solid #F1F5F9;
            transition: background .1s
        }

        .si-item:hover,
        .si-item.foc {
            background: #F0FDF9
        }

        .si-item.oos {
            opacity: .5;
            cursor: not-allowed
        }

        .si-name {
            font-weight: 600;
            font-size: 14px;
            color: #1E293B
        }

        .si-meta {
            font-size: 11px;
            color: #64748B;
            margin-top: 2px
        }

        .si-price {
            font-weight: 700;
            color: var(--teal);
            font-size: 14px;
            text-align: right
        }

        .si-stock {
            font-size: 11px;
            color: #64748B;
            text-align: right;
            margin-top: 2px
        }

        .rx-badge {
            background: #FEE2E2;
            color: #DC2626;
            border: 1px solid #FECACA;
            font-size: 9px;
            padding: 1px 6px;
            border-radius: 4px;
            margin-left: 4px;
            font-weight: 700
        }

        .low-badge {
            background: #FEF3C7;
            color: #92400E;
            border: 1px solid #FDE68A;
            font-size: 9px;
            padding: 1px 6px;
            border-radius: 4px;
            margin-left: 4px;
            font-weight: 700
        }

        /* CART */
        .cart-wrap {
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            background: #fff
        }

        .cart-hd {
            padding: 12px 18px;
            background: #fff;
            border-bottom: 1px solid var(--bdr);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0
        }

        .cart-ttl {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #64748B
        }

        .c-cnt {
            background: var(--teal);
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            padding: 2px 9px;
            border-radius: 20px
        }

        .gst {
            background: #FFF1F2;
            border: 1px solid #FECDD3;
            color: #E11D48;
            font-size: 11px;
            padding: 4px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: all .15s;
            font-weight: 600
        }

        .gst:hover {
            background: #FFE4E6
        }

        .cart-body {
            flex: 1;
            overflow-y: auto;
            background: #fff
        }

        .cart-body::-webkit-scrollbar {
            width: 4px
        }

        .cart-body::-webkit-scrollbar-thumb {
            background: #E2E8F0;
            border-radius: 2px
        }

        .ct {
            width: 100%;
            border-collapse: collapse
        }

        .ct thead th {
            background: #F8FAFC;
            padding: 9px 14px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .9px;
            color: #64748B;
            font-weight: 700;
            border-bottom: 1px solid #E2E8F0;
            position: sticky;
            top: 0
        }

        .ct tbody td {
            padding: 10px 14px;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle
        }

        .ct tbody tr:hover {
            background: #F0FDF9
        }

        .mn {
            font-weight: 600;
            font-size: 13px;
            color: #1E293B
        }

        .ms {
            font-size: 10px;
            color: #64748B;
            margin-top: 2px
        }

        .qc {
            display: flex;
            align-items: center;
            gap: 5px
        }

        .qb {
            width: 28px;
            height: 28px;
            background: #F1F5F9;
            border: 1px solid #E2E8F0;
            border-radius: 7px;
            color: #475569;
            cursor: pointer;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .12s;
            font-weight: 700
        }

        .qb:hover {
            background: var(--teal);
            color: #fff;
            border-color: var(--teal)
        }

        .qi {
            width: 54px;
            text-align: center;
            background: #F8FAFC;
            border: 1.5px solid #E2E8F0;
            border-radius: 7px;
            color: #1E293B;
            font-size: 14px;
            font-weight: 700;
            padding: 5px;
            outline: none
        }

        .qi:focus {
            border-color: var(--teal);
            background: #fff
        }

        .rb {
            width: 26px;
            height: 26px;
            background: transparent;
            border: 1px solid #E2E8F0;
            color: #94A3B8;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .12s
        }

        .rb:hover {
            background: #FEE2E2;
            border-color: #FECACA;
            color: #EF4444
        }

        .lt {
            font-weight: 700;
            color: var(--teal)
        }

        .empty-cart {
            text-align: center;
            padding: 70px 20px;
            color: #94A3B8
        }

        .empty-cart .ei {
            font-size: 64px;
            opacity: .2
        }

        /* COL PAY */
        .col-pay {
            display: flex;
            flex-direction: column;
            background: #fff;
            border-right: 1px solid var(--bdr);
            border-left: 1px solid var(--bdr);
            overflow-y: auto
        }

        .col-pay::-webkit-scrollbar {
            width: 4px
        }

        .col-pay::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 4px
        }

        .ps {
            padding: 14px 16px;
            border-bottom: 1px solid #F1F5F9
        }

        .ps-ttl {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #64748B;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px
        }

        .cinput {
            width: 100%;
            background: #F8FAFC;
            border: 1.5px solid #E2E8F0;
            border-radius: 9px;
            color: #1E293B;
            padding: 10px 13px;
            font-size: 13px;
            outline: none;
            transition: all .15s
        }

        .cinput:focus {
            border-color: var(--teal);
            background: #fff;
            box-shadow: 0 0 0 3px var(--tdim)
        }

        .cinput::placeholder {
            color: #94A3B8
        }

        .ctag {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #F0FDF9;
            border: 1.5px solid #99F6E4;
            border-radius: 9px;
            padding: 9px 13px;
            font-size: 12px;
            margin-top: 8px
        }

        .ctag .cn {
            font-weight: 700;
            color: #0F766E
        }

        .ctag .cx {
            cursor: pointer;
            color: #94A3B8;
            font-size: 20px;
            line-height: 1
        }

        .ctag .cx:hover {
            color: #EF4444
        }

        .total-big {
            background: linear-gradient(135deg, #0F766E 0%, #0EA5A0 100%);
            border-radius: 14px;
            padding: 18px 16px;
            text-align: center;
            margin: 4px 0;
            box-shadow: 0 4px 20px rgba(14, 165, 160, .25)
        }

        .total-big .tl {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255, 255, 255, .75);
            font-weight: 600
        }

        .total-big .ta {
            font-size: 34px;
            font-weight: 900;
            color: #fff;
            letter-spacing: -1px;
            margin-top: 4px
        }

        .pmg {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px
        }

        .pmb {
            padding: 10px 6px;
            background: #F8FAFC;
            border: 2px solid #E2E8F0;
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            color: #64748B;
            transition: all .15s
        }

        .pmb:hover {
            border-color: var(--teal);
            color: var(--teal);
            background: #F0FDF9
        }

        .pmb.on {
            border-color: var(--teal);
            background: linear-gradient(135deg, #F0FDF9, #CCFBF1);
            color: #0F766E;
            box-shadow: 0 2px 8px rgba(14, 165, 160, .15)
        }

        .pmb .mi {
            font-size: 20px;
            display: block;
            margin-bottom: 4px
        }

        .pi {
            width: 100%;
            background: #F8FAFC;
            border: 1.5px solid #E2E8F0;
            border-radius: 9px;
            color: #1E293B;
            font-size: 20px;
            font-weight: 800;
            padding: 10px 13px;
            text-align: right;
            outline: none;
            transition: all .15s
        }

        .pi:focus {
            border-color: var(--teal);
            background: #fff;
            box-shadow: 0 0 0 3px var(--tdim)
        }

        .qbs {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 9px
        }

        .qb2 {
            flex: 1;
            min-width: 52px;
            padding: 7px 4px;
            background: #F1F5F9;
            border: 1.5px solid #E2E8F0;
            border-radius: 8px;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            text-align: center;
            transition: all .15s
        }

        .qb2:hover {
            border-color: var(--teal);
            color: var(--teal);
            background: #F0FDF9
        }

        .cr {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 9px 0;
            font-size: 13px
        }

        .cv {
            font-size: 20px;
            font-weight: 800;
            color: #16A34A
        }

        .discrow {
            display: flex;
            gap: 7px;
            align-items: center
        }

        .discrow .pi {
            font-size: 16px
        }

        .rx-ban {
            background: #FEF2F2;
            border: 1.5px solid #FECACA;
            border-radius: 9px;
            padding: 9px 12px;
            font-size: 12px;
            color: #DC2626;
            display: flex;
            align-items: center;
            gap: 7px;
            font-weight: 500
        }

        .btn-co {
            margin: 12px 16px 16px;
            padding: 15px;
            background: linear-gradient(135deg, #16A34A, #15803D);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            transition: all .18s;
            box-shadow: 0 4px 16px rgba(22, 163, 74, .3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: calc(100% - 32px);
            letter-spacing: .3px
        }

        .btn-co:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(22, 163, 74, .4)
        }

        .btn-co:disabled {
            background: #E2E8F0;
            box-shadow: none;
            cursor: not-allowed;
            color: #94A3B8
        }

        /* COL HIST */
        .col-hist {
            display: flex;
            flex-direction: column;
            background: #FAFBFC;
            overflow: hidden;
            border-left: 1px solid var(--bdr)
        }

        .hist-hd {
            padding: 14px 16px 11px;
            border-bottom: 1px solid #F1F5F9;
            flex-shrink: 0;
            background: #fff
        }

        .hist-hd .ht {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #64748B;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px
        }

        .hist-bd {
            flex: 1;
            overflow-y: auto
        }

        .hist-bd::-webkit-scrollbar {
            width: 4px
        }

        .hist-bd::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 4px
        }

        .hi {
            padding: 11px 15px;
            border-bottom: 1px solid #F1F5F9;
            cursor: pointer;
            transition: all .12s;
            background: #fff
        }

        .hi:hover {
            background: #F0FDF9;
            border-left: 3px solid var(--teal)
        }

        .hi .hcode {
            font-size: 12px;
            font-weight: 800;
            color: #0F766E;
            font-family: monospace;
            letter-spacing: .5px
        }

        .hi .hcust {
            font-size: 11px;
            color: #64748B;
            margin-top: 2px
        }

        .hi .hmeta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 5px
        }

        .hi .hamt {
            font-size: 13px;
            font-weight: 700;
            color: #1E293B
        }

        .hi .htime {
            font-size: 10px;
            color: #94A3B8
        }

        .pb {
            font-size: 9px;
            padding: 2px 7px;
            border-radius: 5px;
            font-weight: 700
        }

        .pb.cash {
            background: #DCFCE7;
            color: #166534
        }

        .pb.card {
            background: #DBEAFE;
            color: #1E40AF
        }

        .pb.transfer {
            background: #EDE9FE;
            color: #6D28D9
        }

        .pb.debt {
            background: #FEE2E2;
            color: #991B1B
        }

        .hist-ft {
            padding: 12px 14px;
            border-top: 1px solid #E2E8F0;
            background: #fff;
            flex-shrink: 0;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, .04)
        }

        .ssum {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px
        }

        .sbox {
            background: linear-gradient(135deg, #F0FDF9, #CCFBF1);
            border: 1px solid #99F6E4;
            border-radius: 10px;
            padding: 10px;
            text-align: center
        }

        .sbox .sl {
            font-size: 10px;
            color: #0F766E;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .8px
        }

        .sbox .sv {
            font-size: 17px;
            font-weight: 800;
            color: #0F766E;
            margin-top: 2px
        }

        /* MODAL */
        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .5);
            z-index: 9000;
            display: none;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(8px)
        }

        .modal2 {
            background: #fff;
            border: 1px solid #E2E8F0;
            border-radius: 20px;
            padding: 40px 36px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 24px 64px rgba(0, 0, 0, .15);
            animation: slideUp .25s ease
        }

        .modal2 .mico {
            font-size: 64px
        }

        .modal2 h3 {
            color: #0F766E;
            margin: 16px 0 6px;
            font-size: 22px;
            font-weight: 800
        }

        .modal2 .mc {
            font-family: monospace;
            font-size: 16px;
            color: #64748B;
            background: #F0FDF9;
            padding: 4px 14px;
            border-radius: 8px;
            display: inline-block
        }

        .modal2 .mt2 {
            font-size: 32px;
            font-weight: 900;
            color: #0F766E;
            margin: 12px 0;
            letter-spacing: -1px
        }

        .modal2 .mch {
            color: #16A34A;
            font-size: 13px;
            margin-bottom: 18px;
            font-weight: 600
        }

        .mbtns {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px
        }

        .mbtn {
            padding: 11px 22px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: all .15s
        }

        .mbtn-p {
            background: #F1F5F9;
            border: 1.5px solid #E2E8F0;
            color: #475569
        }

        .mbtn-p:hover {
            background: #F0FDF9;
            border-color: #99F6E4;
            color: #0F766E
        }

        .mbtn-n {
            background: linear-gradient(135deg, #0F766E, #0EA5A0);
            color: #fff;
            box-shadow: 0 4px 14px rgba(14, 165, 160, .3)
        }

        .mbtn-n:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(14, 165, 160, .4)
        }

        /* CUST DD */
        .cdd {
            position: absolute;
            left: 0;
            right: 0;
            top: 100%;
            z-index: 500;
            background: #fff;
            border: 1.5px solid #E2E8F0;
            border-top: none;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, .10);
            max-height: 220px;
            overflow-y: auto
        }

        .cdi {
            padding: 10px 14px;
            cursor: pointer;
            border-bottom: 1px solid #F1F5F9;
            font-size: 13px;
            transition: background .1s
        }

        .cdi:hover {
            background: #F0FDF9
        }

        .cdi .dn {
            font-weight: 600;
            color: #1E293B
        }

        .cdi .dm {
            font-size: 11px;
            color: #64748B;
            margin-top: 2px
        }

        @keyframes flash {
            0% {
                background: rgba(14, 165, 160, .15)
            }

            100% {
                background: transparent
            }
        }

        .flash-add {
            animation: flash .4s ease
        }

        @keyframes blink {
            50% {
                opacity: 0
            }
        }

        .scanning {
            animation: blink .5s 3
        }

        /* MODAL THÊM KHÁCH HÀNG */
        .cust-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .5);
            z-index: 9500;
            display: none;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(8px)
        }

        .cust-overlay.open {
            display: flex
        }

        .cust-modal {
            background: #fff;
            border: 1px solid #E2E8F0;
            border-radius: 20px;
            padding: 30px 28px 24px;
            width: 440px;
            max-width: 95vw;
            box-shadow: 0 24px 64px rgba(0, 0, 0, .15);
            animation: slideUp .2s ease
        }

        @keyframes slideUp {
            from {
                transform: translateY(16px);
                opacity: 0
            }

            to {
                transform: translateY(0);
                opacity: 1
            }
        }

        .cm-hd {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px
        }

        .cm-hd h4 {
            font-size: 17px;
            font-weight: 800;
            color: #0F766E;
            display: flex;
            align-items: center;
            gap: 9px
        }

        .cm-close {
            background: #F1F5F9;
            border: none;
            color: #64748B;
            font-size: 20px;
            cursor: pointer;
            line-height: 1;
            padding: 4px 8px;
            border-radius: 8px;
            transition: all .15s
        }

        .cm-close:hover {
            background: #FEE2E2;
            color: #EF4444
        }

        .cm-field {
            margin-bottom: 15px
        }

        .cm-field label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .9px;
            color: #64748B;
            margin-bottom: 7px
        }

        .cm-field label span {
            color: #EF4444
        }

        .cm-inp {
            width: 100%;
            background: #F8FAFC;
            border: 1.5px solid #E2E8F0;
            border-radius: 10px;
            color: #1E293B;
            padding: 11px 14px;
            font-size: 14px;
            outline: none;
            transition: all .15s
        }

        .cm-inp:focus {
            border-color: #0EA5A0;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(14, 165, 160, .1)
        }

        .cm-inp::placeholder {
            color: #94A3B8
        }

        .cm-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px
        }

        .cm-sel {
            width: 100%;
            background: #F8FAFC;
            border: 1.5px solid #E2E8F0;
            border-radius: 10px;
            color: #1E293B;
            padding: 11px 14px;
            font-size: 14px;
            outline: none;
            cursor: pointer;
            transition: all .15s
        }

        .cm-sel:focus {
            border-color: #0EA5A0
        }

        .cm-alert {
            background: #FEF2F2;
            border: 1.5px solid #FECACA;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 12px;
            color: #DC2626;
            display: none;
            margin-bottom: 14px;
            line-height: 1.6
        }

        .cm-alert.warn {
            background: #FFFBEB;
            border-color: #FDE68A;
            color: #92400E
        }

        .cm-alert.show {
            display: block
        }

        .cm-btns {
            display: flex;
            gap: 10px;
            margin-top: 22px
        }

        .cm-btn-cancel {
            flex: 1;
            padding: 12px;
            background: #F1F5F9;
            border: 1.5px solid #E2E8F0;
            border-radius: 10px;
            color: #475569;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s
        }

        .cm-btn-cancel:hover {
            background: #FEE2E2;
            border-color: #FECACA;
            color: #EF4444
        }

        .cm-btn-save {
            flex: 2;
            padding: 12px;
            background: linear-gradient(135deg, #0F766E, #0EA5A0);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            box-shadow: 0 4px 14px rgba(14, 165, 160, .3);
            transition: all .15s
        }

        .cm-btn-save:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(14, 165, 160, .4)
        }

        .cm-btn-save:disabled {
            background: #E2E8F0;
            color: #94A3B8;
            cursor: not-allowed;
            box-shadow: none;
            transform: none
        }
    </style>
</head>

<body>

    <div class="topbar">
        <div class="brand"><i class="bi bi-capsule me-1"></i>{{ auth()->user()->pharmacy?->name ?? 'PharmaCare GPP' }}
        </div>
        <div class="clock" id="clock">--:--</div>
        <div class="si">
            <div class="ss"><span>Thu ngân</span><strong>{{ auth()->user()->name }}</strong></div>
            <div class="ss"><span>Doanh thu ca</span><strong
                    id="topRev">{{ number_format($shiftStats['revenue'] / 1000, 0, ',', '.') }}k</strong></div>
            <div class="ss"><span>Hóa đơn</span><strong id="topCnt">{{ $shiftStats['invoice_count'] }}</strong></div>
            <a href="{{ route('dashboard') }}" class="btn-exit"><i class="bi bi-box-arrow-left me-1"></i>Thoát POS</a>
        </div>
    </div>

    <div class="main">

        {{-- COL LEFT --}}
        <div class="col-left">
            <div class="srch-wrap">
                <div class="srch-box" id="SW">
                    <i class="bi bi-search sicon"></i>
                    <input type="text" id="ms" placeholder="Tên thuốc, mã SKU, barcode...  [F2 focus]"
                        autocomplete="off">
                    <div class="bc-ind" id="bci"><i class="bi bi-upc-scan"></i><span>Barcode</span></div>
                    <span class="kbd">F2</span><span class="kbd">F4=Thanh toán</span>
                </div>
                <div class="srch-dd" id="sdd"></div>
            </div>
            <div class="cart-wrap">
                <div class="cart-hd">
                    <div class="d-flex align-items-center gap-2">
                        <span class="cart-ttl">Giỏ hàng</span>
                        <span class="c-cnt" id="cc">0</span>
                    </div>
                    <button class="gst" id="btnClr" style="display:none;"><i class="bi bi-trash me-1"></i>Xóa
                        hết</button>
                </div>
                <div class="cart-body" id="cb">
                    <div class="empty-cart" id="empt">
                        <div class="ei">🛒</div>
                        <p style="margin-top:12px;font-size:13px;">Giỏ hàng trống</p>
                        <p style="font-size:11px;color:var(--muted);margin-top:4px;">Tìm thuốc hoặc quét barcode để thêm
                        </p>
                    </div>
                    <table class="ct" id="ctbl" style="display:none;">
                        <thead>
                            <tr>
                                <th style="width:38%">Tên thuốc</th>
                                <th style="width:14%;text-align:center">Lô / HSD</th>
                                <th style="width:21%;text-align:center">Số lượng</th>
                                <th style="width:13%;text-align:right">Đ.giá</th>
                                <th style="width:11%;text-align:right">T.tiền</th>
                                <th style="width:3%"></th>
                            </tr>
                        </thead>
                        <tbody id="cr"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- COL PAY --}}
        <div class="col-pay">
            <div class="ps">
                <div class="ps-ttl" style="justify-content:space-between;">
                    <span><i class="bi bi-person-circle me-1"></i>Khách hàng</span>
                    <button onclick="openAddCust()"
                        style="background:var(--tdim);border:1px solid var(--teal);color:var(--teal);font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px;cursor:pointer;">
                        <i class="bi bi-plus-lg me-1"></i>Thêm mới
                    </button>
                </div>
                <div class="position-relative" id="CW">
                    <input type="text" class="cinput" id="cust" placeholder="Tìm tên / SĐT...  [hoặc nhấn Thêm mới]"
                        autocomplete="off">
                    <div class="cdd" id="cdd" style="display:none;"></div>
                </div>
                <div id="ctag" style="display:none;"></div>
                <input type="hidden" id="cid">
            </div>
            <div class="ps">
                <div class="total-big">
                    <div class="tl">Tổng tiền cần trả</div>
                    <div class="ta" id="dTotal">0đ</div>
                </div>
            </div>
            <div class="ps">
                <div class="ps-ttl"><i class="bi bi-credit-card"></i>Phương thức</div>
                <div class="pmg">
                    @foreach(['cash' => ['💵', 'Tiền mặt'], 'card' => ['💳', 'Thẻ/ATM'], 'transfer' => ['🏦', 'Chuyển khoản'], 'debt' => ['📋', 'Ghi nợ']] as $v => [$ic, $lb])
                        <div class="pmb {{ $v === 'cash' ? 'on' : '' }}" data-method="{{ $v }}" onclick="selPay('{{ $v }}')">
                            <span class="mi">{{ $ic }}</span>{{ $lb }}
                        </div>
                    @endforeach
                </div>
                <input type="hidden" id="pm" value="cash">
            </div>
            <div class="ps" id="cashSec">
                <div class="ps-ttl"><i class="bi bi-cash"></i>Thu tiền</div>
                <input type="number" class="pi" id="paid" placeholder="0" min="0" step="1000" oninput="calc()">
                <div class="qbs">
                    @foreach([50000, 100000, 200000, 500000, 1000000] as $a)
                        <button class="qb2" onclick="addCash({{ $a }})">{{ number_format($a / 1000) }}k</button>
                    @endforeach
                    <button class="qb2" style="color:var(--teal);" onclick="exact()">Đúng tiền</button>
                </div>
                <div class="cr"><span style="color:var(--muted)">Tiền thối:</span><span class="cv"
                        id="dChange">0đ</span></div>
            </div>
            <div class="ps">
                <div class="ps-ttl"><i class="bi bi-percent"></i>Giảm giá</div>
                <div class="discrow">
                    <input type="number" class="pi" id="disc" placeholder="0" min="0" step="1000" oninput="calc()"
                        style="font-size:15px">
                    <button class="gst" onclick="document.getElementById('disc').value=0;calc()">Xóa</button>
                </div>
            </div>
            <div class="ps" id="rxSec" style="display:none;">
                <div class="rx-ban"><i class="bi bi-exclamation-triangle-fill"></i>Có thuốc kê đơn — cần nhập mã</div>
                <input type="text" class="cinput mt-2" id="rxCode" placeholder="Mã đơn thuốc" style="margin-top:8px">
            </div>
            <button class="btn-co" id="btnCo" disabled onclick="submit()">
                <i class="bi bi-check-circle-fill"></i>
                <span id="coTxt">Thanh toán [F4]</span>
            </button>
        </div>

        {{-- COL HIST --}}
        <div class="col-hist">
            <div class="hist-hd">
                <div class="ht"><i class="bi bi-clock-history me-1"></i>Hóa đơn trong ca</div>
            </div>
            <div class="hist-bd" id="histBd">
                @forelse($recentInvoices as $inv)
                    <div class="hi" onclick="window.open('{{ route('invoices.print', $inv) }}','_blank')">
                        <div class="hcode">{{ $inv->code }}</div>
                        <div class="hcust">{{ $inv->customer?->name ?? 'Khách lẻ' }}</div>
                        <div class="hmeta">
                            <span class="hamt">{{ number_format($inv->total_amount / 1000, 0, ',', '.') }}k</span>
                            <span
                                class="pb {{ $inv->payment_method }}">{{ match ($inv->payment_method) { 'cash' => 'TM', 'card' => 'Thẻ', 'transfer' => 'CK', 'debt' => 'Nợ', default => $inv->payment_method} }}</span>
                        </div>
                        <div class="htime">{{ $inv->invoice_date->format('H:i') }}</div>
                    </div>
                @empty
                    <div style="padding:20px;text-align:center;color:var(--muted);font-size:12px;">Chưa có hóa đơn</div>
                @endforelse
            </div>
            <div class="hist-ft">
                <div class="ssum">
                    <div class="sbox">
                        <div class="sl">Doanh thu ca</div>
                        <div class="sv" id="sRev">{{ number_format($shiftStats['revenue'] / 1000, 0, ',', '.') }}k</div>
                    </div>
                    <div class="sbox">
                        <div class="sl">Số HĐ</div>
                        <div class="sv" id="sCnt">{{ $shiftStats['invoice_count'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="overlay" id="ov">
        <div class="modal2">
            <div class="mico">✅</div>
            <h3>Thanh toán thành công!</h3>
            <div class="mc" id="mCode"></div>
            <div class="mt2" id="mTotal"></div>
            <div class="mch" id="mChg"></div>
            <div class="mbtns">
                <button class="mbtn mbtn-p" onclick="printInv()"><i class="bi bi-printer me-1"></i>In hóa đơn</button>
                <button class="mbtn mbtn-n" onclick="newInv()"><i class="bi bi-plus-lg me-1"></i>Hóa đơn mới</button>
            </div>
        </div>
    </div>

    <!-- MODAL THÊM KHÁCH HÀNG -->
    <div class="cust-overlay" id="custOverlay">
        <div class="cust-modal">
            <div class="cm-hd">
                <h4><i class="bi bi-person-plus-fill"></i> Thêm khách hàng mới</h4>
                <button class="cm-close" onclick="closeAddCust()">×</button>
            </div>

            <div class="cm-alert" id="cmAlert"></div>

            <div class="cm-field">
                <label>Họ và tên <span>*</span></label>
                <input type="text" class="cm-inp" id="cmName" placeholder="VD: Nguyễn Thị Lan" autocomplete="off">
            </div>

            <div class="cm-field">
                <label>Số điện thoại <span>*</span></label>
                <input type="tel" class="cm-inp" id="cmPhone" placeholder="VD: 0912345678" autocomplete="off"
                    oninput="this.value=this.value.replace(/[^0-9+]/g,'')">
            </div>

            <div class="cm-row">
                <div class="cm-field">
                    <label>Ngày sinh</label>
                    <input type="date" class="cm-inp" id="cmDob" max="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="cm-field">
                    <label>Giới tính</label>
                    <select class="cm-sel cm-inp" id="cmGender">
                        <option value="">-- Không chọn --</option>
                        <option value="female">👩 Nữ</option>
                        <option value="male">👨 Nam</option>
                        <option value="other">🧑 Khác</option>
                    </select>
                </div>
            </div>

            <div class="cm-btns">
                <button class="cm-btn-cancel" onclick="closeAddCust()">Hủy</button>
                <button class="cm-btn-save" id="cmSaveBtn" onclick="saveNewCust()">
                    <i class="bi bi-person-check-fill"></i> Lưu & Chọn khách
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        let cart = [], payM = 'cash', lastId = null;
        let shiftRev ={{ $shiftStats['revenue'] }}, shiftCnt ={{ $shiftStats['invoice_count'] }};

        // Clock
        setInterval(() => {
            const n = new Date();
            document.getElementById('clock').textContent =
                String(n.getHours()).padStart(2, '0') + ':' + String(n.getMinutes()).padStart(2, '0') + ':' + String(n.getSeconds()).padStart(2, '0');
        }, 1000);

        // Shortcuts
        document.addEventListener('keydown', e => {
            if (e.key === 'F2') { e.preventDefault(); document.getElementById('ms').focus(); }
            if (e.key === 'F4' && !document.getElementById('btnCo').disabled) { e.preventDefault(); submit(); }
            if (e.key === 'F3') { e.preventDefault(); const p = document.getElementById('paid'); p.focus(); p.select(); }
            if (e.key === 'Escape') closeS();
        });

        // Barcode detection
        let bcBuf = '', bcT = null;
        document.getElementById('ms').addEventListener('input', function () {
            bcBuf = this.value;
            document.getElementById('bci').classList.add('on');
            clearTimeout(bcT);
            bcT = setTimeout(() => document.getElementById('bci').classList.remove('on'), 200);
            clearTimeout(sT);
            const q = this.value.trim();
            if (q.length < 1) { closeS(); return; }
            sT = setTimeout(() => srch(q), 280);
        });
        document.getElementById('ms').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                const q = this.value.trim();
                if (q.length >= 1) { clearTimeout(sT); srch(q, true); }
            }
            // Arrow key navigation
            const dd = document.getElementById('sdd');
            const items = dd.querySelectorAll('.si-item:not(.oos)');
            if (e.key === 'ArrowDown') { e.preventDefault(); fidx = Math.min(fidx + 1, items.length - 1); items.forEach((el, i) => el.classList.toggle('active', i === fidx)); }
            if (e.key === 'ArrowUp') { e.preventDefault(); fidx = Math.max(fidx - 1, 0); items.forEach((el, i) => el.classList.toggle('active', i === fidx)); }
        });

        // Medicine Search
        let sT, fidx = -1;
        async function srch(q, imm = false) {
            const dd = document.getElementById('sdd');
            dd.innerHTML = '<div style="padding:10px;text-align:center;color:var(--muted);font-size:12px;"><span class="spinner-border spinner-border-sm"></span> Đang tìm...</div>';
            dd.style.display = '';
            try {
                const r = await fetch(`/api/medicines/search?q=${encodeURIComponent(q)}`, {
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!r.ok) { dd.innerHTML = `<div style="padding:12px;text-align:center;color:red;font-size:12px;">Lỗi ${r.status} — thử lại</div>`; return; }
                const d = await r.json();
                const ms = d.medicines || [];
                if (imm && ms.length === 1 && ms[0].total_stock > 0) { addToCart(ms[0]); closeS(); document.getElementById('ms').classList.add('scanning'); setTimeout(() => document.getElementById('ms').classList.remove('scanning'), 600); return; }
                renderS(ms, q);
            } catch (err) {
                console.error('Medicine search error:', err);
                dd.innerHTML = '<div style="padding:12px;text-align:center;color:red;font-size:12px;">Không thể kết nối — kiểm tra mạng</div>';
                dd.style.display = '';
            }
        }
        function renderS(meds, q) {
            const dd = document.getElementById('sdd'); fidx = -1;
            if (!meds.length) { dd.innerHTML = `<div style="padding:14px;text-align:center;color:var(--muted);font-size:13px;">Không tìm thấy "<strong>${q}</strong>"</div>`; dd.style.display = ''; return; }
            dd.innerHTML = meds.map(m => {
                const oos = m.total_stock <= 0;
                const safeM = JSON.stringify(JSON.stringify(m));
                return `<div class="si-item${oos ? ' oos' : ''}" onclick="${oos ? 'void(0)' : `addFS(${safeM})`}" style="cursor:${oos ? 'default' : 'pointer'}">
            <div><div class="si-name">${m.name}${m.requires_prescription ? '<span class="rx-badge">Kê đơn</span>' : ''}${m.total_stock > 0 && m.total_stock <= 10 ? '<span class="low-badge">Sắp hết</span>' : ''}</div>
            <div class="si-meta"><code style="font-size:10px;color:var(--teal)">${m.code || ''}</code>${m.next_expiry ? ` · HSD:${m.next_expiry}` : ''}${m.category ? ` · ${m.category}` : ''}</div></div>
            <div><div class="si-price">${fmt(m.sell_price)}</div><div class="si-stock${oos ? ' text-danger fw-bold' : ''}">${oos ? '🚫 Hết hàng' : `Tồn: ${m.total_stock} ${m.unit || ''}`}</div></div>
        </div>`;
            }).join('');
            dd.style.display = '';
        }
        function addFS(js) { try { addToCart(JSON.parse(js)); closeS(); } catch (e) { console.error('addFS error:', e); } }
        function closeS() { document.getElementById('sdd').style.display = 'none'; document.getElementById('ms').value = ''; fidx = -1; }
        document.addEventListener('click', e => {
            if (!document.getElementById('SW').contains(e.target)) closeS();
            if (!document.getElementById('CW').contains(e.target)) document.getElementById('cdd').style.display = 'none';
        });

        // Cart
        function addToCart(m) {
            const ex = cart.find(i => i.medicine_id === m.id);
            if (ex) { if (ex.quantity >= m.total_stock) { alert(`Chỉ còn ${m.total_stock} ${m.unit}!`); return; } ex.quantity++; ex._new = true; }
            else { if (m.total_stock <= 0) { alert('Hết hàng!'); return; } cart.push({ medicine_id: m.id, name: m.name, unit: m.unit, sell_price: m.sell_price, quantity: 1, max_qty: m.total_stock, next_expiry: m.next_expiry || '', requires_prescription: !!m.requires_prescription, usage_instruction: m.default_usage || '', _new: true }); }
            renderCart(); calc();
        }
        function renderCart() {
            const em = document.getElementById('empt'), tb = document.getElementById('ctbl'), rows = document.getElementById('cr'), btn = document.getElementById('btnClr');
            if (!cart.length) { em.style.display = ''; tb.style.display = 'none'; btn.style.display = 'none'; document.getElementById('cc').textContent = '0'; document.getElementById('rxSec').style.display = 'none'; return; }
            em.style.display = 'none'; tb.style.display = ''; btn.style.display = '';
            document.getElementById('cc').textContent = cart.reduce((s, i) => s + i.quantity, 0);
            document.getElementById('rxSec').style.display = cart.some(i => i.requires_prescription) ? '' : 'none';
            rows.innerHTML = cart.map((it, idx) => `
        <tr id="r${idx}" class="${it._new ? 'flash-add' : ''}">
            <td>
              <div class="mn">${it.name}</div>
              <div class="ms">${it.requires_prescription ? '<span class="rx-badge">Kê đơn</span> ' : ''}${it.next_expiry ? `<span style="color:var(--teal);font-size:10px;">HSD:${it.next_expiry}</span>` : ''}</div>
              <div style="margin-top:5px;">
                <input type="text"
                  placeholder="💊 Liều dùng: VD: Ngày 3 lần, mỗi lần 1 viên, sau ăn"
                  value="${it.usage_instruction || ''}"
                  onchange="cart[${idx}].usage_instruction=this.value"
                  style="width:100%;font-size:11px;padding:4px 7px;border:1px dashed #0b5e5a;
                         border-radius:6px;color:#0b5e5a;background:#f0fdf9;outline:none;
                         font-family:inherit;"
                  title="Hướng dẫn sử dụng — sẽ in lên hóa đơn">
              </div>
            </td>
            <td style="text-align:center;font-size:11px;color:var(--muted)">${it.next_expiry || '—'}</td>
            <td><div class="qc"><button class="qb" onclick="chg(${idx},-1)">−</button><input class="qi" type="number" value="${it.quantity}" min="1" max="${it.max_qty}" onchange="setQ(${idx},this.value)"><button class="qb" onclick="chg(${idx},1)">+</button></div><div style="font-size:10px;color:var(--muted);text-align:center;margin-top:2px">Tồn:${it.max_qty} ${it.unit}</div></td>
            <td style="text-align:right;font-size:12px">${fmt(it.sell_price)}</td>
            <td style="text-align:right" class="lt">${fmt(it.sell_price * it.quantity)}</td>
            <td><button class="rb" onclick="rm(${idx})"><i class="bi bi-x"></i></button></td>
        </tr>`).join('');
            cart.forEach(i => i._new = false);
        }
        function chg(idx, d) { const it = cart[idx]; const nq = it.quantity + d; if (nq < 1) { rm(idx); return; } if (nq > it.max_qty) { alert(`Chỉ còn ${it.max_qty}!`); return; } it.quantity = nq; renderCart(); calc(); }
        function setQ(idx, v) { cart[idx].quantity = Math.max(1, Math.min(parseInt(v) || 1, cart[idx].max_qty)); renderCart(); calc(); }
        function rm(idx) { cart.splice(idx, 1); renderCart(); calc(); }
        document.getElementById('btnClr').onclick = () => { if (confirm('Xóa hết giỏ hàng?')) { cart = []; clrCust(); document.getElementById('paid').value = ''; document.getElementById('disc').value = ''; renderCart(); calc(); } };

        // Totals
        function calc() {
            const sub = cart.reduce((s, i) => s + i.sell_price * i.quantity, 0);
            const disc = parseFloat(document.getElementById('disc').value) || 0;
            const tot = Math.max(0, sub - disc);
            const pd = parseFloat(document.getElementById('paid').value) || 0;
            const chg = Math.max(0, pd - tot);
            document.getElementById('dTotal').textContent = fmt(tot);
            document.getElementById('dChange').textContent = fmt(chg);
            const coTxtEl = document.getElementById('coTxt');
            if (coTxtEl) coTxtEl.textContent = `Thanh toán ${tot > 0 ? fmt(tot) : ''}  [F4]`;
            document.getElementById('btnCo').disabled = cart.length === 0 || (payM === 'cash' && pd <= 0);
        }
        function addCash(a) { const c = parseFloat(document.getElementById('paid').value) || 0; document.getElementById('paid').value = c + a; calc(); }
        function exact() { const s = cart.reduce((s, i) => s + i.sell_price * i.quantity, 0); const d = parseFloat(document.getElementById('disc').value) || 0; document.getElementById('paid').value = Math.max(0, s - d); calc(); }

        // Payment
        function selPay(m) {
            payM = m;
            document.querySelectorAll('.pmb').forEach(b => b.classList.toggle('on', b.dataset.method === m));
            document.getElementById('cashSec').style.display = m === 'cash' ? '' : 'none';
            calc();
        }

        // Customer Search
        let cT;
        document.getElementById('cust').addEventListener('input', function () {
            clearTimeout(cT);
            const q = this.value.trim();
            if (q.length < 1) { document.getElementById('cdd').style.display = 'none'; return; }
            cT = setTimeout(() => srchCust(q), 300);
        });
        async function srchCust(q) {
            const dd = document.getElementById('cdd');
            dd.innerHTML = '<div class="cdi" style="color:var(--muted);font-size:12px;"><span class="spinner-border spinner-border-sm"></span> Đang tìm...</div>';
            dd.style.display = '';
            try {
                const r = await fetch(`/api/customers/search?q=${encodeURIComponent(q)}`, {
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!r.ok) { dd.innerHTML = `<div class="cdi" style="color:red;font-size:12px;">Lỗi ${r.status} — thử lại</div>`; dd.style.display = ''; return; }
                const d = await r.json();
                const addBtn = `<div class="cdi" style="color:var(--teal);border-top:1px solid var(--bdr);" onclick="openAddCust('${q.replace(/'/g, "\\'")}')">
            <div class="dn" style="display:flex;align-items:center;gap:6px;"><span style="font-size:16px;">＋</span> Thêm khách mới: "<strong>${q}</strong>"</div>
            <div class="dm">Khách chưa có trong hệ thống</div>
        </div>`;
                if (!d.customers?.length) {
                    dd.innerHTML = '<div class="cdi" style="color:var(--muted);font-size:12px;">Không tìm thấy khách hàng</div>' + addBtn;
                    dd.style.display = ''; return;
                }
                dd.innerHTML = d.customers.map(c => `<div class="cdi" data-customer='${JSON.stringify(c)}'><div class="dn">${c.name}</div><div class="dm">📞 ${c.phone || '—'}${c.current_debt > 0 ? ` · <span style="color:var(--danger)">Nợ: ${fmt(c.current_debt)}</span>` : ''}</div></div>`).join('') + addBtn;
                dd.style.display = '';
                // Attach event listeners
                dd.querySelectorAll('.cdi[data-customer]').forEach(el => {
                    el.addEventListener('click', (e) => {
                        try { selCustObj(JSON.parse(el.getAttribute('data-customer'))); } 
                        catch (err) { console.error('Parse customer error:', err); }
                    });
                });
            } catch (err) {
                console.error('Customer search error:', err);
                dd.innerHTML = '<div class="cdi" style="color:red;font-size:12px;">Không thể kết nối — kiểm tra mạng</div>';
                dd.style.display = '';
            }
        }
        // selCust → see selCustObj below
        function clrCust() { document.getElementById('cid').value = ''; document.getElementById('cust').value = ''; document.getElementById('ctag').style.display = 'none'; }

        // Submit
        async function submit() {
            if (!cart.length) return;
            const btn = document.getElementById('btnCo');
            btn.disabled = true; btn.style.background = '#E2E8F0'; btn.style.color = '#64748B'; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';
            const sub = cart.reduce((s, i) => s + i.sell_price * i.quantity, 0);
            const disc = parseFloat(document.getElementById('disc').value) || 0;
            const tot = Math.max(0, sub - disc);
            const pd = payM === 'cash' ? (parseFloat(document.getElementById('paid').value) || 0) : tot;
            const chg = Math.max(0, pd - tot);
            try {
                const r = await fetch('/invoices', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, body: JSON.stringify({ customer_id: document.getElementById('cid').value || null, payment_method: payM, paid_amount: pd, discount_amount: disc, prescription_code: document.getElementById('rxCode')?.value || null, items: cart.map(i => ({ medicine_id: i.medicine_id, quantity: i.quantity, sell_price: i.sell_price, unit: i.unit, discount_percent: 0, usage_instruction: i.usage_instruction || null })) }) });
                const d = await r.json();
                if (d.success) {
                    lastId = d.invoice_id;
                    shiftRev += tot; shiftCnt++;
                    const rk = n => new Intl.NumberFormat('vi-VN').format(Math.round(n)) + ($1 => $1)('');
                    document.getElementById('sRev').textContent = Math.round(shiftRev / 1000).toLocaleString('vi-VN') + 'k';
                    document.getElementById('sCnt').textContent = shiftCnt;
                    document.getElementById('topRev').textContent = Math.round(shiftRev / 1000).toLocaleString('vi-VN') + 'k';
                    document.getElementById('topCnt').textContent = shiftCnt;
                    prependHist({ code: d.code, tot, pay: payM });
                    document.getElementById('mCode').textContent = d.code;
                    document.getElementById('mTotal').textContent = fmt(tot);
                    document.getElementById('mChg').textContent = chg > 0 ? `Tiền thối: ${fmt(chg)}` : '';
                    resetBtn(); document.getElementById('ov').style.display = 'flex';
                } else { alert('❌ ' + (d.message || 'Lỗi')); resetBtn(); calc(); }
            } catch (e) { alert('Lỗi kết nối'); resetBtn(); }
        }

        // History
        function prependHist({ code, tot, pay }) {
            const lb = { cash: 'TM', card: 'Thẻ', transfer: 'CK', debt: 'Nợ' };
            const el = document.createElement('div');
            el.className = 'hi flash-add';
            el.innerHTML = `<div class="hcode">${code}</div><div class="hcust">Vừa thanh toán</div><div class="hmeta"><span class="hamt">${fmt(tot)}</span><span class="pb ${pay}">${lb[pay] || pay}</span></div><div class="htime">Vừa xong</div>`;
            el.onclick = () => lastId && window.open(`/invoices/${lastId}/print`, '_blank');
            document.getElementById('histBd').prepend(el);
        }

        // Modal
        function printInv() { if (lastId) window.open(`/invoices/${lastId}/print`, '_blank'); }
        function resetBtn() {
            const btn = document.getElementById('btnCo');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i><span id="coTxt">Thanh toán  [F4]</span>';
        }
        function newInv() {
            document.getElementById('ov').style.display = 'none';
            cart = [];
            clrCust();
            document.getElementById('paid').value = '';
            document.getElementById('disc').value = '';
            if (document.getElementById('rxCode')) document.getElementById('rxCode').value = '';
            renderCart();
            resetBtn();   // reset trước
            calc();       // calc sau — sẽ disable vì cart rỗng (đúng)
            document.getElementById('ms').focus();
        }

        // ── Thêm khách hàng nhanh từ POS ────────────────────────────────────────────
        function openAddCust(prefill = '') {
            document.getElementById('cmName').value = '';
            document.getElementById('cmPhone').value = '';
            document.getElementById('cmDob').value = '';
            document.getElementById('cmGender').value = '';
            const al = document.getElementById('cmAlert');
            al.className = 'cm-alert'; al.textContent = '';

            // Nếu prefill là số điện thoại → điền vào SĐT, còn lại → tên
            if (prefill) {
                if (/^[0-9+]{8,}$/.test(prefill)) {
                    document.getElementById('cmPhone').value = prefill;
                } else {
                    document.getElementById('cmName').value = prefill;
                }
            }

            document.getElementById('custOverlay').classList.add('open');
            document.getElementById('cdd').style.display = 'none';
            // Focus vào trường còn trống
            setTimeout(() => {
                const nameEl = document.getElementById('cmName');
                const phoneEl = document.getElementById('cmPhone');
                (nameEl.value ? phoneEl : nameEl).focus();
            }, 120);
        }

        function closeAddCust() {
            document.getElementById('custOverlay').classList.remove('open');
        }

        // Đóng khi click bên ngoài modal
        document.getElementById('custOverlay').addEventListener('click', function (e) {
            if (e.target === this) closeAddCust();
        });

        // Phím Enter trong modal
        document.getElementById('cmPhone').addEventListener('keydown', e => {
            if (e.key === 'Enter') saveNewCust();
        });

        async function saveNewCust() {
            const name = document.getElementById('cmName').value.trim();
            const phone = document.getElementById('cmPhone').value.trim();
            const dob = document.getElementById('cmDob').value;
            const gender = document.getElementById('cmGender').value;
            const al = document.getElementById('cmAlert');
            const btn = document.getElementById('cmSaveBtn');

            // Validate
            al.className = 'cm-alert'; al.textContent = '';
            if (!name) { al.textContent = '⚠ Vui lòng nhập họ tên khách hàng.'; al.classList.add('show'); document.getElementById('cmName').focus(); return; }
            if (!phone) { al.textContent = '⚠ Vui lòng nhập số điện thoại.'; al.classList.add('show'); document.getElementById('cmPhone').focus(); return; }
            if (!/^[0-9+]{9,12}$/.test(phone)) { al.textContent = '⚠ Số điện thoại không hợp lệ (9–12 chữ số).'; al.classList.add('show'); return; }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang lưu...';

            try {
                const res = await fetch('/api/customers/quick-create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: JSON.stringify({ name, phone, date_of_birth: dob || null, gender: gender || null })
                });
                const d = await res.json();

                if (d.duplicate) {
                    // SĐT trùng — hỏi có muốn chọn khách cũ không
                    al.className = 'cm-alert warn show';
                    al.innerHTML = `⚠ ${d.message}<br><button onclick="selCustObj(${JSON.stringify(JSON.stringify(d.customer))});closeAddCust();" style="margin-top:6px;background:var(--teal);border:none;color:#000;padding:5px 14px;border-radius:6px;font-weight:700;cursor:pointer;font-size:12px;">✓ Chọn khách này</button>`;
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-person-check-fill"></i> Lưu & Chọn khách';
                    return;
                }

                if (d.success) {
                    // Tự động select khách vừa tạo vào hóa đơn
                    selCustObj(JSON.stringify(d.customer));
                    closeAddCust();

                    // Flash thông báo thành công
                    showPosToast(`✅ Đã thêm khách hàng: ${d.customer.name} (${d.customer.code})`);
                } else {
                    al.textContent = '❌ ' + (d.message || 'Lỗi không xác định');
                    al.classList.add('show');
                }
            } catch (e) {
                al.textContent = '❌ Lỗi kết nối. Vui lòng thử lại.';
                al.classList.add('show');
            }

            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-person-check-fill"></i> Lưu & Chọn khách';
        }

        // selCust nhận JSON string (từ dropdown hiện tại)
        function selCust(js) { selCustObj(js); }

        // selCustObj nhận JSON string hoặc object
        function selCustObj(jsOrObj) {
            const c = typeof jsOrObj === 'string' ? JSON.parse(jsOrObj) : jsOrObj;
            document.getElementById('cid').value = c.id;
            document.getElementById('cust').value = '';
            document.getElementById('cdd').style.display = 'none';
            document.getElementById('ctag').innerHTML = `<div class="ctag">
        <div>
            <div class="cn">👤 ${c.name} <span style="font-size:10px;opacity:.7">${c.code || ''}</span></div>
            <div style="font-size:11px;color:var(--muted)">
                📞 ${c.phone || '—'}
                ${c.current_debt > 0 ? ` · <span style="color:var(--danger)">Nợ: ${fmt(c.current_debt)}</span>` : ''}
            </div>
        </div>
        <div class="cx" onclick="clrCust()">×</div>
    </div>`;
            document.getElementById('ctag').style.display = '';
        }

        // Toast notification nhỏ
        function showPosToast(msg, duration = 3000) {
            let t = document.getElementById('posToast');
            if (!t) {
                t = document.createElement('div');
                t.id = 'posToast';
                t.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:#fff;border:1.5px solid #99F6E4;color:#0F766E;padding:12px 24px;border-radius:12px;font-weight:700;font-size:13px;z-index:9999;transition:opacity .3s;pointer-events:none;box-shadow:0 8px 24px rgba(0,0,0,.12)';
                document.body.appendChild(t);
            }
            t.textContent = msg;
            t.style.opacity = '1';
            clearTimeout(t._t);
            t._t = setTimeout(() => { t.style.opacity = '0'; }, duration);
        }

        // ── Helpers
        function fmt(n) { return new Intl.NumberFormat('vi-VN').format(Math.round(n || 0)) + 'đ'; }

        // Init
        document.getElementById('ms').focus(); calc();
    </script>
</body>

</html>