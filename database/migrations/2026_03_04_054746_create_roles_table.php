<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── Bảng roles ─────────────────────────────────────────────────────────
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();        // admin | manager | pharmacist | cashier | warehouse
            $table->string('display_name', 100);         // Tên hiển thị: "Quản trị viên"

            // Danh sách quyền dạng JSON array
            // Ví dụ: ["invoice.create", "invoice.view", "report.revenue.view"]
            $table->json('permissions')->nullable();

            $table->text('description')->nullable();
            $table->timestamps();
        });


    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};