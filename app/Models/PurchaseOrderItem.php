<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'medicine_id',
        'batch_id',
        'batch_number',
        'manufacture_date',
        'expiry_date',
        'ordered_quantity',
        'received_quantity',
        'unit',
        'purchase_price',
        'discount_percent',
        'vat_percent',
        'total_amount',
        'note',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'manufacture_date' => 'date',
        'purchase_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'vat_percent' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'ordered_quantity' => 'integer',
        'received_quantity' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    /** Batch được tạo ra khi nhận hàng. NULL nếu chưa nhận. */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    /** Tính thành tiền theo giá và số lượng (trước khi lưu) */
    public function getCalculatedTotalAttribute(): float
    {
        $subtotal = $this->ordered_quantity * (float) $this->purchase_price;
        $discount = $subtotal * ($this->discount_percent / 100);
        $afterDiscount = $subtotal - $discount;
        $vat = $afterDiscount * ($this->vat_percent / 100);
        return $afterDiscount + $vat;
    }

    /** Số lượng còn thiếu (chưa nhận đủ) */
    public function getRemainingQuantityAttribute(): int
    {
        return max(0, $this->ordered_quantity - $this->received_quantity);
    }

    /** Đã nhận đủ chưa */
    public function getIsFullyReceivedAttribute(): bool
    {
        return $this->received_quantity >= $this->ordered_quantity;
    }
}