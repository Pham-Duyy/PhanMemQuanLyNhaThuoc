@extends('layouts.app')
@section('title', 'Chỉnh sửa: ' . $supplier->name)
@section('page-title', 'Chỉnh sửa nhà cung cấp')

@section('content')

    <div class="page-header">
        <h4><i class="bi bi-pencil me-2 text-warning"></i>Chỉnh sửa: {{ $supplier->name }}</h4>
        <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="row g-3 justify-content-center">
        <div class="col-lg-8">
            <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
                @csrf @method('PUT')

                <div class="card mb-3">
                    <div class="card-header py-3 px-4">
                        <h6 class="mb-0 fw-bold">🏭 Thông tin nhà cung cấp</h6>
                    </div>
                    <div class="card-body px-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Mã NCC</label>
                                <input type="text" class="form-control bg-light" value="{{ $supplier->code }}" readonly>
                                <small class="text-muted">Mã không thể thay đổi</small>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Tên nhà cung cấp <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $supplier->name) }}">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Mã số thuế</label>
                                <input type="text" name="tax_code" class="form-control"
                                    value="{{ old('tax_code', $supplier->tax_code) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control"
                                    value="{{ old('phone', $supplier->phone) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control"
                                    value="{{ old('email', $supplier->email) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Địa chỉ</label>
                                <input type="text" name="address" class="form-control"
                                    value="{{ old('address', $supplier->address) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Người liên hệ</label>
                                <input type="text" name="contact_person" class="form-control"
                                    value="{{ old('contact_person', $supplier->contact_person) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">SĐT người liên hệ</label>
                                <input type="text" name="contact_phone" class="form-control"
                                    value="{{ old('contact_phone', $supplier->contact_phone) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header py-3 px-4">
                        <h6 class="mb-0 fw-bold">💰 Điều khoản thanh toán</h6>
                    </div>
                    <div class="card-body px-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Thời hạn thanh toán</label>
                                <div class="input-group">
                                    <input type="number" name="payment_term_days" class="form-control"
                                        value="{{ old('payment_term_days', $supplier->payment_term_days) }}" min="0">
                                    <span class="input-group-text">ngày</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Hạn mức nợ tối đa</label>
                                <div class="input-group">
                                    <input type="number" name="debt_limit" class="form-control"
                                        value="{{ old('debt_limit', $supplier->debt_limit) }}" min="0" step="1000000">
                                    <span class="input-group-text">đ</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Nợ hiện tại</label>
                                <div
                                    class="form-control bg-light fw-bold {{ $supplier->current_debt > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($supplier->current_debt, 0, ',', '.')}}đ
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Ghi chú</label>
                                <textarea name="note" class="form-control"
                                    rows="2">{{ old('note', $supplier->note) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-warning flex-fill py-2 fw-semibold">
                        <i class="bi bi-check-lg me-2"></i>Lưu thay đổi
                    </button>
                    <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-outline-secondary flex-fill py-2">
                        <i class="bi bi-x-lg me-2"></i>Hủy bỏ
                    </a>
                </div>

            </form>
        </div>
    </div>
@endsection