@extends('layouts.app')

@section('title', 'Nhóm thuốc')
@section('page-title', 'Nhóm thuốc')

@section('content')

{{-- ── HEADER ──────────────────────────────────────────────────────────── --}}
<div class="page-header">
    <div>
        <h4 class="mb-0"><i class="bi bi-tags me-2 text-primary"></i>Nhóm thuốc</h4>
        <small class="text-muted">Quản lý phân loại danh mục thuốc trong nhà thuốc</small>
    </div>
    @if(auth()->user()->hasPermission('medicine.create'))
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">
        <i class="bi bi-plus-lg me-1"></i> Thêm nhóm thuốc
    </button>
    @endif
</div>

{{-- ── STATS MINI ───────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f4fd;">
                <span style="font-size:22px;">🏷️</span>
            </div>
            <div>
                <div class="stat-value">{{ $categories->total() }}</div>
                <div class="stat-label">Tổng nhóm thuốc</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f8f0;">
                <span style="font-size:22px;">💊</span>
            </div>
            <div>
                <div class="stat-value text-success">{{ $categories->sum('medicines_count') }}</div>
                <div class="stat-label">Tổng loại thuốc</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3e8;">
                <span style="font-size:22px;">📂</span>
            </div>
            <div>
                <div class="stat-value" style="color:#D35400;">
                    {{ $categories->where('medicines_count', 0)->count() }}
                </div>
                <div class="stat-label">Nhóm chưa có thuốc</div>
            </div>
        </div>
    </div>
</div>

{{-- ── CARD GRID ─────────────────────────────────────────────────────────── --}}
<div class="row g-3">
    @forelse($categories as $cat)
    @php
        $palettes = [
            ['bg'=>'#e8f4fd','color'=>'#1B6FA8','icon'=>'💊'],
            ['bg'=>'#e8f8f0','color'=>'#1E8449','icon'=>'🌿'],
            ['bg'=>'#fef3e8','color'=>'#D35400','icon'=>'🔥'],
            ['bg'=>'#f3e8fd','color'=>'#7D3C98','icon'=>'💉'],
            ['bg'=>'#fdf0f0','color'=>'#C0392B','icon'=>'❤️'],
            ['bg'=>'#e8f4e8','color'=>'#27AE60','icon'=>'🌱'],
            ['bg'=>'#fff8e1','color'=>'#F39C12','icon'=>'⭐'],
            ['bg'=>'#e8f0fe','color'=>'#2980B9','icon'=>'🩺'],
            ['bg'=>'#fce4ec','color'=>'#C2185B','icon'=>'🫀'],
            ['bg'=>'#e0f2f1','color'=>'#00796B','icon'=>'🧪'],
        ];
        $p = $palettes[$loop->index % count($palettes)];
    @endphp

    <div class="col-sm-6 col-xl-4">
        <div class="card h-100" style="border-left:4px solid {{ $p['color'] }};">
            <div class="card-body px-4 py-3">

                {{-- Card header: icon + tên + mã --}}
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:52px;height:52px;background:{{ $p['bg'] }};font-size:26px;">
                        {{ $p['icon'] }}
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="fw-bold fs-6" style="color:{{ $p['color'] }}">
                                {{ $cat->name }}
                            </span>
                            @if($cat->code)
                            <span class="badge rounded-pill"
                                  style="background:{{ $p['bg'] }};color:{{ $p['color'] }};
                                         border:1px solid {{ $p['color'] }}50;font-size:10px;">
                                {{ $cat->code }}
                            </span>
                            @endif
                        </div>
                        <p class="text-muted small mb-0 mt-1"
                           style="display:-webkit-box;-webkit-line-clamp:2;
                                  -webkit-box-orient:vertical;overflow:hidden;min-height:18px;">
                            {{ $cat->description ?: 'Chưa có mô tả' }}
                        </p>
                    </div>
                </div>

                {{-- Stats bar --}}
                <div class="d-flex align-items-center gap-2 rounded-3 px-3 py-2 mb-3"
                     style="background:{{ $p['bg'] }};">
                    <i class="bi bi-capsule" style="color:{{ $p['color'] }};font-size:15px;"></i>
                    <span class="fw-bold" style="color:{{ $p['color'] }}">
                        {{ $cat->medicines_count }}
                    </span>
                    <span class="text-muted small">loại thuốc</span>
                    <span class="ms-auto">
                        @if($cat->medicines_count > 0)
                            <span class="badge bg-success">Đang dùng</span>
                        @else
                            <span class="badge bg-light text-muted border">Trống</span>
                        @endif
                    </span>
                </div>

                {{-- ── NÚT THAO TÁC — rõ ràng, có text label ────────────── --}}
                <div class="d-flex gap-2">

                    {{-- Xem thuốc --}}
                    <a href="{{ route('medicines.index', ['category_id' => $cat->id]) }}"
                       class="btn btn-sm flex-fill fw-semibold"
                       style="background:{{ $p['bg'] }};color:{{ $p['color'] }};
                              border:1px solid {{ $p['color'] }}40;">
                        <i class="bi bi-eye me-1"></i>Xem thuốc
                    </a>

                    @if(auth()->user()->hasPermission('medicine.edit'))

                    {{-- Sửa --}}
                    <button type="button"
                            class="btn btn-sm btn-warning fw-semibold"
                            style="min-width:80px;"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEdit{{ $cat->id }}">
                        <i class="bi bi-pencil me-1"></i>Sửa
                    </button>

                    {{-- Xóa --}}
                    @if($cat->medicines_count == 0)
                    <form action="{{ route('categories.destroy', $cat) }}" method="POST"
                          onsubmit="return confirm('Xóa nhóm \'{{ addslashes($cat->name) }}\'?\nHành động này không thể hoàn tác.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger fw-semibold"
                                style="min-width:70px;">
                            <i class="bi bi-trash me-1"></i>Xóa
                        </button>
                    </form>
                    @else
                    <button class="btn btn-sm btn-outline-secondary fw-semibold"
                            style="min-width:70px;opacity:.45;cursor:not-allowed;"
                            disabled
                            title="Không thể xóa — nhóm đang có {{ $cat->medicines_count }} thuốc">
                        <i class="bi bi-lock me-1"></i>Xóa
                    </button>
                    @endif

                    @endif
                </div>

            </div>{{-- /card-body --}}
        </div>{{-- /card --}}

        {{-- ── MODAL CHỈNH SỬA ─────────────────────────────────────────── --}}
        <div class="modal fade" id="modalEdit{{ $cat->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('categories.update', $cat) }}" method="POST">
                        @csrf @method('PUT')

                        <div class="modal-header py-3"
                             style="background:{{ $p['bg'] }};border-bottom:2px solid {{ $p['color'] }}30;">
                            <h5 class="modal-title fw-bold" style="color:{{ $p['color'] }}">
                                {{ $p['icon'] }} Chỉnh sửa nhóm thuốc
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body px-4 py-4">
                            <div class="row g-3">
                                <div class="col-8">
                                    <label class="form-label fw-semibold">
                                        Tên nhóm <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="name" class="form-control"
                                           value="{{ $cat->name }}" required>
                                </div>
                                <div class="col-4">
                                    <label class="form-label fw-semibold">Mã nhóm</label>
                                    <input type="text" name="code" class="form-control"
                                           value="{{ $cat->code }}" maxlength="20"
                                           style="text-transform:uppercase;"
                                           placeholder="VD: KS">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Mô tả</label>
                                    <textarea name="description" class="form-control" rows="2"
                                              placeholder="Mô tả ngắn về nhóm thuốc...">{{ $cat->description }}</textarea>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Thứ tự hiển thị</label>
                                    <input type="number" name="sort_order" class="form-control"
                                           value="{{ $cat->sort_order }}" min="0">
                                    <small class="text-muted">Số nhỏ hiển thị trước</small>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Số thuốc trong nhóm</label>
                                    <div class="form-control bg-light fw-bold"
                                         style="color:{{ $p['color'] }}">
                                        {{ $p['icon'] }} {{ $cat->medicines_count }} loại thuốc
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer py-3">
                            <button type="button" class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">
                                <i class="bi bi-x-lg me-1"></i>Hủy bỏ
                            </button>
                            <button type="submit" class="btn btn-warning fw-semibold">
                                <i class="bi bi-check-lg me-1"></i>Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>{{-- /col --}}
    @empty

    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <div style="font-size:72px;opacity:.15;">🏷️</div>
                <h5 class="text-muted mt-3 mb-1">Chưa có nhóm thuốc nào</h5>
                <p class="text-muted small">Tạo nhóm để phân loại danh mục thuốc trong hệ thống.</p>
                <button class="btn btn-primary mt-1"
                        data-bs-toggle="modal" data-bs-target="#modalCreate">
                    <i class="bi bi-plus-lg me-1"></i> Thêm nhóm đầu tiên
                </button>
            </div>
        </div>
    </div>

    @endforelse
</div>

@if($categories->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $categories->links('pagination::bootstrap-5') }}
</div>
@endif

{{-- ── MODAL THÊM MỚI ──────────────────────────────────────────────────── --}}
<div class="modal fade" id="modalCreate" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="modal-header py-3"
                     style="background:#e8f4fd;border-bottom:2px solid #1B6FA830;">
                    <h5 class="modal-title fw-bold text-primary">
                        ➕ Thêm nhóm thuốc mới
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <div class="row g-3">
                        <div class="col-8">
                            <label class="form-label fw-semibold">
                                Tên nhóm <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required
                                   placeholder="VD: Kháng sinh, Giảm đau...">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">Mã nhóm</label>
                            <input type="text" name="code" class="form-control"
                                   value="{{ old('code') }}" maxlength="20"
                                   placeholder="KS" style="text-transform:uppercase;">
                            <small class="text-muted">Viết tắt</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Mô tả nhóm</label>
                            <textarea name="description" class="form-control" rows="2"
                                      placeholder="Mô tả ngắn — hiển thị trực tiếp trên card...">{{ old('description') }}</textarea>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Thứ tự hiển thị</label>
                            <input type="number" name="sort_order" class="form-control"
                                   value="{{ old('sort_order', 0) }}" min="0">
                            <small class="text-muted">Số nhỏ hiển thị trước</small>
                        </div>
                        <div class="col-6 d-flex align-items-end pb-1">
                            <div class="alert alert-info py-2 px-3 small mb-0 w-100">
                                <i class="bi bi-palette me-1"></i>
                                Icon & màu gán <strong>tự động</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-3">
                    <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Hủy bỏ
                    </button>
                    <button type="submit" class="btn btn-primary fw-semibold">
                        <i class="bi bi-plus-lg me-1"></i>Thêm nhóm thuốc
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', () => {
        new bootstrap.Modal(document.getElementById('modalCreate')).show();
    });
</script>
@endif

@endsection