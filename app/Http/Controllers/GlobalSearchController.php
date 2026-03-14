<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Customer;
use App\Models\Batch;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));
        $pharmacyId = auth()->user()->pharmacy_id;

        if (strlen($q) < 2) {
            return response()->json(['results' => [], 'query' => $q]);
        }

        $results = [];

        // Thuốc
        Medicine::where('pharmacy_id', $pharmacyId)
            ->where(fn($sq) => $sq->where('name', 'like', "%$q%")
                ->orWhere('code', 'like', "%$q%")
                ->orWhere('generic_name', 'like', "%$q%"))
            ->limit(5)->get()
            ->each(fn($m) => $results[] = [
                'type' => 'medicine',
                'icon' => '💊',
                'title' => $m->name,
                'sub' => 'Thuốc · Tồn: ' . number_format($m->total_stock) . ' ' . $m->unit,
                'url' => route('medicines.show', $m),
                'badge' => $m->code,
            ]);

        // Hóa đơn
        Invoice::where('pharmacy_id', $pharmacyId)
            ->where(fn($sq) => $sq->where('code', 'like', "%$q%")
                ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%$q%")))
            ->limit(5)->get()
            ->each(fn($inv) => $results[] = [
                'type' => 'invoice',
                'icon' => '🧾',
                'title' => $inv->code,
                'sub' => 'Hóa đơn · ' . $inv->invoice_date->format('d/m/Y')
                    . ' · ' . number_format($inv->total_amount, 0, ',', '.') . 'đ',
                'url' => route('invoices.show', $inv),
                'badge' => $inv->status === 'completed' ? 'Hoàn thành' : ucfirst($inv->status),
            ]);

        // Đơn nhập hàng
        PurchaseOrder::where('pharmacy_id', $pharmacyId)
            ->where(fn($sq) => $sq->where('code', 'like', "%$q%")
                ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%$q%")))
            ->limit(3)->get()
            ->each(fn($po) => $results[] = [
                'type' => 'purchase',
                'icon' => '📦',
                'title' => $po->code,
                'sub' => 'Đơn nhập · ' . $po->order_date->format('d/m/Y')
                    . ' · ' . number_format($po->total_amount, 0, ',', '.') . 'đ',
                'url' => route('purchase.show', $po),
                'badge' => $po->status,
            ]);

        // Khách hàng
        Customer::where('pharmacy_id', $pharmacyId)
            ->where(fn($sq) => $sq->where('name', 'like', "%$q%")
                ->orWhere('phone', 'like', "%$q%"))
            ->limit(3)->get()
            ->each(fn($c) => $results[] = [
                'type' => 'customer',
                'icon' => '👤',
                'title' => $c->name,
                'sub' => 'Khách hàng · ' . ($c->phone ?? 'Không có SĐT'),
                'url' => route('customers.show', $c),
                'badge' => '',
            ]);

        // Lô hàng (batch)
        Batch::whereHas('medicine', fn($m) => $m->where('pharmacy_id', $pharmacyId))
            ->where(fn($sq) => $sq->where('batch_number', 'like', "%$q%"))
            ->with('medicine:id,name')
            ->limit(3)->get()
            ->each(fn($b) => $results[] = [
                'type' => 'batch',
                'icon' => '🏷️',
                'title' => 'Lô ' . $b->batch_number,
                'sub' => ($b->medicine?->name ?? '') . ' · HSD: ' . $b->expiry_date->format('d/m/Y')
                    . ' · Tồn: ' . number_format($b->current_quantity),
                'url' => route('inventory.batches', $b->medicine_id),
                'badge' => $b->days_until_expiry <= 30 ? '⚠️ Sắp HH' : '',
            ]);

        return response()->json([
            'results' => $results,
            'total' => count($results),
            'query' => $q,
        ]);
    }
}