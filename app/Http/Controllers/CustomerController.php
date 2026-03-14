<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::where('pharmacy_id', auth()->user()->pharmacy_id);

        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->get('has_debt')) {
            $query->hasDebt();
        }

        $customers = $query->orderBy('name')->paginate(20)->withQueryString();
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'id_card' => 'nullable|string|max:20',
            'debt_limit' => 'nullable|numeric|min:0',
            'medical_note' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        // Tự sinh mã KH
        $count = Customer::where('pharmacy_id', auth()->user()->pharmacy_id)->count() + 1;
        $code = 'KH' . str_pad($count, 4, '0', STR_PAD_LEFT);

        Customer::create(array_merge($data, [
            'pharmacy_id' => auth()->user()->pharmacy_id,
            'code' => $code,
            'current_debt' => 0,
            'is_active' => true,
        ]));

        return redirect()->route('customers.index')
            ->with('success', 'Thêm khách hàng "' . $data['name'] . '" thành công!');
    }

    public function show(Customer $customer)
    {
        $invoices = $customer->invoices()->latest()->limit(10)->get();
        $debtHistory = $customer->debtTransactions()->latest()->limit(10)->get();
        return view('customers.show', compact('customer', 'invoices', 'debtHistory'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'id_card' => 'nullable|string|max:20',
            'debt_limit' => 'nullable|numeric|min:0',
            'medical_note' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $customer->update($data);
        return redirect()->route('customers.index')
            ->with('success', 'Cập nhật khách hàng thành công!');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->current_debt > 0) {
            return back()->with('error', 'Không thể xóa khách hàng đang có công nợ.');
        }
        $customer->delete();
        return back()->with('success', 'Đã xóa khách hàng.');
    }

    // ── API tìm kiếm cho POS ──────────────────────────────────────────────────
    public function search(Request $request)
    {
        $customers = Customer::active()
            ->search($request->get('q', ''))
            ->limit(10)
            ->get(['id', 'name', 'phone', 'code', 'current_debt', 'debt_limit']);

        return response()->json(['customers' => $customers]);
    }

    // ── API nhanh: tạo khách hàng ngay từ POS ────────────────────────────────
    public function quickCreate(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
        ]);

        // Kiểm tra SĐT trùng trong cùng nhà thuốc
        if (!empty($data['phone'])) {
            $exists = Customer::where('pharmacy_id', auth()->user()->pharmacy_id)
                ->where('phone', $data['phone'])
                ->first();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'SĐT ' . $data['phone'] . ' đã tồn tại — khách: ' . $exists->name,
                    'customer' => [
                        'id' => $exists->id,
                        'name' => $exists->name,
                        'phone' => $exists->phone,
                        'code' => $exists->code,
                        'current_debt' => $exists->current_debt,
                    ],
                    'duplicate' => true,
                ], 200);
            }
        }

        $count = Customer::where('pharmacy_id', auth()->user()->pharmacy_id)->count() + 1;
        $code = 'KH' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $customer = Customer::create(array_merge($data, [
            'pharmacy_id' => auth()->user()->pharmacy_id,
            'code' => $code,
            'current_debt' => 0,
            'is_active' => true,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm khách hàng mới!',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'code' => $customer->code,
                'current_debt' => 0,
            ],
        ]);
    }
}