@extends('layouts.app')
@section('title', 'Đơn nhập: ' . $purchaseOrder->code)
@section('page-title', 'Chi tiết đơn nhập hàng')

@section('content')

    <div class="page-header">
        <div class="d-flex align-items-center gap-3">
            <h4 class="mb-0">
                <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                {{ $purchaseOrder->code }}
            </h4>
            <span class="badge bg-{{ $purchaseOrder->status_color }} fs-6 py-2 px-3">
                {{ $purchaseOrder->status_label }}
            </span>
        </div>
        <div class="d-flex gap-2">
            {{-- In phiếu nhập --}}
            <a href="{{ route('purchase.print', $purchaseOrder) }}" target="_blank" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-1"></i> In phiếu
            </a>
            {{-- Workflow buttons --}}
            @if($purchaseOrder->canApprove() && auth()->user()->hasPermission('purchase.approve'))
                <form action="{{ route('purchase.approve', $purchaseOrder) }}" method="POST">
                    @csrf @method('PATCH')
                    <button class="btn btn-success fw-semibold"
                        onclick="return confirm('Duyệt đơn nhập hàng {{ $purchaseOrder->code }}?')">
                        <i class="bi bi-check-circle me-1"></i> Duyệt đơn
                    </button>
                </form>
            @endif

            @if($purchaseOrder->canReceive() && auth()->user()->hasPermission('purchase.receive'))
                <a href="{{ route('purchase.receive.form', $purchaseOrder) }}" class="btn btn-warning fw-semibold">
                    <i class="bi bi-box-arrow-in-down me-1"></i> Nhận hàng
                </a>
            @endif

            @if($purchaseOrder->canCancel() && auth()->user()->hasPermission('purchase.create'))
                <form action="{{ route('purchase.cancel', $purchaseOrder) }}" method="POST">
                    @csrf @method('PATCH')
                    <button class="btn btn-outline-danger" onclick="return confirm('Hủy đơn hàng này? Không thể hoàn tác.')">
                        <i class="bi bi-x-circle me-1"></i> Hủy đơn
                    </button>
                </form>
            @endif

            <a href="{{ route('purchase.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row g-3">

        {{-- ── THÔNG TIN ĐƠN HÀNG ──────────────────────────────────────────── --}}
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header py-3 px-4">
                    <h6 class="mb-0 fw-bold">📋 Thông tin đơn hàng</h6>
                </div>
                <div class="card-body px-4 py-3">
                    @php
                        $infoRows = [
                            ['Mã đơn hàng', '<code class="fw-bold text-primary">' . $purchaseOrder->code . '</code>'],
                            ['Nhà cung cấp', $purchaseOrder->supplier->name],
                            ['Ngày đặt hàng', $purchaseOrder->order_date->format('d/m/Y')],
                            ['Ngày dự kiến nhận', $purchaseOrder->expected_date?->format('d/m/Y') ?? '—'],
                            ['Người tạo', $purchaseOrder->createdBy?->name ?? '—'],
                            ['Người duyệt', $purchaseOrder->approvedBy?->name ?? '—'],
                            ['Ngày duyệt', $purchaseOrder->approved_at?->format('d/m/Y H:i') ?? '—'],
                            ['Người nhận hàng', $purchaseOrder->receivedBy?->name ?? '—'],
                            ['Ngày nhận hàng', $purchaseOrder->received_date?->format('d/m/Y') ?? '—'],
                        ];
                    @endphp
                    @foreach($infoRows as [$label, $value])
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted small">{{ $label }}</span>
                            <span class="fw-semibold text-end small">{!! $value !!}</span>
                        </div>
                    @endforeach

                    @if($purchaseOrder->note)
                        <div class="mt-3 p-3 rounded" style="background:#f8fafc;">
                            <small class="text-muted d-block mb-1">Ghi chú:</small>
                            <span class="small">{{ $purchaseOrder->note }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Tổng kết tài chính --}}
            <div class="card">
                <div class="card-header py-3 px-4">
                    <h6 class="mb-0 fw-bold">💰 Tổng kết tài chính</h6>
                </div>
                <div class="card-body px-4 py-3">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Tổng tiền hàng</span>
                        <strong>{{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}đ</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Đã thanh toán</span>
                        <strong class="text-success">
                            {{ number_format($purchaseOrder->paid_amount ?? 0, 0, ',', '.') }}đ
                        </strong>
                    </div>
                    <div class="d-flex justify-content-between py-3">
                        <span class="fw-bold">Còn nợ</span>
                        @php $debt = $purchaseOrder->total_amount - ($purchaseOrder->paid_amount ?? 0); @endphp
                        <strong class="fs-5 {{ $debt > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($debt, 0, ',', '.') }}đ
                        </strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── DANH SÁCH THUỐC ──────────────────────────────────────────────── --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header py-3 px-4">
                    <h6 class="mb-0 fw-bold">
                        💊 Danh sách thuốc
                        <span class="badge bg-primary ms-1">{{ $purchaseOrder->items->count() }} dòng</span>
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tên thuốc</th>
                                <th class="text-center">ĐVT</th>
                                <th class="text-center">SL đặt</th>
                                <th class="text-center">SL nhận</th>
                                <th class="text-end">Giá nhập</th>
                                <th class="text-end">Thành tiền</th>
                                @if($purchaseOrder->status === 'received')
                                    <th class="text-center">Số lô</th>
                                    <th class="text-center">Hạn dùng</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->items as $i => $item)
                                <tr>
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $item->medicine->name }}</div>
                                        <small class="text-muted">{{ $item->medicine->code }}</small>
                                    </td>
                                    <td class="text-center">{{ $item->unit }}</td>
                                    <td class="text-center fw-semibold">
                                        {{ number_format($item->ordered_quantity) }}
                                    </td>
                                    <td class="text-center">
                                        @if($item->received_quantity)
                                            <span class="text-success fw-bold">
                                                {{ number_format($item->received_quantity) }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($item->purchase_price, 0, ',', '.') }}đ
                                    </td>
                                    <td class="text-end money fw-bold">
                                        {{ number_format($item->total_amount, 0, ',', '.') }}đ
                                    </td>
                                    @if($purchaseOrder->status === 'received')
                                        <td class="text-center">
                                            <code>{{ $item->batch_number ?? '—' }}</code>
                                        </td>
                                        <td class="text-center small">
                                            {{ $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('d/m/Y') : '—' }}
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="{{ $purchaseOrder->status === 'received' ? 6 : 6 }}"
                                    class="text-end fw-bold px-4">Tổng cộng:</td>
                                <td class="text-end fw-bold text-primary">
                                    {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}đ
                                </td>
                                @if($purchaseOrder->status === 'received')
                                    <td colspan="2"></td>
                                @endif
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Timeline trạng thái --}}
            <div class="card mt-3">
                <div class="card-header py-3 px-4">
                    <h6 class="mb-0 fw-bold">🔄 Tiến trình đơn hàng</h6>
                </div>
                <div class="card-body px-4 py-3">
                    <div class="d-flex align-items-center gap-2">
                        @php
                            $steps = [
                                ['key' => 'pending', 'label' => 'Chờ duyệt', 'icon' => 'bi-clock'],
                                ['key' => 'approved', 'label' => 'Đã duyệt', 'icon' => 'bi-check-circle'],
                                ['key' => 'received', 'label' => 'Đã nhận', 'icon' => 'bi-box'],
                            ];
                            $statusOrder = ['pending' => 0, 'approved' => 1, 'received' => 2, 'cancelled' => -1];
                            $currentOrder = $statusOrder[$purchaseOrder->status] ?? 0;
                        @endphp

                        @if($purchaseOrder->status === 'cancelled')
                            <div class="alert alert-danger py-2 px-3 mb-0 w-100 small">
                                <i class="bi bi-x-octagon me-2"></i>
                                <strong>Đơn hàng đã bị hủy</strong>
                            </div>
                        @else
                            @foreach($steps as $i => $step)
                                @php $done = $currentOrder >= $statusOrder[$step['key']]; @endphp
                                <div class="text-center flex-fill">
                                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1" style="width:40px;height:40px;
                                                    background:{{ $done ? '#e8f8f0' : '#f0f2f5' }};
                                                    border:2px solid {{ $done ? '#1E8449' : '#dee2e6' }};">
                                        <i class="bi {{ $step['icon'] }}"
                                            style="color:{{ $done ? '#1E8449' : '#adb5bd' }};font-size:16px;"></i>
                                    </div>
                                    <div class="small fw-semibold" style="color:{{ $done ? '#1E8449' : '#adb5bd' }}">
                                        {{ $step['label'] }}
                                    </div>
                                </div>
                                @if(!$loop->last)
                                    <div class="flex-fill"
                                        style="height:2px;background:{{ $currentOrder > $statusOrder[$step['key']] ? '#1E8449' : '#dee2e6' }};margin-top:-20px;">
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection