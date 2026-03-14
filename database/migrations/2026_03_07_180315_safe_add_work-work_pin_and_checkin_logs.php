<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration an toàn — kiểm tra cột/bảng đã tồn tại trước khi tạo.
 * Chạy: php artisan migrate
 */
return new class extends Migration {
    public function up(): void
    {
        // ── 1. Thêm work_pin vào users (nếu chưa có) ─────────────────────
        if (!Schema::hasColumn('users', 'work_pin')) {
            Schema::table('users', function (Blueprint $table) {
                // Thêm sau cột position nếu tồn tại, không thì thêm cuối
                if (Schema::hasColumn('users', 'position')) {
                    $table->string('work_pin', 6)->nullable()->after('position')
                        ->comment('Mã PIN 4-6 số để chấm công');
                } else {
                    $table->string('work_pin', 6)->nullable()
                        ->comment('Mã PIN 4-6 số để chấm công');
                }
            });
        }

        // ── 2. Thêm base_salary + position vào users (nếu chưa có) ───────
        if (!Schema::hasColumn('users', 'base_salary')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('base_salary', 14, 0)->default(0)
                    ->comment('Lương cơ bản tháng (đ)');
            });
        }

        if (!Schema::hasColumn('users', 'position')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('position', 100)->nullable()
                    ->comment('Chức vụ: Dược sĩ, Thu ngân...');
            });
        }

        // ── 3. Tạo bảng work_shifts (nếu chưa có) ────────────────────────
        if (!Schema::hasTable('work_shifts')) {
            Schema::create('work_shifts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
                $table->string('name', 50);
                $table->time('start_time');
                $table->time('end_time');
                $table->decimal('hours', 4, 1);
                $table->string('color', 20)->default('#0EA5A0');
                $table->decimal('shift_wage', 12, 0)->default(0);
                $table->boolean('is_active')->default(true);
                $table->text('note')->nullable();
                $table->timestamps();
                $table->index(['pharmacy_id', 'is_active']);
            });
        }

        // ── 4. Tạo bảng shift_assignments (nếu chưa có) ──────────────────
        if (!Schema::hasTable('shift_assignments')) {
            Schema::create('shift_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('shift_id')->constrained('work_shifts')->cascadeOnDelete();
                $table->date('work_date');
                $table->enum('status', ['scheduled', 'confirmed', 'completed', 'absent', 'late'])
                    ->default('scheduled');
                $table->time('actual_start')->nullable();
                $table->time('actual_end')->nullable();
                $table->decimal('actual_hours', 4, 1)->nullable();
                $table->decimal('wage_amount', 12, 0)->default(0);
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('note')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'shift_id', 'work_date']);
                $table->index(['pharmacy_id', 'work_date']);
                $table->index(['user_id', 'work_date']);
            });
        }

        // ── 5. Tạo bảng payrolls (nếu chưa có) ───────────────────────────
        if (!Schema::hasTable('payrolls')) {
            Schema::create('payrolls', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->unsignedTinyInteger('month');
                $table->unsignedSmallInteger('year');
                $table->integer('total_shifts')->default(0);
                $table->decimal('total_hours', 6, 1)->default(0);
                $table->integer('absent_days')->default(0);
                $table->integer('late_days')->default(0);
                $table->decimal('base_salary', 14, 0)->default(0);
                $table->decimal('shift_salary', 14, 0)->default(0);
                $table->decimal('bonus', 14, 0)->default(0);
                $table->decimal('deduction', 14, 0)->default(0);
                $table->decimal('net_salary', 14, 0)->default(0);
                $table->enum('status', ['draft', 'confirmed', 'paid'])->default('draft');
                $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('confirmed_at')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'month', 'year']);
                $table->index(['pharmacy_id', 'year', 'month']);
            });
        }

        // ── 6. Tạo bảng checkin_logs (nếu chưa có) ───────────────────────
        if (!Schema::hasTable('checkin_logs')) {
            Schema::create('checkin_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('assignment_id')->nullable()
                    ->constrained('shift_assignments')->nullOnDelete();
                $table->date('work_date');
                $table->time('checkin_time')->nullable();
                $table->time('checkout_time')->nullable();
                $table->integer('late_minutes')->default(0);
                $table->integer('early_minutes')->default(0);
                $table->decimal('actual_hours', 4, 1)->nullable();
                $table->enum('status', ['checked_in', 'checked_out', 'incomplete'])
                    ->default('checked_in');
                $table->string('note', 255)->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'work_date']);
                $table->index(['pharmacy_id', 'work_date']);
            });
        }

        // ── 7. GPP fields: dosage_form + concentration vào medicines ──────
        if (Schema::hasTable('medicines')) {
            if (!Schema::hasColumn('medicines', 'dosage_form')) {
                Schema::table('medicines', function (Blueprint $table) {
                    $table->string('dosage_form', 100)->nullable()
                        ->after('generic_name')
                        ->comment('Dạng bào chế — TT02/2018');
                });
            }
            if (!Schema::hasColumn('medicines', 'concentration')) {
                Schema::table('medicines', function (Blueprint $table) {
                    $table->string('concentration', 100)->nullable()
                        ->after('dosage_form')
                        ->comment('Hàm lượng/nồng độ — TT02/2018');
                });
            }
        }

        // ── 8. usage_instruction vào invoice_items ────────────────────────
        if (
            Schema::hasTable('invoice_items') &&
            !Schema::hasColumn('invoice_items', 'usage_instruction')
        ) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->text('usage_instruction')->nullable()
                    ->comment('Hướng dẫn dùng — TT02 Điều 7');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('checkin_logs');
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('shift_assignments');
        Schema::dropIfExists('work_shifts');

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $cols = ['work_pin', 'base_salary', 'position'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('users', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};