<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Scopes\PharmacyScope;

class DebtTransaction extends Model
{
    protected $fillable = [
        'pharmacy_id',
        'created_by',
        'debtable_type',
        'debtable_id',
        'type',
        'category',
        'amount',
        'balance_after',
        'sourceable_type',
        'sourceable_id',
        'reference_code',
        'description',
        'transaction_date',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    // ── Global Scope ───────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::addGlobalScope(new PharmacyScope());
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Đối tượng bị nợ: Supplier hoặc Customer.
     * Dùng: $debtTx->debtable → Supplier | Customer
     */
    public function debtable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Nguồn phát sinh nợ: Invoice hoặc PurchaseOrder.
     * Dùng: $debtTx->sourceable → Invoice | PurchaseOrder
     */
    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'increase' ? 'Phát sinh nợ' : 'Thanh toán';
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'purchase' => 'Nhập hàng',
            'sale' => 'Bán chịu',
            'payment' => 'Thanh toán',
            'adjustment' => 'Điều chỉnh',
            default => $this->category,
        };
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeOfSupplier($query, int $supplierId)
    {
        return $query->where('debtable_type', Supplier::class)
            ->where('debtable_id', $supplierId);
    }

    public function scopeOfCustomer($query, int $customerId)
    {
        return $query->where('debtable_type', Customer::class)
            ->where('debtable_id', $customerId);
    }

    public function scopeDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('transaction_date', [
            $from . ' 00:00:00',
            $to . ' 23:59:59',
        ]);
    }
}