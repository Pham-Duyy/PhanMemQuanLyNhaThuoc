<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Supplier;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    // ── Báo cáo doanh thu ─────────────────────────────────────────────────────
    public function revenue(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        // Export CSV
        if ($request->get('export')) {
            return $this->exportRevenue($request);
        }

        // Doanh thu theo ngày
        $dailyRevenue = Invoice::selectRaw('
                DATE(invoice_date)      as date,
                COUNT(*)                as invoice_count,
                SUM(total_amount)       as revenue,
                SUM(paid_amount)        as collected,
                SUM(debt_amount)        as debt
            ')
            ->completed()
            ->where('pharmacy_id', auth()->user()->pharmacy_id)
            ->whereBetween(DB::raw('DATE(invoice_date)'), [$from, $to])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Tổng kỳ
        $summary = [
            'revenue' => $dailyRevenue->sum('revenue'),
            'collected' => $dailyRevenue->sum('collected'),
            'debt' => $dailyRevenue->sum('debt'),
            'invoice_count' => $dailyRevenue->sum('invoice_count'),
        ];

        // Lãi gộp = doanh thu - giá vốn
        $grossProfit = InvoiceItem::whereHas(
            'invoice',
            fn($q) =>
            $q->completed()
                ->where('pharmacy_id', auth()->user()->pharmacy_id)
                ->whereBetween(DB::raw('DATE(invoice_date)'), [$from, $to])
        )
            ->selectRaw('SUM((sell_price - purchase_price) * quantity) as profit')
            ->value('profit') ?? 0;

        // Doanh thu theo phương thức thanh toán
        $byPayment = Invoice::selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
            ->completed()
            ->where('pharmacy_id', auth()->user()->pharmacy_id)
            ->whereBetween(DB::raw('DATE(invoice_date)'), [$from, $to])
            ->groupBy('payment_method')
            ->get();

        // Top thuốc bán chạy kỳ này
        $topMedicines = InvoiceItem::selectRaw('
                medicine_id,
                SUM(quantity)     as total_qty,
                SUM(total_amount) as total_revenue,
                SUM((sell_price - purchase_price) * quantity) as gross_profit
            ')
            ->whereHas(
                'invoice',
                fn($q) =>
                $q->completed()
                    ->where('pharmacy_id', auth()->user()->pharmacy_id)
                    ->whereBetween(DB::raw('DATE(invoice_date)'), [$from, $to])
            )
            ->with('medicine:id,name,unit')
            ->groupBy('medicine_id')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        return view('reports.revenue', compact(
            'dailyRevenue',
            'summary',
            'grossProfit',
            'byPayment',
            'topMedicines',
            'from',
            'to'
        ));
    }

    // ── Báo cáo NXT (Nhập - Xuất - Tồn) ─────────────────────────────────────
    public function inventory(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        // Export CSV
        if ($request->get('export')) {
            return $this->exportInventory($request);
        }

        $pharmacyId = auth()->user()->pharmacy_id;

        // Lấy tất cả thuốc có phát sinh trong kỳ
        $medicines = Medicine::with(['batches'])
            ->where('pharmacy_id', $pharmacyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($m) use ($from, $to) {
                // Nhập trong kỳ
                $imported = \App\Models\PurchaseOrderItem::whereHas(
                    'purchaseOrder',
                    fn($q) =>
                    $q->where('pharmacy_id', auth()->user()->pharmacy_id)
                        ->where('status', 'received')
                        ->whereBetween(DB::raw('DATE(received_date)'), [$from, $to])
                )
                    ->where('medicine_id', $m->id)
                    ->sum('received_quantity');

                // Xuất trong kỳ (bán)
                $exported = InvoiceItem::whereHas(
                    'invoice',
                    fn($q) =>
                    $q->completed()
                        ->where('pharmacy_id', auth()->user()->pharmacy_id)
                        ->whereBetween(DB::raw('DATE(invoice_date)'), [$from, $to])
                )
                    ->where('medicine_id', $m->id)
                    ->sum('quantity');

                // Tồn hiện tại
                $currentStock = $m->total_stock;

                // Tồn đầu kỳ = tồn hiện tại - nhập + xuất
                $openingStock = $currentStock - $imported + $exported;

                return [
                    'medicine' => $m,
                    'opening_stock' => max(0, $openingStock),
                    'imported' => $imported,
                    'exported' => $exported,
                    'closing_stock' => $currentStock,
                    'unit' => $m->unit,
                ];
            })
            ->filter(
                fn($row) =>
                $row['imported'] > 0 || $row['exported'] > 0 || $row['closing_stock'] > 0
            );

        return view('reports.inventory', compact('medicines', 'from', 'to'));
    }

    // ── Báo cáo xuất nhập tồn chi tiết theo từng ngày (Ledger) ──────────────
    public function ledger(Request $request)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $medicineId = $request->get('medicine_id');
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $medicines = Medicine::where('pharmacy_id', $pharmacyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'unit']);

        $ledger = collect();
        $medicine = null;
        $openingStock = 0;

        if ($medicineId) {
            $medicine = Medicine::where('pharmacy_id', $pharmacyId)->findOrFail($medicineId);

            // Tồn đầu kỳ — tính từ trước $from
            $importedBefore = \App\Models\PurchaseOrderItem::whereHas(
                'purchaseOrder',
                fn($q) =>
                $q->where('pharmacy_id', $pharmacyId)
                    ->where('status', 'received')
                    ->where(DB::raw('DATE(received_date)'), '<', $from)
            )->where('medicine_id', $medicineId)->sum('received_quantity');

            $exportedBefore = InvoiceItem::whereHas(
                'invoice',
                fn($q) =>
                $q->completed()->where('pharmacy_id', $pharmacyId)
                    ->where(DB::raw('DATE(invoice_date)'), '<', $from)
            )->where('medicine_id', $medicineId)->sum('quantity');

            $adjustBefore = \App\Models\StockAdjustment::where('medicine_id', $medicineId)
                ->whereHas('batch', fn($q) => $q->whereHas('medicine', fn($q2) =>
                    $q2->where('pharmacy_id', $pharmacyId)))
                ->where(DB::raw('DATE(adjustment_date)'), '<', $from)
                ->sum(DB::raw('quantity_after - quantity_before'));

            $openingStock = max(0, $importedBefore - $exportedBefore + $adjustBefore);

            // Thu thập tất cả events trong kỳ
            $events = collect();

            // Nhập hàng
            \App\Models\PurchaseOrderItem::with([
                'purchaseOrder:id,code,received_date,supplier_id',
                'purchaseOrder.supplier:id,name'
            ])
                ->whereHas(
                    'purchaseOrder',
                    fn($q) =>
                    $q->where('pharmacy_id', $pharmacyId)
                        ->where('status', 'received')
                        ->whereBetween(DB::raw('DATE(received_date)'), [$from, $to])
                )->where('medicine_id', $medicineId)
                ->get()
                ->each(function ($item) use (&$events) {
                    $events->push([
                        'date' => $item->purchaseOrder->received_date->format('Y-m-d'),
                        'type' => 'import',
                        'type_label' => 'Nhập hàng',
                        'ref' => $item->purchaseOrder->code,
                        'note' => 'NCC: ' . ($item->purchaseOrder->supplier->name ?? '—'),
                        'import_qty' => $item->received_quantity,
                        'export_qty' => 0,
                        'adjust_qty' => 0,
                    ]);
                });

            // Xuất bán
            InvoiceItem::with(['invoice:id,code,invoice_date'])
                ->whereHas(
                    'invoice',
                    fn($q) =>
                    $q->completed()->where('pharmacy_id', $pharmacyId)
                        ->whereBetween(DB::raw('DATE(invoice_date)'), [$from, $to])
                )->where('medicine_id', $medicineId)
                ->get()
                ->each(function ($item) use (&$events) {
                    $events->push([
                        'date' => $item->invoice->invoice_date->format('Y-m-d'),
                        'type' => 'export',
                        'type_label' => 'Bán hàng',
                        'ref' => $item->invoice->code,
                        'note' => '',
                        'import_qty' => 0,
                        'export_qty' => $item->quantity,
                        'adjust_qty' => 0,
                    ]);
                });

            // Điều chỉnh tồn
            \App\Models\StockAdjustment::with('batch:id,batch_number')
                ->where('medicine_id', $medicineId)
                ->whereHas('batch', fn($q) => $q->whereHas('medicine', fn($q2) =>
                    $q2->where('pharmacy_id', $pharmacyId)))
                ->whereBetween(DB::raw('DATE(adjustment_date)'), [$from, $to])
                ->get()
                ->each(function ($adj) use (&$events) {
                    $change = $adj->quantity_after - $adj->quantity_before;
                    $events->push([
                        'date' => \Carbon\Carbon::parse($adj->adjustment_date)->format('Y-m-d'),
                        'type' => 'adjust',
                        'type_label' => 'Điều chỉnh',
                        'ref' => 'Lô: ' . ($adj->batch->batch_number ?? '?'),
                        'note' => $adj->reason,
                        'import_qty' => $change > 0 ? $change : 0,
                        'export_qty' => $change < 0 ? abs($change) : 0,
                        'adjust_qty' => $change,
                    ]);
                });

            // Sắp xếp theo ngày
            $events = $events->sortBy('date');

            // Tính tồn luỹ kế
            $running = $openingStock;
            $ledger = $events->map(function ($e) use (&$running) {
                $running += $e['import_qty'] - $e['export_qty'];
                return array_merge($e, ['balance' => $running]);
            });

            // Export CSV
            if ($request->get('export') === '1') {
                return $this->exportLedger($ledger, $medicine, $from, $to, $openingStock);
            }
        }

        return view('reports.ledger', compact(
            'medicines',
            'medicine',
            'ledger',
            'from',
            'to',
            'openingStock',
            'medicineId'
        ));
    }

    private function exportLedger($ledger, $medicine, $from, $to, $openingStock)
    {
        $filename = 'xuat-nhap-ton-' . str_replace(' ', '-', $medicine->name) . '-' . $from . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ];

        $callback = function () use ($ledger, $medicine, $from, $to, $openingStock) {
            $f = fopen('php://output', 'w');
            fprintf($f, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($f, ['BÁO CÁO XUẤT NHẬP TỒN — ' . $medicine->name]);
            fputcsv($f, ['Kỳ báo cáo:', $from . ' đến ' . $to]);
            fputcsv($f, ['Tồn đầu kỳ:', $openingStock, $medicine->unit]);
            fputcsv($f, []);
            fputcsv($f, ['Ngày', 'Loại', 'Chứng từ', 'Ghi chú', 'Nhập', 'Xuất', 'Tồn cuối']);
            foreach ($ledger as $row) {
                fputcsv($f, [
                    $row['date'],
                    $row['type_label'],
                    $row['ref'],
                    $row['note'],
                    $row['import_qty'] ?: '',
                    $row['export_qty'] ?: '',
                    $row['balance'],
                ]);
            }
            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Báo cáo công nợ ──────────────────────────────────────────────────────
    public function debt(Request $request)
    {
        $type = $request->get('type', 'supplier'); // supplier | customer

        // Export CSV
        if ($request->get('export')) {
            return $this->exportDebt($request);
        }

        if ($type === 'supplier') {
            $records = Supplier::where('pharmacy_id', auth()->user()->pharmacy_id)
                ->where('current_debt', '>', 0)
                ->orderByDesc('current_debt')
                ->get();
        } else {
            $records = Customer::where('pharmacy_id', auth()->user()->pharmacy_id)
                ->where('current_debt', '>', 0)
                ->orderByDesc('current_debt')
                ->get();
        }

        $totalDebt = $records->sum('current_debt');

        return view('reports.debt', compact('records', 'type', 'totalDebt'));
    }

    // ── Báo cáo công nợ chi tiết — lịch sử giao dịch từng đối tác ───────────
    public function debtDetail(Request $request)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $type = $request->get('type', 'supplier'); // supplier | customer
        $partnerId = $request->get('partner_id');
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        // Danh sách đối tác để chọn
        if ($type === 'supplier') {
            $partners = Supplier::where('pharmacy_id', $pharmacyId)->orderBy('name')->get(['id', 'name', 'current_debt']);
            $partnerClass = Supplier::class;
        } else {
            $partners = Customer::where('pharmacy_id', $pharmacyId)->orderBy('name')->get(['id', 'name', 'current_debt']);
            $partnerClass = Customer::class;
        }

        $partner = null;
        $transactions = collect();
        $summary = [];

        if ($partnerId) {
            $partner = ($type === 'supplier')
                ? Supplier::where('pharmacy_id', $pharmacyId)->findOrFail($partnerId)
                : Customer::where('pharmacy_id', $pharmacyId)->findOrFail($partnerId);

            $txQuery = \App\Models\DebtTransaction::where('debtable_type', $partnerClass)
                ->where('debtable_id', $partnerId)
                ->with('createdBy:id,name')
                ->whereBetween(DB::raw('DATE(transaction_date)'), [$from, $to])
                ->orderBy('transaction_date');

            if ($request->get('export') === '1') {
                return $this->exportDebtDetail($txQuery->get(), $partner, $from, $to, $type);
            }

            $transactions = $txQuery->paginate(25)->withQueryString();

            // Thống kê tổng hợp
            $allTx = \App\Models\DebtTransaction::where('debtable_type', $partnerClass)
                ->where('debtable_id', $partnerId)
                ->whereBetween(DB::raw('DATE(transaction_date)'), [$from, $to])
                ->get();

            $summary = [
                'total_debit' => $allTx->where('type', 'debit')->sum('amount'),
                'total_credit' => $allTx->where('type', 'credit')->sum('amount'),
                'tx_count' => $allTx->count(),
                'current_debt' => $partner->current_debt,
            ];
        }

        return view('reports.debt_detail', compact('partners', 'partner', 'type', 'transactions', 'from', 'to', 'partnerId', 'summary'));
    }

    private function exportDebtDetail($transactions, $partner, $from, $to, $type)
    {
        $typeName = $type === 'supplier' ? 'ncc' : 'kh';
        $filename = 'cong-no-' . $typeName . '-' . Str::slug($partner->name) . '-' . $from . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($transactions, $partner, $from, $to) {
            $f = fopen('php://output', 'w');
            fprintf($f, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($f, ['BÁO CÁO CÔNG NỢ — ' . $partner->name]);
            fputcsv($f, ['Kỳ:', $from . ' đến ' . $to]);
            fputcsv($f, []);
            fputcsv($f, ['Ngày', 'Loại', 'Danh mục', 'Mô tả', 'Mã chứng từ', 'Phát sinh Nợ', 'Phát sinh Có', 'Số dư']);
            foreach ($transactions as $tx) {
                fputcsv($f, [
                    \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y H:i'),
                    $tx->type === 'debit' ? 'Phát sinh Nợ' : 'Phát sinh Có',
                    $tx->category,
                    $tx->description,
                    $tx->reference_code ?? '—',
                    $tx->type === 'debit' ? number_format($tx->amount, 0, ',', '.') : '',
                    $tx->type === 'credit' ? number_format($tx->amount, 0, ',', '.') : '',
                    number_format($tx->balance_after, 0, ',', '.'),
                ]);
            }
            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Export Excel: Doanh thu ───────────────────────────────────────────────
    public function exportRevenue(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $rows = Invoice::with(['customer:id,name', 'createdBy:id,name', 'items'])
            ->completed()
            ->where('pharmacy_id', auth()->user()->pharmacy_id)
            ->whereBetween(DB::raw('DATE(invoice_date)'), [$from, $to])
            ->orderBy('invoice_date')
            ->get();

        // Tạo CSV đơn giản (không cần package)
        $filename = 'doanh-thu-' . $from . '-den-' . $to . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            // BOM UTF-8 để Excel đọc được tiếng Việt
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'Mã HĐ',
                'Ngày',
                'Khách hàng',
                'Tổng tiền',
                'Đã thu',
                'Còn nợ',
                'Thanh toán',
                'Nhân viên'
            ]);

            foreach ($rows as $inv) {
                fputcsv($file, [
                    $inv->code,
                    $inv->invoice_date->format('d/m/Y H:i'),
                    $inv->customer?->name ?? 'Khách lẻ',
                    $inv->total_amount,
                    $inv->paid_amount,
                    $inv->debt_amount,
                    $inv->payment_method_label,
                    $inv->createdBy?->name ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Export Excel: Tồn kho ─────────────────────────────────────────────────
    public function exportInventory(Request $request)
    {
        $filename = 'ton-kho-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $pharmacyId = auth()->user()->pharmacy_id;

        $batches = Batch::with(['medicine:id,name,code,unit,pharmacy_id', 'supplier:id,name'])
            ->whereHas('medicine', fn($q) => $q->where('pharmacy_id', $pharmacyId))
            ->where('is_active', true)
            ->where('current_quantity', '>', 0)
            ->orderBy('expiry_date')
            ->get();

        $callback = function () use ($batches) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'Mã thuốc',
                'Tên thuốc',
                'Số lô',
                'Hạn dùng',
                'Tồn kho',
                'Đơn vị',
                'Giá nhập',
                'Giá trị tồn',
                'NCC',
                'Trạng thái'
            ]);

            foreach ($batches as $b) {
                fputcsv($file, [
                    $b->medicine?->code,
                    $b->medicine?->name,
                    $b->batch_number,
                    $b->expiry_date->format('d/m/Y'),
                    $b->current_quantity,
                    $b->medicine?->unit,
                    $b->purchase_price,
                    $b->stock_value,
                    $b->supplier?->name,
                    $b->expiry_status_label,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Export CSV: Công nợ ───────────────────────────────────────────────────
    public function exportDebt(Request $request)
    {
        $type = $request->get('type', 'supplier');
        $filename = 'cong-no-' . $type . '-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $pharmacyId = auth()->user()->pharmacy_id;

        if ($type === 'supplier') {
            $records = Supplier::where('pharmacy_id', $pharmacyId)
                ->where('current_debt', '>', 0)
                ->orderByDesc('current_debt')
                ->get();

            $callback = function () use ($records) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");
                fputcsv($file, ['Mã NCC', 'Tên nhà cung cấp', 'Điện thoại', 'Người liên hệ', 'Công nợ', 'Hạn mức', 'Trạng thái']);
                foreach ($records as $r) {
                    fputcsv($file, [
                        $r->code,
                        $r->name,
                        $r->phone,
                        $r->contact_person,
                        $r->current_debt,
                        $r->debt_limit ?: 'Không giới hạn',
                        $r->current_debt >= $r->debt_limit && $r->debt_limit > 0 ? 'Vượt hạn mức' : 'Đang nợ',
                    ]);
                }
                fclose($file);
            };
        } else {
            $records = Customer::where('pharmacy_id', $pharmacyId)
                ->where('current_debt', '>', 0)
                ->orderByDesc('current_debt')
                ->get();

            $callback = function () use ($records) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");
                fputcsv($file, ['Mã KH', 'Tên khách hàng', 'Điện thoại', 'Công nợ', 'Hạn mức', 'Trạng thái']);
                foreach ($records as $r) {
                    fputcsv($file, [
                        $r->code,
                        $r->name,
                        $r->phone,
                        $r->current_debt,
                        $r->debt_limit ?: 'Không giới hạn',
                        $r->current_debt >= $r->debt_limit && $r->debt_limit > 0 ? 'Vượt hạn mức' : 'Đang nợ',
                    ]);
                }
                fclose($file);
            };
        }

        return response()->stream($callback, 200, $headers);
    }

}