@extends('layouts.app')
@section('title','Thêm nhà cung cấp')
@section('page-title','Thêm nhà cung cấp')

@section('content')

<div class="page-header">
    <h4><i class="bi bi-plus-circle me-2 text-primary"></i>Thêm nhà cung cấp</h4>
    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Quay lại
    </a>
</div>

<div class="row g-3 justify-content-center">
<div class="col-lg-8">
<form action="{{ route('suppliers.store') }}" method="POST">
@csrf

    <div class="card mb-3">
        <div class="card-header py-3 px-4"><h6 class="mb-0 fw-bold">🏭 Thông tin nhà cung cấp</h6></div>
        <div class="card-body px-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Mã NCC <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                           value="{{ old('code') }}" placeholder="VD: NCC001" style="text-transform:uppercase;">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Tên nhà cung cấp <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" placeholder="VD: Công ty Dược phẩm ABC">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Mã số thuế</label>
                    <input type="text" name="tax_code" class="form-control"
                           value="{{ old('tax_code') }}" placeholder="0123456789">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control"
                           value="{{ old('phone') }}" placeholder="028 xxxx xxxx">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="{{ old('email') }}" placeholder="contact@company.com">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Địa chỉ</label>
                    <input type="text" name="address" class="form-control"
                           value="{{ old('address') }}" placeholder="Địa chỉ đầy đủ">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Người liên hệ</label>
                    <input type="text" name="contact_person" class="form-control"
                           value="{{ old('contact_person') }}" placeholder="Họ tên người liên hệ">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">SĐT người liên hệ</label>
                    <input type="text" name="contact_phone" class="form-control"
                           value="{{ old('contact_phone') }}" placeholder="090x xxx xxx">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header py-3 px-4"><h6 class="mb-0 fw-bold">💰 Điều khoản thanh toán</h6></div>
        <div class="card-body px-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Thời hạn thanh toán</label>
                    <div class="input-group">
                        <input type="number" name="payment_term_days" class="form-control"
                               value="{{ old('payment_term_days', 30) }}" min="0">
                        <span class="input-group-text">ngày</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Hạn mức nợ tối đa</label>
                    <div class="input-group">
                        <input type="number" name="debt_limit" class="form-control"
                               value="{{ old('debt_limit', 0) }}" min="0" step="1000000">
                        <span class="input-group-text">đ</span>
                    </div>
                    <small class="text-muted">0 = không giới hạn</small>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Ghi chú</label>
                    <textarea name="note" class="form-control" rows="2"
                              placeholder="Ghi chú về điều khoản hợp đồng, ưu đãi...">{{ old('note') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-3">
        <button type="submit" class="btn btn-primary flex-fill py-2 fw-semibold">
            <i class="bi bi-check-lg me-2"></i>Lưu nhà cung cấp
        </button>
        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary flex-fill py-2">
            <i class="bi bi-x-lg me-2"></i>Hủy bỏ
        </a>
    </div>

</form>
</div>
</div>
@endsection