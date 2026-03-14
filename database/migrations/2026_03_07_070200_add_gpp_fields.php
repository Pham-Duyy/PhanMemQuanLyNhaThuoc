<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── 1. Bổ sung bảng medicines ────────────────────────────────────
        Schema::table('medicines', function (Blueprint $table) {
            // Dạng bào chế: Viên nén, Viên nang, Siro, Dung dịch tiêm...
            $table->string('dosage_form', 100)->nullable()
                ->after('generic_name')
                ->comment('Dạng bào chế — TT02/2018 bắt buộc');
            // Hàm lượng riêng: 500mg, 250mg/5ml, 10%...
            $table->string('concentration', 100)->nullable()
                ->after('dosage_form')
                ->comment('Hàm lượng/nồng độ — TT02/2018 bắt buộc');
        });

        // ── 2. Bổ sung invoice_items — hướng dẫn sử dụng ────────────────
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->text('usage_instruction')->nullable()
                ->after('batch_number')
                ->comment('Hướng dẫn dùng cho item này — TT02 Điều 7');
        });

        // ── 3. Bổ sung purchase_order_items — số HĐ nhà cung cấp ────────
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->string('supplier_invoice_no', 100)->nullable()
                ->after('expiry_date')
                ->comment('Số hóa đơn NCC để đối chiếu thanh tra');
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn(['dosage_form', 'concentration']);
        });
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('usage_instruction');
        });
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn('supplier_invoice_no');
        });
    }
};