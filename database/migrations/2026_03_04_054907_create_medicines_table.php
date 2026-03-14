<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Bảng medicines = DANH MỤC THUỐC (thông tin chung).
     *
     * LƯU Ý QUAN TRỌNG:
     * - Bảng này KHÔNG chứa tồn kho. Tồn kho nằm ở bảng `batches`.
     * - sell_price là giá BÁN LẺ hiện tại (có thể thay đổi).
     * - Giá NHẬP từng lô nằm ở batches.purchase_price.
     */
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();

            // ── Liên kết ───────────────────────────────────────────────────────
            $table->foreignId('pharmacy_id')
                ->constrained('pharmacies')
                ->cascadeOnDelete();
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('medicine_categories')
                ->nullOnDelete();

            // ── Định danh ──────────────────────────────────────────────────────
            $table->string('code', 30);                        // Mã nội bộ: TH001
            $table->string('barcode', 50)->nullable();         // Mã vạch EAN-13
            $table->string('registration_number', 50)->nullable(); // Số đăng ký lưu hành

            // ── Tên thuốc ──────────────────────────────────────────────────────
            $table->string('name', 200);                       // Tên thương mại: "Panadol Extra"
            $table->string('generic_name', 200)->nullable();   // Tên hoạt chất: "Paracetamol 500mg"
            $table->string('manufacturer', 200)->nullable();   // Nhà sản xuất
            $table->string('country_of_origin', 100)->nullable(); // Nước sản xuất

            // ── Đơn vị ─────────────────────────────────────────────────────────
            $table->string('unit', 30);                        // Đơn vị bán lẻ: Viên, Lọ, Gói
            $table->string('package_unit', 30)->nullable();    // Đơn vị đóng gói: Hộp, Lốc
            $table->integer('units_per_package')->default(1);  // Số đơn vị/gói: 1 hộp = 24 viên

            // ── Giá ────────────────────────────────────────────────────────────
            $table->decimal('sell_price', 15, 2)->default(0);  // Giá bán lẻ hiện tại
            $table->decimal('wholesale_price', 15, 2)->default(0); // Giá bán sỉ (nếu có)

            // ── Tồn kho ────────────────────────────────────────────────────────
            $table->integer('min_stock')->default(0);          // Ngưỡng cảnh báo tồn tối thiểu
            $table->integer('max_stock')->default(0);          // Tồn tối đa (để nhắc nhập hàng)

            // ── Phân loại GPP ──────────────────────────────────────────────────
            $table->boolean('requires_prescription')->default(false); // Thuốc kê đơn
            $table->boolean('is_narcotic')->default(false);           // Thuốc gây nghiện
            $table->boolean('is_psychotropic')->default(false);       // Thuốc hướng tâm thần
            $table->boolean('is_antibiotic')->default(false);         // Kháng sinh

            // ── Thông tin thêm ─────────────────────────────────────────────────
            $table->text('description')->nullable();           // Công dụng, cách dùng
            $table->text('contraindication')->nullable();      // Chống chỉ định
            $table->string('storage_instruction', 255)->nullable(); // Hướng dẫn bảo quản
            $table->string('image', 255)->nullable();          // Ảnh sản phẩm

            // ── Trạng thái ─────────────────────────────────────────────────────
            $table->boolean('is_active')->default(true);       // 0 = ngừng kinh doanh

            $table->timestamps();
            $table->softDeletes();

            // ── Index ──────────────────────────────────────────────────────────
            $table->unique(['pharmacy_id', 'code']);           // Mã không trùng trong 1 nhà thuốc
            $table->index('barcode');
            $table->index('category_id');
            $table->index(['pharmacy_id', 'is_active']);
            $table->index('requires_prescription');
            // FULLTEXT cho tìm kiếm POS nhanh
            $table->fullText(['name', 'generic_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};