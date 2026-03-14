<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            // ── ADMIN: Toàn quyền ──────────────────────────────────────────────
            [
                'name' => 'admin',
                'display_name' => 'Quản trị viên',
                'description' => 'Toàn quyền hệ thống',
                'permissions' => ['*'],  // Wildcard = tất cả quyền
            ],

            // ── MANAGER: Quản lý ───────────────────────────────────────────────
            [
                'name' => 'manager',
                'display_name' => 'Quản lý',
                'description' => 'Duyệt đơn hàng, xem báo cáo, quản lý nhân sự',
                'permissions' => [
                    // Thuốc
                    'medicine.view',
                    'medicine.create',
                    'medicine.edit',
                    // Kho
                    'inventory.view',
                    'inventory.adjust',
                    // Nhập hàng
                    'purchase.view',
                    'purchase.create',
                    'purchase.edit',
                    'purchase.approve',
                    'purchase.receive',
                    // Bán hàng
                    'invoice.view',
                    'invoice.create',
                    'invoice.cancel',
                    // Đối tác
                    'supplier.view',
                    'supplier.create',
                    'supplier.edit',
                    'customer.view',
                    'customer.create',
                    'customer.edit',
                    // Tài chính
                    'cash.view',
                    'cash.create',
                    'debt.view',
                    // Báo cáo
                    'report.view',
                    'report.revenue',
                    'report.inventory',
                    'report.debt',
                    'report.export',
                    // Nhân viên
                    'user.view',
                ],
            ],

            // ── PHARMACIST: Dược sĩ ────────────────────────────────────────────
            [
                'name' => 'pharmacist',
                'display_name' => 'Dược sĩ',
                'description' => 'Bán hàng, nhập hàng, quản lý thuốc và kho',
                'permissions' => [
                    // Thuốc
                    'medicine.view',
                    'medicine.create',
                    'medicine.edit',
                    // Kho
                    'inventory.view',
                    // Nhập hàng
                    'purchase.view',
                    'purchase.create',
                    'purchase.receive',
                    // Bán hàng
                    'invoice.view',
                    'invoice.create',
                    // Đối tác
                    'supplier.view',
                    'customer.view',
                    'customer.create',
                    // Tài chính
                    'cash.view',
                    'debt.view',
                    // Báo cáo
                    'report.inventory',
                ],
            ],

            // ── CASHIER: Thu ngân ──────────────────────────────────────────────
            [
                'name' => 'cashier',
                'display_name' => 'Thu ngân',
                'description' => 'Chỉ bán hàng và thu tiền',
                'permissions' => [
                    // Thuốc (chỉ xem để bán)
                    'medicine.view',
                    // Kho (chỉ xem tồn kho)
                    'inventory.view',
                    // Bán hàng
                    'invoice.view',
                    'invoice.create',
                    // Khách hàng
                    'customer.view',
                    'customer.create',
                    // Sổ quỹ (chỉ xem ca của mình)
                    'cash.view',
                ],
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }

        $this->command->info('✅ Roles seeded: admin, manager, pharmacist, cashier.');
    }
}