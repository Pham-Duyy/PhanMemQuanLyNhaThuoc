<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained('pharmacies')->cascadeOnDelete();

            // ── Định danh ──────────────────────────────────────────────────────
            $table->string('code', 30);                        // Mã NCC nội bộ: NCC001
            $table->string('name', 200);                       // Tên công ty/cá nhân
            $table->string('tax_code', 20)->nullable();        // Mã số thuế

            // ── Liên hệ ────────────────────────────────────────────────────────
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('province', 100)->nullable();
            $table->string('contact_person', 100)->nullable(); // Người đại diện
            $table->string('contact_phone', 20)->nullable();   // SĐT người đại diện

            // ── Thông tin tài khoản ngân hàng ─────────────────────────────────
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account', 50)->nullable();
            $table->string('bank_branch', 100)->nullable();

            // ── Công nợ ────────────────────────────────────────────────────────
            // QUAN TRỌNG: current_debt được cập nhật mỗi khi có giao dịch
            // KHÔNG tính lại từ đầu mỗi lần query (quá chậm)
            $table->decimal('current_debt', 18, 2)->default(0); // Tổng đang nợ NCC
            $table->decimal('debt_limit', 18, 2)->default(0);   // Hạn mức tín dụng (0 = không giới hạn)
            $table->integer('payment_term_days')->default(30);  // Số ngày được nợ

            // ── Trạng thái ─────────────────────────────────────────────────────
            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── Index ──────────────────────────────────────────────────────────
            $table->unique(['pharmacy_id', 'code']);
            $table->index(['pharmacy_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};