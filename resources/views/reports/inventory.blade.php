@extends('layouts.app')
@section('title','Báo cáo NXT')
@section('page-title','Báo cáo Nhập - Xuất - Tồn')

@section('content')

<div class="page-header">
    <div>
        <h4 class="mb-0"><i class="bi bi-clipboard-data me-2 text-primary"></i>Báo cáo Nhập — Xuất — Tồn</h4>
        <small class="text-muted">Theo dõi biến động tồn kho trong kỳ theo chuẩn GPP</small>
    </div>
    @if(auth()->user()->hasPermission('report.export'))
    <a href="{{ route('reports.inventory.export', ['from'=>$from,'to'=>$to]) }}"
       class="btn btn-outline-success">
        <i class="bi bi-download me-1"></i> Xuất CSV
    </a>
    @endif
</div>

{{-- Bộ lọc --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Từ ngày</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Đến ngày</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
            </div>
            <div class="col-md-6 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search me-1"></i>Xem báo cáo
                </button>
                @foreach([
                    ['Tháng này',   now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
                    ['Tháng trước', now()->subMonth()->startOfMonth()->format('Y-m-d'), now()->subMonth()->endOfMonth()->format('Y-m-d')],
                    ['Quý này',     now()->startOfQuarter()->format('Y-m-d'), now()->format('Y-m-d')],
                ] as [$label, $f, $t])
                <a href="{{ route('reports.inventory', ['from'=>$f,'to'=>$t]) }}"
                   class="btn btn-sm btn-outline-secondary {{ $from==$f && $to==$t ? 'active':'' }}">
                    {{ $label }}
                </a>
                @endforeach
            </div>
        </form>
    </div>
</div>

{{-- Tóm tắt kỳ --}}
<div class="row g-3 mb-4">
    @php
        $totalImport  = $medicines->sum('imported');
        $totalExport  = $medicines->sum('exported');
        $totalClosing = $medicines->sum('closing_stock');
    @endphp
    <div class="col-sm-4">
        <div class="card" style="border-left:4px solid #1E8449;">
            <div class="card-body px-4 py-3 text-center">
                <div class="text-success fw-bold fs-2">
                    +{{ number_format($totalImport) }}
                </div>
                <div class="fw-semibold">Tổng nhập trong kỳ</div>
                <small class="text-muted">{{ $medicines->filter(fn($m)=>$m['imported']>0)->count() }} loại thuốc</small>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card" style="border-left:4px solid #C0392B;">
            <div class="card-body px-4 py-3 text-center">
                <div class="text-danger fw-bold fs-2">
                    -{{ number_format($totalExport) }}
                </div>
                <div class="fw-semibold">Tổng xuất trong kỳ</div>
                <small class="text-muted">{{ $medicines->filter(fn($m)=>$m['exported']>0)->count() }} loại thuốc</small>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card" style="border-left:4px solid #1B6FA8;">
            <div class="card-body px-4 py-3 text-center">
                <div class="text-primary fw-bold fs-2">
                    {{ number_format($totalClosing) }}
                </div>
                <div class="fw-semibold">Tổng tồn cuối kỳ</div>
                <small class="text-muted">Tất cả loại thuốc</small>
            </div>
        </div>
    </div>
</div>

{{-- Bảng NXT --}}
<div class="card">
    <div class="card-header py-3 px-4 d-flex justify-content-between">
        <h6 class="mb-0 fw-bold">
            Báo cáo NXT từ
            <span class="text-primary">{{ \Carbon\Carbon::parse($from)->format('d/m/Y') }}</span>
            đến
            <span class="text-primary">{{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</span>
        </h6>
        <small class="text-muted">{{ $medicines->count() }} loại thuốc có phát sinh</small>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>Tên thuốc</th>
                    <th class="text-center">ĐVT</th>
                    <th class="text-center" style="background:#e8f0fe;">Tồn đầu kỳ</th>
                    <th class="text-center" style="background:#d4edda;">Nhập kỳ</th>
                    <th class="text-center" style="background:#f8d7da;">Xuất kỳ</th>
                    <th class="text-center" style="background:#e8f4fd;">Tồn cuối kỳ</th>
                    <th class="text-center">Biến động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($medicines->values() as $i => $row)
                @php
                    $diff    = $row['closing_stock'] - $row['opening_stock'];
                    $hasMove = $row['imported'] > 0 || $row['exported'] > 0;
                @endphp
                <tr class="{{ !$hasMove ? 'text-muted' : '' }}">
                    <td class="text-muted small">{{ $i + 1 }}</td>
                    <td>
                        <div class="fw-semibold">{{ $row['medicine']->name }}</div>
                        <small class="text-muted">{{ $row['medicine']->code }}</small>
                    </td>
                    <td class="text-center small">{{ $row['unit'] }}</td>
                    <td class="text-center fw-semibold" style="background:#e8f0fe20;">
                        {{ number_format($row['opening_stock']) }}
                    </td>
                    <td class="text-center" style="background:#d4edda20;">
                        @if($row['imported'] > 0)
                            <span class="text-success fw-bold">+{{ number_format($row['imported']) }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center" style="background:#f8d7da20;">
                        @if($row['exported'] > 0)
                            <span class="text-danger fw-bold">-{{ number_format($row['exported']) }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center fw-bold" style="background:#e8f4fd20;">
                        <span class="{{ $row['closing_stock'] == 0 ? 'text-danger' : 'text-primary' }}">
                            {{ number_format($row['closing_stock']) }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if($diff > 0)
                            <span class="badge bg-success">▲ {{ number_format($diff) }}</span>
                        @elseif($diff < 0)
                            <span class="badge bg-danger">▼ {{ number_format(abs($diff)) }}</span>
                        @else
                            <span class="text-muted small">Không đổi</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="3" class="text-end px-4">Tổng cộng:</td>
                    <td class="text-center">
                        {{ number_format($medicines->sum('opening_stock')) }}
                    </td>
                    <td class="text-center text-success">
                        +{{ number_format($totalImport) }}
                    </td>
                    <td class="text-center text-danger">
                        -{{ number_format($totalExport) }}
                    </td>
                    <td class="text-center text-primary">
                        {{ number_format($totalClosing) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@endsection