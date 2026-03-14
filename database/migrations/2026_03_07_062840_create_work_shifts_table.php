<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── 1. Định nghĩa ca làm việc ─────────────────────────────────────
        Schema::create('work_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);                    // Sáng / Chiều / Tối
            $table->time('start_time');                    // 07:00
            $table->time('end_time');                      // 12:00
            $table->decimal('hours', 4, 1);                // 5.0 giờ
            $table->string('color', 20)->default('#0EA5A0'); // màu badge
            $table->decimal('shift_wage', 12, 0)->default(0); // lương/ca (đ)
            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['pharmacy_id', 'is_active']);
        });

        // ── 2. Phân công ca theo tuần ────────────────────────────────────
        Schema::create('shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('work_shifts')->cascadeOnDelete();
            $table->date('work_date');                     // ngày làm
            $table->enum('status', [
                'scheduled',   // đã xếp
                'confirmed',   // nhân viên xác nhận
                'completed',   // đã hoàn thành
                'absent',      // vắng mặt
                'late',        // đi muộn
            ])->default('scheduled');
            $table->time('actual_start')->nullable();      // giờ vào thực tế
            $table->time('actual_end')->nullable();        // giờ ra thực tế
            $table->decimal('actual_hours', 4, 1)->nullable();
            $table->decimal('wage_amount', 12, 0)->default(0); // lương thực tế ca này
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'shift_id', 'work_date']); // không trùng ca cùng ngày
            $table->index(['pharmacy_id', 'work_date']);
            $table->index(['user_id', 'work_date']);
        });

        // ── 3. Bảng lương tháng ─────────────────────────────────────────
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('month');          // 1-12
            $table->unsignedSmallInteger('year');
            $table->integer('total_shifts')->default(0);   // tổng ca làm
            $table->decimal('total_hours', 6, 1)->default(0);
            $table->integer('absent_days')->default(0);
            $table->integer('late_days')->default(0);
            $table->decimal('base_salary', 14, 0)->default(0);   // lương cơ bản
            $table->decimal('shift_salary', 14, 0)->default(0);  // tổng lương ca
            $table->decimal('bonus', 14, 0)->default(0);
            $table->decimal('deduction', 14, 0)->default(0);     // khấu trừ
            $table->decimal('net_salary', 14, 0)->default(0);    // thực lĩnh
            $table->enum('status', ['draft', 'confirmed', 'paid'])->default('draft');
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'month', 'year']);
            $table->index(['pharmacy_id', 'year', 'month']);
        });

        // ── 4. Thêm cột lương cơ bản vào users ──────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('base_salary', 14, 0)->default(0)->after('is_active')
                ->comment('Lương cơ bản tháng (đ)');
            $table->string('position', 100)->nullable()->after('base_salary')
                ->comment('Chức vụ: Dược sĩ, Thu ngân...');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['base_salary', 'position']);
        });
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('shift_assignments');
        Schema::dropIfExists('work_shifts');
    }
};