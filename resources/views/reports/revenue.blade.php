@extends('layouts.app')
@section('title','Báo cáo doanh thu')
@section('page-title','Báo cáo doanh thu')

@section('content')

<div class="page-header">
    <div>
        <h4 class="mb-0"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Báo cáo doanh thu</h4>
        <small class="text-muted">Phân tích doanh thu, lãi gộp và phương thức thanh toán</small>
    </div>
    @if(auth()->user()->hasPermission('report.export'))
    <a href="{{ route('reports.revenue.export', ['from' => $from, 'to' => $to]) }}"
       class="btn btn-outline-success">
        <i class="bi bi-download me-1"></i> Xuất CSV
    </a>
    @endif
</div>

{{-- Bộ lọc ngày --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Từ ngày</label>
                <input type="date" name="from" class="form-control form-control-sm"
                       value="{{ $from }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Đến ngày</label>
                <input type="date" name="to" class="form-control form-control-sm"
                       value="{{ $to }}">
            </div>
            <div class="col-md-6 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search me-1"></i>Xem báo cáo
                </button>
                {{-- Shortcuts --}}
                @foreach([
                    ['Hôm nay',   now()->format('Y-m-d'),                  now()->format('Y-m-d')],
                    ['Tuần này',  now()->startOfWeek()->format('Y-m-d'),   now()->format('Y-m-d')],
                    ['Tháng này', now()->startOfMonth()->format('Y-m-d'),  now()->format('Y-m-d')],
                    ['Tháng trước', now()->subMonth()->startOfMonth()->format('Y-m-d'), now()->subMonth()->endOfMonth()->format('Y-m-d')],
                ] as [$label, $f, $t])
                <a href="{{ route('reports.revenue', ['from'=>$f,'to'=>$t]) }}"
                   class="btn btn-sm btn-outline-secondary {{ $from==$f && $to==$t ? 'active' : '' }}">
                    {{ $label }}
                </a>
                @endforeach
            </div>
        </form>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    @php
        $margin = $summary['revenue'] > 0 ? ($grossProfit / $summary['revenue'] * 100) : 0;
    @endphp
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f4fd;font-size:22px;">💰</div>
            <div>
                <div class="stat-value text-primary" style="font-size:18px;">
                    {{ number_format($summary['revenue']/1000000, 2) }}tr
                </div>
                <div class="stat-label">Tổng doanh thu</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f8f0;font-size:22px;">📈</div>
            <div>
                <div class="stat-value text-success" style="font-size:18px;">
                    {{ number_format($grossProfit/1000000, 2) }}tr
                </div>
                <div class="stat-label">Lãi gộp ({{ number_format($margin,1) }}%)</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff8e1;font-size:22px;">🧾</div>
            <div>
                <div class="stat-value text-warning">{{ number_format($summary['invoice_count']) }}</div>
                <div class="stat-label">Số hóa đơn</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fdf0f0;font-size:22px;">📒</div>
            <div>
                <div class="stat-value text-danger" style="font-size:18px;">
                    {{ number_format($summary['debt']/1000000, 2) }}tr
                </div>
                <div class="stat-label">Tổng công nợ</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">

    {{-- Biểu đồ doanh thu theo ngày --}}
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header py-3 px-4">
                <h6 class="mb-0 fw-bold">📊 Doanh thu theo ngày</h6>
            </div>
            <div class="card-body px-4">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
    </div>

    {{-- Phương thức thanh toán --}}
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header py-3 px-4">
                <h6 class="mb-0 fw-bold">💳 Phương thức thanh toán</h6>
            </div>
            <div class="card-body px-4 d-flex flex-column justify-content-center">
                <canvas id="paymentChart" height="200"></canvas>
                <div class="mt-3">
                    @foreach($byPayment as $p)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="fw-semibold">
                            @php
                                $icons = ['cash'=>'💵','card'=>'💳','transfer'=>'📲','debt'=>'📒','mixed'=>'🔀'];
                                $labels = ['cash'=>'Tiền mặt','card'=>'Thẻ','transfer'=>'Chuyển khoản','debt'=>'Công nợ','mixed'=>'Kết hợp'];
                            @endphp
                            {{ $icons[$p->payment_method] ?? '💰' }}
                            {{ $labels[$p->payment_method] ?? $p->payment_method }}
                        </span>
                        <div class="text-end">
                            <div class="fw-bold money">{{ number_format($p->total/1000000,2) }}tr đ</div>
                            <small class="text-muted">{{ $p->count }} HĐ</small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Top thuốc bán chạy --}}
