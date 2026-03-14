<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Đơn đặt hàng (Purchase Order) gồm 2 bảng:
     *
     * purchase_orders      = Header (thông tin tổng đơn)
     * purchase_order_items = Chi tiết từng dòng thuốc
     *
     * State machine của PO:
     * draft → pending → approved → received (hoặc partial)
     *                 ↘ cancelled
     *
     * LƯU Ý: purchase_order_items.batch_id = NULL cho đến khi hàng về.
     * Khi nhận hàng → tạo Batch → gán batch_id vào item.
     */
    public function up(): void
    {
        // ── purchase_orders ────────────────────────────────────────────────────
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();

            // Liên kết
            $table->foreignId('pharmacy_id')->constrained('pharmacies')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();

            // Định danh
            $table->string('code', 30)->unique();              // PO-2025-00001

            // Trạng thái
            $table->enum('status', [
                'draft',      // Nháp - đang soạn
                'pending',    // Chờ duyệt
                'approved',   // Đã duyệt - chờ hàng về
                'received',   // Đã nhận đủ hàng
                'partial',    // Nhận một phần
                'cancelled',  // Đã hủy
            ])->default('draft');

            // Ngày tháng
            $table->date('order_date');                        // Ngày lập đơn
            $table->date('expected_date')->nullable();         // Ngày dự kiến nhận
            $table->timestamp('received_date')->nullable();    // Ngày thực tế nhận
            $table->timestamp('approved_at')->nullable();

            // Tiền
            $table->decimal('subtotal', 18, 2)->default(0);    // Tổng tiền hàng
            $table->decimal('discount_amount', 18, 2)->default(0); // Chiết khấu thương mại
            $table->decimal('total_amount', 18, 2)->default(0);    // Tổng sau chiết khấu
            $table->decimal('paid_amount', 18, 2)->default(0);     // Đã thanh toán
            $table->decimal('vat_amount', 18, 2)->default(0);      // Thuế VAT

            // Ghi chú
            $table->text('note')->nullable();
            $table->string('delivery_address', 255)->nullable(); // Địa chỉ giao hàng

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index(['pharmacy_id', 'status']);
            $table->index(['pharmacy_id', 'order_date']);
            $table->index('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
