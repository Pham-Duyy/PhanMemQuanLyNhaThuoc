<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\CashTransaction;
use App\Models\PurchaseOrder;
use App\Models\StockAdjustment;
use App\View\Composers\SidebarBadgesComposer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // ══════════════════════════════════════════════════════════════════════════
    // Dashboard chính
    // ══════════════════════════════════════════════════════════════════════════

    public function index()
    {
        $pharmacyId = auth()->user()->pharmacy_id;

        // ── A. KPI hôm nay (cache 2 phút — cần tương đối real-time) ──────────
        $todayKpi = Cache::remember("dashboard_today_{$pharmacyId}", 120, function () use ($pharmacyId) {
            $revenue = Invoice::where('pharmacy_id', $pharmacyId)
                ->completed()->today()->sum('total_amount');

            $invoiceCount = Invoice::where('pharmacy_id', $pharmacyId)
                ->completed()->today()->count();

            // Lãi gộp hôm nay
            $grossProfit = InvoiceItem::whereHas(
                'invoice',
                fn($q) =>
                $q->where('pharmacy_id', $pharmacyId)->completed()->today()
            )
                ->selectRaw('SUM((sell_price - purchase_price) * quantity) as gp')
                ->value('gp') ?? 0;

            // Tiền mặt thực thu hôm nay (cash + transfer)
            $cashIn = CashTransaction::where('pharmacy_id', $pharmacyId)
                ->where('type', 'receipt')
                ->whereIn('category', ['sale', 'debt_receipt'])
                ->today()
                ->sum('amount');

            $newCustomers = Customer::where('pharmacy_id', $pharmacyId)
                ->whereDate('created_at', today())->count();

            // So sánh với hôm qua (% tăng/giảm)
            $yesterdayRevenue = Invoice::where('pharmacy_id', $pharmacyId)
                ->completed()
                ->whereDate('invoice_date', now()->subDay()->toDateString())
                ->sum('total_amount');

            $revenueGrowth = $yesterdayRevenue > 0
                ? round(($revenue - $yesterdayRevenue) / $yesterdayRevenue * 100, 1)
                : null;

            return compact(
                'revenue',
                'invoiceCount',
                'grossProfit',
                'cashIn',
                'newCustomers',
                'revenueGrowth',
                'yesterdayRevenue'
            );
        });

        // ── B. Thống kê tháng này (cache 10 phút) ─────────────────────────────
        $monthKpi = Cache::remember("dashboard_month_{$pharmacyId}", 600, function () use ($pharmacyId) {
            $monthStart = now()->startOfMonth();
            $monthEnd = now()->endOfMonth();

            $revenue = Invoice::where('pharmacy_id', $pharmacyId)
                ->completed()
                ->whereBetween('invoice_date', [$monthStart, $monthEnd])
                ->sum('total_amount');

            $invoiceCount = Invoice::where('pharmacy_id', $pharmacyId)
                ->completed()
                ->whereBetween('invoice_date', [$monthStart, $monthEnd])
                ->count();

            $grossProfit = InvoiceItem::whereHas(
                'invoice',
                fn($q) =>
                $q->where('pharmacy_id', $pharmacyId)
                    ->completed()
                    ->whereBetween('invoice_date', [$monthStart, $monthEnd])
            )
                ->selectRaw('SUM((sell_price - purchase_price) * quantity) as gp')
                ->value('gp') ?? 0;

            // Tổng công nợ khách hàng
            $totalCustomerDebt = Customer::where('pharmacy_id', $pharmacyId)
                ->sum('current_debt');

            // Tổng công nợ nhà cung cấp
            $totalSupplierDebt = Supplier::where('pharmacy_id', $pharmacyId)
                ->sum('current_debt');

            // Tổng giá trị tồn kho
            $inventoryValue = Batch::whereHas('medicine', fn($q) =>
                $q->where('pharmacy_id', $pharmacyId))
                ->where('is_active', true)
                ->where('is_expired', false)
                ->selectRaw('SUM(current_quantity * purchase_price) as total')
                ->value('total') ?? 0;

            // Tháng trước để so sánh
            $lastMonthRevenue = Invoice::where('pharmacy_id', $pharmacyId)
                ->completed()
                ->whereBetween('invoice_date', [
                    now()->subMonth()->startOfMonth(),
                    now()->subMonth()->endOfMonth(),
                ])
                ->sum('total_amount');

            $monthGrowth = $lastMonthRevenue > 0
                ? round(($revenue - $lastMonthRevenue) / $lastMonthRevenue * 100, 1)
                : null;

            return compact(
                'revenue',
                'invoiceCount',
                'grossProfit',
                'totalCustomerDebt',
                'totalSupplierDebt',
                'inventoryValue',
                'monthGrowth',
                'lastMonthRevenue'
            );
        });

        // ── C. Biểu đồ doanh thu 30 ngày (cache 10 phút) ─────────────────────
        $revenueChartData = Cache::remember("dashboard_chart30_{$pharmacyId}", 600, function () use ($pharmacyId) {
            $raw = Invoice::where('pharmacy_id', $pharmacyId)
                ->selectRaw('
                    DATE(invoice_date)                                     as date,
                    COUNT(*)                                               as invoice_count,
                    SUM(total_amount)                                      as revenue,
                    SUM(paid_amount)                                       as collected
                ')
                ->completed()
                ->where('invoice_date', '>=', now()->subDays(29)->startOfDay())
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            // Lãi gộp mỗi ngày (query riêng — join nặng)
            $profitRaw = InvoiceItem::selectRaw('
                    DATE(invoices.invoice_date) as date,
                    SUM((invoice_items.sell_price - invoice_items.purchase_price) * invoice_items.quantity) as profit
                ')
                ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
                ->where('invoices.pharmacy_id', $pharmacyId)
                ->where('invoices.status', 'completed')
                ->where('invoices.invoice_date', '>=', now()->subDays(29)->startOfDay())
                ->groupBy('date')
                ->get()
                ->keyBy('date');

            $labels = [];
            $revenues = [];
            $profits = [];
            $counts = [];

            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $labels[] = now()->subDays($i)->format('d/m');
                $revenues[] = (float) ($raw[$date]->revenue ?? 0);
                $profits[] = (float) ($profitRaw[$date]->profit ?? 0);
                $counts[] = (int) ($raw[$date]->invoice_count ?? 0);
            }

            return compact('labels', 'revenues', 'profits', 'counts');
        });

        // ── D. Biểu đồ doanh thu 12 tháng + so sánh năm ngoái (cache 1 giờ) ──
        $monthlyChartData = Cache::remember("dashboard_chart12m_{$pharmacyId}", 3600, function () use ($pharmacyId) {
            // Năm nay
            $raw = Invoice::where('pharmacy_id', $pharmacyId)
                ->selectRaw('
                    YEAR(invoice_date)  as year,
                    MONTH(invoice_date) as month,
                    SUM(total_amount)   as revenue,
                    COUNT(*)            as invoice_count
                ')
                ->completed()
                ->whereYear('invoice_date', now()->year)
                ->groupBy('year', 'month')
                ->orderBy('year')->orderBy('month')
                ->get()
                ->keyBy(fn($r) => $r->year . '-' . str_pad($r->month, 2, '0', STR_PAD_LEFT));

            // Năm ngoái
            $rawLastYear = Invoice::where('pharmacy_id', $pharmacyId)
                ->selectRaw('
                    YEAR(invoice_date)  as year,
                    MONTH(invoice_date) as month,
                    SUM(total_amount)   as revenue
                ')
                ->completed()
                ->whereYear('invoice_date', now()->year - 1)
                ->groupBy('year', 'month')
                ->get()
                ->keyBy(fn($r) => $r->year . '-' . str_pad($r->month, 2, '0', STR_PAD_LEFT));

            $labels = [];
            $revenues = [];
            $lastYearRevs = [];
            $profits = [];

            // Lãi gộp theo tháng năm nay
            $profitRaw = InvoiceItem::selectRaw('
                    MONTH(invoices.invoice_date) as month,
                    SUM((invoice_items.sell_price - invoice_items.purchase_price) * invoice_items.quantity) as profit
                ')
                ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
                ->where('invoices.pharmacy_id', $pharmacyId)
                ->where('invoices.status', 'completed')
                ->whereYear('invoices.invoice_date', now()->year)
                ->groupBy('month')
                ->get()->keyBy('month');

            for ($m = 1; $m <= 12; $m++) {
                $key = now()->year . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
                $keyLast = (now()->year - 1) . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
                $labels[] = Carbon::createFromDate(now()->year, $m, 1)->translatedFormat('M') . '/' . now()->year;
                $revenues[] = (float) ($raw[$key]->revenue ?? 0);
                $lastYearRevs[] = (float) ($rawLastYear[$keyLast]->revenue ?? 0);
                $profits[] = (float) ($profitRaw[$m]->profit ?? 0);
            }

            $totalThisYear = array_sum($revenues);
            $totalLastYear = array_sum($lastYearRevs);
            $yearGrowth = $totalLastYear > 0
                ? round(($totalThisYear - $totalLastYear) / $totalLastYear * 100, 1)
                : null;

            return compact('labels', 'revenues', 'lastYearRevs', 'profits', 'totalThisYear', 'totalLastYear', 'yearGrowth');
        });

        // ── E. Top 10 thuốc bán chạy tháng này (cache 15 phút) ───────────────
        $topMedicines = Cache::remember("dashboard_top10_{$pharmacyId}", 900, function () use ($pharmacyId) {
            return InvoiceItem::selectRaw('
                        medicine_id,
                        SUM(quantity)                                              as total_sold,
                        SUM(total_amount)                                          as revenue,
                        SUM((sell_price - purchase_price) * quantity)              as gross_profit
                    ')
                ->whereHas(
                    'invoice',
                    fn($q) =>
                    $q->where('pharmacy_id', $pharmacyId)
                        ->completed()
                        ->whereMonth('invoice_date', now()->month)
                        ->whereYear('invoice_date', now()->year)
                )
                ->with('medicine:id,name,unit,code,category_id', 'medicine.category:id,name')
                ->groupBy('medicine_id')
                ->orderByDesc('revenue')
                ->limit(10)
                ->get();
        });

        // ── F. Cảnh báo lô sắp hết hạn (cache 5 phút) ────────────────────────
        $expiringBatches = Cache::remember("dashboard_expiring_{$pharmacyId}", 300, function () use ($pharmacyId) {
            return Batch::with('medicine:id,name,unit,pharmacy_id')
                ->whereHas('medicine', fn($q) => $q->where('pharmacy_id', $pharmacyId))
                ->expiringSoon(30)
                ->orderBy('expiry_date')
                ->limit(8)
                ->get();
        });

        // ── G. Cảnh báo thuốc sắp hết tồn (cache 5 phút) ────────────────────
        $lowStockMedicines = Cache::remember("dashboard_lowstock_{$pharmacyId}", 300, function () use ($pharmacyId) {
            return Medicine::where('pharmacy_id', $pharmacyId)
                ->where('is_active', true)
                ->lowStock()           // dùng scope đã fix (whereRaw subquery)
                ->with([
                    'batches' => fn($q) =>
                        $q->where('is_active', true)->where('is_expired', false)
                ])
                ->orderByRaw('
                    COALESCE((
                        SELECT SUM(b.current_quantity)
                        FROM batches b
                        WHERE b.medicine_id = medicines.id
                          AND b.is_active   = 1
                          AND b.is_expired  = 0
                          AND b.deleted_at IS NULL
                    ), 0) ASC
                ')
                ->limit(8)
                ->get();
        });

        // ── H. Đơn nhập hàng chờ duyệt (cache 2 phút) ────────────────────────
        $pendingOrders = Cache::remember("dashboard_pending_{$pharmacyId}", 120, function () use ($pharmacyId) {
            return PurchaseOrder::where('pharmacy_id', $pharmacyId)
                ->pending()
                ->with('supplier:id,name')
                ->withSum('items as total_items', 'ordered_quantity')
                ->latest('order_date')
                ->limit(5)
                ->get();
        });

        // ── I. Hoạt động gần đây — last 10 actions (cache 1 phút) ────────────
        $recentActivity = Cache::remember("dashboard_activity_{$pharmacyId}", 60, function () use ($pharmacyId) {
            $invoices = Invoice::where('pharmacy_id', $pharmacyId)
                ->completed()
                ->with('createdBy:id,name', 'customer:id,name')
                ->latest('invoice_date')
                ->limit(5)
                ->get()
                ->map(fn($inv) => [
                    'type' => 'invoice',
                    'icon' => '🧾',
                    'color' => 'success',
                    'title' => "HĐ {$inv->code}",
                    'desc' => ($inv->customer?->name ?? 'Khách lẻ') .
                        ' — ' . number_format($inv->total_amount, 0, ',', '.') . 'đ',
                    'user' => $inv->createdBy?->name,
                    'time' => $inv->invoice_date,
                    'route' => route('invoices.show', $inv),
                ]);

            $purchases = PurchaseOrder::where('pharmacy_id', $pharmacyId)
                ->whereIn('status', ['received', 'pending', 'approved'])
                ->with('createdBy:id,name', 'supplier:id,name')
                ->latest('order_date')
                ->limit(3)
                ->get()
                ->map(fn($po) => [
                    'type' => 'purchase',
                    'icon' => '📦',
                    'color' => 'primary',
                    'title' => "ĐNH {$po->code}",
                    'desc' => $po->supplier?->name .
                        ' — ' . $po->status_label,
                    'user' => $po->createdBy?->name,
                    'time' => $po->order_date->startOfDay(),
                    'route' => route('purchase.show', $po),
                ]);

            $adjustments = StockAdjustment::where('pharmacy_id', $pharmacyId)
                ->with('createdBy:id,name', 'medicine:id,name')
                ->latest('adjustment_date')
                ->limit(3)
                ->get()
                ->map(fn($adj) => [
                    'type' => 'adjustment',
                    'icon' => '⚙️',
                    'color' => 'warning',
                    'title' => 'Điều chỉnh kho',
                    'desc' => $adj->medicine?->name .
                        ' (' . $adj->type_label . ')',
                    'user' => $adj->createdBy?->name,
                    'time' => $adj->adjustment_date,
                    'route' => route('inventory.index'),
                ]);

            return $invoices->concat($purchases)->concat($adjustments)
                ->sortByDesc('time')
                ->take(10)
                ->values();
        });

        // ── J. Phân tích doanh thu theo giờ hôm nay (cache 5 phút) ───────────
        $hourlyToday = Cache::remember("dashboard_hourly_{$pharmacyId}", 300, function () use ($pharmacyId) {
            $raw = Invoice::where('pharmacy_id', $pharmacyId)
                ->selectRaw('HOUR(invoice_date) as hour, COUNT(*) as cnt, SUM(total_amount) as total')
                ->completed()
                ->today()
                ->groupBy('hour')
                ->get()
                ->keyBy('hour');

            $labels = [];
            $revenues = [];
            for ($h = 7; $h <= 21; $h++) {    // 7h–21h khung giờ nhà thuốc
                $labels[] = "{$h}h";
                $revenues[] = (float) ($raw[$h]->total ?? 0);
            }

            return compact('labels', 'revenues');
        });

        return view('dashboard', compact(
            'todayKpi',
            'monthKpi',
            'revenueChartData',
            'monthlyChartData',
            'topMedicines',
            'expiringBatches',
            'lowStockMedicines',
            'pendingOrders',
            'recentActivity',
            'hourlyToday',
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // API endpoint: xóa cache dashboard (gọi sau khi lưu dữ liệu quan trọng)
    // Route: POST /dashboard/refresh-cache
    // ══════════════════════════════════════════════════════════════════════════

    public function refreshCache()
    {
        $pharmacyId = auth()->user()->pharmacy_id;

        $keys = [
            "dashboard_today_{$pharmacyId}",
            "dashboard_month_{$pharmacyId}",
            "dashboard_chart30_{$pharmacyId}",
            "dashboard_chart12m_{$pharmacyId}",
            "dashboard_top5_{$pharmacyId}",
            "dashboard_expiring_{$pharmacyId}",
            "dashboard_lowstock_{$pharmacyId}",
            "dashboard_pending_{$pharmacyId}",
            "dashboard_activity_{$pharmacyId}",
            "dashboard_hourly_{$pharmacyId}",
            "sidebar_badges_{$pharmacyId}",
            "pharmacy_{$pharmacyId}",
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        // Xóa sidebar badge cache
        SidebarBadgesComposer::clearCache($pharmacyId);

        return response()->json(['ok' => true, 'cleared' => count($keys)]);
    }
}