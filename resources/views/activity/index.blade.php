@extends('layouts.app')
@section('title', 'Lịch sử hoạt động')
@section('page-title', 'Lịch sử hoạt động')

@section('content')

    <div class="page-header">
        <div>
            <h4 class="mb-0"><i class="bi bi-clock-history me-2 text-info"></i>Lịch sử hoạt động</h4>
            <small class="text-muted">Audit log — Theo dõi mọi thao tác trong hệ thống theo chuẩn GPP</small>
        </div>
    </div>

    {{-- Stats hôm nay --}}
    <div class="row g-3 mb-4">
        @php
            $statDefs = [
                ['create', 'Tạo mới', 'success', '🟢'],
                ['update', 'Cập nhật', 'warning', '🟡'],
                ['delete', 'Xóa', 'danger', '🔴'],
                ['login', 'Đăng nhập', 'info', '🔵'],
            ];
        @endphp
        @foreach($statDefs as [$action, $label, $color, $icon])
            <div class="col-sm-3">
                <div class="card h-100" style="border-left:4px solid var(--bs-{{ $color }});">
                    <div class="card-body py-3 px-4">
                        <div class="text-muted small text-uppercase fw-semibold mb-1">{{ $icon }} {{ $label }} hôm nay</div>
                        <div class="fw-bold fs-3">{{ $todayStats[$action] ?? 0 }}</div>
                        <div class="text-muted small">thao tác</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Bộ lọc --}}
    <div class="card mb-4">
        <div class="card-body px-4 py-3">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Tìm kiếm</label>
                    <input type="text" name="q" class="form-control form-control-sm" value="{{ request('q') }}"
                        placeholder="Mô tả hoạt động...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Loại hành động</label>
                    <select name="action" class="form-select form-select-sm">
                        <option value="">Tất cả</option>
                        @foreach($actions as $a)
                            <option value="{{ $a }}" {{ request('action') == $a ? 'selected' : '' }}>
                                {{ ucfirst($a) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Module</label>
                    <select name="module" class="form-select form-select-sm">
                        <option value="">Tất cả</option>
                        @foreach($modules as $m)
                            <option value="{{ $m }}" {{ request('module') == $m ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $m)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Người dùng</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">Tất cả</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-semibold mb-1">Từ ngày</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-semibold mb-1">Đến</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="{{ route('activity.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Timeline log --}}
    <div class="card">
        <div class="card-header py-3 px-4 d-flex justify-content-between">
            <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2"></i>{{ $logs->total() }} bản ghi</h6>
            <small class="text-muted">Trang {{ $logs->currentPage() }}/{{ $logs->lastPage() }}</small>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0 table-sm">
                <thead class="table-light">
                    <tr>
                        <th style="width:160px;">Thời gian</th>
                        <th style="width:140px;">Người dùng</th>
                        <th style="width:100px;">Hành động</th>
                        <th style="width:120px;">Module</th>
                        <th>Mô tả</th>
                        <th style="width:120px;">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="text-muted small text-nowrap">
                                {{ $log->created_at->format('H:i:s d/m/Y') }}
                                <div class="text-muted" style="font-size:10px;">
                                    {{ $log->created_at->diffForHumans() }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold small">{{ $log->user?->name ?? '—' }}</div>
                                <small class="text-muted">{{ $log->user?->roles->first()?->name ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $log->action_color }}">
                                    {{ $log->action_icon }} {{ $log->action_label }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border small">
                                    {{ ucwords(str_replace('_', ' ', $log->module)) }}
                                </span>
                            </td>
                            <td class="small">
                                {{ $log->description }}
                                @if($log->record_id)
                                    <span class="text-muted">#{{ $log->record_id }}</span>
                                @endif
                                @if($log->changes)
                                    <button class="btn btn-link btn-sm p-0 ms-1" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#changes-{{ $log->id }}">
                                        <i class="bi bi-chevron-down" style="font-size:10px;"></i>
                                    </button>
                                    <div class="collapse" id="changes-{{ $log->id }}">
                                        <pre class="mt-1 p-2 rounded small"
                                            style="background:#f8f9fa;font-size:11px;max-height:100px;overflow:auto;">{{ json_encode($log->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                @endif
                            </td>
                            <td class="text-muted small font-monospace">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-clock-history fs-2 d-block mb-2"></i>
                                Chưa có hoạt động nào được ghi lại.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="card-footer py-3 px-4">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@endsection