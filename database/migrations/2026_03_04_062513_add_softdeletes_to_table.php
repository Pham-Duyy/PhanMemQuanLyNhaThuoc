<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix: Thêm cột deleted_at (SoftDeletes) vào các bảng còn thiếu.
 *
 * Nguyên nhân lỗi:
 * Model dùng SoftDeletes nhưng migration chưa gọi $table->softDeletes()
 */
return new class extends Migration {
    public function up(): void
    {
        // medicines
        if (!Schema::hasColumn('medicines', 'deleted_at')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // suppliers
        if (!Schema::hasColumn('suppliers', 'deleted_at')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // customers
        if (!Schema::hasColumn('customers', 'deleted_at')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // batches
        if (!Schema::hasColumn('batches', 'deleted_at')) {
            Schema::table('batches', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // purchase_orders
        if (!Schema::hasColumn('purchase_orders', 'deleted_at')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // invoices
        if (!Schema::hasColumn('invoices', 'deleted_at')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // users (nếu chưa có)
        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'medicines',
            'suppliers',
            'customers',
            'batches',
            'purchase_orders',
            'invoices',
            'users'
        ];

        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropSoftDeletes();
                });
            }
        }
    }
};