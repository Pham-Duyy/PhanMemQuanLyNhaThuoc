<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Pharmacy;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $pharmacy = Pharmacy::first();

        $users = [
            // ── Admin ──────────────────────────────────────────────────────────
            [
                'data' => [
                    'pharmacy_id' => $pharmacy->id,
                    'name' => 'Admin Hệ Thống',
                    'email' => 'admin@nhathuoc.com',
                    'phone' => '0901 234 567',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ],
                'role' => 'admin',
            ],

            // ── Quản lý ────────────────────────────────────────────────────────
            [
                'data' => [
                    'pharmacy_id' => $pharmacy->id,
                    'name' => 'Nguyễn Thị Lan',
                    'email' => 'manager@nhathuoc.com',
                    'phone' => '0902 345 678',
                    'password' => Hash::make('password'),
                    'gender' => 'female',
                    'is_active' => true,
                ],
                'role' => 'manager',
            ],

            // ── Dược sĩ ────────────────────────────────────────────────────────
            [
                'data' => [
                    'pharmacy_id' => $pharmacy->id,
                    'name' => 'Trần Văn Minh',
                    'email' => 'pharmacist@nhathuoc.com',
                    'phone' => '0903 456 789',
                    'password' => Hash::make('password'),
                    'gender' => 'male',
                    'is_active' => true,
                ],
                'role' => 'pharmacist',
            ],

            // ── Thu ngân ───────────────────────────────────────────────────────
            [
                'data' => [
                    'pharmacy_id' => $pharmacy->id,
                    'name' => 'Lê Thị Hoa',
                    'email' => 'cashier@nhathuoc.com',
                    'phone' => '0904 567 890',
                    'password' => Hash::make('password'),
                    'gender' => 'female',
                    'is_active' => true,
                ],
                'role' => 'cashier',
            ],
        ];

        foreach ($users as $item) {
            $user = User::create($item['data']);
            $role = Role::where('name', $item['role'])->first();
            $user->roles()->attach($role->id, ['assigned_at' => now()]);
        }

        $this->command->info('✅ Users seeded:');
        $this->command->table(
            ['Email', 'Mật khẩu', 'Vai trò'],
            [
                ['admin@nhathuoc.com', 'password', 'Quản trị viên'],
                ['manager@nhathuoc.com', 'password', 'Quản lý'],
                ['pharmacist@nhathuoc.com', 'password', 'Dược sĩ'],
                ['cashier@nhathuoc.com', 'password', 'Thu ngân'],
            ]
        );
    }
}