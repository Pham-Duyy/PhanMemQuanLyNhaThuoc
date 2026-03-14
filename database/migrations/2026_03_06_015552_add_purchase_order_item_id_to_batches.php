<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Chỉ thêm cột nếu chưa tồn tại (idempotent)
        if (!Schema::hasColumn('batches', 'purchase_order_item_id')) {
            Schema::table('batches', function (Blueprint $table) {
                $table->foreignId('purchase_order_item_id')
                    ->nullable()
                    ->after('supplier_id')
                    ->constrained('purchase_order_items')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('batches', 'purchase_order_item_id')) {
            Schema::table('batches', function (Blueprint $table) {
                $table->dropForeign(['purchase_order_item_id']);
                $table->dropColumn('purchase_order_item_id');
            });
        }
    }
};