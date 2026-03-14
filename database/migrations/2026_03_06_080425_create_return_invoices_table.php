<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('return_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->string('code', 30)->unique(); // TH-2024-001
            $table->date('return_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('refund_amount', 15, 2)->default(0); // Thực tế hoàn tiền
            $table->string('refund_method')->default('cash'); // cash|account
            $table->text('reason'); // Lý do trả hàng
            $table->text('note')->nullable();
            $table->string('status')->default('completed'); // completed|cancelled
            $table->timestamps();
        });

        Schema::create('return_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_item_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity'); // Số lượng trả
            $table->decimal('unit_price', 15, 2); // Giá bán lúc đầu
            $table->decimal('total_amount', 15, 2);
            $table->string('unit')->default('');
            $table->text('reason')->nullable(); // Lý do từng dòng
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_invoice_items');
        Schema::dropIfExists('return_invoices');
    }
};