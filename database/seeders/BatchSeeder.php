<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Supplier;
use App\Models\Pharmacy;

/**
 * BatchSeeder — Tạo lô hàng mẫu để demo FEFO.
 *
 * Mỗi thuốc có 2-3 lô với hạn dùng KHÁC NHAU để demo:
 * → Khi bán, hệ thống sẽ tự lấy lô HẠN GẦN NHẤT trước (FEFO).
 *
 * Lô mẫu gồm 3 loại:
 *   - Lô cũ: hạn còn ~6 tháng (xuất trước)
 *   - Lô mới: hạn còn ~12 tháng (xuất sau)
 *   - Lô sắp hết hạn: hạn còn ~25 ngày (để demo cảnh báo Dashboard)
 */
class BatchSeeder extends Seeder
{
    public function run(): void
    {
        $pharmacy = Pharmacy::first();

        // Lấy supplier không qua PharmacyScope (đang trong seeder chưa auth)
        $suppliers = Supplier::withoutGlobalScope(\App\Models\Scopes\PharmacyScope::class)
            ->where('pharmacy_id', $pharmacy->id)
            ->pluck('id', 'code');

        // Lấy tất cả thuốc không qua scope
        $medicines = Medicine::withoutGlobalScope(\App\Models\Scopes\PharmacyScope::class)
            ->where('pharmacy_id', $pharmacy->id)
            ->pluck('id', 'code');

        $today = now();

        // ── Định nghĩa các lô ────────────────────────────────────────────────
        // Format: [medicine_code, supplier_code, batch_number, expiry (months from now), qty, price]
        $batches = [

            // Paracetamol 500mg — 3 lô để demo FEFO rõ ràng
            ['TH004', 'NCC001', 'PC-2024-A01', -1, 0, 350],  // Hết hạn 1 tháng trước → is_expired
            ['TH004', 'NCC001', 'PC-2024-B02', 1, 80, 350],  // Còn 1 tháng → critical
            ['TH004', 'NCC001', 'PC-2025-C03', 10, 200, 380],  // Còn 10 tháng → good

            // Amoxicillin 500mg — 2 lô
            ['TH001', 'NCC002', 'AMX-2024-01', 3, 120, 1800],  // Còn 3 tháng → warning
            ['TH001', 'NCC002', 'AMX-2025-02', 14, 200, 1900],  // Còn 14 tháng → good

            // Augmentin 625mg — 2 lô
            ['TH002', 'NCC005', 'AUG-2024-01', 2, 42, 14000], // Còn 2 tháng → warning
            ['TH002', 'NCC005', 'AUG-2025-02', 11, 70, 14500], // Còn 11 tháng → good

            // Azithromycin — 1 lô
            ['TH003', 'NCC002', 'AZI-2025-01', 13, 30, 16000], // Còn 13 tháng → good

            // Efferalgan sủi — 2 lô
            ['TH005', 'NCC005', 'EFF-2024-01', 0, 32, 3200], // Còn ~25 ngày → critical (demo cảnh báo)
            ['TH005', 'NCC005', 'EFF-2025-02', 12, 96, 3300], // Còn 12 tháng → good

            // Ibuprofen — 2 lô
            ['TH006', 'NCC001', 'IBU-2024-01', 4, 100, 2200],  // Còn 4 tháng
            ['TH006', 'NCC001', 'IBU-2025-02', 15, 120, 2300],  // Còn 15 tháng

            // Omeprazole — 2 lô
            ['TH007', 'NCC003', 'OMP-2024-01', 5, 56, 3800],
            ['TH007', 'NCC003', 'OMP-2025-02', 14, 112, 4000],

            // Motilium — 1 lô
            ['TH008', 'NCC005', 'MTL-2025-01', 16, 60, 4500],

            // Smecta — 1 lô
            ['TH009', 'NCC005', 'SMC-2025-01', 18, 90, 5500],

            // Zyrtec — 2 lô
            ['TH010', 'NCC005', 'ZYR-2024-01', 3, 40, 9000],
            ['TH010', 'NCC005', 'ZYR-2025-02', 15, 80, 9200],

            // Ambroxol — 1 lô
            ['TH011', 'NCC002', 'AMB-2025-01', 12, 100, 1400],

            // Vitamin C sủi — 2 lô
            ['TH012', 'NCC005', 'VTC-2024-01', 2, 40, 5500],  // Còn 2 tháng → warning
            ['TH012', 'NCC005', 'VTC-2025-02', 18, 80, 5800],

            // Calcium Corbiere — 1 lô
            ['TH013', 'NCC005', 'CAL-2025-01', 20, 50, 50000],

            // Centrum — 1 lô
            ['TH014', 'NCC005', 'CTM-2025-01', 24, 60, 9000],

            // Amlodipine — 2 lô
            ['TH015', 'NCC003', 'AML-2024-01', 6, 60, 2500],
            ['TH015', 'NCC003', 'AML-2025-02', 18, 90, 2600],

            // Losartan — 1 lô
            ['TH016', 'NCC003', 'LOS-2025-01', 16, 90, 3200],

            // Metformin — 2 lô
            ['TH017', 'NCC002', 'MET-2024-01', 5, 120, 1400],
            ['TH017', 'NCC002', 'MET-2025-02', 17, 150, 1500],

            // Gentrisone Cream — 1 lô
            ['TH018', 'NCC005', 'GEN-2025-01', 22, 20, 32000],

            // Berocca — 1 lô
            ['TH019', 'NCC005', 'BER-2025-01', 18, 45, 7500],

            // Blackmores — 1 lô
            ['TH020', 'NCC005', 'BLK-2025-01', 24, 60, 9500],
        ];

        $created = 0;

        foreach ($batches as [$medCode, $supCode, $batchNo, $monthsFromNow, $qty, $price]) {

            $medicineId = $medicines[$medCode] ?? null;
            $supplierId = $suppliers[$supCode] ?? null;

            if (!$medicineId || !$supplierId) {
                $this->command->warn("⚠️  Bỏ qua: $medCode / $supCode không tìm thấy.");
                continue;
            }

            // Tính ngày hết hạn
            if ($monthsFromNow < 0) {
                // Lô đã hết hạn
                $expiryDate = $today->copy()->subMonths(abs($monthsFromNow))->toDateString();
                $isExpired = true;
                $currentQty = $qty; // Vẫn có tồn để demo cảnh báo
            } elseif ($monthsFromNow === 0) {
                // Lô còn ~25 ngày (demo cảnh báo critical)
                $expiryDate = $today->copy()->addDays(25)->toDateString();
                $isExpired = false;
                $currentQty = $qty;
            } else {
                $expiryDate = $today->copy()->addMonths($monthsFromNow)->toDateString();
                $isExpired = false;
                $currentQty = $qty;
            }

            // Ngày sản xuất = hết hạn - 24 tháng
            $manufactureDate = $today->copy()
                ->addMonths($monthsFromNow)
                ->subMonths(24)
                ->toDateString();

            Batch::create([
                'medicine_id' => $medicineId,
                'supplier_id' => $supplierId,
                'batch_number' => $batchNo,
                'manufacture_date' => $manufactureDate,
                'expiry_date' => $expiryDate,
                'initial_quantity' => $qty,
                'current_quantity' => $currentQty,
                'purchase_price' => $price,
                'is_expired' => $isExpired,
                'is_active' => true,
            ]);

            $created++;
        }

        $this->command->info("✅ Batches seeded: {$created} lô hàng.");
        $this->command->info('');
        $this->command->info('📋 Demo FEFO với Paracetamol 500mg (TH004):');
        $this->command->table(
            ['Số lô', 'Hạn dùng', 'Tồn kho', 'Trạng thái'],
            [
                ['PC-2024-A01', $today->copy()->subMonths(1)->format('d/m/Y'), '0', '❌ Hết hạn (is_expired=1)'],
                ['PC-2024-B02', $today->copy()->addMonths(1)->format('d/m/Y'), '80', '🔴 Critical (<30 ngày) → Xuất TRƯỚC'],
                ['PC-2025-C03', $today->copy()->addMonths(10)->format('d/m/Y'), '200', '🟢 Good → Xuất SAU'],
            ]
        );
        $this->command->info('→ Khi bán 50 viên: hệ thống lấy 50 từ PC-2024-B02 (hạn gần nhất)');
        $this->command->info('→ Khi bán 100 viên: lấy 80 từ PC-2024-B02 + 20 từ PC-2025-C03');
    }
}