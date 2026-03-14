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
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();

            // Liên kết
            $table->foreignId('purchase_order_id')
                ->constrained('purchase_orders')
                ->cascadeOnDelete();

            $table->foreignId('medicine_id')
                ->constrained('medicines');

            // batch_id = NULL khi đặt hàng, được set khi nhận hàng
            $table->foreignId('batch_id')
                ->nullable()
                ->constrained('batches')
                ->nullOnDelete();

            // ── Thông tin lô (nhập khi nhận hàng) ─────────────────────────────
            // Lưu riêng để không mất khi batch bị xóa
            $table->string('batch_number', 50)->nullable();
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();

            // ── Số lượng ───────────────────────────────────────────────────────
            $table->integer('ordered_quantity');               // Số lượng đặt
            $table->integer('received_quantity')->default(0); // Số lượng thực nhận
            $table->string('unit', 30);                       // Đơn vị tính

            // ── Giá ────────────────────────────────────────────────────────────
            $table->decimal('purchase_price', 15, 2);         // Giá nhập thỏa thuận
            $table->decimal('discount_percent', 5, 2)->default(0); // Chiết khấu dòng hàng (%)
            $table->decimal('vat_percent', 5, 2)->default(0);     // VAT dòng hàng (%)
            $table->decimal('total_amount', 18, 2);           // Thành tiền (đã tính CK + VAT)

            $table->text('note')->nullable();

            $table->timestamps();

            // Index
            $table->index('purchase_order_id');
            $table->index('medicine_id');
            $table->index('batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
