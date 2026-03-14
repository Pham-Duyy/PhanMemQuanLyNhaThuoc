<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Scopes\PharmacyScope;

class CashTransaction extends Model
{
    protected $fillable = [
        'pharmacy_id',
        'created_by',
        'type',
        'category',
        'amount',
        'balance_after',
        'transactionable_type',
        'transactionable_id',
        'reference_code',
        'description',
        'transaction_date',
        'is_confirmed',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'transaction_date' => 'datetime',
        'is_confirmed' => 'boolean',
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
     * Polymorphic: liên kết đến Invoice, PurchaseOrder, hoặc NULL.
     * Dùng: $cashTx->transactionable → trả về object Invoice | PurchaseOrder | null
     */
    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'receipt' ? 'Thu' : 'Chi';
    }

    public function getTypeColorAttribute(): string
    {
        return $this->type === 'receipt' ? 'success' : 'danger';
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'sale' => 'Bán hàng',
            'purchase' => 'Nhập hàng',
            'debt_receipt' => 'Thu nợ KH',
            'debt_payment' => 'Trả nợ NCC',
            'expense' => 'Chi phí',
            'other' => 'Khác',
            default => $this->category,
        };
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeReceipts($query)
    {
        return $query->where('type', 'receipt');
    }

    public function scopePayments($query)
    {
        return $query->where('type', 'payment');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('transaction_date', today());
    }

    public function scopeDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('transaction_date', [
            $from . ' 00:00:00',
            $to . ' 23:59:59',
        ]);
    }

    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}