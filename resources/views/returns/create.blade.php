@extends('layouts.app')
@section('title', 'Tạo phiếu trả hàng')
@section('page-title', 'Tạo phiếu trả hàng')

@section('content')

    <div class="page-header">
        <div>
            <h4 class="mb-0"><i class="bi bi-arrow-return-left me-2 text-warning"></i>Tạo phiếu trả hàng</h4>
            <small class="text-muted">Hoàn trả sản phẩm và hoàn tiền cho khách</small>
        </div>
        <a href="{{ route('returns.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Quay lại
        </a>
    </div>

    {{-- Bước 1: Tìm hóa đơn --}}
    @if(!$invoice)
        <div class="card mb-4">
            <div class="card-header py-3 px-4">
                <h6 class="mb-0 fw-bold">🔍 Bước 1: Tìm hóa đơn gốc cần trả</h6>
            </div>
            <div class="card-body px-4 py-3">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mã hóa đơn hoặc tên khách hàng</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="invoiceSearch" class="form-control"
                                placeholder="VD: HĐ-202403-001 hoặc Nguyễn Văn A..." autocomplete="off">
                        </div>
                        <div id="invoiceDropdown" class="dropdown-menu w-100"
                            style="display:none;max-height:300px;overflow-y:auto;"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Hoặc nhập trực tiếp mã HĐ</label>
                        <input type="hidden" name="invoice_id" id="selectedInvoiceId">
                        <input type="text" id="invoiceIdDirect" class="form-control" placeholder="ID hóa đơn">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-warning fw-semibold w-100"
                            onclick="document.querySelector('[name=invoice_id]').value = document.getElementById('invoiceIdDirect').value">
                            <i class="bi bi-search me-1"></i>Tìm hóa đơn
                        </button>
                    </div>
                </form>

                {{-- Hóa đơn gần đây --}}
                @if($recentInvoices->isNotEmpty())
                    <div class="mt-4">
                        <div class="fw-semibold small text-muted mb-2">📋 Hóa đơn gần đây:</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Mã HĐ</th>
                                        <th>Khách hàng</th>
                                        <th>Ngày</th>
                                        <th class="text-end">Tổng tiền</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentInvoices as $inv)
                                        <tr>
                                            <td><code class="text-primary">{{ $inv->code }}</code></td>
                                            <td>{{ $inv->customer?->name ?? 'Khách lẻ' }}</td>
                                            <td>{{ $inv->invoice_date->format('d/m/Y') }}</td>
                                            <td class="text-end">{{ number_format($inv->total_amount, 0, ',', '.') }}đ</td>
                                            <td>
                                                <a href="{{ route('returns.create', ['invoice_id' => $inv->id]) }}"
                                                    class="btn btn-sm btn-outline-warning">Chọn</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    @else

        {{-- Bước 2: Form trả hàng --}}
        <form method="POST" action="{{ route('returns.store') }}" id="returnForm">
            @csrf
            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

            <div class="row g-4">
                {{-- Cột trái: thông tin HĐ gốc + chọn SP trả --}}
                <div class="col-lg-8">

                    {{-- Thông tin HĐ gốc --}}
                    <div class="card mb-4" style="border-left:4px solid #2E86C1;">
                        <div class="card-body py-3 px-4">
                            <div class="row g-3 align-items-center">
                                <div class="col-sm-6">
                                    <div class="text-muted small">Hóa đơn gốc</div>
                                    <div class="fw-bold text-primary fs-5">{{ $invoice->code }}</div>
                                    <div class="text-muted small">
                                        {{ $invoice->invoice_date->format('d/m/Y') }} &nbsp;|&nbsp;
                                        {{ $invoice->customer?->name ?? 'Khách lẻ' }}
                                    </div>
                                </div>
                                <div class="col-sm-6 text-sm-end">
                                    <div class="text-muted small">Tổng giá trị HĐ</div>
                                    <div class="fw-bold fs-5">{{ number_format($invoice->total_amount, 0, ',', '.') }}đ</div>
                                    <a href="{{ route('returns.create') }}" class="text-muted small">Đổi hóa đơn</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Danh sách SP để chọn trả --}}
                    <div class="card">
                        <div class="card-header py-3 px-4">
                            <h6 class="mb-0 fw-bold">💊 Chọn sản phẩm cần trả</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:40px;"><input type="checkbox" id="checkAll" class="form-check-input">
                                        </th>
                                        <th>Sản phẩm</th>
                                        <th class="text-center">Đã mua</th>
                                        <th class="text-center">SL trả</th>
                                        <th class="text-end">Đơn giá</th>
                                        <th class="text-end">Hoàn tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->items as $i => $item)
                                        <tr class="item-row" data-price="{{ $item->unit_price }}" data-max="{{ $item->quantity }}">
                                            <td>
                                                <input type="checkbox" class="form-check-input item-check"
                                                    name="items[{{ $i }}][return]" value="1" onchange="toggleRow(this)">
                                                <input type="hidden" name="items[{{ $i }}][invoice_item_id]"
                                                    value="{{ $item->id }}">
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $item->medicine?->name }}</div>
                                                @if($item->batch)
                                                    <small class="text-muted">Lô: {{ $item->batch->batch_number }}
                                                        | HSD: {{ $item->batch->expiry_date->format('d/m/Y') }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center fw-semibold">
                                                {{ number_format($item->quantity) }} {{ $item->unit }}
                                            </td>
                                            <td class="text-center" style="width:120px;">
                                                <input type="number" name="items[{{ $i }}][quantity]"
                                                    class="form-control form-control-sm text-center qty-input"
                                                    value="{{ $item->quantity }}" min="1" max="{{ $item->quantity }}" disabled
                                                    oninput="updateTotal()">
                                            </td>
                                            <td class="text-end">{{ number_format($item->unit_price, 0, ',', '.') }}đ</td>
                                            <td class="text-end fw-bold item-total">0đ</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                {{-- Cột phải: thông tin trả --}}
                <div class="col-lg-4">
                    <div class="card sticky-top" style="top:80px;">
                        <div class="card-header py-3 px-4">
                            <h6 class="mb-0 fw-bold">📋 Thông tin phiếu trả</h6>
                        </div>
                        <div class="card-body px-4 py-3">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Ngày trả <span class="text-danger">*</span></label>
                                <input type="date" name="return_date" class="form-control"
                                    value="{{ today()->format('Y-m-d') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Lý do trả hàng <span class="text-danger">*</span></label>
                                <select name="reason" class="form-select" required onchange="toggleCustomReason(this)">
                                    <option value="">-- Chọn lý do --</option>
                                    <option value="Thuốc bị hỏng / biến chất">Thuốc bị hỏng / biến chất</option>
                                    <option value="Không đúng sản phẩm yêu cầu">Không đúng sản phẩm yêu cầu</option>
                                    <option value="Khách hàng không dùng nữa">Khách hàng không dùng nữa</option>
                                    <option value="Sản phẩm gần hết hạn">Sản phẩm gần hết hạn</option>
                                    <option value="Lỗi nhập sai sản phẩm">Lỗi nhập sai sản phẩm</option>
                                    <option value="other">Lý do khác...</option>
                                </select>
                                <input type="text" id="customReason" name="reason_custom" class="form-control mt-2 d-none"
                                    placeholder="Nhập lý do cụ thể...">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Hình thức hoàn tiền <span
                                        class="text-danger">*</span></label>
                                <select name="refund_method" class="form-select" required>
                                    <option value="cash">💵 Tiền mặt</option>
                                    <option value="account">🏦 Chuyển khoản</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Ghi chú</label>
                                <textarea name="note" class="form-control" rows="2" placeholder="Ghi chú thêm..."></textarea>
                            </div>

                            <hr>

                            {{-- Tổng hoàn tiền --}}
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted">Số SP trả:</span>
                                <span class="fw-bold" id="summaryItems">0 SP</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted fw-semibold">Tổng hoàn tiền:</span>
                                <span class="fw-bold fs-5 text-danger" id="summaryTotal">0đ</span>
                            </div>

                            @if($errors->any())
                                <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
                            @endif

                            <button type="submit" class="btn btn-warning fw-bold w-100"
                                onclick="return confirm('Xác nhận tạo phiếu trả hàng? Tồn kho sẽ được cộng lại.')">
                                <i class="bi bi-arrow-return-left me-1"></i>Xác nhận trả hàng
                            </button>

                            <div class="mt-2 p-2 rounded-2 small text-muted" style="background:#f8fafc;">
                                ⚠️ Sau khi tạo, tồn kho sẽ tự động được cộng lại cho từng lô tương ứng.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    @endif

@endsection

@push('scripts')
    <script>
        // Check all / uncheck all
        document.getElementById('checkAll')?.addEventListener('change', function () {
            document.querySelectorAll('.item-check').forEach(cb => {
                cb.checked = this.checked;
                toggleRow(cb);
            });
        });

        function toggleRow(cb) {
            const row = cb.closest('tr');
            const qtyInput = row.querySelector('.qty-input');
            qtyInput.disabled = !cb.checked;
            if (!cb.checked) {
                row.querySelector('.item-total').textContent = '0đ';
            }
            updateTotal();
        }

        function updateTotal() {
            let total = 0, items = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const cb = row.querySelector('.item-check');
                const qty = parseInt(row.querySelector('.qty-input').value) || 0;
                const price = parseFloat(row.dataset.price) || 0;
                if (cb.checked && qty > 0) {
                    const sub = qty * price;
                    total += sub;
                    items++;
                    row.querySelector('.item-total').textContent = formatMoney(sub) + 'đ';
                }
            });
            document.getElementById('summaryTotal').textContent = formatMoney(total) + 'đ';
            document.getElementById('summaryItems').textContent = items + ' SP';
        }

        function formatMoney(n) {
            return Math.round(n).toLocaleString('vi-VN');
        }

        function toggleCustomReason(sel) {
            const custom = document.getElementById('customReason');
            const isOther = sel.value === 'other';
            custom.classList.toggle('d-none', !isOther);
            custom.required = isOther;
            if (isOther) sel.name = '_reason_select'; // Disable select name, use custom
            else sel.name = 'reason';
        }
    </script>
@endpush