<div class="row g-3 mb-4">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header py-3 px-4">
                <h6 class="mb-0 fw-bold">🏆 Top 10 thuốc bán chạy trong kỳ</h6>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px" class="text-center">#</th>
                            <th>Tên thuốc</th>
                            <th class="text-center">SL bán</th>
                            <th class="text-end">Doanh thu</th>
                            <th class="text-end">Lãi gộp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topMedicines as $i => $item)
                        <tr>
                            <td class="text-center fw-bold"
                                style="color:{{ ['#f39c12','#95a5a6','#cd7f32','#556a7e','#556a7e','#556a7e','#556a7e','#556a7e','#556a7e','#556a7e'][$i] ?? '#aaa' }}">
                                {{ $i+1 }}
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $item->medicine?->name }}</div>
                                <small class="text-muted">{{ $item->medicine?->unit }}</small>
                            </td>
                            <td class="text-center fw-bold">
                                {{ number_format($item->total_qty) }}
                            </td>
                            <td class="text-end money fw-semibold">
                                {{ number_format($item->total_revenue/1000, 0, ',', '.') }}k
                            </td>
                            <td class="text-end text-success fw-semibold">
                                {{ number_format($item->gross_profit/1000, 0, ',', '.') }}k
                                @php
                                    $m = $item->total_revenue > 0 ? ($item->gross_profit/$item->total_revenue*100) : 0;
                                @endphp
                                <small class="d-block text-muted">{{ number_format($m,1) }}%</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Chưa có dữ liệu bán hàng</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Doanh thu theo ngày (bảng) --}}
    <div class="col-xl-5">
        <div class="card">
            <div class="card-header py-3 px-4 d-flex justify-content-between">
                <h6 class="mb-0 fw-bold">📅 Chi tiết theo ngày</h6>
                <small class="text-muted">{{ $dailyRevenue->count() }} ngày có doanh thu</small>
            </div>
            <div class="table-responsive" style="max-height:360px;overflow-y:auto;">
                <table class="table mb-0">
                    <thead style="position:sticky;top:0;background:#f8fafc;z-index:1;">
                        <tr>
                            <th>Ngày</th>
                            <th class="text-center">HĐ</th>
                            <th class="text-end">Doanh thu</th>
                            <th class="text-end">Còn nợ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailyRevenue->sortByDesc('date') as $day)
                        <tr>
                            <td class="small fw-semibold">
                                {{ \Carbon\Carbon::parse($day->date)->format('d/m/Y') }}
                            </td>
                            <td class="text-center">{{ $day->invoice_count }}</td>
                            <td class="text-end money fw-semibold">
                                {{ number_format($day->revenue/1000, 0, ',', '.') }}k
                            </td>
                            <td class="text-end small {{ $day->debt > 0 ? 'text-danger' : 'text-muted' }}">
                                {{ $day->debt > 0 ? number_format($day->debt/1000,0,',','.').'k' : '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Không có dữ liệu</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Biểu đồ doanh thu theo ngày ──────────────────────────────────────────
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'bar',
    data: {
        @php $chartLabels = $dailyRevenue->pluck('date')->map(function($d){ return \Carbon\Carbon::parse($d)->format('d/m'); })->values(); @endphp
        labels: @json($chartLabels),
        datasets: [
            {
                label: 'Doanh thu',
                data: @json($dailyRevenue->pluck('revenue')),
                backgroundColor: 'rgba(36,113,163,0.7)',
                borderColor: '#1B4F72',
                borderWidth: 1,
                borderRadius: 4,
                yAxisID: 'y',
            },
            {
                label: 'Lãi gộp',
                data: @json(array_fill(0, $dailyRevenue->count(), 0)),
                type: 'line',
                borderColor: '#27AE60',
                backgroundColor: 'transparent',
                borderWidth: 2,
                pointRadius: 3,
                yAxisID: 'y',
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { intersect: false, mode: 'index' },
        plugins: {
            legend: { position: 'top' },
            tooltip: {
                callbacks: {
                    label: ctx => '  ' + ctx.dataset.label + ': ' +
                        new Intl.NumberFormat('vi-VN').format(ctx.raw) + 'đ'
                }
            }
        },
        scales: {
            x: { grid: { display: false } },
            y: {
                beginAtZero: true,
                ticks: { callback: v => (v/1000000).toFixed(1) + 'tr' }
            }
        }
    }
});

// ── Biểu đồ phương thức thanh toán ───────────────────────────────────────
@php
$paymentLabels = $byPayment->map(function($p) {
    $map = ['cash'=>'Tiền mặt','card'=>'Thẻ','transfer'=>'Chuyển khoản','debt'=>'Công nợ','mixed'=>'Kết hợp'];
    return $map[$p->payment_method] ?? $p->payment_method;
})->values();
@endphp
const payCtx = document.getElementById('paymentChart').getContext('2d');
new Chart(payCtx, {
    type: 'doughnut',
    data: {
        labels: @json($paymentLabels),
        datasets: [{
            data: @json($byPayment->pluck('total')),
            backgroundColor: ['#2471A3','#27AE60','#F39C12','#C0392B','#8E44AD'],
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => '  ' + ctx.label + ': ' +
                        new Intl.NumberFormat('vi-VN').format(ctx.raw) + 'đ'
                }
            }
        },
        cutout: '65%',
    }
});
</script>
@endpush