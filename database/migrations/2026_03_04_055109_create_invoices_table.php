<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Hóa đơn bán hàng gồm 2 bảng:
     *
     * invoices       = Header (thông tin tổng hóa đơn)
     * invoice_items  = Chi tiết từng dòng thuốc + LÔ CỤ THỂ đã xuất
     *
     * ĐIỂM THEN CHỐT của thiết kế:
     * invoice_items.batch_id → biết CHÍNH XÁC lô nào đã xuất
     *
     * Tại sao quan trọng?
     * 1. Hủy hóa đơn → hoàn tồn kho về ĐÚNG lô (không hoàn nhầm)
     * 2. Truy xuất nguồn gốc: "Thuốc này bán cho ai, từ lô nào?"
     * 3. Tính lãi gộp chính xác: sell_price - purchase_price (của lô đó)
     *
     * Các cột expiry_date, batch_number, purchase_price trong invoice_items
     * là SNAPSHOT từ batch → Phòng trường hợp batch bị xóa/sửa sau này.
     */
    public function up(): void
    {
        // ── invoices ───────────────────────────────────────────────────────────
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Liên kết
            $table->foreignId('pharmacy_id')->constrained('pharmacies')->cascadeOnDelete();
            $table->foreignId('customer_id')
                ->nullable()                                 // NULL = khách vãng lai
                ->constrained('customers')
                ->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();

            // Định danh
            $table->string('code', 30)->unique();              // INV-20250115-0001

            // Trạng thái
            $table->enum('status', [
                'completed',  // Hoàn thành
                'cancelled',  // Đã hủy (và đã hoàn kho)
                'refunded',   // Đã hoàn trả một phần
            ])->default('completed');

            // Thời gian
            $table->timestamp('invoice_date');                 // Thời điểm tạo hóa đơn

            // ── Tiền ───────────────────────────────────────────────────────────
            $table->decimal('subtotal', 18, 2)->default(0);    // Tổng tiền hàng (trước CK)
            $table->decimal('discount_amount', 18, 2)->default(0); // Chiết khấu tổng đơn
            $table->decimal('total_amount', 18, 2)->default(0);    // = subtotal - discount
            $table->decimal('paid_amount', 18, 2)->default(0);     // Tiền KH đưa / đã thanh toán
            $table->decimal('change_amount', 18, 2)->default(0);   // Tiền thối lại
            $table->decimal('debt_amount', 18, 2)->default(0);     // Số tiền ghi nợ

            // ── Thanh toán ─────────────────────────────────────────────────────
            $table->enum('payment_method', [
                'cash',       // Tiền mặt
                'card',       // Thẻ ngân hàng
                'transfer',   // Chuyển khoản
                'debt',       // Ghi nợ
                'mixed',      // Kết hợp (tiền mặt + nợ)
            ])->default('cash');

            // ── Yêu cầu GPP ────────────────────────────────────────────────────
            // Thuốc kê đơn BẮT BUỘC phải có mã đơn thuốc
            $table->string('prescription_code', 50)->nullable();
            $table->string('doctor_name', 100)->nullable();    // Tên bác sĩ kê đơn

            // ── Hủy đơn ────────────────────────────────────────────────────────
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── Index ──────────────────────────────────────────────────────────
            $table->index(['pharmacy_id', 'invoice_date']);    // Báo cáo theo ngày
            $table->index(['pharmacy_id', 'status']);
            $table->index('customer_id');
            $table->index('created_by');                       // Báo cáo theo nhân viên
        });


    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};