<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineCategory;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    // ── Danh sách ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Medicine::with(['category', 'batches'])
            ->where('pharmacy_id', auth()->user()->pharmacy_id);

        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $medicines = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = MedicineCategory::where('pharmacy_id', auth()->user()->pharmacy_id)
            ->active()->ordered()->get();

        return view('medicines.index', compact('medicines', 'categories'));
    }

    // ── Form tạo mới ──────────────────────────────────────────────────────────
    public function create()
    {
        $categories = MedicineCategory::where('pharmacy_id', auth()->user()->pharmacy_id)
            ->active()->ordered()->get();
        return view('medicines.create', compact('categories'));
    }

    // ── Lưu mới ───────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:200',
            'generic_name' => 'nullable|string|max:200',
            'category_id' => 'nullable|exists:medicine_categories,id',
            'manufacturer' => 'nullable|string|max:200',
            'barcode' => 'nullable|string|max:50',
            'unit' => 'required|string|max:30',
            'package_unit' => 'nullable|string|max:30',
            'units_per_package' => 'nullable|integer|min:1',
            'sell_price' => 'required|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'requires_prescription' => 'nullable|boolean',
            'is_narcotic' => 'nullable|boolean',
            'is_antibiotic' => 'nullable|boolean',
            'description' => 'nullable|string',
            'storage_instruction' => 'nullable|string|max:255',
        ]);

        // Kiểm tra mã thuốc trùng trong nhà thuốc
        $exists = Medicine::withTrashed()
            ->where('pharmacy_id', auth()->user()->pharmacy_id)
            ->where('code', $data['code'])
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['code' => 'Mã thuốc đã tồn tại trong hệ thống.']);
        }

        Medicine::create(array_merge($data, [
            'pharmacy_id' => auth()->user()->pharmacy_id,
            'requires_prescription' => $request->boolean('requires_prescription'),
            'is_narcotic' => $request->boolean('is_narcotic'),
            'is_antibiotic' => $request->boolean('is_antibiotic'),
            'is_active' => true,
        ]));

        return redirect()->route('medicines.index')
            ->with('success', 'Thêm thuốc "' . $data['name'] . '" thành công!');
    }

    // ── Chi tiết ──────────────────────────────────────────────────────────────
    public function show(Medicine $medicine)
    {
        $medicine->load([
            'category',
            'batches' => fn($q) => $q->orderBy('expiry_date')->orderBy('created_at')
        ]);
        return view('medicines.show', compact('medicine'));
    }

    // ── Form sửa ──────────────────────────────────────────────────────────────
    public function edit(Medicine $medicine)
    {
        $categories = MedicineCategory::where('pharmacy_id', auth()->user()->pharmacy_id)
            ->active()->ordered()->get();
        return view('medicines.edit', compact('medicine', 'categories'));
    }

    // ── Cập nhật ──────────────────────────────────────────────────────────────
    public function update(Request $request, Medicine $medicine)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'generic_name' => 'nullable|string|max:200',
            'category_id' => 'nullable|exists:medicine_categories,id',
            'manufacturer' => 'nullable|string|max:200',
            'barcode' => 'nullable|string|max:50',
            'unit' => 'required|string|max:30',
            'package_unit' => 'nullable|string|max:30',
            'units_per_package' => 'nullable|integer|min:1',
            'sell_price' => 'required|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'requires_prescription' => 'nullable|boolean',
            'is_narcotic' => 'nullable|boolean',
            'is_antibiotic' => 'nullable|boolean',
            'description' => 'nullable|string',
            'storage_instruction' => 'nullable|string|max:255',
        ]);

        $medicine->update(array_merge($data, [
            'requires_prescription' => $request->boolean('requires_prescription'),
            'is_narcotic' => $request->boolean('is_narcotic'),
            'is_antibiotic' => $request->boolean('is_antibiotic'),
        ]));

        return redirect()->route('medicines.index')
            ->with('success', 'Cập nhật thuốc "' . $medicine->name . '" thành công!');
    }

    // ── Xóa mềm ───────────────────────────────────────────────────────────────
    public function destroy(Medicine $medicine)
    {
        if ($medicine->total_stock > 0) {
            return back()->with(
                'error',
                'Không thể xóa thuốc còn tồn kho (' . $medicine->total_stock . ' ' . $medicine->unit . ').'
            );
        }
        $medicine->delete();
        return redirect()->route('medicines.index')
            ->with('success', 'Đã xóa thuốc "' . $medicine->name . '".');
    }

    // ── Bật/tắt trạng thái ────────────────────────────────────────────────────
    public function toggle(Medicine $medicine)
    {
        $medicine->update(['is_active' => !$medicine->is_active]);
        $status = $medicine->is_active ? 'kích hoạt' : 'ngừng kinh doanh';
        return back()->with('success', "Đã {$status} thuốc \"{$medicine->name}\".");
    }

    // ── API: Tìm kiếm cho POS (JSON) ──────────────────────────────────────────
    public function search(Request $request)
    {
        $medicines = Medicine::active()
            ->search($request->get('q', ''))
            ->with(['batches' => fn($q) => $q->availableFEFO()->limit(1)])
            ->limit(10)
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'code' => $m->code,
                'unit' => $m->unit,
                'sell_price' => (float) $m->sell_price,
                'total_stock' => $m->total_stock,
                'next_expiry' => $m->nearest_expiry_date,
                'requires_prescription' => $m->requires_prescription,
                'default_usage' => $m->description ?: '',  // hướng dẫn mặc định
            ]);

        return response()->json(['medicines' => $medicines]);
    }

    // ── API: Thông tin tồn kho (JSON) ─────────────────────────────────────────
    public function stockInfo(Medicine $medicine)
    {
        return response()->json([
            'total_stock' => $medicine->total_stock,
            'batches' => $medicine->availableBatches()->get(),
        ]);
    }
    // ── In nhãn thuốc ──────────────────────────────────────────────────────
    public function label(Medicine $medicine, Request $request)
    {
        $medicine->load([
            'category',
            'batches' => fn($q) =>
                $q->where('is_active', true)->where('is_expired', false)->orderBy('expiry_date')
        ]);

        // Lô được chọn (hoặc lấy lô gần hết hạn nhất theo FEFO)
        $batchId = $request->get('batch_id');
        $selectedBatch = $batchId
            ? $medicine->batches->firstWhere('id', $batchId)
            : $medicine->batches->first();

        $quantity = (int) $request->get('quantity', 1);   // Số nhãn in
        $customNote = $request->get('note', '');
        $patientName = $request->get('patient_name', '');
        $dosage = $request->get('dosage', '');

        return view('medicines.label', compact('medicine', 'selectedBatch', 'quantity', 'customNote', 'patientName', 'dosage'));
    }

    public function printLabel(Medicine $medicine, Request $request)
    {
        $batchId = $request->get('batch_id');
        $batch = $batchId ? \App\Models\Batch::find($batchId) : null;

        if (!$batch) {
            $batch = \App\Models\Batch::where('medicine_id', $medicine->id)
                ->where('is_active', true)->where('is_expired', false)
                ->orderBy('expiry_date')->first();
        }

        $quantity = max(1, min(100, (int) $request->get('quantity', 1)));
        $patientName = $request->get('patient_name', '');
        $dosage = $request->get('dosage', '');
        $note = $request->get('note', '');
        $labelSize = $request->get('size', 'medium'); // small|medium|large

        return view('medicines.label_print', compact('medicine', 'batch', 'quantity', 'patientName', 'dosage', 'note', 'labelSize'));
    }

}