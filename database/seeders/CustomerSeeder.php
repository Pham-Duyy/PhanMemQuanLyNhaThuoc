<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Pharmacy;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $pharmacyId = Pharmacy::first()->id;

        $customers = [
            [
                'code' => 'KH001',
                'name' => 'Nguyễn Văn Bình',
                'phone' => '0901 111 001',
                'gender' => 'male',
                'date_of_birth' => '1980-05-15',
                'address' => '12 Lê Lợi, Quận 1, TP.HCM',
                'debt_limit' => 500000,
                'medical_note' => 'Dị ứng Penicillin. Tiền sử cao huyết áp.',
            ],
            [
                'code' => 'KH002',
                'name' => 'Trần Thị Mai',
                'phone' => '0902 111 002',
                'gender' => 'female',
                'date_of_birth' => '1975-08-20',
                'address' => '34 Nguyễn Huệ, Quận 1, TP.HCM',
                'debt_limit' => 1000000,
                'medical_note' => 'Tiểu đường type 2. Đang dùng Metformin.',
            ],
            [
                'code' => 'KH003',
                'name' => 'Lê Minh Tuấn',
                'phone' => '0903 111 003',
                'gender' => 'male',
                'date_of_birth' => '1990-12-03',
                'address' => '56 Hai Bà Trưng, Quận 3, TP.HCM',
                'debt_limit' => 0,
            ],
            [
                'code' => 'KH004',
                'name' => 'Phạm Thị Lan',
                'phone' => '0904 111 004',
                'gender' => 'female',
                'date_of_birth' => '1968-03-25',
                'address' => '78 Đinh Tiên Hoàng, Bình Thạnh, TP.HCM',
                'debt_limit' => 2000000,
                'medical_note' => 'Dị ứng Aspirin. Bệnh tim mạch.',
            ],
            [
                'code' => 'KH005',
                'name' => 'Hoàng Văn Dũng',
                'phone' => '0905 111 005',
                'gender' => 'male',
                'date_of_birth' => '1985-07-14',
                'address' => '90 Cách Mạng Tháng 8, Quận 10, TP.HCM',
                'debt_limit' => 500000,
            ],
            [
                'code' => 'KH006',
                'name' => 'Vũ Thị Hằng',
                'phone' => '0906 111 006',
                'gender' => 'female',
                'date_of_birth' => '1992-11-30',
                'address' => '102 Lý Thường Kiệt, Quận 11, TP.HCM',
                'debt_limit' => 0,
            ],
            [
                'code' => 'KH007',
                'name' => 'Đặng Quốc Hùng',
                'phone' => '0907 111 007',
                'gender' => 'male',
                'date_of_birth' => '1955-02-18',
                'address' => '15 Tô Hiến Thành, Quận 10, TP.HCM',
                'debt_limit' => 3000000,
                'medical_note' => 'Bệnh thận mãn tính. Cần thận trọng liều dùng.',
            ],
            [
                'code' => 'KH008',
                'name' => 'Ngô Thị Hoa',
                'phone' => '0908 111 008',
                'gender' => 'female',
                'date_of_birth' => '1998-09-09',
                'address' => '23 Pasteur, Quận 3, TP.HCM',
                'debt_limit' => 0,
            ],
            [
                'code' => 'KH009',
                'name' => 'Bùi Thành Long',
                'phone' => '0909 111 009',
                'gender' => 'male',
                'date_of_birth' => '1972-04-22',
                'address' => '67 Nam Kỳ Khởi Nghĩa, Quận 3, TP.HCM',
                'debt_limit' => 1500000,
            ],
            [
                'code' => 'KH010',
                'name' => 'Đinh Thị Phương',
                'phone' => '0910 111 010',
                'gender' => 'female',
                'date_of_birth' => '1988-06-11',
                'address' => '89 Trần Hưng Đạo, Quận 5, TP.HCM',
                'debt_limit' => 500000,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create(array_merge($customer, [
                'pharmacy_id' => $pharmacyId,
                'current_debt' => 0,
                'loyalty_points' => 0,
                'is_active' => true,
            ]));
        }

        $this->command->info('✅ Customers seeded: ' . count($customers) . ' khách hàng.');
    }
}