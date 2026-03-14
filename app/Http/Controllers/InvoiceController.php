<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\CashTransaction;
use App\Models\DebtTransaction;
use App\Exceptions\InsufficientStockException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    // ── Danh sách hóa đơn ────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Invoice::with(['customer:id,name', 'createdBy:id,name'])
            ->where('pharmacy_id', auth()->user()->pharmacy_id);

        if ($request->filled('search')) {
            $query->where('code', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('invoice_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('invoice_date', '<=', $request->to);
        }

        $invoices = $query->latest('invoice_date')->paginate(20)->withQueryString();
        return view('invoices.index', compact('invoices'));
    }

    // ── Màn hình POS ──────────────────────────────────────────────────────────
    public function pos()
    {
        $pharmacyId = auth()->user()->pharmacy_id;

        // 5 hóa đơn gần nhất hôm nay (cho panel lịch sử ca)
        $recentInvoices = Invoice::where('pharmacy_id', $pharmacyId)
            ->with('customer:id,name')
            ->completed()
            ->whereDate('invoice_date', today())
            ->latest('invoice_date')
            ->limit(8)
            ->get(['id', 'code', 'total_amount', 'payment_method', 'invoice_date', 'customer_id']);

        // Thống kê ca làm việc hôm nay
        $shiftStats = [
            'revenue' => Invoice::where('pharmacy_id', $pharmacyId)->completed()->whereDate('invoice_date', today())->sum('total_amount'),
            'invoice_count' => Invoice::where('pharmacy_id', $pharmacyId)->completed()->whereDate('invoice_date', today())->count(),
            'cashier' => auth()->user()->name,
        ];

        return view('invoices.pos', compact('recentInvoices', 'shiftStats'));
    }

    // ── TẠO HÓA ĐƠN (FIFO/FEFO) ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,card,transfer,debt,mixed',
            'paid_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.sell_price' => 'required|numeric|min:0',
            'items.*.unit' => 'required|string',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.usage_instruction' => 'nullable|string|max:500',
        ]);

        try {
            $invoice = DB::transaction(function () use ($request) {
                // ── 1. Tạo invoice header ──────────────────────────────────
                $count = Invoice::where('pharmacy_id', auth()->user()->pharmacy_id)
                    ->whereDate('invoice_date', today())->count() + 1;
                $code = 'INV-' . now()->format('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

                $invoice = Invoice::create([
                    'pharmacy_id' => auth()->user()->pharmacy_id,
                    'customer_id' => $request->customer_id ?: null,
                    'created_by' => auth()->id(),
                    'code' => $code,
                    'status' => 'completed',
                    'invoice_date' => now(),
                    'payment_method' => $request->payment_method,
                    'subtotal' => 0,
                    'discount_amount' => $request->discount_amount ?? 0,
                    'total_amount' => 0,
                    'paid_amount' => $request->paid_amount,
                    'change_amount' => 0,
                    'debt_amount' => 0,
                    'prescription_code' => $request->prescription_code,
                ]);

                $subtotal = 0;

                // ── 2. Xử lý từng thuốc → FIFO ────────────────────────────
                foreach ($request->items as $item) {
                    $allocations = $this->allocateFEFO(
                        $item['medicine_id'],
                        $item['quantity']
                    );

                    foreach ($allocations as $alloc) {
                        $discountPct = $item['discount_percent'] ?? 0;
                        $lineTotal = $alloc['quantity'] * $item['sell_price'];
                        $lineTotal *= (1 - $discountPct / 100);

                        // ── 3. Tạo invoice_item với LÔ CỤ THỂ ────────────
                        InvoiceItem::create([
                            'invoice_id' => $invoice->id,
                            'medicine_id' => $item['medicine_id'],
                            'batch_id' => $alloc['batch_id'],
                            'quantity' => $alloc['quantity'],
                            'unit' => $item['unit'],
                            'sell_price' => $item['sell_price'],
                            'purchase_price' => $alloc['purchase_price'],
                            'discount_percent' => $discountPct,
                            'total_amount' => $lineTotal,
                            'expiry_date' => $alloc['expiry_date'],
                            'batch_number' => $alloc['batch_number'],
                            'usage_instruction' => $item['usage_instruction'] ?? null,
                        ]);

                        // ── 4. GIẢM TỒN KHO của lô ────────────────────────
                        Batch::where('id', $alloc['batch_id'])
                            ->decrement('current_quantity', $alloc['quantity']);

                        $subtotal += $lineTotal;
                    }
                }

                // ── 5. Cập nhật tổng tiền ──────────────────────────────────
                $discountAmt = $request->discount_amount ?? 0;
                $total = $subtotal - $discountAmt;
                $paid = (float) $request->paid_amount;
                $debt = max(0, $total - $paid);
                $change = max(0, $paid - $total);

                $invoice->update([
                    'subtotal' => $subtotal,
                    'total_amount' => $total,
                    'paid_amount' => $paid,
                    'change_amount' => $change,
                    'debt_amount' => $debt,
                ]);

                // ── 6. Ghi sổ quỹ ─────────────────────────────────────────
                if ($paid > 0) {
                    CashTransaction::create([
                        'pharmacy_id' => auth()->user()->pharmacy_id,
                        'created_by' => auth()->id(),
                        'type' => 'receipt',
                        'category' => 'sale',
                        'amount' => $paid,
                        'transactionable_type' => Invoice::class,
                        'transactionable_id' => $invoice->id,
                        'reference_code' => $invoice->code,
                        'description' => 'Thu tiền hóa đơn ' . $invoice->code,
                        'transaction_date' => now(),
                        'balance_after' => 0, // Cập nhật sau nếu cần
                    ]);
                }

                // ── 7. Ghi công nợ khách hàng ─────────────────────────────
                if ($debt > 0 && $invoice->customer_id) {
                    $customer = Customer::find($invoice->customer_id);
                    $balanceAfter = $customer->current_debt + $debt;

                    DebtTransaction::create([
                        'pharmacy_id' => auth()->user()->pharmacy_id,
                        'created_by' => auth()->id(),
                        'debtable_type' => Customer::class,
                        'debtable_id' => $customer->id,
                        'type' => 'increase',
                        'category' => 'sale',
                        'amount' => $debt,
                        'balance_after' => $balanceAfter,
                        'sourceable_type' => Invoice::class,
                        'sourceable_id' => $invoice->id,
                        'reference_code' => $invoice->code,
                        'description' => 'Bán chịu hóa đơn ' . $invoice->code,
                        'transaction_date' => now(),
                    ]);

                    $customer->increment('current_debt', $debt);
                }

                return $invoice;
            });

            return response()->json([
                'success' => true,
                'invoice_id' => $invoice->id,
                'code' => $invoice->code,
                'message' => 'Tạo hóa đơn ' . $invoice->code . ' thành công!',
            ]);

        } catch (InsufficientStockException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Invoice creation failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ── Chi tiết hóa đơn ─────────────────────────────────────────────────────
    public function show(Invoice $invoice)
    {
        $invoice->load(['items.medicine', 'items.batch', 'customer', 'createdBy']);
        return view('invoices.show', compact('invoice'));
    }

    // ── In hóa đơn ───────────────────────────────────────────────────────────
    public function print(Invoice $invoice)
    {
        $invoice->load(['items.medicine', 'customer', 'createdBy']);
        return view('invoices.print', compact('invoice'));
    }

    // ── Hủy hóa đơn → Hoàn kho ───────────────────────────────────────────────
    public function cancel(Request $request, Invoice $invoice)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:255',
        ]);

        if (!$invoice->canCancel()) {
            return back()->with('error', 'Hóa đơn này không thể hủy.');
        }

        DB::transaction(function () use ($request, $invoice) {
            $invoice->load('items');

            // ── Hoàn trả tồn kho về ĐÚNG từng lô ─────────────────────────
            foreach ($invoice->items as $item) {
                Batch::where('id', $item->batch_id)
                    ->increment('current_quantity', $item->quantity);
            }

            // Hoàn tiền sổ quỹ
            if ($invoice->paid_amount > 0) {
                CashTransaction::create([
                    'pharmacy_id' => auth()->user()->pharmacy_id,
                    'created_by' => auth()->id(),
                    'type' => 'payment',
                    'category' => 'sale',
                    'amount' => $invoice->paid_amount,
                    'transactionable_type' => Invoice::class,
                    'transactionable_id' => $invoice->id,
                    'reference_code' => $invoice->code,
                    'description' => 'Hoàn tiền hủy HĐ ' . $invoice->code,
                    'transaction_date' => now(),
                    'balance_after' => 0,
                ]);
            }

            // Hoàn công nợ KH nếu có — trừ đúng số nợ thực tế còn lại
            if ($invoice->debt_amount > 0 && $invoice->customer_id) {
                $customer = Customer::find($invoice->customer_id);
                if ($customer) {
                    // Trừ tối đa số nợ hiện tại (tránh âm nếu KH đã trả một phần)
                    $deductDebt = min($invoice->debt_amount, max(0, $customer->current_debt));
                    if ($deductDebt > 0) {
                        $customer->decrement('current_debt', $deductDebt);

                        // Ghi DebtTransaction để có lịch sử kiểm tra
                        \App\Models\DebtTransaction::create([
                            'pharmacy_id' => $invoice->pharmacy_id,
                            'created_by' => auth()->id(),
                            'debtable_type' => Customer::class,
                            'debtable_id' => $customer->id,
                            'type' => 'credit',
                            'category' => 'cancel_invoice',
                            'amount' => $deductDebt,
                            'balance_after' => max(0, $customer->current_debt - $deductDebt),
                            'sourceable_type' => Invoice::class,
                            'sourceable_id' => $invoice->id,
                            'reference_code' => $invoice->code,
                            'description' => 'Hoàn nợ do hủy hóa đơn ' . $invoice->code,
                            'transaction_date' => now(),
                        ]);
                    }
                }
            }

            $invoice->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'cancel_reason' => $request->cancel_reason,
            ]);
        });

        return back()->with('success', 'Đã hủy hóa đơn ' . $invoice->code . '. Tồn kho đã hoàn trả.');
    }

    // ── FIFO/FEFO: Phân bổ lô ────────────────────────────────────────────────
    private function allocateFEFO(int $medicineId, int $needed): array
    {
        // lockForUpdate() = Pessimistic Lock chống race condition
        $batches = Batch::where('medicine_id', $medicineId)
            ->availableFEFO()
            ->lockForUpdate()
            ->get();

        $allocations = [];
        $remaining = $needed;

        foreach ($batches as $batch) {
            if ($remaining <= 0)
                break;

            $take = min($batch->current_quantity, $remaining);
            $allocations[] = [
                'batch_id' => $batch->id,
                'quantity' => $take,
                'purchase_price' => $batch->purchase_price,
                'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                'batch_number' => $batch->batch_number,
            ];
            $remaining -= $take;
        }

        if ($remaining > 0) {
            $medicine = \App\Models\Medicine::find($medicineId);
            throw new InsufficientStockException(
                "Không đủ tồn kho cho \"{$medicine->name}\". " .
                "Cần: {$needed}, có: " . ($needed - $remaining) . " {$medicine->unit}."
            );
        }

        return $allocations;
    }
}