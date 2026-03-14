<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Medicine;
use App\Models\Batch;
use App\Models\DebtTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    // ── Danh sách đơn nhập ────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = PurchaseOrder::with('supplier:id,name')
            ->where('pharmacy_id', auth()->user()->pharmacy_id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('from')) {
            $query->where('order_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('order_date', '<=', $request->to);
        }

        $orders = $query->latest()->paginate(20)->withQueryString();
        $suppliers = Supplier::where('pharmacy_id', auth()->user()->pharmacy_id)
            ->active()->get(['id', 'name']);

        return view('purchase.index', compact('orders', 'suppliers'));
    }

    // ── Form tạo đơn ──────────────────────────────────────────────────────────
    public function create()
    {
        $suppliers = Supplier::where('pharmacy_id', auth()->user()->pharmacy_id)
            ->active()->orderBy('name')->get(['id', 'name', 'code']);
        $medicines = Medicine::where('pharmacy_id', auth()->user()->pharmacy_id)
            ->active()->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
                'unit',
                'sell_price',
                'generic_name',
                'concentration',
                'dosage_form',
                'registration_number',
                'is_narcotic',
                'is_psychotropic',
                'requires_prescription',
                'is_antibiotic'
            ]);
        $categories = \App\Models\MedicineCategory::where('pharmacy_id', auth()->user()->pharmacy_id)
            ->orderBy('name')->get(['id', 'name']);

        return view('purchase.create', compact('suppliers', 'medicines', 'categories'));
    }

    // ── Lưu đơn đặt hàng ─────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'expected_date' => 'nullable|date|after_or_equal:today',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
            'items.*.unit' => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            // Sinh mã PO
            $count = PurchaseOrder::where('pharmacy_id', auth()->user()->pharmacy_id)
                ->whereYear('created_at', now()->year)->count() + 1;
            $code = 'PO-' . now()->format('Y') . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);

            $po = PurchaseOrder::create([
                'pharmacy_id' => auth()->user()->pharmacy_id,
                'supplier_id' => $request->supplier_id,
                'created_by' => auth()->id(),
                'code' => $code,
                'status' => 'pending',
                'order_date' => today(),
                'expected_date' => $request->expected_date,
                'note' => $request->note,
            ]);

            $total = 0;
            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['purchase_price'];
                $lineTotal *= (1 - ($item['discount_percent'] ?? 0) / 100);

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'medicine_id' => $item['medicine_id'],
                    'ordered_quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'purchase_price' => $item['purchase_price'],
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'total_amount' => $lineTotal,
                ]);
                $total += $lineTotal;
            }

            $po->update(['total_amount' => $total, 'subtotal' => $total]);
        });

        return redirect()->route('purchase.index')
            ->with('success', 'Tạo đơn nhập hàng thành công! Đang chờ duyệt.');
        \App\Models\ActivityLog::log('create', 'purchase', $po->id ?? 0, 'Tạo đơn nhập hàng mới');
    }

    // ── Chi tiết đơn ──────────────────────────────────────────────────────────
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'createdBy',
            'approvedBy',
            'items.medicine',
            'items.batch',
        ]);
        return view('purchase.show', compact('purchaseOrder'));
    }

    // ── Duyệt đơn ────────────────────────────────────────────────────────────
    public function approve(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canApprove()) {
            return back()->with('error', 'Đơn hàng không ở trạng thái chờ duyệt.');
        }

        $purchaseOrder->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        \App\Models\ActivityLog::log('approve', 'purchase', $purchaseOrder->id, "Duyệt đơn nhập " . $purchaseOrder->code);
        return back()->with('success', 'Đã duyệt đơn nhập hàng ' . $purchaseOrder->code);
    }

    // ── Form nhận hàng ────────────────────────────────────────────────────────
    public function receiveForm(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canReceive()) {
            return back()->with('error', 'Đơn hàng chưa được duyệt hoặc đã nhận đủ hàng.');
        }
        $purchaseOrder->load(['supplier', 'items.medicine']);
        return view('purchase.receive', compact('purchaseOrder'));
    }

    // ── Xử lý nhận hàng → TẠO BATCH ─────────────────────────────────────────
    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:purchase_order_items,id',
            'items.*.batch_number' => 'required|string|max:50',
            'items.*.expiry_date' => 'required|date|after:today',
            'items.*.received_quantity' => 'required|integer|min:1',
            'items.*.manufacture_date' => 'nullable|date|before:today',
        ]);

        DB::transaction(function () use ($request, $purchaseOrder) {
            $totalReceived = 0;

            foreach ($request->items as $itemData) {
                $poItem = PurchaseOrderItem::findOrFail($itemData['id']);

                // ── TẠO BATCH ← Đây là bước cốt lõi nhất ──────────────────
                $batch = Batch::create([
                    'medicine_id' => $poItem->medicine_id,
                    'supplier_id' => $purchaseOrder->supplier_id,
                    'purchase_order_item_id' => $poItem->id,
                    'batch_number' => $itemData['batch_number'],
                    'manufacture_date' => $itemData['manufacture_date'] ?? null,
                    'expiry_date' => $itemData['expiry_date'],
                    'initial_quantity' => $itemData['received_quantity'],
                    'current_quantity' => $itemData['received_quantity'],
                    'purchase_price' => $poItem->purchase_price,
                ]);

                // Gán batch vào PO item
                $poItem->update([
                    'batch_id' => $batch->id,
                    'batch_number' => $itemData['batch_number'],
                    'expiry_date' => $itemData['expiry_date'],
                    'manufacture_date' => $itemData['manufacture_date'] ?? null,
                    'received_quantity' => $itemData['received_quantity'],
                ]);

                $totalReceived += $itemData['received_quantity'];
            }

            // Cập nhật trạng thái PO
            $purchaseOrder->update([
                'status' => 'received',
                'received_date' => now(),
                'received_by' => auth()->id(),
            ]);

            // ── GHI CÔNG NỢ NHÀ CUNG CẤP ────────────────────────────────
            $debtAmount = $purchaseOrder->total_amount - $purchaseOrder->paid_amount;
            if ($debtAmount > 0) {
                $supplier = $purchaseOrder->supplier;
                $balanceAfter = $supplier->current_debt + $debtAmount;

                DebtTransaction::create([
                    'pharmacy_id' => auth()->user()->pharmacy_id,
                    'created_by' => auth()->id(),
                    'debtable_type' => \App\Models\Supplier::class,
                    'debtable_id' => $supplier->id,
                    'type' => 'increase',
                    'category' => 'purchase',
                    'amount' => $debtAmount,
                    'balance_after' => $balanceAfter,
                    'sourceable_type' => PurchaseOrder::class,
                    'sourceable_id' => $purchaseOrder->id,
                    'reference_code' => $purchaseOrder->code,
                    'description' => 'Nhận hàng đơn ' . $purchaseOrder->code,
                    'transaction_date' => now(),
                ]);

                $supplier->increment('current_debt', $debtAmount);
            }
        });

        return redirect()->route('purchase.show', $purchaseOrder)
            ->with('success', 'Nhận hàng thành công! Tồn kho đã được cập nhật.');
    }

    // ── Hủy đơn ──────────────────────────────────────────────────────────────
    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canCancel()) {
            return back()->with('error', 'Không thể hủy đơn hàng này.');
        }
        $purchaseOrder->update(['status' => 'cancelled']);
        return back()->with('success', 'Đã hủy đơn nhập hàng ' . $purchaseOrder->code);
    }
    // ── In phiếu nhập hàng ────────────────────────────────────────────────────
    public function print(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'pharmacy',
            'createdBy:id,name',
            'approvedBy:id,name',
            'items.medicine:id,name,unit,generic_name,manufacturer',
        ]);

        return view('purchase.print', compact('purchaseOrder'));
    }


    // ── Tạo thuốc mới nhanh từ form đặt hàng (AJAX) ─────────────────────────
    public function quickCreateMedicine(Request $request)
    {
        $pid = auth()->user()->pharmacy_id;

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'generic_name' => 'nullable|string|max:200',
            'concentration' => 'nullable|string|max:100',
            'dosage_form' => 'nullable|string|max:100',
            'unit' => 'required|string|max:30',
            'manufacturer' => 'nullable|string|max:200',
            'country_of_origin' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:50',
            'category_id' => 'nullable|exists:medicine_categories,id',
            'requires_prescription' => 'boolean',
            'is_narcotic' => 'boolean',
            'is_psychotropic' => 'boolean',
            'is_antibiotic' => 'boolean',
            'sell_price' => 'nullable|numeric|min:0',
            'storage_instruction' => 'nullable|string|max:255',
        ]);

        // Sinh mã thuốc tự động
        $count = \App\Models\Medicine::where('pharmacy_id', $pid)->count() + 1;
        $code = 'TH' . str_pad($count, 4, '0', STR_PAD_LEFT);
        while (\App\Models\Medicine::where('pharmacy_id', $pid)->where('code', $code)->exists()) {
            $count++;
            $code = 'TH' . str_pad($count, 4, '0', STR_PAD_LEFT);
        }

        $medicine = \App\Models\Medicine::create(array_merge($validated, [
            'pharmacy_id' => $pid,
            'code' => $code,
            'sell_price' => $validated['sell_price'] ?? 0,
            'is_active' => true,
        ]));

        \App\Models\ActivityLog::log(
            'create',
            'medicine',
            $medicine->id,
            'Thêm thuốc mới nhanh từ đơn nhập hàng: ' . $medicine->name
        );

        return response()->json([
            'success' => true,
            'medicine' => [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'code' => $medicine->code,
                'unit' => $medicine->unit,
                'sell_price' => $medicine->sell_price,
                'generic_name' => $medicine->generic_name,
                'concentration' => $medicine->concentration,
                'dosage_form' => $medicine->dosage_form,
                'registration_number' => $medicine->registration_number,
                'requires_prescription' => $medicine->requires_prescription,
                'is_narcotic' => $medicine->is_narcotic,
                'is_psychotropic' => $medicine->is_psychotropic,
                'is_antibiotic' => $medicine->is_antibiotic,
            ],
        ]);
    }

}