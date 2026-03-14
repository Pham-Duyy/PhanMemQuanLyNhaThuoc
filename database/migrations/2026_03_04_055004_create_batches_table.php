<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Bảng BATCHES - TRÁI TIM của hệ thống GPP.
     *
     * Mỗi lần nhập hàng = tạo 1 batch mới, dù cùng loại thuốc.
     * Lý do: mỗi lô có số lô, hạn sử dụng, giá nhập khác nhau.
     *
     * Bảng này là nơi DUY NHẤT lưu:
     * - Tồn kho thực tế (current_quantity)
     * - Thông tin truy xuất nguồn gốc (batch_number, expiry_date)
     * - Giá vốn thực tế từng lô (purchase_price → tính lãi gộp)
     *
     * FIFO/FEFO hoạt động DỰA TRÊN bảng này:
     * SELECT * FROM batches
     * WHERE medicine_id = ?
     *   AND current_quantity > 0
     *   AND is_expired = 0
     * ORDER BY expiry_date ASC, created_at ASC  ← FEFO
     * FOR UPDATE;  ← Chống race condition
     */
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();

            // ── Liên kết ───────────────────────────────────────────────────────
            $table->foreignId('medicine_id')
                ->constrained('medicines')
                ->cascadeOnDelete();

            $table->foreignId('supplier_id')
                ->constrained('suppliers');


            // ── Thông tin lô (theo chuẩn GPP) ─────────────────────────────────
            $table->string('batch_number', 50);                // Số lô của nhà SX: LOT2024A01
            $table->date('manufacture_date')->nullable();       // Ngày sản xuất
            $table->date('expiry_date');                        // Ngày hết hạn - KHÔNG ĐƯỢC NULL

            // ── Số lượng ───────────────────────────────────────────────────────
            $table->integer('initial_quantity');                // Số lượng nhập ban đầu
            $table->integer('current_quantity');                // Tồn kho HIỆN TẠI (real-time)

            // ── Giá ────────────────────────────────────────────────────────────
            $table->decimal('purchase_price', 15, 2);          // Giá nhập của LÔ NÀY
            // LƯU Ý: sell_price KHÔNG lưu ở đây, lấy từ medicines.sell_price

            // ── Bảo quản ───────────────────────────────────────────────────────
            $table->string('storage_condition', 100)->nullable(); // "Nhiệt độ 15-25°C"
            $table->string('storage_location', 50)->nullable();   // Vị trí kệ: "A1-2"

            // ── Trạng thái ─────────────────────────────────────────────────────
            // is_expired: được Job tự động set = true khi expiry_date < today
            $table->boolean('is_expired')->default(false);
            // is_active: false khi bị thu hồi hoặc xuất hủy toàn bộ
            $table->boolean('is_active')->default(true);

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();                              // Thu hồi lô

            // ── Index - CỰC KỲ QUAN TRỌNG cho hiệu năng FEFO ──────────────────

            // Index 1: FEFO query chính
            // SELECT * FROM batches WHERE medicine_id=? AND current_quantity>0
            //   AND is_expired=0 ORDER BY expiry_date ASC, created_at ASC
            $table->index(['medicine_id', 'expiry_date', 'current_quantity'], 'idx_batch_fefo');

            // Index 2: Tìm lô theo số lô (tra cứu nguồn gốc)
            $table->index('batch_number');

            // Index 3: Cảnh báo hết hạn (Job + Dashboard)
            $table->index(['expiry_date', 'is_expired', 'current_quantity'], 'idx_batch_expiry');

            // Index 4: Tổng hợp tồn kho theo thuốc
            $table->index(['medicine_id', 'is_active', 'is_expired'], 'idx_batch_stock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};