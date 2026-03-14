<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\ReturnInvoice;
use App\Models\ReturnInvoiceItem;
use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnInvoiceController extends Controller
{
    // ── Danh sách phiếu trả ──────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = ReturnInvoice::with(['invoice', 'customer', 'createdBy'])
            ->latest();

        if ($q = $request->get('q')) {
            $query->where(function ($sq) use ($q) {
                $sq->where('code', 'like', "%$q%")
                    ->orWhereHas('invoice', fn($i) => $i->where('code', 'like', "%$q%"))
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%$q%"));
            });
        }

        if ($from = $request->get('from')) {
            $query->whereDate('return_date', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('return_date', '<=', $to);
        }

        $returns = $query->paginate(20)->withQueryString();
        $totalToday = ReturnInvoice::whereDate('return_date', today())->sum('refund_amount');

        return view('returns.index', compact('returns', 'totalToday'));
    }

    // ── Form tạo phiếu trả (chọn hóa đơn gốc) ────────────────────────────
    public function create(Request $request)
    {
        $invoiceId = $request->get('invoice_id');
        $invoice = null;

        if ($invoiceId) {
            $invoice = Invoice::with(['items.medicine', 'items.batch', 'customer'])
                ->where('status', 'completed')
                ->findOrFail($invoiceId);
        }

        // Tìm hóa đơn gần đây để gợi ý
        $recentInvoices = Invoice::with('customer')
            ->where('status', 'completed')
            ->latest('invoice_date')
            ->limit(10)
            ->get(['id', 'code', 'invoice_date', 'total_amount', 'customer_id']);

        return view('returns.create', compact('invoice', 'recentInvoices'));
    }

    // ── Lưu phiếu trả ───────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'return_date' => 'required|date',
            'reason' => 'required|string|max:500',
            'refund_method' => 'required|in:cash,account',
            'items' => 'required|array|min:1',
            'items.*.invoice_item_id' => 'required|exists:invoice_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.return' => 'nullable|boolean',
        ], [
            'items.required' => 'Phải chọn ít nhất 1 sản phẩm để trả.',
            'reason.required' => 'Vui lòng nhập lý do trả hàng.',
        ]);

        $invoice = Invoice::with('items.batch')->findOrFail($request->invoice_id);

        // Lọc chỉ những item được đánh dấu trả
        $returnItems = collect($request->items)->filter(fn($i) => !empty($i['return']) && $i['quantity'] > 0);

        if ($returnItems->isEmpty()) {
            return back()->withErrors(['items' => 'Vui lòng chọn ít nhất 1 sản phẩm cần trả.'])->withInput();
        }

        DB::transaction(function () use ($request, $invoice, $returnItems) {
            $totalAmount = 0;

            // Tạo phiếu trả
            $return = ReturnInvoice::create([
                'pharmacy_id' => auth()->user()->pharmacy_id,
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'created_by' => auth()->id(),
                'return_date' => $request->return_date,
                'refund_method' => $request->refund_method,
                'reason' => $request->reason,
                'note' => $request->note,
                'status' => 'completed',
                'total_amount' => 0, // Update sau
                'refund_amount' => 0,
            ]);

            foreach ($returnItems as $itemData) {
                $invoiceItem = $invoice->items->firstWhere('id', $itemData['invoice_item_id']);
                if (!$invoiceItem)
                    continue;

                $qty = (int) $itemData['quantity'];
                $price = $invoiceItem->unit_price;
                $total = $qty * $price;
                $totalAmount += $total;

                // Tạo dòng trả
                ReturnInvoiceItem::create([
                    'return_invoice_id' => $return->id,
                    'medicine_id' => $invoiceItem->medicine_id,
                    'batch_id' => $invoiceItem->batch_id,
                    'invoice_item_id' => $invoiceItem->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_amount' => $total,
                    'unit' => $invoiceItem->unit ?? '',
                    'reason' => $itemData['item_reason'] ?? null,
                ]);

                // Hoàn lại tồn kho vào lô gốc
                if ($invoiceItem->batch_id) {
                    Batch::where('id', $invoiceItem->batch_id)
                        ->increment('current_quantity', $qty);
                }
            }

            // Cập nhật tổng
            $return->update([
                'total_amount' => $totalAmount,
                'refund_amount' => $totalAmount,
            ]);

            // Ghi activity log nếu có
            if (class_exists(\App\Models\ActivityLog::class)) {
                \App\Models\ActivityLog::log(
                    'create',
                    'return_invoice',
                    $return->id,
                    "Tạo phiếu trả hàng {$return->code} từ HĐ {$invoice->code}"
                );
            }
        });

        return redirect()->route('returns.index')
            ->with('success', '✅ Đã tạo phiếu trả hàng thành công! Tồn kho đã được cập nhật.');
    }

    // ── Chi tiết phiếu trả ───────────────────────────────────────────────
    public function show(ReturnInvoice $return)
    {
        $return->load(['invoice', 'customer', 'createdBy', 'items.medicine', 'items.batch']);
        return view('returns.show', compact('return'));
    }

    // ── In phiếu trả ────────────────────────────────────────────────────
    public function print(ReturnInvoice $return)
    {
        $return->load(['invoice', 'customer', 'createdBy', 'items.medicine', 'pharmacy']);
        return view('returns.print', compact('return'));
    }
}