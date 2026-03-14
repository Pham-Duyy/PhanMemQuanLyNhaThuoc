<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\PharmacyScope;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pharmacy_id',
        'customer_id',
        'created_by',
        'cancelled_by',
        'code',
        'status',
        'invoice_date',
        'subtotal',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'change_amount',
        'debt_amount',
        'payment_method',
        'prescription_code',
        'doctor_name',
        'cancelled_at',
        'cancel_reason',
        'note',
    ];

    protected $casts = [
        'invoice_date' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'debt_amount' => 'decimal:2',
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

    /** NULL = khách vãng lai */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /** Chi tiết từng dòng hàng + thông tin lô xuất */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function cashTransactions()
    {
        return $this->morphMany(CashTransaction::class, 'transactionable');
    }

    public function debtTransactions()
    {
        return $this->morphMany(DebtTransaction::class, 'sourceable');
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    /**
     * Label trạng thái tiếng Việt.
     * Dùng: $invoice->status_label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'refunded' => 'Hoàn trả',
            'pending' => 'Chờ xử lý',
            default => ucfirst($this->status),
        };
    }

    /**
     * Màu badge Bootstrap theo trạng thái.
     * Dùng: <span class="badge bg-{{ $invoice->status_color }}">
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'success',
            'cancelled' => 'danger',
            'refunded' => 'warning',
            'pending' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Label phương thức thanh toán tiếng Việt.
     * Dùng: $invoice->payment_method_label
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Tiền mặt',
            'card' => 'Thẻ ngân hàng',
            'transfer' => 'Chuyển khoản',
            'debt' => 'Ghi nợ',
            'mixed' => 'Kết hợp',
            default => $this->payment_method ?? '—',
        };
    }

    /**
     * Icon cho phương thức thanh toán.
     * Dùng: $invoice->payment_method_icon
     */
    public function getPaymentMethodIconAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => '💵',
            'card' => '💳',
            'transfer' => '📲',
            'debt' => '📒',
            'mixed' => '🔀',
            default => '💰',
        };
    }

    /**
     * Tính lãi gộp của toàn hóa đơn.
     * = Σ (sell_price - purchase_price) × quantity mỗi dòng
     *
     * Tự dùng query nếu items chưa được eager-load,
     * tránh return 0 sai khi gọi trực tiếp.
     * Dùng: $invoice->gross_profit
     */
    public function getGrossProfitAttribute(): float
    {
        if ($this->relationLoaded('items')) {
            return $this->items->sum(
                fn($item) =>
                ((float) $item->sell_price - (float) $item->purchase_price) * $item->quantity
            );
        }

        return (float) $this->items()
            ->selectRaw('SUM((sell_price - purchase_price) * quantity) as gp')
            ->value('gp') ?? 0;
    }

    /**
     * Tỷ suất lãi gộp (%).
     * Dùng: $invoice->gross_profit_margin
     */
    public function getGrossProfitMarginAttribute(): float
    {
        if ((float) $this->total_amount <= 0)
            return 0;
        return round($this->gross_profit / (float) $this->total_amount * 100, 1);
    }

    /**
     * Hóa đơn này có thuốc kê đơn không?
     * Dùng khi in receipt để hiển thị cảnh báo GPP.
     */
    public function getHasPrescriptionItemsAttribute(): bool
    {
        if ($this->relationLoaded('items')) {
            return $this->items->contains(
                fn($item) => $item->medicine?->requires_prescription === true
            );
        }
        return $this->items()
            ->whereHas('medicine', fn($q) => $q->where('requires_prescription', true))
            ->exists();
    }

    /**
     * Số tiền khách còn thiếu nếu trả chưa đủ mà không ghi nợ.
     * Dùng: $invoice->shortfall
     */
    public function getShortfallAttribute(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->paid_amount);
    }

    /**
     * Hóa đơn có thể hủy không?
     * Chỉ được hủy khi status = completed.
     */
    public function canCancel(): bool
    {
        return $this->status === 'completed';
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /** Hóa đơn còn công nợ chưa thanh toán */
    public function scopeHasDebt($query)
    {
        return $query->where('debt_amount', '>', 0);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('invoice_date', today());
    }

    public function scopeDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('invoice_date', [
            $from . ' 00:00:00',
            $to . ' 23:59:59',
        ]);
    }

    public function scopeOfCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeOfStaff($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }
}