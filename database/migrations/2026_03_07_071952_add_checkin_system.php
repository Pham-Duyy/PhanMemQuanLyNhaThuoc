<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Thêm cột work_pin vào users
        Schema::table('users', function (Blueprint $table) {
            $table->string('work_pin', 6)->nullable()->after('position')
                ->comment('Mã PIN 4-6 số để chấm công');
        });

        // Bảng log check-in / check-out
        Schema::create('checkin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assignment_id')->nullable()
                ->constrained('shift_assignments')->nullOnDelete();
            $table->date('work_date');
            $table->time('checkin_time')->nullable();
            $table->time('checkout_time')->nullable();
            $table->integer('late_minutes')->default(0);   // số phút đi muộn
            $table->integer('early_minutes')->default(0);  // về sớm
            $table->decimal('actual_hours', 4, 1)->nullable();
            $table->enum('status', ['checked_in', 'checked_out', 'incomplete'])->default('checked_in');
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'work_date']); // 1 log/người/ngày
            $table->index(['pharmacy_id', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkin_logs');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('work_pin');
        });
    }
};