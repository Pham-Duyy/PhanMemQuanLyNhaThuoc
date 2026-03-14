<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\PharmacyScope;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pharmacy_id',
        'supplier_id',
        'created_by',
        'approved_by',
        'received_by',
        'code',
        'status',
        'order_date',
        'expected_date',
        'received_date',
        'approved_at',
        'subtotal',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'vat_amount',
        'note',
        'delivery_address',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'received_date' => 'datetime',
        'approved_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
    ];

    // Danh sách trạng thái hợp lệ
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_RECEIVED = 'received';
    const STATUS_PARTIAL = 'partial';
    const STATUS_CANCELLED = 'cancelled';

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

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function cashTransactions()
    {
        return $this->morphMany(CashTransaction::class, 'transactionable');
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    /**
     * Số tiền còn nợ NCC = total - paid (luôn >= 0).
     * Dùng: $po->debt_amount
     */
    public function getDebtAmountAttribute(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->paid_amount);
    }

    /**
     * Label trạng thái tiếng Việt.
     * Dùng: $po->status_label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Nháp',
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'received' => 'Đã nhận hàng',
            'partial' => 'Nhận một phần',
            'cancelled' => 'Đã hủy',
            default => ucfirst($this->status),
        };
    }

    /**
     * Màu badge Bootstrap theo trạng thái.
     * Dùng: <span class="badge bg-{{ $po->status_color }}">
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'pending' => 'warning',
            'approved' => 'primary',
            'received' => 'success',
            'partial' => 'info',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Icon emoji theo trạng thái.
     * Dùng: $po->status_icon
     */
    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            'draft' => '📝',
            'pending' => '⏳',
            'approved' => '✅',
            'received' => '📦',
            'partial' => '📬',
            'cancelled' => '❌',
            default => '❓',
        };
    }

    /**
     * Tỷ lệ nhận hàng (%).
     * Dùng hiển thị progress bar ở receive view.
     * Cần load('items') trước.
     */
    public function getReceiveProgressAttribute(): float
    {
        if (!$this->relationLoaded('items'))
            return 0;
        $ordered = $this->items->sum('ordered_quantity');
        $received = $this->items->sum('received_quantity');
        if ($ordered <= 0)
            return 0;
        return round($received / $ordered * 100, 1);
    }

    // ── Workflow helpers ───────────────────────────────────────────────────────

    /** Có thể duyệt không? (pending → approved) */
    public function canApprove(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /** Có thể nhận hàng không? (approved|partial → received) */
    public function canReceive(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_PARTIAL]);
    }

    /** Có thể hủy không? (draft|pending → cancelled) */
    public function canCancel(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING]);
    }

    /** Có thể chỉnh sửa không? (chỉ draft) */
    public function canEdit(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOfSupplier($query, int $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('order_date', [$from, $to]);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /** Đơn còn nợ NCC chưa thanh toán hết */
    public function scopeHasDebt($query)
    {
        return $query->whereColumn('paid_amount', '<', 'total_amount');
    }
}