<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Giao dịch công nợ - theo dõi AP (phải trả) và AR (phải thu).
     *
     * CÁCH HOẠT ĐỘNG:
     *
     * [Công nợ NCC - AP]:
     * - Nhập hàng chưa TT  → tạo record: debtable=Supplier, type=increase, amount=+X
     * - Thanh toán NCC      → tạo record: debtable=Supplier, type=decrease, amount=-X
     * - Đồng thời cập nhật: suppliers.current_debt += hoặc -=
     *
     * [Công nợ KH - AR]:
     * - Bán hàng ghi nợ    → tạo record: debtable=Customer, type=increase, amount=+X
     * - KH trả nợ          → tạo record: debtable=Customer, type=decrease, amount=-X
     * - Đồng thời cập nhật: customers.current_debt += hoặc -=
     *
     * Tại sao lưu cả trong bảng này lẫn cập nhật current_debt?
     * - current_debt → truy vấn nhanh số dư hiện tại
     * - debt_transactions → lịch sử chi tiết, đối soát, kiểm toán
     */
    public function up(): void
    {
        Schema::create('debt_transactions', function (Blueprint $table) {
            $table->id();

            // Liên kết nhà thuốc
            $table->foreignId('pharmacy_id')->constrained('pharmacies')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');

            // ── Đối tượng công nợ (Polymorphic) ───────────────────────────────
            // debtable_type: App\Models\Supplier | App\Models\Customer
            // debtable_id:   ID của NCC hoặc KH
            $table->string('debtable_type', 100);
            $table->unsignedBigInteger('debtable_id');

            // ── Loại giao dịch ─────────────────────────────────────────────────
            $table->enum('type', ['increase', 'decrease']);    // Tăng nợ | Giảm nợ
            $table->enum('category', [
                'purchase',       // Nợ phát sinh khi nhập hàng (AP)
                'sale',           // Nợ phát sinh khi bán chịu (AR)
                'payment',        // Thanh toán giảm nợ
                'adjustment',     // Điều chỉnh thủ công (cần duyệt)
            ]);

            // ── Số tiền ────────────────────────────────────────────────────────
            $table->decimal('amount', 18, 2);                  // Luôn dương
            $table->decimal('balance_after', 18, 2);           // Số dư sau giao dịch (snapshot)

            // ── Nguồn gốc giao dịch (Polymorphic) ────────────────────────────
            // Liên kết đến Invoice hoặc PurchaseOrder gốc
            $table->string('sourceable_type', 100)->nullable();
            $table->unsignedBigInteger('sourceable_id')->nullable();

            // ── Thông tin ──────────────────────────────────────────────────────
            $table->string('reference_code', 50)->nullable();  // Mã HĐ / PO liên quan
            $table->text('description');                       // Diễn giải
            $table->timestamp('transaction_date');

            $table->text('note')->nullable();

            $table->timestamps();

            // ── Index ──────────────────────────────────────────────────────────
            $table->index(['debtable_type', 'debtable_id']);   // Lấy lịch sử nợ 1 đối tác
            $table->index(['pharmacy_id', 'transaction_date']);
            $table->index(['sourceable_type', 'sourceable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_transactions');
    }
};