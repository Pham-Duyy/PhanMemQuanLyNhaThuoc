<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\Pharmacy;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $pharmacyId = Pharmacy::first()->id;

        $suppliers = [
            [
                'code' => 'NCC001',
                'name' => 'Công ty TNHH Dược Phẩm Việt Đức',
                'tax_code' => '0301234567',
                'phone' => '028 3812 0001',
                'email' => 'vietduc@duocpham.vn',
                'address' => '45 Điện Biên Phủ, Quận 3, TP.HCM',
                'contact_person' => 'Nguyễn Văn An',
                'contact_phone' => '0911 001 001',
                'payment_term_days' => 30,
                'debt_limit' => 50000000,
            ],
            [
                'code' => 'NCC002',
                'name' => 'Công ty CP Dược Hậu Giang (DHG)',
                'tax_code' => '1800108656',
                'phone' => '0292 389 1433',
                'email' => 'info@dhgpharma.com.vn',
                'address' => '288 Bis Nguyễn Văn Cừ, Ninh Kiều, Cần Thơ',
                'contact_person' => 'Trần Thị Bình',
                'contact_phone' => '0912 002 002',
                'payment_term_days' => 45,
                'debt_limit' => 100000000,
            ],
            [
                'code' => 'NCC003',
                'name' => 'Công ty TNHH Dược Phẩm Đông Nam Á',
                'tax_code' => '0302345678',
                'phone' => '028 3855 0003',
                'email' => 'dongnama@pharma.vn',
                'address' => '12 Võ Thị Sáu, Quận 1, TP.HCM',
                'contact_person' => 'Lê Minh Cường',
                'contact_phone' => '0913 003 003',
                'payment_term_days' => 30,
                'debt_limit' => 30000000,
            ],
            [
                'code' => 'NCC004',
                'name' => 'Công ty CP Traphaco',
                'tax_code' => '0100107518',
                'phone' => '024 3826 6047',
                'email' => 'info@traphaco.com.vn',
                'address' => '75 Yên Ninh, Ba Đình, Hà Nội',
                'contact_person' => 'Phạm Thu Hương',
                'contact_phone' => '0914 004 004',
                'payment_term_days' => 60,
                'debt_limit' => 80000000,
            ],
            [
                'code' => 'NCC005',
                'name' => 'Công ty TNHH Zuellig Pharma',
                'tax_code' => '0303456789',
                'phone' => '028 3914 6888',
                'email' => 'vn@zuelligpharma.com',
                'address' => 'Lô E2-3, KCN Tân Bình, TP.HCM',
                'contact_person' => 'Đặng Văn Nam',
                'contact_phone' => '0915 005 005',
                'payment_term_days' => 45,
                'debt_limit' => 200000000,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create(array_merge($supplier, [
                'pharmacy_id' => $pharmacyId,
                'current_debt' => 0,
                'is_active' => true,
            ]));
        }

        $this->command->info('✅ Suppliers seeded: ' . count($suppliers) . ' nhà cung cấp.');
    }
}