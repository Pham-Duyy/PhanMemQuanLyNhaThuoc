<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    // ══════════════════════════════════════════════════════════════════════════
    // register() — bind services, không dùng auth() ở đây
    // ══════════════════════════════════════════════════════════════════════════

    public function register(): void
    {
        //
    }

    // ══════════════════════════════════════════════════════════════════════════
    // boot() — đăng ký mọi thứ sau khi framework khởi động xong
    // ══════════════════════════════════════════════════════════════════════════

    public function boot(): void
    {
        // 1. Pagination dùng Bootstrap 5
        Paginator::useBootstrapFive();

        // 2. Blade directives (permission / role)
        $this->registerBladeDirectives();

        // 3. View composers (sidebar badges, global alerts)
        $this->registerViewComposers();

        // 4. Blade components shortcut (optional)
        $this->registerBladeComponents();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 2. Blade directives
    // ──────────────────────────────────────────────────────────────────────────

    private function registerBladeDirectives(): void
    {
        /**
         * @can('invoice.create')
         *     <a href="...">Tạo HĐ</a>
         * @endcan
         *
         * @can('invoice.create', 'invoice.edit')   ← OR logic
         * @endcan
         */
        Blade::if('can', function (string ...$permissions) {
            if (!auth()->check())
                return false;
            foreach ($permissions as $permission) {
                if (auth()->user()->hasPermission($permission))
                    return true;
            }
            return false;
        });

        /**
         * @cannot('medicine.delete')
         *     <span>Không có quyền</span>
         * @endcannot
         */
        Blade::if('cannot', function (string ...$permissions) {
            if (!auth()->check())
                return true;
            foreach ($permissions as $permission) {
                if (auth()->user()->hasPermission($permission))
                    return false;
            }
            return true;
        });

        /**
         * @role('admin')
         *     ...
         * @endrole
         */
        Blade::if('role', function (string $role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });

        /**
         * @money(1500000)  →  1.500.000đ
         * Dùng trong attribute: {{ $val | money }} không được — dùng directive này
         */
        Blade::directive('money', function (string $expression) {
            return "<?php echo number_format({$expression}, 0, ',', '.') . 'đ'; ?>";
        });

        /**
         * @vnd(1500000)  →  1.500.000 ₫
         */
        Blade::directive('vnd', function (string $expression) {
            return "<?php echo number_format({$expression}, 0, ',', '.') . ' ₫'; ?>";
        });

        /**
         * @date($model->created_at)  →  25/12/2024 14:30
         */
        Blade::directive('date', function (string $expression) {
            return "<?php echo ({$expression})?->format('d/m/Y H:i') ?? '—'; ?>";
        });

        /**
         * @dateonly($model->order_date)  →  25/12/2024
         */
        Blade::directive('dateonly', function (string $expression) {
            return "<?php echo ({$expression})?->format('d/m/Y') ?? '—'; ?>";
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 3. View Composers — inject dữ liệu global vào nhóm view
    // ──────────────────────────────────────────────────────────────────────────

    private function registerViewComposers(): void
    {
        /**
         * SIDEBAR COMPOSER — chạy cho mọi view dùng layout app.blade.php.
         *
         * Inject: số badge cảnh báo để hiển thị bên sidebar menu.
         * Cache 5 phút để tránh query DB mỗi request.
         *
         * Variables injected vào mọi view:
         *   $sidebarBadges = [
         *       'expiring'  => 3,   // lô sắp hết hạn
         *       'lowStock'  => 5,   // thuốc sắp hết tồn
         *       'pending'   => 2,   // đơn nhập chờ duyệt
         *       'debt'      => 1,   // khách hàng vượt hạn mức nợ
         *   ]
         */
        View::composer('layouts.app', function ($view) {
            if (!auth()->check())
                return;

            $user = auth()->user();
            $pharmacyId = $user->pharmacy_id;
            $cacheKey = "sidebar_badges_{$pharmacyId}";

            $badges = cache()->remember($cacheKey, now()->addMinutes(5), function () use ($pharmacyId) {
                // Lô sắp hết hạn trong 30 ngày
                $expiring = \App\Models\Batch::whereHas('medicine', fn($q) =>
                    $q->where('pharmacy_id', $pharmacyId))
                    ->expiringSoon(30)
                    ->count();

                // Thuốc dưới mức tồn tối thiểu
                $lowStock = \App\Models\Medicine::where('pharmacy_id', $pharmacyId)
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

                // Đơn nhập hàng chờ duyệt
                $pending = \App\Models\PurchaseOrder::where('pharmacy_id', $pharmacyId)
                    ->where('status', 'pending')
                    ->count();

                // Khách hàng vượt hạn mức nợ
                $overDebt = \App\Models\Customer::where('pharmacy_id', $pharmacyId)
                    ->where('debt_limit', '>', 0)
                    ->whereColumn('current_debt', '>=', 'debt_limit')
                    ->count();

                return compact('expiring', 'lowStock', 'pending', 'overDebt');
            });

            $view->with('sidebarBadges', $badges);
        });

        /**
         * FLASH ALERTS COMPOSER — chuẩn hóa session flash messages.
         *
         * Hỗ trợ: success | error | warning | info
         * Dùng trong controller: return redirect()->with('success', 'Lưu thành công!');
         *
         * Variables injected:
         *   $flashAlerts = [
         *       ['type' => 'success', 'message' => 'Lưu thành công!', 'icon' => '✅'],
         *       ['type' => 'error',   'message' => 'Đã xảy ra lỗi',  'icon' => '❌'],
         *   ]
         */
        View::composer('*', function ($view) {
            $alerts = [];
            $map = [
                'success' => ['success', '✅'],
                'error' => ['danger', '❌'],
                'warning' => ['warning', '⚠️'],
                'info' => ['info', 'ℹ️'],
            ];

            foreach ($map as $sessionKey => [$bsClass, $icon]) {
                if (session()->has($sessionKey)) {
                    $alerts[] = [
                        'type' => $bsClass,
                        'message' => session($sessionKey),
                        'icon' => $icon,
                    ];
                }
            }

            // Hỗ trợ cả key 'alerts' (array) từ controller
            if (session()->has('alerts') && is_array(session('alerts'))) {
                foreach (session('alerts') as $alert) {
                    $alerts[] = $alert;
                }
            }

            $view->with('flashAlerts', $alerts);
        });

        /**
         * PHARMACY INFO COMPOSER — inject thông tin nhà thuốc hiện tại.
         *
         * Variables injected vào layouts.*:
         *   $currentPharmacy  — object Pharmacy | null
         */
        View::composer('layouts.*', function ($view) {
            if (!auth()->check())
                return;

            $pharmacy = cache()->remember(
                'pharmacy_' . auth()->user()->pharmacy_id,
                now()->addHour(),
                fn() => \App\Models\Pharmacy::find(auth()->user()->pharmacy_id)
            );

            $view->with('currentPharmacy', $pharmacy);
        });

        /**
         * DASHBOARD COMPOSER — dữ liệu riêng cho dashboard view.
         *
         * Không cần controller truyền — composer tự lo.
         * Variables injected vào views/dashboard.blade.php:
         *   $todayStats     — array thống kê hôm nay
         *   $monthStats     — array thống kê tháng
         *   $quickAlerts    — array cảnh báo cần xử lý ngay
         */
        View::composer('dashboard', function ($view) {
            if (!auth()->check())
                return;
            $pharmacyId = auth()->user()->pharmacy_id;

            // Hôm nay
            $todayStats = cache()->remember(
                "dashboard_today_{$pharmacyId}",
                now()->addMinutes(2),   // refresh mỗi 2 phút cho dashboard
                function () use ($pharmacyId) {
                    return [
                        'revenue' => \App\Models\Invoice::completed()->today()
                            ->where('pharmacy_id', $pharmacyId)
                            ->sum('total_amount'),
                        'invoices' => \App\Models\Invoice::completed()->today()
                            ->where('pharmacy_id', $pharmacyId)
                            ->count(),
                        'new_customers' => \App\Models\Customer::where('pharmacy_id', $pharmacyId)
                            ->whereDate('created_at', today())->count(),
                    ];
                }
            );

            $view->with('composerTodayStats', $todayStats);
        });

        /**
         * BREADCRUMB COMPOSER — auto inject route name để layout tô sáng menu.
         *
         * Variables injected vào mọi view:
         *   $currentRouteName  → 'invoices.pos' | 'medicines.index' | ...
         *   $currentModule     → 'invoices' | 'medicines' | 'purchase' | ...
         */
        View::composer('*', function ($view) {
            $routeName = request()->route()?->getName() ?? '';
            $module = explode('.', $routeName)[0] ?? '';

            $view->with([
                'currentRouteName' => $routeName,
                'currentModule' => $module,
            ]);
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 4. Blade anonymous components
    // ──────────────────────────────────────────────────────────────────────────

    private function registerBladeComponents(): void
    {
        /**
         * Đăng ký components trong resources/views/components/
         * Đặt file vào resources/views/components/stat-card.blade.php
         * Dùng: <x-stat-card icon="💰" value="1.5tr" label="Doanh thu" color="primary"/>
         *
         * Không cần đăng ký thủ công nếu dùng Laravel 8+ auto-discovery.
         * Để đây làm tài liệu + có thể thêm alias.
         */

        // Alias ngắn cho các component hay dùng
        // Blade::component('stat-card',    \App\View\Components\StatCard::class);
        // Blade::component('batch-badge',  \App\View\Components\BatchBadge::class);
        // Blade::component('money-display',\App\View\Components\MoneyDisplay::class);
    }
}