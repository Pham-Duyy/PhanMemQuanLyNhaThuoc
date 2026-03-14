<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            // Liên kết
            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            $table->foreignId('medicine_id')
                ->constrained('medicines');

            // LÔ CỤ THỂ ĐÃ XUẤT - đây là cột then chốt của FIFO
            $table->foreignId('batch_id')
                ->constrained('batches');

            // ── Số lượng & Đơn vị ──────────────────────────────────────────────
            $table->integer('quantity');                       // Số lượng xuất từ lô này
            $table->string('unit', 30);                       // Đơn vị tính

            // ── Giá ────────────────────────────────────────────────────────────
            $table->decimal('sell_price', 15, 2);              // Giá bán tại thời điểm xuất
            $table->decimal('purchase_price', 15, 2);          // Giá nhập (snapshot từ batch)
            $table->decimal('discount_percent', 5, 2)->default(0); // CK dòng hàng (%)
            $table->decimal('total_amount', 18, 2);            // Thành tiền sau CK

            // ── SNAPSHOT từ batch ──────────────────────────────────────────────
            // Sao chép vào đây để KHÔNG mất dữ liệu lịch sử
            // nếu batch bị sửa/xóa sau này
            $table->date('expiry_date');                       // Hạn dùng của lô
            $table->string('batch_number', 50);               // Số lô

            $table->timestamps();

            // ── Index ──────────────────────────────────────────────────────────
            $table->index('invoice_id');
            $table->index('medicine_id');
            $table->index('batch_id');                        // Truy xuất: lô này đã bán cho ai
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
