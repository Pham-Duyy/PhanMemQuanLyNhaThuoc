<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * InvoiceItem — Chi tiết từng dòng hàng trong hóa đơn.
 *
 * Điểm quan trọng nhất: cột batch_id lưu LÔ CỤ THỂ đã xuất.
 * Không được để NULL sau khi hóa đơn hoàn tất.
 *
 * Các cột snapshot (expiry_date, batch_number, purchase_price)
 * được sao chép từ Batch lúc bán để bảo toàn lịch sử.
 */
class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'medicine_id',
        'batch_id',
        'quantity',
        'unit',
        'sell_price',
        'purchase_price',
        'discount_percent',
        'total_amount',
        'expiry_date',
        'batch_number',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'sell_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'quantity' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    /** Lô cụ thể đã xuất — đây là cột cốt lõi của FIFO */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    /**
     * Lãi gộp của dòng hàng này.
     * = (sell_price - purchase_price) × quantity
     * Dùng: $item->gross_profit
     */
    public function getGrossProfitAttribute(): float
    {
        return ((float) $this->sell_price - (float) $this->purchase_price) * $this->quantity;
    }

    /**
     * Tỷ suất lãi gộp trên doanh thu dòng này (%).
     * Dùng: $item->gross_profit_margin
     */
    public function getGrossProfitMarginAttribute(): float
    {
        $revenue = (float) $this->sell_price * $this->quantity;
        if ($revenue <= 0)
            return 0;
        return round($this->gross_profit / $revenue * 100, 1);
    }

    /**
     * Doanh thu dòng này (sau chiết khấu).
     * total_amount đã lưu sẵn, accessor này để fallback nếu NULL.
     */
    public function getLineRevenueAttribute(): float
    {
        if ($this->total_amount)
            return (float) $this->total_amount;
        $gross = (float) $this->sell_price * $this->quantity;
        return $gross * (1 - ((float) $this->discount_percent / 100));
    }

    /**
     * Label trạng thái HSD của lô đã xuất (snapshot).
     * Dùng khi in hóa đơn để hiển thị cảnh báo.
     */
    public function getExpiryWarningAttribute(): ?string
    {
        if (!$this->expiry_date)
            return null;
        $days = now()->diffInDays($this->expiry_date, false);
        if ($days < 0)
            return 'Đã hết hạn';
        if ($days <= 30)
            return "Còn {$days} ngày";
        return null;
    }
}