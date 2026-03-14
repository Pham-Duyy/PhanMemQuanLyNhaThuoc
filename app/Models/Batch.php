<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Batch — Lô hàng nhập kho.
 *
 * ĐÂY LÀ MODEL QUAN TRỌNG NHẤT trong hệ thống GPP.
 * Toàn bộ logic FIFO/FEFO đều dựa vào Model này.
 *
 * KHÔNG thêm PharmacyScope vào đây vì Batch không có cột pharmacy_id.
 * Thay vào đó, luôn truy cập Batch qua Medicine (đã có scope).
 */
class Batch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'medicine_id',
        'supplier_id',
        'purchase_order_item_id',
        'batch_number',
        'manufacture_date',
        'expiry_date',
        'initial_quantity',
        'current_quantity',
        'purchase_price',
        'storage_condition',
        'storage_location',
        'is_expired',
        'is_active',
        'note',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'manufacture_date' => 'date',
        'purchase_price' => 'decimal:2',
        'is_expired' => 'boolean',
        'is_active' => 'boolean',
        'initial_quantity' => 'integer',
        'current_quantity' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    /**
     * Số ngày còn lại đến hết hạn.
     * Âm = đã hết hạn X ngày rồi.
     *
     * Dùng: $batch->days_until_expiry  → -5 | 0 | 30 | 180
     */
    public function getDaysUntilExpiryAttribute(): int
    {
        return (int) now()->startOfDay()->diffInDays(
            $this->expiry_date->startOfDay(),
            false   // false = cho phép trả về âm
        );
    }

    /**
     * Trạng thái hạn dùng (cho badge màu sắc ở UI).
     *
     * Dùng: $batch->expiry_status  → 'expired' | 'critical' | 'warning' | 'good'
     */
    public function getExpiryStatusAttribute(): string
    {
        $days = $this->days_until_expiry;

        if ($days < 0)
            return 'expired';   // Đã hết hạn → đỏ đậm
        if ($days <= 30)
            return 'critical';  // Còn ≤ 30 ngày → đỏ
        if ($days <= 90)
            return 'warning';   // Còn ≤ 90 ngày → vàng
        return 'good';                       // Còn > 90 ngày → xanh
    }

    /**
     * Label hiển thị cho expiry_status.
     * Dùng: $batch->expiry_status_label  → "Hết hạn" | "Sắp hết hạn" | ...
     */
    public function getExpiryStatusLabelAttribute(): string
    {
        return match ($this->expiry_status) {
            'expired' => 'Hết hạn',
            'critical' => 'Sắp hết hạn (<30 ngày)',
            'warning' => 'Cảnh báo (<90 ngày)',
            default => 'Còn hạn',
        };
    }

    /**
     * Màu Bootstrap badge theo trạng thái hạn dùng.
     * expired/critical → danger | warning → warning | good → success
     * Dùng trong Blade: <span class="badge bg-{{ $batch->expiry_badge_color }}">
     */
    public function getExpiryBadgeColorAttribute(): string
    {
        return match ($this->expiry_status) {
            'expired' => 'danger',
            'critical' => 'danger',
            'warning' => 'warning',
            default => 'success',
        };
    }

    /**
     * Label hiển thị ngắn cho lô: "Lô ABC-001 (HSD 15/03/2025)".
     * Dùng: $batch->batch_label
     */
    public function getBatchLabelAttribute(): string
    {
        return sprintf(
            'Lô %s (HSD %s)',
            $this->batch_number,
            $this->expiry_date->format('d/m/Y')
        );
    }

    /**
     * Có phải lô cần ưu tiên bán không?
     * = còn hàng + chưa hết hạn + HSD trong 90 ngày tới.
     * Dùng: $batch->is_near_expiry
     */
    public function getIsNearExpiryAttribute(): bool
    {
        return !$this->is_expired
            && $this->current_quantity > 0
            && $this->days_until_expiry <= 90
            && $this->days_until_expiry >= 0;
    }

    /**
     * Phần trăm đã xuất kho so với ban đầu.
     * Dùng: $batch->sold_percent  → 75.5
     */
    public function getSoldPercentAttribute(): float
    {
        if ($this->initial_quantity <= 0)
            return 0;
        return round(
            $this->sold_quantity / $this->initial_quantity * 100,
            1
        );
    }

    /**
     * Số lượng đã xuất = initial - current.
     * Dùng: $batch->sold_quantity
     */
    public function getSoldQuantityAttribute(): int
    {
        return $this->initial_quantity - $this->current_quantity;
    }

    /**
     * Giá trị tồn kho của lô này.
     * Dùng: $batch->stock_value  → 1,500,000
     */
    public function getStockValueAttribute(): float
    {
        return $this->current_quantity * (float) $this->purchase_price;
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    /**
     * Scope FEFO — lấy lô theo thứ tự xuất hàng đúng chuẩn GPP.
     *
     * FEFO = First Expired First Out
     * Ưu tiên: hạn dùng gần nhất → nhập trước (FIFO tie-break)
     *
     * QUAN TRỌNG: Scope này PHẢI được gọi trong DB::transaction()
     * kết hợp với ->lockForUpdate() để chống race condition.
     *
     * Dùng:
     *   Batch::where('medicine_id', $id)->availableFEFO()->lockForUpdate()->get()
     */
    public function scopeAvailableFEFO($query)
    {
        return $query
            ->where('current_quantity', '>', 0)
            ->where('is_expired', false)
            ->where('is_active', true)
            // SoftDeletes đã tự thêm deleted_at is null — không cần whereNull thủ công
            ->orderBy('expiry_date', 'asc')   // FEFO: hạn gần → xuất trước
            ->orderBy('created_at', 'asc');   // FIFO tie-break: nhập trước → xuất trước
    }

    /** Các lô đang còn hàng (bất kể hết hạn không) */
    public function scopeInStock($query)
    {
        return $query->where('current_quantity', '>', 0)
            ->where('is_active', true);
    }

    /** Lô sắp hết hạn trong N ngày */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('is_expired', false)
            ->where('current_quantity', '>', 0)
            ->where('expiry_date', '<=', now()->addDays($days)->toDateString())
            ->where('expiry_date', '>=', now()->toDateString())
            ->orderBy('expiry_date');
    }

    /** Lô đã hết hạn nhưng vẫn còn tồn kho (cần xử lý) */
    public function scopeExpiredWithStock($query)
    {
        return $query->where('is_expired', true)
            ->where('current_quantity', '>', 0);
    }

    /** Lọc theo thuốc */
    public function scopeOfMedicine($query, int $medicineId)
    {
        return $query->where('medicine_id', $medicineId);
    }
}