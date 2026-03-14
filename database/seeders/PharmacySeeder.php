<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pharmacy;

class PharmacySeeder extends Seeder
{
    public function run(): void
    {
        Pharmacy::create([
            'name' => 'Nhà Thuốc Sức Khỏe Vàng',
            'code' => 'NTK001',
            'license_number' => 'GPP-HCM-2024-001',
            'tax_code' => '0123456789',
            'phone' => '028 3812 3456',
            'email' => 'suckhoevangnhathuoc@gmail.com',
            'address' => '123 Nguyễn Thị Minh Khai, Phường 6',
            'province' => 'TP. Hồ Chí Minh',
            'pharmacist_name' => 'DS. Nguyễn Thị Lan',
            'pharmacist_license' => 'CCHN-DS-2020-4521',
            'currency' => 'VND',
            'timezone' => 'Asia/Ho_Chi_Minh',
            'is_active' => true,
        ]);

        $this->command->info('✅ Pharmacy seeded.');
    }
}