<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Batch;
use App\Models\StockAdjustment;
use App\Models\MedicineCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    // ── Tổng quan tồn kho ─────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Medicine::with([
            'category',
            'batches' => fn($q) => $q->where('is_active', true)->orderBy('expiry_date')
        ])
            ->where('pharmacy_id', auth()->user()->pharmacy_id)
            ->where('is_active', true);

        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $medicines = $query->orderBy('name')->paginate(25)->withQueryString();
        $categories = MedicineCategory::where('pharmacy_id', auth()->user()->pharmacy_id)
            ->active()->ordered()->get();

        // Tổng giá trị kho
        $totalValue = Batch::whereHas('medicine', fn($q) =>
            $q->where('pharmacy_id', auth()->user()->pharmacy_id))
            ->where('is_active', true)
            ->where('is_expired', false)
            ->selectRaw('SUM(current_quantity * purchase_price) as total')
            ->value('total') ?? 0;

        return view('inventory.index', compact('medicines', 'categories', 'totalValue'));
    }

    // ── Danh sách lô của 1 thuốc ──────────────────────────────────────────────
    public function batches(Medicine $medicine)
    {
        $batches = Batch::where('medicine_id', $medicine->id)
            ->with('supplier:id,name')
            ->orderBy('expiry_date')
            ->orderBy('created_at')
            ->get();

        return view('inventory.batches', compact('medicine', 'batches'));
    }

    // ── Lô sắp hết hạn ────────────────────────────────────────────────────────
    public function expiring(Request $request)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $days = (int) $request->get('days', 30);
        $action = $request->get('action'); // filter: destroy/return/sale

        $query = Batch::with([
            'medicine:id,name,unit,pharmacy_id,category_id',
            'medicine.category:id,name',
            'supplier:id,name'
        ])
            ->whereHas('medicine', fn($q) => $q->where('pharmacy_id', $pharmacyId))
            ->expiringSoon($days)
            ->orderBy('expiry_date');

        // Export CSV
        if ($request->get('export') === '1') {
            return $this->exportExpiring($query->get(), $days);
        }

        $batches = $query->paginate(20)->withQueryString();

        // Stats tổng hợp
        $allExpiring = Batch::with('medicine')
            ->whereHas('medicine', fn($q) => $q->where('pharmacy_id', $pharmacyId))
            ->expiringSoon($days)
            ->get();

        $stats = [
            'critical' => $allExpiring->filter(fn($b) => $b->days_until_expiry <= 7)->count(),
            'warning' => $allExpiring->filter(fn($b) => $b->days_until_expiry > 7 && $b->days_until_expiry <= 30)->count(),
            'caution' => $allExpiring->filter(fn($b) => $b->days_until_expiry > 30)->count(),
            'total_value' => $allExpiring->sum(fn($b) => $b->current_quantity * $b->purchase_price),
            'total_qty' => $allExpiring->sum('current_quantity'),
        ];

        return view('inventory.expiring', compact('batches', 'days', 'stats'));
    }

    private function exportExpiring($batches, $days)
    {
        $filename = 'lo-sap-het-han-' . $days . 'ngay-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ];

        $callback = function () use ($batches) {
            $f = fopen('php://output', 'w');
            fprintf($f, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($f, ['Tên thuốc', 'Số lô', 'Hạn dùng', 'Còn lại (ngày)', 'Tồn kho', 'ĐVT', 'Giá trị (đ)', 'NCC', 'Mức độ']);
            foreach ($batches as $b) {
                $level = $b->days_until_expiry <= 7 ? 'Khẩn cấp'
                    : ($b->days_until_expiry <= 30 ? 'Cảnh báo' : 'Theo dõi');
                fputcsv($f, [
                    $b->medicine?->name,
                    $b->batch_number,
                    $b->expiry_date->format('d/m/Y'),
                    $b->days_until_expiry,
                    $b->current_quantity,
                    $b->medicine?->unit,
                    number_format($b->current_quantity * $b->purchase_price, 0, ',', '.'),
                    $b->supplier?->name ?? '—',
                    $level,
                ]);
            }
            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Thuốc sắp hết tồn ────────────────────────────────────────────────────
    public function lowStock()
    {
        $medicines = Medicine::with([
            'batches' => fn($q) =>
                $q->where('is_active', true)->where('is_expired', false)
        ])
            ->where('pharmacy_id', auth()->user()->pharmacy_id)
            ->where('is_active', true)
            ->where('min_stock', '>', 0)
            ->get()
            ->filter(fn($m) => $m->total_stock <= $m->min_stock)
            ->sortBy('total_stock');

        return view('inventory.low-stock', compact('medicines'));
    }

    // ── Form điều chỉnh tồn kho ───────────────────────────────────────────────
    public function createAdjust(Request $request)
    {
        $batch = null;
        if ($request->filled('batch_id')) {
            $batch = Batch::with('medicine')->findOrFail($request->batch_id);
        }
        return view('inventory.adjust', compact('batch'));
    }

    // ── Lưu điều chỉnh tồn kho ───────────────────────────────────────────────
    public function storeAdjust(Request $request)
    {
        $data = $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'quantity_after' => 'required|integer|min:0',
            'type' => 'required|in:count,destroy,return,correction,other',
            'reason' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);

        $batch = Batch::with('medicine')->findOrFail($data['batch_id']);
        $before = $batch->current_quantity;
        $after = (int) $data['quantity_after'];
        $change = $after - $before;

        DB::transaction(function () use ($batch, $data, $before, $after, $change) {
            // Cập nhật tồn kho thực tế
            $batch->update(['current_quantity' => $after]);

            // Ghi log điều chỉnh (GPP: bắt buộc)
            StockAdjustment::create([
                'pharmacy_id' => auth()->user()->pharmacy_id,
                'batch_id' => $batch->id,
                'medicine_id' => $batch->medicine_id,
                'created_by' => auth()->id(),
                'type' => $data['type'],
                'quantity_before' => $before,
                'quantity_after' => $after,
                'quantity_change' => $change,
                'reason' => $data['reason'],
                'note' => $data['note'] ?? null,
                'status' => 'approved',
                'adjustment_date' => now(),
            ]);
        });

        return redirect()->route('inventory.batches', $batch->medicine_id)
            ->with(
                'success',
                "Điều chỉnh thành công: {$before} → {$after} " . $batch->medicine->unit
            );
    }
    // ── API: Danh sách lô của 1 thuốc (JSON cho adjust form) ─────────────────
    public function batchesApi(Medicine $medicine)
    {
        $batches = Batch::where('medicine_id', $medicine->id)
            ->with('supplier:id,name')
            ->where('is_active', true)
            ->orderBy('expiry_date')
            ->get()
            ->map(fn($b) => [
                'id' => $b->id,
                'batch_number' => $b->batch_number,
                'current_quantity' => $b->current_quantity,
                'expiry_date' => $b->expiry_date->format('Y-m-d'),
                'expiry_date_formatted' => $b->expiry_date->format('d/m/Y'),
                'days_until_expiry' => now()->diffInDays($b->expiry_date, false),
                'supplier_name' => $b->supplier?->name,
                'medicine_name' => $medicine->name,
                'purchase_price' => $b->purchase_price,
            ]);

        return response()->json(['batches' => $batches]);
    }

}