@extends('layouts.app')
@section('title', $customer->name)
@section('page-title', 'Chi tiết khách hàng')

@section('content')
    <div class="page-header">
        <div class="d-flex align-items-center gap-3">
            <h4 class="mb-0"><i class="bi bi-person me-2 text-primary"></i>{{ $customer->name }}</h4>
            @if($customer->current_debt > 0)
                <span class="badge bg-warning text-dark fs-6">
                    Nợ: {{ number_format($customer->current_debt, 0, ',', '.')}}đ
                </span>
            @endif
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()->hasPermission('customer.edit'))
                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i> Chỉnh sửa
                </a>
            @endif
            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header py-3 px-4">
                    <h6 class="mb-0 fw-bold">👤 Thông tin</h6>
                </div>
                <div class="card-body px-4 py-3">
                    @foreach([
                            ['Mã KH', '<code>' . $customer->code . '</code>'],
                            ['SĐT', $customer->phone ?? '—'],
                            ['Ngày sinh', $customer->date_of_birth?->format('d/m/Y') ?? '—'],
                            ['Giới tính', ['male' => 'Nam', 'female' => 'Nữ', 'other' => 'Khác'][$customer->gender] ?? '—'],
                            ['CCCD', $customer->id_card ?? '—'],
                            ['Địa chỉ', $customer->address ?? '—'],
                        ] as [$label, $value])
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted small">{{ $label }}</span>
                            <span class="text-end small">{!! $value !!}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @if($customer->medical_note)
                <div class="card border-warning">
                    <div class="card-header py-2 px-4" style="background:#fff8e1;">
                        <h6 class="mb-0 fw-bold text-warning">⚕️ Ghi chú y tế</h6>
                    </div>
                    <div class="card-body px-4 py-3 small">
                        {{ $customer->medical_note }}
                    </div>


                                </div>
            @endif
        </div>

        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header py-3 px-4"><h6 class="mb-0 fw-bold">🧾 Hóa đơn gần đây</h6></div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Mã HĐ</th>
                                <th class="text-center">Ngày</th>
                                <th class="text-end">Tổng tiền</th>
                                <th class="text-end">Còn nợ</th>
                                <th class="text-center">TT</th>
                            </tr>
                         </thead>
                        <tbody>
                            @forelse($invoices as $inv)
                                <tr>
                                    <td>
                                        <a href="{{ route('invoices.show', $inv) }}"
                                           class="fw-semibold text-primary text-decoration-none">{{ $inv->code }}</a>
                                    </td>
                                    <td class="text-center small">{{ $inv->invoice_date->format('d/m/Y') }}</td>
                                    <td class="text-end money">{{ number_format($inv->total_amount, 0, ',', '.')}}đ</td>
                                    <td class="text-end {{ $inv->debt_amount > 0 ? 'text-danger fw-bold' : 'text-muted' }}">

                                        {{ $inv->debt_amount > 0 ? number_format($inv->debt_amount, 0, ',', '.') . 'đ' : '—' }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $inv->status_color }}">{{ $inv->status_label }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-3 text-muted">Chưa có hóa đơn</td></tr>
                            @endforelse


                                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header py-3 px-4"><h6 class="mb-0 fw-bold">📒 Lịch sử công nợ</h6></div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Mô tả</th>
                                <th class="text-center">Ngày</th>
                                <th class="text-end">Số tiền</th>
                                <th class="text-end">Số dư</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($debtHistory as $tx)
                                <tr>
                                    <td class="small">{{ $tx->description }}</td>

                                    <td class="text-center small">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                                    <td class="text-end {{ $tx->type === 'increase' ? 'text-danger' : 'text-success' }} fw-semibold">
                                        {{ $tx->type === 'increase' ? '+' : '-' }}{{ number_format($tx->amount, 0, ',', '.')}}đ
                                    </td>
                                    <td class="text-end small text-muted">{{ number_format($tx->balance_after, 0, ',', '.')}}đ</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center py-3 text-muted">Chưa có công nợ</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection