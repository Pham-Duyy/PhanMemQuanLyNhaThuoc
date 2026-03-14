@extends('layouts.app')

@section('title', 'Sửa nhóm thuốc')
@section('page-title', 'Sửa nhóm thuốc')

@section('content')

    <div class="page-header">
        <h4><i class="bi bi-pencil-square me-2 text-primary"></i>Sửa nhóm: {{ $category->name }}</h4>
        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('categories.update', $category) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Tên nhóm thuốc <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $category->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mã nhóm</label>
                            <input type="text" name="code" class="form-control" value="{{ old('code', $category->code) }}"
                                style="text-transform:uppercase;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Thứ tự hiển thị</label>
                            <input type="number" name="sort_order" class="form-control"
                                value="{{ old('sort_order', $category->sort_order) }}" min="0">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Mô tả</label>
                            <textarea name="description" class="form-control"
                                rows="2">{{ old('description', $category->description) }}</textarea>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary px-4">Hủy</a>
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="bi bi-check-lg me-1"></i> Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection