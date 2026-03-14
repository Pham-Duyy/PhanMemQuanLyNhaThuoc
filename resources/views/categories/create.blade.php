@extends('layouts.app')

@section('title', 'Thêm nhóm thuốc')
@section('page-title', 'Thêm nhóm thuốc')

@section('content')

    <div class="page-header">
        <h4><i class="bi bi-plus-circle me-2 text-primary"></i>Thêm nhóm thuốc</h4>
        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('categories.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Tên nhóm thuốc <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" placeholder="VD: Kháng sinh, Vitamin, Tiêu hóa..." required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mã nhóm</label>
                            <input type="text" name="code" class="form-control" value="{{ old('code') }}"
                                placeholder="VD: KS, VTM, TH..." style="text-transform:uppercase;">
                            <small class="text-muted">Mã viết tắt, không bắt buộc</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Thứ tự hiển thị</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}"
                                min="0">
                            <small class="text-muted">Số nhỏ hơn hiển thị trước</small>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Mô tả</label>
                            <textarea name="description" class="form-control" rows="2"
                                placeholder="Mô tả nhóm thuốc...">{{ old('description') }}</textarea>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary px-4">Hủy</a>
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="bi bi-check-lg me-1"></i> Lưu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection