@extends('layouts.app')
@section('title', 'Chỉnh sửa: ' . $customer->name)
@section('page-title', 'Chỉnh sửa khách hàng')

@section('content')

    <div class="page-header">
        <div>
            <h4 class="mb-0">
                <i class="bi bi-pencil me-2 text-warning"></i>
                Chỉnh sửa: <span class="text-primary">{{ $customer->name }}</span>
            </h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary">
                <i class="bi bi-eye me-1"></i> Xem chi tiết
            </a>
            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <form action="{{ route('customers.update', $customer) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card mb-3">
                    <div class="card-header py-3 px-4">
                        <h6 class="mb-0 fw-bold">👤 Thông tin khách hàng</h6>
                    </div>
                    <div class="card-body px-4">
                        <div class="row g-3">
                            {{-- Mã KH không cho sửa --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Mã khách hàng</label>
                                <input type="text" class="form-control bg-light" value="{{ $customer->code }}" readonly>
                                <small class="text-muted">Mã KH không thể thay đổi</small>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-semibold">
                                    Họ tên <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $customer->name) }}">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Giới tính</label>
                                <select name="gender" class="form-select">
                                    <option value="">—</option>
                                    @foreach(['male' => 'Nam', 'female' => 'Nữ', 'other' => 'Khác'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ old('gender', $customer->gender) === $val ? 'selected' : '' }}>
                                            {{ $lbl }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control"
                                    value="{{ old('phone', $customer->phone) }}" placeholder="09xx xxx xxx">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Ngày sinh</label>
                                <input type="date" name="date_of_birth" class="form-control"
                                    value="{{ old('date_of_birth', $customer->date_of_birth?->format('Y-m-d')) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">CCCD / Passport</label>
                                <input type="text" name="id_card" class="form-control"
                                    value="{{ old('id_card', $customer->id_card) }}" placeholder="0xxxxxxxxx">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Hạn mức nợ</label>
                                <div class="input-group">
                                    <input type="number" name="debt_limit" class="form-control"
                                        value="{{ old('debt_limit', $customer->debt_limit) }}" min="0" step="100000">
                                    <span class="input-group-text">đ</span>
                                </div>
                                <small class="text-muted">0 = không giới hạn</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Địa chỉ</label>
                                <input type="text" name="address" class="form-control"
                                    value="{{ old('address', $customer->address) }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    Ghi chú y tế
                                    <span class="badge bg-warning text-dark ms-1">GPP</span>
                                </label>
                                <textarea name="medical_note" class="form-control" rows="3"
                                    placeholder="Dị ứng thuốc, bệnh nền, đang dùng thuốc gì...">{{ old('medical_note', $customer->medical_note) }}</textarea>
                                <small class="text-muted">
                                    <i class="bi bi-exclamation-circle me-1 text-warning"></i>
                                    Thông tin này sẽ hiển thị khi bán hàng để dược sĩ tư vấn đúng
                                </small>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Ghi chú khác</label>
                                <textarea name="note" class="form-control"
                                    rows="2">{{ old('note', $customer->note) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Thống kê hiện tại (readonly) --}}
                <div class="card mb-3">
                    <div class="card-header py-3 px-4">
                        <h6 class="mb-0 fw-bold">📊 Tình trạng hiện tại</h6>
                    </div>
                    <div class="card-body px-4 py-3">
                        <div class="row g-3 text-center">
                            <div class="col-4">
                                <div
                                    class="fw-bold fs-4 {{ $customer->current_debt > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($customer->current_debt, 0, ',', '.') }}đ
                                </div>
                                <small class="text-muted">Công nợ hiện tại</small>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold fs-4 text-primary">
                                    {{ $customer->invoices()->completed()->count() }}
                                </div>
                                <small class="text-muted">Tổng hóa đơn</small>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold fs-4 text-success" style="font-size:14px!important;">
                                    {{ number_format($customer->invoices()->completed()->sum('total_amount') / 1000, 0, ',', '.') }}k
                                    đ
                                </div>
                                <small class="text-muted">Tổng chi tiêu</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-warning flex-fill py-2 fw-semibold">
                        <i class="bi bi-check-lg me-2"></i>Lưu thay đổi
                    </button>
                    <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary flex-fill py-2">
                        <i class="bi bi-x-lg me-2"></i>Hủy bỏ
                    </a>
                </div>

            </form>
        </div>
    </div>

@endsection