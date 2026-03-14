@extends('layouts.app')
@section('title', 'Báo cáo xuất nhập tồn')
@section('page-title', 'Xuất nhập tồn')

@section('content')

    <div class="page-header">
        <div>
            <h4 class="mb-0"><i class="bi bi-journal-text me-2 text-primary"></i>Báo cáo xuất nhập tồn</h4>
            <small class="text-muted">Sổ theo dõi từng loại thuốc theo ngày — Chuẩn GPP</small>
        </div>
        @if($medicine && $ledger->isNotEmpty())
            <a href="{{ request()->fullUrlWithQuery(['export' => '1']) }}" class="btn btn-outline-success">
                <i class="bi bi-download me-1"></i>Xuất CSV
            </a>
        @endif
    </div>

    {{-- ── BỘ LỌC ──────────────────────────────────────────────────────────── --}}
    <div class="card mb-4">
        <div class="card-header py-3 px-4">
            <h6 class="mb-0 fw-bold"><i class="bi bi-funnel me-2"></i>Chọn thuốc & kỳ báo cáo</h6>
        </div>
        <div class="card-body px-4 py-3">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold small">Tên thuốc <span class="text-danger">*</span></label>
                    <select name="medicine_id" class="form-select" required>
                        <option value="">-- Chọn thuốc cần xem --</option>
                        @foreach($medicines as $m)
                            <option value="{{ $m->id }}" {{ $medicineId == $m->id ? 'selected' : '' }}>
                                {{ $m->name }} ({{ $m->unit }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Từ ngày</label>
                    <input type="date" name="from" class="form-control" value="{{ $from }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Đến ngày</label>
                    <input type="date" name="to" class="form-control" value="{{ $to }}">
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search me-1"></i>Xem báo cáo
                        </button>
                        {{-- Quick filters --}}
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown">
                                Nhanh
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="setRange('month')">Tháng này</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setRange('lastmonth')">Tháng trước</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setRange('quarter')">Quý này</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setRange('year')">Năm nay</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($medicine)

        {{-- ── HEADER THÔNG TIN THUỐC ─────────────────────────────────────────── --}}
        <div class="card mb-4" style="border-left:4px solid #2E86C1;">
            <div class="card-body py-3 px-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center"
                                style="width:52px;height:52px;background:#eaf4fb;font-size:26px;">💊</div>
                            <div>
                                <div class="fw-bold fs-5">{{ $medicine->name }}</div>
                                <small class="text-muted">
                                    ĐVT: {{ $medicine->unit }} &nbsp;|&nbsp;
                                    Kỳ: {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} —
                                    {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row g-3 text-center">
                            <div class="col-4">
                                <div class="text-muted small">Tồn đầu kỳ</div>
                                <div class="fw-bold fs-5 text-secondary">{{ number_format($openingStock ?? 0) }}</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">Tổng nhập</div>
                                <div class="fw-bold fs-5 text-success">
                                    +{{ number_format($ledger->sum('import_qty')) }}
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">Tổng xuất</div>
                                <div class="fw-bold fs-5 text-danger">
                                    -{{ number_format($ledger->sum('export_qty')) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── BẢNG LEDGER ──────────────────────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-table me-2"></i>
                    Sổ xuất nhập tồn — {{ $ledger->count() }} dòng phát sinh
                </h6>
                @php
                    $closingStock = ($openingStock ?? 0) + $ledger->sum('import_qty') - $ledger->sum('export_qty');
                @endphp
                <span class="badge bg-primary fs-6">
                    Tồn cuối kỳ: {{ number_format($closingStock) }} {{ $medicine->unit }}
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 table-sm">
                    <thead class="table-light">
                        <tr>
                            <th style="width:110px;">Ngày</th>
                            <th style="width:100px;">Loại CT</th>
                            <th>Số chứng từ</th>
                            <th>Diễn giải</th>
                            <th class="text-center" style="width:90px;">Nhập</th>
                            <th class="text-center" style="width:90px;">Xuất</th>
                            <th class="text-center" style="width:90px;">Tồn</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Dòng tồn đầu kỳ --}}
                        <tr class="table-secondary">
                            <td class="fw-semibold">{{ \Carbon\Carbon::parse($from)->format('d/m/Y') }}</td>
                            <td><span class="badge bg-secondary">Đầu kỳ</span></td>
                            <td colspan="2" class="text-muted fst-italic">Tồn đầu kỳ kết chuyển</td>
                            <td class="text-center">—</td>
                            <td class="text-center">—</td>
                            <td class="text-center fw-bold">{{ number_format($openingStock ?? 0) }}</td>
                        </tr>

                        @forelse($ledger as $row)
                            <tr>
                                <td class="text-muted small">
                                    {{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}
                                </td>
                                <td>
                                    @if($row['type'] === 'import')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-box-arrow-in-down me-1"></i>Nhập
                                        </span>
                                    @elseif($row['type'] === 'export')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="bi bi-box-arrow-up me-1"></i>Xuất
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                            <i class="bi bi-sliders me-1"></i>Điều chỉnh
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <code class="text-primary small">{{ $row['ref'] }}</code>
                                </td>
                                <td class="text-muted small">{{ $row['note'] ?: '—' }}</td>
                                <td class="text-center fw-semibold text-success">
                                    {{ $row['import_qty'] > 0 ? '+' . number_format($row['import_qty']) : '—' }}
                                </td>
                                <td class="text-center fw-semibold text-danger">
                                    {{ $row['export_qty'] > 0 ? '-' . number_format($row['export_qty']) : '—' }}
                                </td>
                                <td class="text-center fw-bold">
                                    <span
                                        class="{{ $row['balance'] <= 0 ? 'text-danger' : ($row['balance'] <= 10 ? 'text-warning' : '') }}">
                                        {{ number_format($row['balance']) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Không có phát sinh trong kỳ báo cáo này.
                                </td>
                            </tr>
                        @endforelse

                        {{-- Dòng cộng cuối kỳ --}}
                        @if($ledger->isNotEmpty())
                            <tr class="table-primary fw-bold">
                                <td colspan="4" class="text-end px-4">Cộng phát sinh trong kỳ:</td>
                                <td class="text-center text-success">+{{ number_format($ledger->sum('import_qty')) }}</td>
                                <td class="text-center text-danger">-{{ number_format($ledger->sum('export_qty')) }}</td>
                                <td class="text-center text-primary fs-6">{{ number_format($closingStock) }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    @else
        {{-- Trạng thái chưa chọn thuốc --}}
        <div class="card">
            <div class="card-body text-center py-5">
                <div style="font-size:64px;">📋</div>
                <h5 class="fw-bold mt-3">Chọn thuốc để xem sổ xuất nhập tồn</h5>
                <p class="text-muted">Chọn tên thuốc và khoảng thời gian ở bộ lọc phía trên.</p>
                <div class="mt-3 p-3 rounded-3 text-start d-inline-block" style="background:#f8fafc;max-width:500px;">
                    <div class="fw-semibold small mb-2">📌 Báo cáo này gồm:</div>
                    <ul class="small text-muted mb-0">
                        <li>Tồn đầu kỳ</li>
                        <li>Từng lần nhập hàng (từ đơn nhập)</li>
                        <li>Từng lần xuất bán (từ hóa đơn)</li>
                        <li>Điều chỉnh tồn kho</li>
                        <li>Tồn cuối kỳ theo từng ngày</li>
                    </ul>
                </div>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
    <script>
        function setRange(type) {
            const today = new Date();
            let from, to = today.toISOString().split('T')[0];

            if (type === 'month') {
                from = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
            } else if (type === 'lastmonth') {
                from = new Date(today.getFullYear(), today.getMonth() - 1, 1).toISOString().split('T')[0];
                to = new Date(today.getFullYear(), today.getMonth(), 0).toISOString().split('T')[0];
            } else if (type === 'quarter') {
                const q = Math.floor(today.getMonth() / 3);
                from = new Date(today.getFullYear(), q * 3, 1).toISOString().split('T')[0];
            } else if (type === 'year') {
                from = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
            }

            document.querySelector('[name=from]').value = from;
            document.querySelector('[name=to]').value = to;
        }
    </script>
@endpush