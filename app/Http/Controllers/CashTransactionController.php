<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use Illuminate\Http\Request;

class CashTransactionController extends Controller
{
    // ── Sổ quỹ ────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = CashTransaction::with('createdBy:id,name')
            ->where('pharmacy_id', auth()->user()->pharmacy_id);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('from')) {
            $query->whereDate('transaction_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('transaction_date', '<=', $request->to);
        }

        // Mặc định xem hôm nay
        if (!$request->filled('from') && !$request->filled('to')) {
            $query->whereDate('transaction_date', today());
        }

        $transactions = $query->latest('transaction_date')->paginate(30)->withQueryString();

        // Tổng thu / chi trong kỳ
        $totalReceipt = (clone $query)->where('type', 'receipt')->sum('amount');
        $totalPayment = (clone $query)->where('type', 'payment')->sum('amount');

        return view('cash.index', compact('transactions', 'totalReceipt', 'totalPayment'));
    }

    // ── Thu chi thủ công ──────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:receipt,payment',
            'category' => 'required|in:sale,purchase,debt_receipt,debt_payment,expense,other',
            'amount' => 'required|numeric|min:1000',
            'description' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);

        CashTransaction::create(array_merge($data, [
            'pharmacy_id' => auth()->user()->pharmacy_id,
            'created_by' => auth()->id(),
            'transaction_date' => now(),
            'balance_after' => 0,
            'is_confirmed' => true,
        ]));

        $label = $data['type'] === 'receipt' ? 'Thu' : 'Chi';
        return back()->with(
            'success',
            "{$label} " . number_format($data['amount'], 0, ',', '.') . 'đ thành công!'
        );
    }
}