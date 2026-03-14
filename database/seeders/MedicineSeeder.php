<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\Pharmacy;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $pharmacyId = Pharmacy::first()->id;

        // Lấy ID các nhóm thuốc theo code
        $cats = MedicineCategory::withoutGlobalScope(\App\Models\Scopes\PharmacyScope::class)
            ->pluck('id', 'code');

        $medicines = [

            // ── Kháng sinh (KS) ────────────────────────────────────────────────
            [
                'code' => 'TH001',
                'name' => 'Amoxicillin 500mg',
                'generic_name' => 'Amoxicillin trihydrate 500mg',
                'manufacturer' => 'Mekophar',
                'category_code' => 'KS',
                'unit' => 'Viên',
                'package_unit' => 'Hộp',
                'units_per_package' => 20,
                'sell_price' => 2500,
                'min_stock' => 100,
                'requires_prescription' => true,
                'is_antibiotic' => true,
            ],
            [
                'code' => 'TH002',
                'name' => 'Augmentin 625mg',
                'generic_name' => 'Amoxicillin 500mg + Acid clavulanic 125mg',
                'manufacturer' => 'GlaxoSmithKline',
                'category_code' => 'KS',
                'unit' => 'Viên',
                'package_unit' => 'Hộp',
                'units_per_package' => 14,
                'sell_price' => 18500,
                'min_stock' => 50,
                'requires_prescription' => true,
                'is_antibiotic' => true,
            ],
            [
                'code' => 'TH003',
                'name' => 'Azithromycin 500mg',
                'generic_name' => 'Azithromycin 500mg',
                'manufacturer' => 'DHG Pharma',
                'category_code' => 'KS',
                'unit' => 'Viên',
                'package_unit' => 'Hộp',
                'units_per_package' => 3,
                'sell_price' => 22000,
                'min_stock' => 30,
                'requires_prescription' => true,
                'is_antibiotic' => true,
            ],

            // ── Giảm đau - Hạ sốt (GT) ────────────────────────────────────────
            [
                'code' => 'TH004',
                'name' => 'Paracetamol 500mg',
                'generic_name' => 'Paracetamol 500mg',
                'manufacturer' => 'Mekophar',
                'category_code' => 'GT',
                'unit' => 'Viên',
                'package_unit' => 'Vỉ',
                'units_per_package' => 10,
                'sell_price' => 500,
                'min_stock' => 200,
                'requires_prescription' => false,
            ],
            [
                'code' => 'TH005',
                'name' => 'Efferalgan 500mg (Sủi)',
                'generic_name' => 'Paracetamol 500mg',
                'manufacturer' => 'UPSA',
                'category_code' => 'GT',
                'unit' => 'Viên',
                'package_unit' => 'Tuýp',
                'units_per_package' => 16,
                'sell_price' => 4500,
                'min_stock' => 80,
                'requires_prescription' => false,
            ],
            [
                'code' => 'TH006',
                'name' => 'Ibuprofen 400mg',
                'generic_name' => 'Ibuprofen 400mg',
                'manufacturer' => 'Imexpharm',
                'category_code' => 'GT',
                'unit' => 'Viên',
                'package_unit' => 'Hộp',
                'units_per_package' => 20,
                'sell_price' => 3000,
                'min_stock' => 100,
                'requires_prescription' => false,
            ],

            // ── Tiêu hóa (TH) ─────────────────────────────────────────────────
            [
                'code' => 'TH007',
                'name' => 'Omeprazole 20mg',
                'generic_name' => 'Omeprazole 20mg',
                'manufacturer' => 'Stada',
                'category_code' => 'TH',
                'unit' => 'Viên',
                'package_unit' => 'Hộp',
                'units_per_package' => 28,
                'sell_price' => 5500,
                'min_stock' => 80,
                'requires_prescription' => true,
            ],
            [
                'code' => 'TH008',
                'name' => 'Motilium-M 10mg',
                'generic_name' => 'Domperidone 10mg',
                'manufacturer' => 'Janssen',
                'category_code' => 'TH',
                'unit' => 'Viên',
                'package_unit' => 'Hộp',
                'units_per_package' => 30,
                'sell_price' => 6000,
                'min_stock' => 60,
                'requires_prescription' => false,
            ],
            [
                'code' => 'TH009',
                'name' => 'Smecta 3g',
                'generic_name' => 'Diosmectite 3g',
                'manufacturer' => 'Ipsen Pharma',
                'category_code' => 'TH',
                'unit' => 'Gói',
                'package_unit' => 'Hộp',
                'units_per_package' => 30,
                'sell_price' => 8000,
                'min_stock' => 50,
                'requires_prescription' => false,
            ],

            // ── Hô hấp - Tai mũi họng (HH) ────────────────────────────────────
            [
                'code' => 'TH010',
                'name' => 'Zyrtec 10mg',
                'generic_name' => 'Cetirizine hydrochloride 10mg',
                'manufacturer' => 'UCB',
                'category_code' => 'HH',
                'unit' => 'Viên',
                'package_unit' => 'Hộp',
                'units_per_package' => 10,
                'sell_price' => 12000,
                'min_stock' => 60,
                'requires_prescription' => false,
            ],
            [
                'code' => 'TH011',
                'name' => 'Ambroxol 30mg',
                'generic_name' => 'Ambroxol hydrochloride 30mg',
                'manufacturer' => 'DHG Pharma',
                'category_code' => 'HH',
                'unit' => 'Viên',
                'package_unit' => 'Hộp',
                'units_per_package' => 20,
                'sell_price' => 2000,
                'min_stock' => 100,
                'requires_prescription' => false,
            ],

            // ── Vitamin - Khoáng chất (VTM) ────────────────────────────────────
            [
                'code' => 'TH012',
                'name' => 'Vitamin C 1000mg (Sủi)',
                'generic_name' => 'Ascorbic acid 1000mg',
                'manufacturer' => 'Bayer',
                'category_code' => 'VTM',
                'unit' => 'Viên',
                'package_unit' => 'Tuýp',
                'units_per_package' => 10,
                'sell_price' => 7500,
                'min_stock' => 80,
                'requires_prescription' => false,
            ],
            [
                'code' => 'TH013',
                'name' => 'Calcium Corbiere',
                'generic_name' => 'Calcium gluconate + Vitamin C',
                'manufacturer' => 'Corbiere',
                'category_code' => 'VTM',
                'unit' => 'Ống',
                'package_unit' => 'Hộp',
                'units_per_package' => 10,
                'sell_price' => 65000,
                'min_stock' => 40,
                'requires_prescription' => false,
            ],
            [
                'code' => 'TH014',
                'name' => 'Multivitamin Centrum',
                'generic_name' => 'Multivitamin + Multimineral',
                'manufacturer' => 'Pfizer Consumer Healthcare',
                'category_code' => 'VTM',
                'unit' => 'Viên',
                'package_unit' => 'Hộp',
                'units_per_package' => 30,
                'sell_price' => 12000,
                'min_stock' => 50,
                'requires_prescription' => false,
            ],

            // ── Tim mạch - Huyết áp (TM) ──────────────────────────────────────
            [
                'code' => 'TH015',
                'name' => 'Amlodipine 5mg',
                'generic_name' => 'Amlodipine besylate 5mg',
                'manufacturer' => 'Stada',
                'category_code' => 'TM',
                'unit' => 'Viên',
                'package_unit' => 'Hộp',
                'units_per_package' => 30,
                'sell_price' => 3500,
                'min_stock' => 60,
                'requires_prescription' => true,
            ],
            [
                'code' => 'TH016',
                'name' => 'Losartan 50mg',
                'generic_name' => 'Losartan potassium 50mg',
                'manufacturer' => 'Imexpharm',
                'category_code' => 'TM',
                'unit' => 'Viên',
                'package_unit' => 'Hộp',
                'units_per_package' => 30,
                'sell_price' => 4500,
                'min_stock' => 60,
                'requires_prescription' => true,
            ],

            // ── Đái tháo đường (DT) ────────────────────────────────────────────
            [
                'code' => 'TH017',
                'name' => 'Metformin 500mg',
                'generic_name' => 'Metformin hydrochloride 500mg',
                'manufacturer' => 'DHG Pharma',
                'category_code' => 'DT',
                'unit' => 'Viên',
                'package_unit' => 'Hộp',
                'units_per_package' => 30,
                'sell_price' => 2000,
                'min_stock' => 80,
                'requires_prescription' => true,
            ],

            // ── Ngoài da - Nhãn khoa (NK) ──────────────────────────────────────
            [
                'code' => 'TH018',
                'name' => 'Gentrisone Cream',
                'generic_name' => 'Betamethasone + Gentamicin + Clotrimazole',
                'manufacturer' => 'Schering-Plough',
                'category_code' => 'NK',
                'unit' => 'Tuýp',
                'package_unit' => 'Tuýp',
                'units_per_package' => 1,
                'sell_price' => 45000,
                'min_stock' => 20,
                'requires_prescription' => true,
            ],

            // ── Bổ tổng hợp (BT) ──────────────────────────────────────────────
            [
                'code' => 'TH019',
                'name' => 'Berocca Performance',
                'generic_name' => 'Vitamin B Complex + Vitamin C + Zinc',
                'manufacturer' => 'Bayer',
                'category_code' => 'BT',
                'unit' => 'Viên',
                'package_unit' => 'Tuýp',
                'units_per_package' => 15,
                'sell_price' => 9500,
                'min_stock' => 40,
                'requires_prescription' => false,
            ],
            [
                'code' => 'TH020',
                'name' => 'Blackmores Bio C 1000mg',
                'generic_name' => 'Ascorbic acid 1000mg (Blackmores)',
                'manufacturer' => 'Blackmores',
                'category_code' => 'BT',
                'unit' => 'Viên',
                'package_unit' => 'Hộp',
                'units_per_package' => 60,
                'sell_price' => 12500,
                'min_stock' => 30,
                'requires_prescription' => false,
            ],
        ];

        foreach ($medicines as $item) {
            $categoryCode = $item['category_code'];
            unset($item['category_code']);

            Medicine::create(array_merge($item, [
                'pharmacy_id' => $pharmacyId,
                'category_id' => $cats[$categoryCode] ?? null,
                'is_active' => true,
            ]));
        }

        $this->command->info('✅ Medicines seeded: ' . count($medicines) . ' loại thuốc.');
    }
}