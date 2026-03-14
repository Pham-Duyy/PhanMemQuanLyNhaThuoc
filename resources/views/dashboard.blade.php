@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

    {{-- ── A. KPI Cards hôm nay ─────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">

        {{-- Doanh thu --}}
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f4fd;font-size:24px;">💰</div>
                <div class="flex-fill">
                    <div class="stat-value text-primary">
                        {{ number_format($todayKpi['revenue'] / 1000000, 2) }}tr đ
                    </div>
                    <div class="stat-label d-flex justify-content-between align-items-center">
                        <span>Doanh thu hôm nay</span>
                        @if($todayKpi['revenueGrowth'] !== null)
                            <span class="badge {{ $todayKpi['revenueGrowth'] >= 0 ? 'bg-success' : 'bg-danger' }}"
                                style="font-size:10px;"
                                title="So với hôm qua: {{ number_format($todayKpi['yesterdayRevenue'] / 1000000, 2) }}tr đ">
                                {{ $todayKpi['revenueGrowth'] >= 0 ? '▲' : '▼' }}
                                {{ abs($todayKpi['revenueGrowth']) }}%
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Lãi gộp --}}
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f8f0;font-size:24px;">📈</div>
                <div class="flex-fill">
                    <div class="stat-value text-success">
                        {{ number_format($todayKpi['grossProfit'] / 1000000, 2) }}tr đ
                    </div>
                    <div class="stat-label d-flex justify-content-between align-items-center">
                        <span>Lãi gộp hôm nay</span>
                        @if($todayKpi['revenue'] > 0)
                            <span class="badge bg-light text-success border" style="font-size:10px;">
                                {{ number_format($todayKpi['grossProfit'] / $todayKpi['revenue'] * 100, 1) }}%
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Số HĐ --}}
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3e8;font-size:24px;">🧾</div>
                <div class="flex-fill">
                    <div class="stat-value" style="color:#D35400;">{{ $todayKpi['invoiceCount'] }}</div>
                    <div class="stat-label d-flex justify-content-between align-items-center">
                        <span>Hóa đơn hôm nay</span>
                        @if($todayKpi['invoiceCount'] > 0)
                            <span class="badge bg-light text-muted border" style="font-size:10px;">
                                TB: {{ number_format($todayKpi['revenue'] / $todayKpi['invoiceCount'] / 1000, 0) }}k/HĐ
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Tháng này --}}
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#f3e8fd;font-size:24px;">📅</div>
                <div class="flex-fill">
                    <div class="stat-value" style="color:#7D3C98;">
                        {{ number_format($monthKpi['revenue'] / 1000000, 1) }}tr đ
                    </div>
                    <div class="stat-label d-flex justify-content-between align-items-center">
                        <span>Doanh thu tháng {{ now()->month }}</span>
                        @if($monthKpi['monthGrowth'] !== null)
                            <span class="badge {{ $monthKpi['monthGrowth'] >= 0 ? 'bg-success' : 'bg-danger' }}"
                                style="font-size:10px;">
                                {{ $monthKpi['monthGrowth'] >= 0 ? '▲' : '▼' }}
                                {{ abs($monthKpi['monthGrowth']) }}%
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── B. Hàng thống kê phụ ────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 rounded-3 border text-center" style="background:#f8fafc;">
                <div class="fw-bold fs-5 text-danger">
                    {{ number_format($monthKpi['totalCustomerDebt'] / 1000000, 2) }}tr đ
                </div>
                <div class="small text-muted">📒 Tổng nợ khách hàng</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 rounded-3 border text-center" style="background:#f8fafc;">
                <div class="fw-bold fs-5 text-warning">
                    {{ number_format($monthKpi['totalSupplierDebt'] / 1000000, 2) }}tr đ
                </div>
                <div class="small text-muted">🏭 Tổng nợ nhà cung cấp</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 rounded-3 border text-center" style="background:#f8fafc;">
                <div class="fw-bold fs-5 text-primary">
                    {{ number_format($monthKpi['inventoryValue'] / 1000000, 1) }}tr đ
                </div>
                <div class="small text-muted">📦 Giá trị tồn kho</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="p-3 rounded-3 border text-center" style="background:#f8fafc;">
                @if($monthKpi['revenue'] > 0)
                    <div class="fw-bold fs-5 text-success">
                        {{ number_format($monthKpi['grossProfit'] / $monthKpi['revenue'] * 100, 1) }}%
                    </div>
                @else
                    <div class="fw-bold fs-5 text-muted">—</div>
                @endif
                <div class="small text-muted">📈 Biên lãi gộp tháng</div>
            </div>
        </div>
    </div>

    {{-- ── C. Biểu đồ ──────────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">

        {{-- Biểu đồ 30 ngày --}}
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">📊 Doanh thu & lãi gộp 30 ngày gần nhất</h6>
                    <div class="d-flex gap-2">
                        <span class="badge bg-primary">Doanh thu</span>
                        <span class="badge bg-success">Lãi gộp</span>
                    </div>
                </div>
                <div class="card-body px-4 pb-3">
                    <canvas id="chart30days" height="90"></canvas>
                </div>
            </div>
        </div>

        {{-- Biểu đồ giờ hôm nay --}}
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header py-3 px-4">
                    <h6 class="mb-0 fw-bold">⏰ Doanh thu theo giờ hôm nay</h6>
                </div>
                <div class="card-body px-3 pb-3">
                    <canvas id="chartHourly" height="160"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ── C2. Biểu đồ 12 tháng so sánh năm ngoái ────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">📅 Doanh thu 12 tháng — So sánh năm ngoái</h6>
                    <div class="d-flex align-items-center gap-3">
                        @if(isset($monthlyChartData['yearGrowth']) && $monthlyChartData['yearGrowth'] !== null)
                            <span class="badge fs-6 {{ $monthlyChartData['yearGrowth'] >= 0 ? 'bg-success' : 'bg-danger' }}">
                                {{ $monthlyChartData['yearGrowth'] >= 0 ? '▲' : '▼' }}
                                {{ abs($monthlyChartData['yearGrowth']) }}% so với {{ now()->year - 1 }}
                            </span>
                        @endif
                        <div class="d-flex gap-2">
                            <span class="badge" style="background:#2471A3;">{{ now()->year }}</span>
                            <span class="badge bg-secondary">{{ now()->year - 1 }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body px-4 pb-3">
                    {{-- KPI tổng năm --}}
                    <div class="row g-2 mb-3">
                        <div class="col-sm-4">
                            <div class="p-3 rounded-3 text-center" style="background:#eaf4fb;">
                                <div class="text-muted small">Tổng DT năm {{ now()->year }}</div>
                                <div class="fw-bold" style="color:#2471A3;font-size:18px;">
                                    {{ number_format(($monthlyChartData['totalThisYear'] ?? 0) / 1000000, 1) }}tr đ
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 rounded-3 text-center" style="background:#f8f9fa;">
                                <div class="text-muted small">Tổng DT năm {{ now()->year - 1 }}</div>
                                <div class="fw-bold text-secondary" style="font-size:18px;">
                                    {{ number_format(($monthlyChartData['totalLastYear'] ?? 0) / 1000000, 1) }}tr đ
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-3 rounded-3 text-center" style="background:#e8f8f0;">
                                <div class="text-muted small">Tổng lãi gộp năm {{ now()->year }}</div>
                                <div class="fw-bold text-success" style="font-size:18px;">
                                    {{ number_format(array_sum($monthlyChartData['profits'] ?? []) / 1000000, 1) }}tr đ
                                </div>
                            </div>
                        </div>
                    </div>
                    <canvas id="chart12months" height="80"></canvas>
                </div>
            </div>
        </div>

        {{-- Top 10 thuốc bán chạy --}}
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">🏆 Top 10 thuốc tháng {{ now()->month }}</h6>
                    <a href="{{ route('reports.revenue') }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                        Báo cáo
                    </a>
                </div>
                <div style="overflow-y:auto;max-height:420px;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width:32px;">#</th>
                                <th>Thuốc</th>
                                <th class="text-end">Doanh thu</th>
                                <th class="text-end">Lãi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topMedicines as $i => $item)
                                @php
                                    $medals = ['🥇', '🥈', '🥉'];
                                    $medal = $medals[$i] ?? ($i + 1);
                                    $margin = $item->revenue > 0
                                        ? round($item->gross_profit / $item->revenue * 100, 0) : 0;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $medal }}</td>
                                    <td>
                                        <div class="fw-semibold" style="font-size:12px;line-height:1.3;">
                                            {{ Str::limit($item->medicine?->name, 28) }}
                                        </div>
                                        <div style="font-size:10px;color:#888;">
                                            {{ number_format($item->total_sold) }} {{ $item->medicine?->unit }}
                                        </div>
                                    </td>
                                    <td class="text-end fw-semibold" style="font-size:12px;">
                                        {{ number_format($item->revenue / 1000, 0, ',', '.') }}k
                                    </td>
                                    <td class="text-end" style="font-size:11px;">
                                        <span
                                            class="badge bg-{{ $margin >= 20 ? 'success' : ($margin >= 10 ? 'warning text-dark' : 'secondary') }}">
                                            {{ $margin }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Chưa có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ── D. Cảnh báo + Top 5 ─────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">

        {{-- Lô sắp hết hạn --}}
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header py-3 px-4 d-flex justify-content-between">
                    <h6 class="mb-0 fw-bold">⚠️ Lô sắp hết hạn (30 ngày)</h6>
                    <a href="{{ route('inventory.expiring') }}" class="btn btn-sm btn-outline-danger py-0 px-2">Xem tất
                        cả</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($expiringBatches as $batch)
                        <a href="{{ route('inventory.batches', $batch->medicine_id) }}"
                            class="list-group-item list-group-item-action px-4 py-2 {{ $batch->days_until_expiry <= 7 ? 'list-group-item-danger' : 'list-group-item-warning' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="fw-semibold small">{{ $batch->medicine?->name }}</div>
                                <span class="badge bg-{{ $batch->expiry_badge_color }} ms-2" style="white-space:nowrap;">
                                    {{ $batch->days_until_expiry }}n
                                </span>
                            </div>
                            <div class="d-flex justify-content-between" style="font-size:11px;color:#666;">
                                <span>Lô: <code>{{ $batch->batch_number }}</code></span>
                                <span>Còn: {{ number_format($batch->current_quantity) }} {{ $batch->medicine?->unit }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="list-group-item text-center text-muted py-4">
                            ✅ Không có lô nào sắp hết hạn
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Thuốc sắp hết tồn --}}
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header py-3 px-4 d-flex justify-content-between">
                    <h6 class="mb-0 fw-bold">🔻 Thuốc sắp hết tồn</h6>
                    <a href="{{ route('inventory.low-stock') }}" class="btn btn-sm btn-outline-warning py-0 px-2">Xem tất
                        cả</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($lowStockMedicines as $med)
                        <a href="{{ route('inventory.batches', $med) }}" class="list-group-item list-group-item-action px-4 py-2
                                  {{ $med->total_stock == 0 ? 'list-group-item-danger' : 'list-group-item-warning' }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fw-semibold small">{{ $med->name }}</div>
                                <span class="fw-bold {{ $med->total_stock == 0 ? 'text-danger' : 'text-warning' }}">
                                    {{ $med->total_stock }}
                                    <small class="fw-normal text-muted">/ {{ $med->min_stock }}</small>
                                </span>
                            </div>
                            <div style="font-size:11px;color:#666;">
                                {{ $med->unit }} · {{ $med->category?->name ?? 'Chưa phân nhóm' }}
                            </div>
                        </a>
                    @empty
                        <div class="list-group-item text-center text-muted py-4">
                            ✅ Tất cả thuốc đủ tồn kho
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Drill-down: Tồn kho thấp chi tiết --}}
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header py-3 px-4 d-flex justify-content-between">
                    <h6 class="mb-0 fw-bold">🔻 Tồn kho thấp — Chi tiết</h6>
                    <a href="{{ route('inventory.low-stock') }}" class="btn btn-sm btn-outline-warning py-0 px-2">Xem tất
                        cả</a>
                </div>
                <div class="list-group list-group-flush" style="overflow-y:auto;max-height:320px;">
                    @forelse($lowStockMedicines as $med)
                        @php
                            $stock = $med->total_stock;
                            $min = $med->min_stock ?: 1;
                            $pct = min(100, $stock / $min * 100);
                            $color = $stock == 0 ? 'danger' : ($pct < 50 ? 'warning' : 'info');
                        @endphp
                        <div class="list-group-item px-4 py-2">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div>
                                    <div class="fw-semibold small">{{ $med->name }}</div>
                                    <small class="text-muted">{{ $med->category?->name ?? '—' }}</small>
                                </div>
                                <div class="text-end ms-2 flex-shrink-0">
                                    <span class="fw-bold {{ $stock == 0 ? 'text-danger' : 'text-warning' }}"
                                        style="font-size:15px;">{{ $stock }}</span>
                                    <small class="text-muted"> / {{ $min }} {{ $med->unit }}</small>
                                </div>
                            </div>
                            <div class="progress" style="height:5px;border-radius:3px;">
                                <div class="progress-bar bg-{{ $color }}" style="width:{{ $pct }}%;"></div>
                            </div>
                            @if($stock == 0)
                                <small class="text-danger fw-semibold">⚡ Hết hàng — Cần nhập ngay!</small>
                            @endif
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted py-4">✅ Tất cả đủ tồn</div>
                    @endforelse
                </div>
                <div class="card-footer py-2 px-4 text-center">
                    <a href="{{ route('purchase.create') }}" class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-plus-lg me-1"></i>Tạo đơn nhập hàng
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── E. Đơn nhập chờ duyệt + Hoạt động gần đây ─────────────────────── --}}
    <div class="row g-3">

        {{-- Đơn nhập chờ duyệt --}}
        @if(auth()->user()->hasPermission('purchase.approve') && $pendingOrders->count() > 0)
            <div class="col-xl-5">
                <div class="card">
                    <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            ⏳ Đơn nhập chờ duyệt
                            <span class="badge bg-warning text-dark ms-1">{{ $pendingOrders->count() }}</span>
                        </h6>
                        <a href="{{ route('purchase.index', ['status' => 'pending']) }}"
                            class="btn btn-sm btn-outline-warning py-0 px-2">Xem tất cả</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Nhà cung cấp</th>
                                    <th class="text-end">Tổng tiền</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingOrders as $po)
                                    <tr>
                                        <td>
                                            <a href="{{ route('purchase.show', $po) }}"
                                                class="fw-semibold text-primary text-decoration-none">
                                                {{ $po->code }}
                                            </a>
                                            <div class="small text-muted">{{ $po->order_date->format('d/m/Y') }}</div>
                                        </td>
                                        <td class="small">{{ $po->supplier?->name }}</td>
                                        <td class="text-end fw-semibold small">
                                            {{ number_format($po->total_amount / 1000, 0, ',', '.') }}k
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('purchase.show', $po) }}"
                                                class="btn btn-sm btn-warning py-0 px-2">Duyệt</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Hoạt động gần đây --}}
        <div
            class="col-xl-{{ auth()->user()->hasPermission('purchase.approve') && $pendingOrders->count() > 0 ? '7' : '12' }}">
            <div class="card">
                <div class="card-header py-3 px-4">
                    <h6 class="mb-0 fw-bold">🕐 Hoạt động gần đây</h6>
                </div>
                <div class="list-group list-group-flush" style="max-height:340px;overflow-y:auto;">
                    @forelse($recentActivity as $activity)
                        <a href="{{ $activity['route'] }}"
                            class="list-group-item list-group-item-action px-4 py-2 text-decoration-none">
                            <div class="d-flex align-items-start gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:32px;height:32px;background:#f0f2f5;font-size:14px;">
                                    {{ $activity['icon'] }}
                                </div>
                                <div class="flex-fill min-width-0">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-semibold small">{{ $activity['title'] }}</span>
                                        <span class="text-muted" style="font-size:11px;white-space:nowrap;margin-left:8px;">
                                            {{ $activity['time']?->diffForHumans() }}
                                        </span>
                                    </div>
                                    <div class="text-muted"
                                        style="font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $activity['desc'] }}
                                    </div>
                                    @if($activity['user'])
                                        <div style="font-size:11px;color:#aaa;">
                                            👤 {{ $activity['user'] }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="list-group-item text-center text-muted py-4">
                            Chưa có hoạt động nào hôm nay
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // ── Biểu đồ doanh thu 30 ngày ──────────────────────────────────────────────
        const ctx30 = document.getElementById('chart30days').getContext('2d');
        new Chart(ctx30, {
            type: 'bar',
            data: {
                labels: @json($revenueChartData['labels']),
                datasets: [
                    {
                        label: 'Doanh thu',
                        data: @json($revenueChartData['revenues']),
                        backgroundColor: 'rgba(36,113,163,0.75)',
                        borderColor: '#1B4F72',
                        borderWidth: 1,
                        borderRadius: 3,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Lãi gộp',
                        data: @json($revenueChartData['profits']),
                        type: 'line',
                        borderColor: '#27AE60',
                        backgroundColor: 'rgba(39,174,96,0.08)',
                        borderWidth: 2,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                        fill: true,
                        tension: 0.3,
                        yAxisID: 'y',
                    },
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { size: 12 } } },
                    tooltip: {
                        callbacks: {
                            label: ctx => '  ' + ctx.dataset.label + ': ' +
                                new Intl.NumberFormat('vi-VN').format(ctx.raw) + 'đ',
                            afterBody: (items) => {
                                const revenues = @json($revenueChartData['revenues']);
                                const profits = @json($revenueChartData['profits']);
                                const idx = items[0]?.dataIndex;
                                if (revenues[idx] > 0) {
                                    const margin = (profits[idx] / revenues[idx] * 100).toFixed(1);
                                    return ['  Biên lãi gộp: ' + margin + '%'];
                                }
                                return [];
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { size: 11 },
                            callback: v => v >= 1000000
                                ? (v / 1000000).toFixed(1) + 'tr'
                                : (v / 1000).toFixed(0) + 'k'
                        },
                        grid: { color: 'rgba(0,0,0,.05)' }
                    }
                }
            }
        });

        // ── Biểu đồ 12 tháng so sánh năm ngoái ────────────────────────────────────
        const ctx12m = document.getElementById('chart12months').getContext('2d');
        new Chart(ctx12m, {
            type: 'bar',
            data: {
                labels: @json($monthlyChartData['labels']),
                datasets: [
                    {
                        label: 'Năm {{ now()->year }}',
                        data: @json($monthlyChartData['revenues']),
                        backgroundColor: 'rgba(36,113,163,0.8)',
                        borderColor: '#1B4F72',
                        borderWidth: 1,
                        borderRadius: 4,
                        order: 2,
                    },
                    {
                        label: 'Năm {{ now()->year - 1 }}',
                        data: @json($monthlyChartData['lastYearRevs']),
                        backgroundColor: 'rgba(150,150,150,0.35)',
                        borderColor: 'rgba(120,120,120,0.6)',
                        borderWidth: 1,
                        borderRadius: 4,
                        order: 3,
                    },
                    {
                        label: 'Lãi gộp {{ now()->year }}',
                        data: @json($monthlyChartData['profits']),
                        type: 'line',
                        borderColor: '#27AE60',
                        backgroundColor: 'rgba(39,174,96,0.06)',
                        borderWidth: 2.5,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.35,
                        order: 1,
                    },
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { size: 12 } } },
                    tooltip: {
                        callbacks: {
                            label: ctx => '  ' + ctx.dataset.label + ': ' +
                                new Intl.NumberFormat('vi-VN').format(ctx.raw) + 'đ',
                            afterBody: items => {
                                const revenues = @json($monthlyChartData['revenues']);
                                const lastYr = @json($monthlyChartData['lastYearRevs']);
                                const idx = items[0]?.dataIndex;
                                const thisM = revenues[idx] || 0;
                                const lastM = lastYr[idx] || 0;
                                if (lastM > 0) {
                                    const growth = ((thisM - lastM) / lastM * 100).toFixed(1);
                                    const icon = growth >= 0 ? '▲' : '▼';
                                    return [`  So năm ngoái: ${icon} ${Math.abs(growth)}%`];
                                }
                                return [];
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { size: 11 },
                            callback: v => v >= 1000000
                                ? (v / 1000000).toFixed(0) + 'tr'
                                : (v / 1000).toFixed(0) + 'k'
                        },
                        grid: { color: 'rgba(0,0,0,.05)' }
                    }
                }
            }
        });

        // ── Biểu đồ doanh thu theo giờ ─────────────────────────────────────────────
        const ctxH = document.getElementById('chartHourly').getContext('2d');
        new Chart(ctxH, {
            type: 'bar',
            data: {
                labels: @json($hourlyToday['labels']),
                datasets: [{
                    label: 'Doanh thu',
                    data: @json($hourlyToday['revenues']),
                    backgroundColor: (ctx) => {
                        const h = ctx.dataIndex + 7; // offset vì bắt đầu từ 7h
                        const now = {{ now()->hour }};
                        return h === now
                            ? 'rgba(211,84,0,0.85)'      // giờ hiện tại → cam đậm
                            : 'rgba(36,113,163,0.6)';    // các giờ khác → xanh
                    },
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => '  ' + new Intl.NumberFormat('vi-VN').format(ctx.raw) + 'đ'
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { size: 10 },
                            callback: v => v >= 1000000
                                ? (v / 1000000).toFixed(1) + 'tr'
                                : (v / 1000).toFixed(0) + 'k'
                        },
                        grid: { color: 'rgba(0,0,0,.04)' }
                    }
                }
            }
        });
    </script>
@endpush