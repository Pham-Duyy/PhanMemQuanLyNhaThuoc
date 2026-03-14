@extends('layouts.app')
@section('title', 'Thêm khách hàng')
@section('page-title', 'Thêm khách hàng')

@section('content')
    <div class="page-header">
        <h4><i class="bi bi-plus-circle me-2 text-primary"></i>Thêm khách hàng</h4>
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại
        </a>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <form action="{{ route('customers.store') }}" method="POST">
                @csrf
                <div class="card mb-3">
                    <div class="card-header py-3 px-4">
                        <h6 class="mb-0 fw-bold">👤 Thông tin khách hàng</h6>
                    </div>
                    <div class="card-body px-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Họ tên <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="Nguyễn Văn A">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Giới tính</label>
                                <select name="gender" class="form-select">
                                    <option value="">—</option>
                                    <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Nam</option>
                                    <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Nữ</option>
                                    <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Khác</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}"
                                    placeholder="09xx xxx xxx">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ngày sinh</label>
                                <input type="date" name="date_of_birth" class="form-control"
                                    value="{{ old('date_of_birth') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">CCCD / Passport</label>
                                <input type="text" name="id_card" class="form-control" value="{{ old('id_card') }}"
                                    placeholder="0xxxxxxxxx">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Hạn mức nợ</label>
                                <div class="input-group">
                                    <input type="number" name="debt_limit" class="form-control"
                                        value="{{ old('debt_limit', 0) }}" min="0" step="100000">
                                    <span class="input-group-text">đ</span>
                                </div>
                                <small class="text-muted">0 = không giới hạn</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Địa chỉ</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address') }}"
                                    placeholder="Địa chỉ">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    Ghi chú y tế
                                    <span class="badge bg-warning text-dark ms-1">GPP</span>
                                </label>
                                <textarea name="medical_note" class="form-control" rows="2"
                                    placeholder="Dị ứng thuốc, bệnh nền, đang dùng thuốc gì... (quan trọng!)">{{ old('medical_note') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Ghi chú khác</label>
                                <textarea name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary flex-fill py-2 fw-semibold">
                        <i class="bi bi-check-lg me-2"></i>Lưu khách hàng
                    </button>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary flex-fill py-2">
                        <i class="bi bi-x-lg me-2"></i>Hủy bỏ
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection