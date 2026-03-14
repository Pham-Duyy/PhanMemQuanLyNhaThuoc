<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained('pharmacies')->cascadeOnDelete();

            // ── Định danh ──────────────────────────────────────────────────────
            $table->string('code', 30);                        // Mã KH: KH001
            $table->string('name', 100);                       // Họ tên

            // ── Liên hệ ────────────────────────────────────────────────────────
            $table->string('phone', 20)->nullable();           // INDEX vì tìm KH thường dùng SĐT
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();

            // ── Thông tin cá nhân ──────────────────────────────────────────────
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            // CCCD/CMND - BẮT BUỘC cho KH mua thuốc gây nghiện (quy định GPP)
            $table->string('id_card', 20)->nullable();

            // ── Công nợ ────────────────────────────────────────────────────────
            $table->decimal('current_debt', 18, 2)->default(0); // Dư nợ hiện tại
            $table->decimal('debt_limit', 18, 2)->default(0);   // Hạn mức nợ (0 = không cho nợ)

            // ── Tích điểm (gamification) ───────────────────────────────────────
            $table->integer('loyalty_points')->default(0);     // Điểm tích lũy

            // ── Thông tin y tế ─────────────────────────────────────────────────
            // Lưu dị ứng, bệnh lý nền → Dược sĩ xem khi tư vấn
            $table->text('medical_note')->nullable();           // Ghi chú dị ứng, bệnh lý
            $table->text('note')->nullable();                   // Ghi chú nội bộ

            // ── Trạng thái ─────────────────────────────────────────────────────
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // ── Index ──────────────────────────────────────────────────────────
            $table->unique(['pharmacy_id', 'code']);
            $table->index(['pharmacy_id', 'phone']);            // Tìm KH theo SĐT nhanh
            $table->index(['pharmacy_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};