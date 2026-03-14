<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MedicineCategoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Purchase\PurchaseOrderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\CashTransactionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReturnInvoiceController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\CheckinController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ── Auth routes (do Laravel UI tạo sẵn) ──────────────────────────────────
Auth::routes();

// ── Redirect root ─────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('dashboard'));

// ── Tất cả routes yêu cầu đăng nhập + có pharmacy ────────────────────────
Route::middleware(['auth', 'pharmacy'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // ── DANH MỤC THUỐC ────────────────────────────────────────────────────
    Route::middleware('permission:medicine.view')->group(function () {
        Route::resource('medicines', MedicineController::class)
            ->except(['destroy']);

        Route::delete('medicines/{medicine}', [MedicineController::class, 'destroy'])
            ->name('medicines.destroy')
            ->middleware('permission:medicine.edit');

        // In nhãn thuốc
        Route::get('medicines/{medicine}/label', [MedicineController::class, 'label'])
            ->name('medicines.label');
        Route::get('medicines/{medicine}/label/print', [MedicineController::class, 'printLabel'])
            ->name('medicines.label.print');

        // Toggle active/inactive
        Route::patch('medicines/{medicine}/toggle', [MedicineController::class, 'toggle'])
            ->name('medicines.toggle')
            ->middleware('permission:medicine.edit');
    });

    // Nhóm thuốc
    Route::middleware('permission:medicine.view')->group(function () {
        Route::resource('medicine-categories', MedicineCategoryController::class)
            ->names('categories');
    });

    // ── KHO HÀNG ──────────────────────────────────────────────────────────
    Route::middleware('permission:inventory.view')
        ->prefix('inventory')
        ->name('inventory.')
        ->group(function () {
            Route::get('/', [InventoryController::class, 'index'])->name('index');

            // Xem danh sách lô của 1 thuốc
            Route::get('/medicine/{medicine}/batches', [InventoryController::class, 'batches'])
                ->name('batches');

            // Điều chỉnh tồn kho (cần quyền cao hơn)
            Route::get('/adjust', [InventoryController::class, 'createAdjust'])
                ->name('adjust.create')
                ->middleware('permission:inventory.adjust');

            Route::post('/adjust', [InventoryController::class, 'storeAdjust'])
                ->name('adjust.store')
                ->middleware('permission:inventory.adjust');

            // Cảnh báo
            Route::get('/expiring', [InventoryController::class, 'expiring'])->name('expiring');
            Route::get('/low-stock', [InventoryController::class, 'lowStock'])->name('low-stock');
        });

    // ── NHÀ CUNG CẤP ──────────────────────────────────────────────────────
    Route::middleware('permission:supplier.view')->group(function () {
        Route::resource('suppliers', SupplierController::class);
        Route::post('suppliers/{supplier}/pay-debt', [SupplierController::class, 'payDebt'])
            ->name('suppliers.pay-debt')
            ->middleware('permission:purchase.create');
    });

    // ── KHÁCH HÀNG ────────────────────────────────────────────────────────
    Route::middleware('permission:customer.view')->group(function () {
        Route::resource('customers', CustomerController::class);
    });

    // ── NHẬP HÀNG ─────────────────────────────────────────────────────────
    Route::middleware('permission:purchase.view')
        ->prefix('purchase-orders')
        ->name('purchase.')
        ->group(function () {
            Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');

            Route::get('/create', [PurchaseOrderController::class, 'create'])
                ->name('create')
                ->middleware('permission:purchase.create');

            Route::post('/', [PurchaseOrderController::class, 'store'])
                ->name('store')
                ->middleware('permission:purchase.create');

            // Thêm thuốc mới nhanh từ form đặt hàng (AJAX)
            Route::post('/quick-create-medicine', [PurchaseOrderController::class, 'quickCreateMedicine'])
                ->name('quick-create-medicine')
                ->middleware('permission:purchase.create');

            Route::get('/{purchaseOrder}', [PurchaseOrderController::class, 'show'])
                ->name('show');

            // Duyệt đơn
            Route::patch('/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])
                ->name('approve')
                ->middleware('permission:purchase.approve');

            // Nhận hàng → Tạo batch
            Route::get('/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receiveForm'])
                ->name('receive.form')
                ->middleware('permission:purchase.receive');

            Route::post('/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])
                ->name('receive')
                ->middleware('permission:purchase.receive');

            // Hủy đơn
            Route::patch('/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])
                ->name('cancel')
                ->middleware('permission:purchase.create');
        });

    // ── BÁN HÀNG ──────────────────────────────────────────────────────────
    Route::middleware('permission:invoice.view')->group(function () {
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    });

    Route::middleware('permission:invoice.create')->group(function () {
        Route::get('/pos', [InvoiceController::class, 'pos'])->name('invoices.pos');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    });

    Route::patch('/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])
        ->name('invoices.cancel')
        ->middleware('permission:invoice.cancel');

    // ── SỔ QUỸ ────────────────────────────────────────────────────────────
    Route::middleware('permission:cash.view')->group(function () {
        Route::get('/cash', [CashTransactionController::class, 'index'])->name('cash.index');

        Route::post('/cash', [CashTransactionController::class, 'store'])
            ->name('cash.store')
            ->middleware('permission:cash.create');
    });

    // ── BÁO CÁO ───────────────────────────────────────────────────────────
    Route::middleware('permission:report.view')
        ->prefix('reports')
        ->name('reports.')
        ->group(function () {
            Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
            Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
            Route::get('/debt', [ReportController::class, 'debt'])->name('debt');

            // Export
            Route::get('/revenue/export', [ReportController::class, 'exportRevenue'])
                ->name('revenue.export')
                ->middleware('permission:report.export');

            Route::get('/inventory/export', [ReportController::class, 'exportInventory'])
                ->name('inventory.export')
                ->middleware('permission:report.export');

            Route::get('/debt/export', [ReportController::class, 'exportDebt'])
                ->name('debt.export')
                ->middleware('permission:report.export');
        });

    // ── QUẢN LÝ NGƯỜI DÙNG ────────────────────────────────────────────────
    Route::middleware('permission:user.view')->group(function () {
        Route::resource('users', UserController::class);
        // Admin reset mật khẩu không cần email
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->name('users.reset-password')
            ->middleware('permission:user.edit');

        // ── Ca làm việc ─────────────────────────────────────────────────
        Route::prefix('shifts')->name('shifts.')->group(function () {
            // Nhân viên xem lịch của mình (không cần permission đặc biệt)
            Route::get('/my-schedule', [ShiftController::class, 'mySchedule'])->name('my-schedule');

            // Manager xếp ca, báo cáo
            Route::middleware('permission:user.view')->group(function () {
                Route::get('/', [ShiftController::class, 'index'])->name('index');
                Route::get('/manage', [ShiftController::class, 'shifts'])->name('manage');
                Route::get('/attendance', [ShiftController::class, 'attendance'])->name('attendance');
                Route::get('/payroll', [ShiftController::class, 'payroll'])->name('payroll');
            });

            Route::middleware('permission:user.edit')->group(function () {
                Route::post('/assign', [ShiftController::class, 'assign'])->name('assign');
                Route::post('/auto-assign', [ShiftController::class, 'autoAssign'])->name('auto-assign');
                Route::patch('/{assignment}/status', [ShiftController::class, 'updateStatus'])->name('update-status');
                Route::delete('/{assignment}', [ShiftController::class, 'removeAssignment'])->name('remove');
                Route::post('/generate-payroll', [ShiftController::class, 'generatePayroll'])->name('generate-payroll');
                Route::patch('/payroll/{payroll}/confirm', [ShiftController::class, 'confirmPayroll'])->name('confirm-payroll');
                Route::post('/manage', [ShiftController::class, 'storeShift'])->name('store-shift');
                Route::patch('/manage/{shift}', [ShiftController::class, 'updateShift'])->name('update-shift');
            });
        });
    });

    // ── Ca làm việc ──────────────────────────────────────────────────────────

    // ── Chấm công PIN ─────────────────────────────────────────────────────────


    // ── Ca làm việc ──────────────────────────────────────────────────────────

    // ── Chấm công PIN ─────────────────────────────────────────────────────────
    Route::prefix('checkin')->name('checkin.')->group(function () {
        Route::get('/', [CheckinController::class, 'index'])->name('index');
        Route::post('/check-in', [CheckinController::class, 'checkIn'])->name('check-in');
        Route::post('/check-out', [CheckinController::class, 'checkOut'])->name('check-out');
        Route::get('/history', [CheckinController::class, 'history'])->name('history')
            ->middleware('permission:user.view');
        Route::post('/users/{user}/set-pin', [CheckinController::class, 'setPin'])
            ->name('set-pin')->middleware('permission:user.edit');
    });

    // ── API NỘI BỘ (AJAX từ Blade) ────────────────────────────────────────
    // ── HỒ SƠ CÁ NHÂN ────────────────────────────────────────────────────────
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');

    // ── IN PHIẾU NHẬP HÀNG ───────────────────────────────────────────────────
    Route::get('/purchase/{purchaseOrder}/print', [PurchaseOrderController::class, 'print'])
        ->name('purchase.print')
        ->middleware('permission:purchase.view');

    // ── Phiếu trả hàng ────────────────────────────────────────────────────────
    Route::resource('returns', ReturnInvoiceController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/returns/{return}/print', [ReturnInvoiceController::class, 'print'])
        ->name('returns.print');

    // ── Báo cáo xuất nhập tồn (Ledger) ────────────────────────────────────────
    Route::get('/reports/ledger', [ReportController::class, 'ledger'])
        ->name('reports.ledger')
        ->middleware('permission:report.view');

    Route::get('/reports/debt-detail', [ReportController::class, 'debtDetail'])
        ->name('reports.debt.detail')
        ->middleware('permission:report.view');

    // ── Lịch sử hoạt động ─────────────────────────────────────────────────────
    Route::get('/activity', [ActivityLogController::class, 'index'])
        ->name('activity.index')
        ->middleware('permission:user.view');

    // ── Global Search API ──────────────────────────────────────────────────────
    Route::prefix('api')->name('api.')->group(function () {
        // Tìm thuốc cho POS (trả về JSON)
        Route::get('/medicines/search', [MedicineController::class, 'search'])
            ->name('medicines.search');

        // Lấy thông tin tồn kho + lô của 1 thuốc
        Route::get('/medicines/{medicine}/stock', [MedicineController::class, 'stockInfo'])
            ->name('medicines.stock');

        // Lấy danh sách lô của 1 thuốc (cho adjust form)
        Route::get('/medicines/{medicine}/batches', [InventoryController::class, 'batchesApi'])
            ->name('medicines.batches');

        // Tìm khách hàng
        Route::get('/customers/search', [CustomerController::class, 'search'])
            ->name('customers.search');

        // Tạo nhanh khách hàng từ POS
        Route::post('/customers/quick-create', [CustomerController::class, 'quickCreate'])
            ->name('customers.quick-create');

        // Global search
        Route::get('/search', [GlobalSearchController::class, 'search'])
            ->name('global.search');
    });
});