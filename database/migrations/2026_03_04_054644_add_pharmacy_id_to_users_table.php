<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Laravel đã tạo sẵn bảng users qua auth scaffolding.
     * Migration này THÊM các cột cần thiết cho hệ thống nhà thuốc.
     *
     * Chạy SAU migration mặc định của Laravel (0001_01_01_000000_create_users_table.php)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Thêm pharmacy_id sau cột 'id'
            $table->foreignId('pharmacy_id')
                ->nullable()
                ->after('id')
                ->constrained('pharmacies')
                ->nullOnDelete();

            // Thêm thông tin nhân viên
            $table->string('phone', 20)->nullable()->after('email');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('phone');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->string('id_card', 20)->nullable()->after('date_of_birth'); // CCCD
            $table->text('address')->nullable()->after('id_card');
            $table->string('avatar', 255)->nullable()->after('address');

            // Index
            $table->index('pharmacy_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['pharmacy_id']);
            $table->dropColumn([
                'pharmacy_id',
                'phone',
                'gender',
                'date_of_birth',
                'id_card',
                'address',
                'avatar',
                'is_active',
                'last_login_at',
                'deleted_at',
            ]);
        });
    }
};