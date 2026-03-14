<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\CashTransaction;
use App\Models\DebtTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::where('pharmacy_id', auth()->user()->pharmacy_id);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $suppliers = $query->orderBy('name')->paginate(20)->withQueryString();
        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:200',
            'tax_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:20',
            'payment_term_days' => 'nullable|integer|min:0',
            'debt_limit' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $exists = Supplier::where('pharmacy_id', auth()->user()->pharmacy_id)
            ->where('code', $data['code'])->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['code' => 'Mã nhà cung cấp đã tồn tại.']);
        }

        Supplier::create(array_merge($data, [
            'pharmacy_id' => auth()->user()->pharmacy_id,
            'current_debt' => 0,
            'is_active' => true,
        ]));

        return redirect()->route('suppliers.index')
            ->with('success', 'Thêm nhà cung cấp "' . $data['name'] . '" thành công!');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['purchaseOrders' => fn($q) => $q->latest()->limit(10)]);
        $debtHistory = $supplier->debtTransactions()->latest()->limit(20)->get();
        return view('suppliers.show', compact('supplier', 'debtHistory'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'tax_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:20',
            'payment_term_days' => 'nullable|integer|min:0',
            'debt_limit' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $supplier->update($data);
        return redirect()->route('suppliers.index')
            ->with('success', 'Cập nhật nhà cung cấp thành công!');
    }

    // Pay debt to supplier
    public function payDebt(Request $request, Supplier $supplier)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,card,transfer',
            'note' => 'nullable|string|max:255',
            'payment_date' => 'nullable|date',
        ]);

        $amount = (float) $request->amount;
        $currentDebt = (float) $supplier->current_debt;
        $pharmacyId = auth()->user()->pharmacy_id;

        if ($currentDebt <= 0) {
            return back()->with('error', 'Nha cung cap khong co cong no can thanh toan.');
        }

        if ($amount > $currentDebt) {
            return back()->with(
                'error',
                'So tien thanh toan (' . number_format($amount, 0, ',', '.') . 'd) ' .
                'vuot qua so no hien tai (' . number_format($currentDebt, 0, ',', '.') . 'd).'
            );
        }

        DB::transaction(function () use ($request, $supplier, $amount, $currentDebt, $pharmacyId) {

            $paymentDate = $request->filled('payment_date')
                ? \Carbon\Carbon::parse($request->payment_date)
                : now();

            $balanceAfter = max(0.0, $currentDebt - $amount);

            $supplier->decrement('current_debt', $amount);

            DebtTransaction::create([
                'pharmacy_id' => $pharmacyId,
                'created_by' => auth()->id(),
                'debtable_type' => Supplier::class,
                'debtable_id' => $supplier->id,
                'type' => 'decrease',  // decrease = giam no (da tra)
                'category' => 'payment',
                'amount' => $amount,
                'balance_after' => $balanceAfter,
                'reference_code' => 'TT-NCC-' . now()->format('YmdHis'),
                'description' => 'Thanh toan no NCC: ' . $supplier->name
                    . ($request->note ? ' | ' . $request->note : ''),
                'transaction_date' => $paymentDate,
                'note' => $request->note,
            ]);

            CashTransaction::create([
                'pharmacy_id' => $pharmacyId,
                'created_by' => auth()->id(),
                'type' => 'payment',
                'category' => 'debt_payment',
                'amount' => $amount,
                'transactionable_type' => Supplier::class,
                'transactionable_id' => $supplier->id,
                'reference_code' => 'TT-NCC-' . $supplier->code,
                'description' => 'Thanh toan no cho: ' . $supplier->name
                    . ($request->note ? ' | ' . $request->note : ''),
                'transaction_date' => $paymentDate,
                'balance_after' => 0,
                'note' => $request->note,
            ]);
        });

        $remaining = (float) ($supplier->fresh()->current_debt ?? 0);

        $msg = 'Da thanh toan ' . number_format($amount, 0, ',', '.') . 'd cho ' . $supplier->name . '.';
        if ($remaining > 0) {
            $msg .= ' Con no: ' . number_format($remaining, 0, ',', '.') . 'd.';
        } else {
            $msg .= ' Da thanh toan het no!';
        }

        return back()->with('success', $msg);
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->current_debt > 0) {
            return back()->with('error', 'Không thể xóa nhà cung cấp đang có công nợ.');
        }
        $supplier->delete();
        return back()->with('success', 'Đã xóa nhà cung cấp "' . $supplier->name . '".');
    }
}