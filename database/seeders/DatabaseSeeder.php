<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder — Chạy tất cả seeder theo đúng thứ tự.
 *
 * Thứ tự QUAN TRỌNG vì có foreign key phụ thuộc nhau:
 * Pharmacy → Roles → Users → Categories → Medicines
 * → Suppliers → Customers → Batches
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PharmacySeeder::class,          // 1. Tạo nhà thuốc
            RoleSeeder::class,              // 2. Tạo roles + permissions
            UserSeeder::class,              // 3. Tạo users (cần pharmacy + roles)
            MedicineCategorySeeder::class,  // 4. Tạo nhóm thuốc
            SupplierSeeder::class,          // 5. Tạo nhà cung cấp
            CustomerSeeder::class,          // 6. Tạo khách hàng
            MedicineSeeder::class,          // 7. Tạo danh mục thuốc (cần categories)
            BatchSeeder::class,             // 8. Tạo lô hàng (cần medicines + suppliers)
        ]);
    }
}