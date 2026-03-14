<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Điều chỉnh tồn kho thủ công.
     *
     * Dùng khi:
     * - Kiểm kê: tồn thực tế khác tồn hệ thống
     * - Xuất hủy: thuốc hết hạn, vỡ, hỏng
     * - Điều chỉnh đính chính sai sót
     *
     * QUAN TRỌNG: Mọi điều chỉnh đều phải ghi log lý do (yêu cầu GPP).
     * Không được xóa record này sau khi tạo.
     */
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();

            // Liên kết
            $table->foreignId('pharmacy_id')->constrained('pharmacies')->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained('batches');
            $table->foreignId('medicine_id')->constrained('medicines');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            // ── Loại điều chỉnh ────────────────────────────────────────────────
            $table->enum('type', [
                'count',       // Kiểm kê
                'destroy',     // Xuất hủy (hết hạn, hỏng vỡ)
                'return',      // Trả NCC
                'correction',  // Đính chính sai sót nhập liệu
                'other',       // Khác
            ]);

            // ── Số lượng ───────────────────────────────────────────────────────
            $table->integer('quantity_before');                // Tồn trước điều chỉnh
            $table->integer('quantity_after');                 // Tồn sau điều chỉnh
            $table->integer('quantity_change');                // = after - before (âm = giảm)

            // ── Bắt buộc nhập lý do (yêu cầu GPP) ────────────────────────────
            $table->string('reason', 255);                     // Lý do bắt buộc
            $table->text('note')->nullable();                  // Ghi chú thêm

            // ── Phê duyệt ──────────────────────────────────────────────────────
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved'); // pending: chờ duyệt, approved: đã duyệt, rejected: đã từ chối
            $table->timestamp('approved_at')->nullable(); // Thời gian phê duyệt

            $table->dateTime('adjustment_date')->nullable(); // Thời gian điều chỉnh

            $table->timestamps();
            // KHÔNG có softDeletes - record này không được xóa (audit trail GPP)

            // Index
            $table->index(['pharmacy_id', 'adjustment_date']); // Sổ điều chỉnh theo ngày   
            $table->index('batch_id'); // Lô thuốc
            $table->index('medicine_id'); // Thuốc
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};