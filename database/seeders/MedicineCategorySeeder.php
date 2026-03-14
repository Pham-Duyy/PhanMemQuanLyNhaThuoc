<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedicineCategory;
use App\Models\Pharmacy;

class MedicineCategorySeeder extends Seeder
{
    public function run(): void
    {
        $pharmacyId = Pharmacy::first()->id;

        $categories = [
            ['code' => 'KS', 'name' => 'Kháng sinh', 'sort_order' => 1],
            ['code' => 'GT', 'name' => 'Giảm đau - Hạ sốt', 'sort_order' => 2],
            ['code' => 'TH', 'name' => 'Tiêu hóa', 'sort_order' => 3],
            ['code' => 'HH', 'name' => 'Hô hấp - Tai mũi họng', 'sort_order' => 4],
            ['code' => 'VTM', 'name' => 'Vitamin - Khoáng chất', 'sort_order' => 5],
            ['code' => 'TM', 'name' => 'Tim mạch - Huyết áp', 'sort_order' => 6],
            ['code' => 'DT', 'name' => 'Đái tháo đường', 'sort_order' => 7],
            ['code' => 'NK', 'name' => 'Ngoài da - Nhãn khoa', 'sort_order' => 8],
            ['code' => 'BT', 'name' => 'Bổ tổng hợp', 'sort_order' => 9],
            ['code' => 'KH', 'name' => 'Khác', 'sort_order' => 10],
        ];

        foreach ($categories as $cat) {
            MedicineCategory::create(array_merge($cat, [
                'pharmacy_id' => $pharmacyId,
                'is_active' => true,
            ]));
        }

        $this->command->info('✅ Medicine categories seeded: ' . count($categories) . ' nhóm.');
    }
}