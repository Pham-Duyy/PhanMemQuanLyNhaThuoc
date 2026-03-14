<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pharmacies', function (Blueprint $table) {
            $table->id();

            // Thông tin cơ bản
            $table->string('name', 200);                          // Tên nhà thuốc
            $table->string('code', 30)->unique();                 // Mã nhà thuốc
            $table->string('license_number', 50)->nullable();     // Số giấy phép GPP
            $table->string('tax_code', 20)->nullable();           // Mã số thuế

            // Liên hệ
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('province', 100)->nullable();          // Tỉnh/Thành phố

            // Người phụ trách chuyên môn
            $table->string('pharmacist_name', 100)->nullable();   // Tên dược sĩ phụ trách
            $table->string('pharmacist_license', 50)->nullable(); // Số chứng chỉ hành nghề

            // Cài đặt
            $table->string('currency', 10)->default('VND');
            $table->string('timezone', 50)->default('Asia/Ho_Chi_Minh');
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacies');
    }
};