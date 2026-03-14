<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('medicine_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained('pharmacies')->cascadeOnDelete();

            $table->string('name', 100);                       // Tên nhóm: "Kháng sinh", "Vitamin"
            $table->string('code', 20)->nullable();            // Mã nhóm: KS, VTM
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);         // Thứ tự hiển thị
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Một nhà thuốc không được có 2 nhóm trùng tên
            $table->unique(['pharmacy_id', 'name']);
            $table->index('pharmacy_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_categories');
    }
};