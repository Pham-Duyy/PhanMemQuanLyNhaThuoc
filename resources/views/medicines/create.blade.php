@extends('layouts.app')

@section('title', 'Thêm thuốc mới')
@section('page-title', 'Thêm thuốc mới')

@section('content')

<div class="page-header">
    <h4><i class="bi bi-plus-circle me-2 text-primary"></i>Thêm thuốc mới</h4>
    <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Quay lại
    </a>
</div>

<form action="{{ route('medicines.store') }}" method="POST">
@csrf

<div class="row g-3">

    {{-- ── CỘT TRÁI: Thông tin cơ bản ──────────────────────────────────── --}}
    <div class="col-lg-8">

        {{-- Thông tin định danh --}}
        <div class="card mb-3">
            <div class="card-header py-3 px-4">
                <h6 class="mb-0 fw-bold">📋 Thông tin định danh</h6>
            </div>
            <div class="card-body px-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Mã thuốc <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                               value="{{ old('code') }}" placeholder="VD: TH021"
                               style="text-transform:uppercase;">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mã vạch (Barcode)</label>
                        <input type="text" name="barcode" class="form-control"
                               value="{{ old('barcode') }}" placeholder="EAN-13">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Số đăng ký lưu hành</label>
                        <input type="text" name="registration_number" class="form-control"
                               value="{{ old('registration_number') }}" placeholder="VD-12345-21">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-semibold">
                            Tên thuốc (thương mại) <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="VD: Panadol Extra 500mg">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nhóm thuốc</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- Chọn nhóm --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Tên hoạt chất (generic name)</label>
                        <input type="text" name="generic_name" class="form-control"
                               value="{{ old('generic_name') }}"
                               placeholder="VD: Paracetamol 500mg + Caffeine 65mg">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nhà sản xuất</label>
                        <input type="text" name="manufacturer" class="form-control"
                               value="{{ old('manufacturer') }}" placeholder="VD: GlaxoSmithKline">
                    </div>
                </div>
            </div>
        </div>

        {{-- Đơn vị & Giá --}}
        <div class="card mb-3">
            <div class="card-header py-3 px-4">
                <h6 class="mb-0 fw-bold">💰 Đơn vị & Giá bán</h6>
            </div>
            <div class="card-body px-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            Đơn vị bán lẻ <span class="text-danger">*</span>
                        </label>
                        <select name="unit" class="form-select @error('unit') is-invalid @enderror">
                            <option value="">-- Chọn --</option>
                            @foreach(['Viên','Gói','Ống','Lọ','Chai','Tuýp','Vỉ','Hộp','Cái'] as $u)
                                <option value="{{ $u }}" {{ old('unit') === $u ? 'selected' : '' }}>
                                    {{ $u }}
                                </option>
                            @endforeach
                        </select>
                        @error('unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Đơn vị đóng gói</label>
                        <select name="package_unit" class="form-select">
                            <option value="">-- Chọn --</option>
                            @foreach(['Hộp','Lốc','Chai','Túi','Thùng'] as $u)
                                <option value="{{ $u }}" {{ old('package_unit') === $u ? 'selected' : '' }}>
                                    {{ $u }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Số lượng/gói</label>
                        <input type="number" name="units_per_package" class="form-control"
                               value="{{ old('units_per_package', 1) }}" min="1"
                               placeholder="VD: 24">
                        <small class="text-muted">1 hộp = ? viên</small>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            Tồn kho tối thiểu
                        </label>
                        <input type="number" name="min_stock" class="form-control"
                               value="{{ old('min_stock', 0) }}" min="0"
                               placeholder="Ngưỡng cảnh báo">
                        <small class="text-muted">Cảnh báo khi tồn ≤ mức này</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Giá bán lẻ (VNĐ) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" name="sell_price"
                                   class="form-control @error('sell_price') is-invalid @enderror"
                                   value="{{ old('sell_price') }}" min="0" step="500"
                                   placeholder="VD: 2500">
                            <span class="input-group-text">đ</span>
                        </div>
                        @error('sell_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Giá bán sỉ (VNĐ)</label>
                        <div class="input-group">
                            <input type="number" name="wholesale_price" class="form-control"
                                   value="{{ old('wholesale_price') }}" min="0" step="500"
                                   placeholder="Để trống nếu không có">
                            <span class="input-group-text">đ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mô tả --}}
        <div class="card mb-3">
            <div class="card-header py-3 px-4">
                <h6 class="mb-0 fw-bold">📝 Thông tin bổ sung</h6>
            </div>
            <div class="card-body px-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Công dụng / Mô tả</label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Công dụng, liều dùng, cách sử dụng...">{{ old('description') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Chống chỉ định</label>
                        <textarea name="contraindication" class="form-control" rows="2"
                                  placeholder="Các trường hợp không nên dùng...">{{ old('contraindication') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Hướng dẫn bảo quản</label>
                        <input type="text" name="storage_instruction" class="form-control"
                               value="{{ old('storage_instruction') }}"
                               placeholder="VD: Bảo quản nơi khô ráo, nhiệt độ dưới 30°C">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── CỘT PHẢI: Phân loại GPP ──────────────────────────────────────── --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header py-3 px-4">
                <h6 class="mb-0 fw-bold">⚕️ Phân loại GPP</h6>
            </div>
            <div class="card-body px-4">
                <p class="text-muted small mb-3">
                    Phân loại đúng theo quy định để kiểm soát bán hàng tự động.
                </p>

                {{-- Thuốc kê đơn --}}
                <div class="form-check form-switch mb-3 p-3 rounded"
                     style="background:#fff3cd; border: 1px solid #ffc107;">
                    <input class="form-check-input" type="checkbox" role="switch"
                           name="requires_prescription" id="requires_prescription"
                           value="1" {{ old('requires_prescription') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="requires_prescription">
                        <span class="badge bg-danger me-1">Kê đơn</span>
                        Thuốc kê đơn (Rx)
                    </label>
                    <div class="text-muted small mt-1">
                        Bắt buộc có đơn của bác sĩ khi bán
                    </div>
                </div>

                {{-- Kháng sinh --}}
                <div class="form-check form-switch mb-3 p-3 rounded"
                     style="background:#fff8e8; border: 1px solid #ffc107;">
                    <input class="form-check-input" type="checkbox" role="switch"
                           name="is_antibiotic" id="is_antibiotic"
                           value="1" {{ old('is_antibiotic') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="is_antibiotic">
                        <span class="badge bg-warning text-dark me-1">KS</span>
                        Kháng sinh
                    </label>
                    <div class="text-muted small mt-1">
                        Cần tư vấn dược sĩ khi bán
                    </div>
                </div>

                {{-- Gây nghiện --}}
                <div class="form-check form-switch mb-3 p-3 rounded"
                     style="background:#f8d7da; border: 1px solid #dc3545;">
                    <input class="form-check-input" type="checkbox" role="switch"
                           name="is_narcotic" id="is_narcotic"
                           value="1" {{ old('is_narcotic') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="is_narcotic">
                        <span class="badge bg-dark me-1">GN</span>
                        Thuốc gây nghiện
                    </label>
                    <div class="text-muted small mt-1">
                        Bắt buộc ghi CCCD người mua
                    </div>
                </div>

                <div class="alert alert-info py-2 px-3 small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Thuốc không thuộc các loại trên sẽ được xem là
                    <strong>OTC</strong> (không kê đơn).
                </div>
            </div>
        </div>

        {{-- Nút hành động --}}
        <div class="card">
            <div class="card-body px-4">
                <button type="submit" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-check-lg me-2"></i>Lưu thuốc mới
                </button>
                <a href="{{ route('medicines.index') }}"
                   class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x-lg me-2"></i>Hủy bỏ
                </a>
            </div>
        </div>
    </div>

</div>{{-- /row --}}
</form>

@endsection