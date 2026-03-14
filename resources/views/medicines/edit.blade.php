@extends('layouts.app')

@section('title', 'Chỉnh sửa: ' . $medicine->name)
@section('page-title', 'Chỉnh sửa thuốc')

@section('content')

<div class="page-header">
    <h4>
        <i class="bi bi-pencil me-2 text-warning"></i>
        Chỉnh sửa: <span class="text-primary">{{ $medicine->name }}</span>
    </h4>
    <div class="d-flex gap-2">
        <a href="{{ route('medicines.show', $medicine) }}" class="btn btn-outline-secondary">
            <i class="bi bi-eye me-1"></i> Xem chi tiết
        </a>
        <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại
        </a>
    </div>
</div>

<form action="{{ route('medicines.update', $medicine) }}" method="POST">
@csrf
@method('PUT')

<div class="row g-3">
    <div class="col-lg-8">

        {{-- Thông tin định danh --}}
        <div class="card mb-3">
            <div class="card-header py-3 px-4">
                <h6 class="mb-0 fw-bold">📋 Thông tin định danh</h6>
            </div>
            <div class="card-body px-4">
                <div class="row g-3">
                    {{-- Mã thuốc không cho sửa --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mã thuốc</label>
                        <input type="text" class="form-control bg-light"
                               value="{{ $medicine->code }}" readonly>
                        <small class="text-muted">Mã thuốc không thể thay đổi</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mã vạch (Barcode)</label>
                        <input type="text" name="barcode" class="form-control"
                               value="{{ old('barcode', $medicine->barcode) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Số đăng ký lưu hành</label>
                        <input type="text" name="registration_number" class="form-control"
                               value="{{ old('registration_number', $medicine->registration_number) }}">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-semibold">
                            Tên thuốc <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $medicine->name) }}">
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
                                    {{ old('category_id', $medicine->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Tên hoạt chất</label>
                        <input type="text" name="generic_name" class="form-control"
                               value="{{ old('generic_name', $medicine->generic_name) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nhà sản xuất</label>
                        <input type="text" name="manufacturer" class="form-control"
                               value="{{ old('manufacturer', $medicine->manufacturer) }}">
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
                        <select name="unit" class="form-select">
                            @foreach(['Viên','Gói','Ống','Lọ','Chai','Tuýp','Vỉ','Hộp','Cái'] as $u)
                                <option value="{{ $u }}"
                                    {{ old('unit', $medicine->unit) === $u ? 'selected' : '' }}>
                                    {{ $u }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Đơn vị đóng gói</label>
                        <select name="package_unit" class="form-select">
                            <option value="">-- Chọn --</option>
                            @foreach(['Hộp','Lốc','Chai','Túi','Thùng'] as $u)
                                <option value="{{ $u }}"
                                    {{ old('package_unit', $medicine->package_unit) === $u ? 'selected' : '' }}>
                                    {{ $u }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Số lượng/gói</label>
                        <input type="number" name="units_per_package" class="form-control"
                               value="{{ old('units_per_package', $medicine->units_per_package) }}" min="1">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tồn kho tối thiểu</label>
                        <input type="number" name="min_stock" class="form-control"
                               value="{{ old('min_stock', $medicine->min_stock) }}" min="0">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Giá bán lẻ (VNĐ) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" name="sell_price"
                                   class="form-control @error('sell_price') is-invalid @enderror"
                                   value="{{ old('sell_price', $medicine->sell_price) }}"
                                   min="0" step="500">
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
                                   value="{{ old('wholesale_price', $medicine->wholesale_price) }}"
                                   min="0" step="500">
                            <span class="input-group-text">đ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mô tả --}}
        <div class="card">
            <div class="card-header py-3 px-4">
                <h6 class="mb-0 fw-bold">📝 Thông tin bổ sung</h6>
            </div>
            <div class="card-body px-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Công dụng / Mô tả</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $medicine->description) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Chống chỉ định</label>
                        <textarea name="contraindication" class="form-control" rows="2">{{ old('contraindication', $medicine->contraindication) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Hướng dẫn bảo quản</label>
                        <input type="text" name="storage_instruction" class="form-control"
                               value="{{ old('storage_instruction', $medicine->storage_instruction) }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cột phải --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header py-3 px-4">
                <h6 class="mb-0 fw-bold">⚕️ Phân loại GPP</h6>
            </div>
            <div class="card-body px-4">
                <div class="form-check form-switch mb-3 p-3 rounded"
                     style="background:#fff3cd; border:1px solid #ffc107;">
                    <input class="form-check-input" type="checkbox" role="switch"
                           name="requires_prescription" id="requires_prescription" value="1"
                           {{ old('requires_prescription', $medicine->requires_prescription) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="requires_prescription">
                        <span class="badge bg-danger me-1">Kê đơn</span> Thuốc kê đơn (Rx)
                    </label>
                </div>

                <div class="form-check form-switch mb-3 p-3 rounded"
                     style="background:#fff8e8; border:1px solid #ffc107;">
                    <input class="form-check-input" type="checkbox" role="switch"
                           name="is_antibiotic" id="is_antibiotic" value="1"
                           {{ old('is_antibiotic', $medicine->is_antibiotic) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="is_antibiotic">
                        <span class="badge bg-warning text-dark me-1">KS</span> Kháng sinh
                    </label>
                </div>

                <div class="form-check form-switch mb-3 p-3 rounded"
                     style="background:#f8d7da; border:1px solid #dc3545;">
                    <input class="form-check-input" type="checkbox" role="switch"
                           name="is_narcotic" id="is_narcotic" value="1"
                           {{ old('is_narcotic', $medicine->is_narcotic) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="is_narcotic">
                        <span class="badge bg-dark me-1">GN</span> Thuốc gây nghiện
                    </label>
                </div>
            </div>
        </div>

        {{-- Thông tin hiện tại --}}
        <div class="card mb-3">
            <div class="card-header py-3 px-4">
                <h6 class="mb-0 fw-bold">📊 Tình trạng hiện tại</h6>
            </div>
            <div class="card-body px-4">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Tồn kho</span>
                    <strong class="{{ $medicine->total_stock == 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($medicine->total_stock) }} {{ $medicine->unit }}
                    </strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Hạn gần nhất</span>
                    <strong>{{ $medicine->nearest_expiry_date ?? '—' }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Số lô đang có</span>
                    <strong>{{ $medicine->batches->where('is_active', true)->count() }} lô</strong>
                </div>
            </div>
        </div>

        {{-- Nút hành động --}}
        <div class="card">
            <div class="card-body px-4">
                <button type="submit" class="btn btn-warning w-100 mb-2">
                    <i class="bi bi-check-lg me-2"></i>Lưu thay đổi
                </button>
                <a href="{{ route('medicines.show', $medicine) }}"
                   class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x-lg me-2"></i>Hủy bỏ
                </a>
            </div>
        </div>
    </div>
</div>

</form>
@endsection