@extends('layouts.app')
@section('title', 'Báo cáo công nợ')
@section('page-title', 'Báo cáo công nợ')

@section('content')

    <div class="page-header">
        <div>
            <h4 class="mb-0"><i class="bi bi-journal-text me-2 text-primary"></i>Báo cáo công nợ</h4>
            <small class="text-muted">Theo dõi công nợ với nhà cung cấp và khách hàng</small>
        </div>
        @if(auth()->user()->hasPermission('report.export'))
            <a href="{{ route('reports.debt.export', ['type' => $type]) }}" class="btn btn-outline-success">
                <i class="bi bi-download me-1"></i> Xuất CSV
            </a>
        @endif
    </div>

    {{-- Tabs loại công nợ --}}
    <div class="d-flex gap-2 mb-4">
        <a href="{{ route('reports.debt', ['type' => 'supplier']) }}"
            class="btn {{ $type === 'supplier' ? 'btn-primary' : 'btn-outline-secondary' }} fw-semibold">
            <i class="bi bi-truck me-1"></i> Nợ nhà cung cấp
        </a>
        <a href="{{ route('reports.debt', ['type' => 'customer']) }}"
            class="btn {{ $type === 'customer' ? 'btn-primary' : 'btn-outline-secondary' }} fw-semibold">
            <i class="bi bi-people me-1"></i> Nợ khách hàng
        </a>
    </div>

    {{-- KPI --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="stat-card" style="border-left:4px solid #C0392B;">
                <div class="stat-icon" style="background:#fdf0f0;font-size:22px;">
                    {{ $type === 'supplier' ? '🏭' : '👥' }}
                </div>
                <div>
                    <div class="stat-value text-danger" style="font-size:18px;">
                        {{ number_format($totalDebt / 1000000, 2) }}tr đ
                    </div>
                    <div class="stat-label">
                        Tổng nợ {{ $type === 'supplier' ? 'nhà cung cấp' : 'khách hàng' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f4fd;font-size:22px;">📋</div>
                <div>
                    <div class="stat-value">{{ $records->count() }}</div>
                    <div class="stat-label">
                        Số {{ $type === 'supplier' ? 'nhà cung cấp' : 'khách hàng' }} đang nợ
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff8e1;font-size:22px;">📊</div>
                <div>
                    <div class="stat-value text-warning" style="font-size:18px;">
                        {{ $records->count() > 0
        ? number_format($totalDebt / $records->count() / 1000, 0, ',', '.') . 'k'
        : '0' }}
                    </div>
                    <div class="stat-label">Nợ trung bình / đơn vị</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Biểu đồ top 5 --}}
    @if($records->count() > 0)
        <div class="card mb-4">
            <div class="card-header py-3 px-4">
                <h6 class="mb-0 fw-bold">
                    📊 Top {{ min(5, $records->count()) }}
                    {{ $type === 'supplier' ? 'nhà cung cấp' : 'khách hàng' }} có nợ cao nhất
                </h6>
            </div>
            <div class="card-body px-4 py-3">
                @foreach($records->take(5) as $record)
                    @php
                        $pct = $totalDebt > 0 ? ($record->current_debt / $totalDebt * 100) : 0;
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold">{{ $record->name }}</span>
                            <span class="fw-bold text-danger">
                                {{ number_format($record->current_debt, 0, ',', '.') }}đ
                                <small class="text-muted fw-normal">({{ number_format($pct, 1) }}%)</small>
                            </span>
                        </div>
                        <div class="progress" style="height:8px;border-radius:4px;">
                            <div class="progress-bar bg-danger" role="progressbar" style="width:{{ $pct }}%;border-radius:4px;">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Bảng chi tiết --}}
    <div class="card">
        <div class="card-header py-3 px-4">
            <h6 class="mb-0 fw-bold">
                {{ $type === 'supplier' ? '🏭 Danh sách nợ nhà cung cấp' : '👥 Danh sách nợ khách hàng' }}
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Tên {{ $type === 'supplier' ? 'nhà cung cấp' : 'khách hàng' }}</th>
                        @if($type === 'supplier')
                            <th>Mã NCC</th>
                            <th>SĐT liên hệ</th>
                            <th class="text-end">Hạn mức nợ</th>
                        @else
                            <th>Mã KH</th>
                            <th>Số điện thoại</th>
                            <th class="text-end">Hạn mức nợ</th>
                        @endif
                        <th class="text-end">Nợ hiện tại</th>
                        <th class="text-center">Mức độ</th>
                        <th class="text-center">Tỷ lệ</th>
                        <th class="text-center">Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $i => $record)
                        @php
                            $pct = $totalDebt > 0 ? ($record->current_debt / $totalDebt * 100) : 0;
                            $overLimit = $record->debt_limit > 0 && $record->current_debt > $record->debt_limit;
                            $nearLimit = $record->debt_limit > 0 && $record->current_debt > $record->debt_limit * 0.8;
                        @endphp
                        <tr class="{{ $overLimit ? 'table-danger' : ($nearLimit ? 'table-warning' : '') }}">
                            <td class="text-muted small">{{ $i + 1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $record->name }}</div>
                                @if($type === 'supplier' && $record->contact_person)
                                    <small class="text-muted">{{ $record->contact_person }}</small>
                                @endif
                                @if($overLimit)
                                    <span class="badge bg-danger ms-1">Vượt hạn mức</span>
                                @endif
                            </td>
                            <td><code class="small">{{ $record->code }}</code></td>
                            <td class="small text-muted">
                                {{ $type === 'supplier' ? ($record->contact_phone ?? '—') : ($record->phone ?? '—') }}
                            </td>
                            <td class="text-end small text-muted">
                                @if($record->debt_limit > 0)
                                    {{ number_format($record->debt_limit, 0, ',', '.') }}đ
                                @else
                                    <span class="text-muted">Không giới hạn</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold {{ $overLimit ? 'text-danger' : 'text-warning' }}">
                                {{ number_format($record->current_debt, 0, ',', '.') }}đ
                            </td>
                            <td class="text-center">
                                @if($overLimit)
                                    <span class="badge bg-danger">🔴 Vượt hạn</span>
                                @elseif($nearLimit)
                                    <span class="badge bg-warning text-dark">🟡 Gần hạn</span>
                                @else
                                    <span class="badge bg-success">🟢 Bình thường</span>
                                @endif
                            </td>
                            <td class="text-center" style="width:120px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-fill" style="height:6px;">
                                        <div class="progress-bar {{ $overLimit ? 'bg-danger' : 'bg-warning' }}"
                                            style="width:{{ min(100, $pct) }}%;"></div>
                                    </div>
                                    <small class="text-muted" style="width:35px;">
                                        {{ number_format($pct, 0) }}%
                                    </small>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($type === 'supplier')
                                    <a href="{{ route('suppliers.show', $record) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @else
                                    <a href="{{ route('customers.show', $record) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-check-circle text-success fs-1 d-block mb-2"></i>
                                Không có công nợ nào.
                                {{ $type === 'supplier' ? 'Tất cả nhà cung cấp đã thanh toán đầy đủ.' : 'Tất cả khách hàng đã thanh toán đầy đủ.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($records->count() > 0)
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="5" class="text-end px-4">Tổng cộng:</td>
                            <td class="text-end text-danger fw-bold fs-6">
                                {{ number_format($totalDebt, 0, ',', '.') }}đ
                            </td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

@endsection