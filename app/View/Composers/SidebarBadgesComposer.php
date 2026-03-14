<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use App\Models\Batch;
use App\Models\Medicine;
use App\Models\PurchaseOrder;
use App\Models\Customer;
use App\Models\Invoice;

/**
 * SidebarBadgesComposer
 *
 * Inject số badge cảnh báo vào sidebar layout.
 * Được cache 5 phút để tránh N+1 queries mỗi request.
 *
 * Đăng ký trong AppServiceProvider:
 *   View::composer('layouts.app', SidebarBadgesComposer::class);
 *
 * Dùng trong layouts/app.blade.php:
 *   @if($sidebarBadges['expiring'] > 0)
 *       <span class="badge bg-danger">{{ $sidebarBadges['expiring'] }}</span>
 *   @endif
 */
class SidebarBadgesComposer
{
    /**
     * Cache TTL — sidebar không cần refresh theo thời gian thực.
     * Tăng lên 10 phút nếu server bận.
     */
    private const CACHE_MINUTES = 5;

    public function compose(View $view): void
    {
        if (!auth()->check()) {
            $view->with('sidebarBadges', $this->emptyBadges());
            return;
        }

        $pharmacyId = auth()->user()->pharmacy_id;
        $cacheKey = "sidebar_badges_{$pharmacyId}";

        $badges = Cache::remember(
            $cacheKey,
            now()->addMinutes(self::CACHE_MINUTES),
            fn() => $this->computeBadges($pharmacyId)
        );

        $view->with('sidebarBadges', $badges);
    }

    /**
     * Tính toán tất cả badge counts.
     * Mỗi query được tối ưu để chỉ SELECT COUNT(*).
     */
    private function computeBadges(int $pharmacyId): array
    {
        // ── 1. Lô sắp hết hạn (≤ 30 ngày) ──────────────────────────────────
        $expiring = Batch::whereHas('medicine', fn($q) =>
            $q->where('pharmacy_id', $pharmacyId))
            ->where('is_expired', false)
            ->where('current_quantity', '>', 0)
            ->where('expiry_date', '<=', now()->addDays(30)->toDateString())
            ->where('expiry_date', '>=', now()->toDateString())
            ->count();

        // ── 2. Lô đã hết hạn còn tồn kho (cần xử lý khẩn) ─────────────────
        $expiredWithStock = Batch::whereHas('medicine', fn($q) =>
            $q->where('pharmacy_id', $pharmacyId))
            ->where('is_expired', true)
            ->where('current_quantity', '>', 0)
            ->count();

        // ── 3. Thuốc dưới mức tồn tối thiểu ─────────────────────────────────
        // Dùng whereRaw + correlated subquery thay havingRaw để tránh lỗi
        // MySQL ONLY_FULL_GROUP_BY / non-grouping field in HAVING
        $lowStock = Medicine::where('pharmacy_id', $pharmacyId)
            ->where('is_active', true)
            ->where('min_stock', '>', 0)
            ->whereRaw('
                min_stock >= COALESCE((
                    SELECT SUM(b.current_quantity)
                    FROM batches b
                    WHERE b.medicine_id = medicines.id
                      AND b.is_active   = 1
                      AND b.is_expired  = 0
                      AND b.deleted_at IS NULL
                ), 0)
            ')
            ->count();

        // ── 4. Đơn nhập hàng chờ duyệt ──────────────────────────────────────
        $pendingOrders = PurchaseOrder::where('pharmacy_id', $pharmacyId)
            ->where('status', 'pending')
            ->count();

        // ── 5. Khách hàng vượt hạn mức nợ ───────────────────────────────────
        $overDebtCustomers = Customer::where('pharmacy_id', $pharmacyId)
            ->where('debt_limit', '>', 0)
            ->whereColumn('current_debt', '>=', 'debt_limit')
            ->count();

        // ── 6. Hóa đơn hôm nay (info badge, không phải cảnh báo) ────────────
        $todayInvoices = Invoice::where('pharmacy_id', $pharmacyId)
            ->completed()
            ->today()
            ->count();

        // ── Tổng số cảnh báo cần xử lý ngay ─────────────────────────────────
        $totalAlerts = $expiring + $expiredWithStock + $lowStock
            + $pendingOrders + $overDebtCustomers;

        return [
            'expiring' => $expiring,
            'expired_with_stock' => $expiredWithStock,
            'low_stock' => $lowStock,
            'pending_orders' => $pendingOrders,
            'over_debt_customers' => $overDebtCustomers,
            'today_invoices' => $todayInvoices,
            'total_alerts' => $totalAlerts,   // badge tổng trên bell icon
        ];
    }

    private function emptyBadges(): array
    {
        return [
            'expiring' => 0,
            'expired_with_stock' => 0,
            'low_stock' => 0,
            'pending_orders' => 0,
            'over_debt_customers' => 0,
            'today_invoices' => 0,
            'total_alerts' => 0,
        ];
    }

    /**
     * Xóa cache badge khi có thay đổi tồn kho / đơn hàng.
     * Gọi từ các Controller sau khi lưu dữ liệu:
     *   SidebarBadgesComposer::clearCache(auth()->user()->pharmacy_id);
     */
    public static function clearCache(int $pharmacyId): void
    {
        Cache::forget("sidebar_badges_{$pharmacyId}");
    }
}