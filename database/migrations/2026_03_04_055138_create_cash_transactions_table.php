<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Sổ quỹ tiền mặt (Cash Ledger).
     *
     * Mỗi giao dịch tiền mặt (thu/chi) đều tạo 1 record ở đây.
     *
     * POLYMORPHIC RELATIONSHIP:
     * transactionable_type + transactionable_id cho phép liên kết
     * đến BẤT KỲ nguồn nào (invoice, purchase_order, hoặc thủ công).
     *
     * Ví dụ:
     * - Bán hàng thu tiền:   type=receipt, transactionable=App\Models\Invoice:15
     * - Thanh toán NCC:      type=payment, transactionable=App\Models\PurchaseOrder:8
     * - Chi tiền điện nước:  type=payment, transactionable=NULL (thu chi khác)
     */
    public function up(): void
    {
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();

            // Liên kết
            $table->foreignId('pharmacy_id')->constrained('pharmacies')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');

            // ── Loại giao dịch ─────────────────────────────────────────────────
            $table->enum('type', ['receipt', 'payment']);      // Thu | Chi
            $table->enum('category', [
                'sale',          // Thu từ bán hàng
                'purchase',      // Chi thanh toán nhập hàng
                'debt_receipt',  // Thu nợ từ khách hàng
                'debt_payment',  // Trả nợ cho nhà cung cấp
                'expense',       // Chi phí hoạt động (điện, nước, lương...)
                'other',         // Thu/chi khác
            ]);

            // ── Số tiền ────────────────────────────────────────────────────────
            $table->decimal('amount', 18, 2);                  // Luôn dương
            $table->decimal('balance_after', 18, 2)->default(0); // Số dư sau giao dịch (snapshot)

            // ── Liên kết polymorphic đến nghiệp vụ gốc ────────────────────────
            // Ví dụ: App\Models\Invoice | App\Models\PurchaseOrder
            $table->string('transactionable_type', 100)->nullable();
            $table->unsignedBigInteger('transactionable_id')->nullable();

            // ── Thông tin chứng từ ─────────────────────────────────────────────
            $table->string('reference_code', 50)->nullable();  // Mã chứng từ gốc (số HĐ, số PO)
            $table->text('description');                       // Diễn giải: "Thu tiền HĐ INV-001"
            $table->timestamp('transaction_date');             // Thời điểm giao dịch

            // ── Trạng thái ─────────────────────────────────────────────────────
            $table->boolean('is_confirmed')->default(true);    // false = cần xác nhận lại

            $table->text('note')->nullable();

            $table->timestamps();

            // ── Index ──────────────────────────────────────────────────────────
            $table->index(['pharmacy_id', 'transaction_date']); // Sổ quỹ theo ngày
            $table->index(['pharmacy_id', 'type', 'category']);
            $table->index(['transactionable_type', 'transactionable_id']); // Polymorphic
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};