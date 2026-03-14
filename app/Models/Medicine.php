<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\PharmacyScope;

class Medicine extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pharmacy_id',
        'category_id',
        'code',
        'barcode',
        'registration_number',
        'name',
        'generic_name',
        'manufacturer',
        'country_of_origin',
        'unit',
        'package_unit',
        'units_per_package',
        'sell_price',
        'wholesale_price',
        'min_stock',
        'max_stock',
        'requires_prescription',
        'is_narcotic',
        'is_psychotropic',
        'is_antibiotic',
        'description',
        'contraindication',
        'storage_instruction',
        'image',
        'is_active',
    ];

    protected $casts = [
        'sell_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'requires_prescription' => 'boolean',
        'is_narcotic' => 'boolean',
        'is_psychotropic' => 'boolean',
        'is_antibiotic' => 'boolean',
        'is_active' => 'boolean',
        'min_stock' => 'integer',
        'max_stock' => 'integer',
        'units_per_package' => 'integer',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(MedicineCategory::class, 'category_id');
    }

    /** Tất cả lô của thuốc này */
    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    /** Chỉ lấy lô còn hàng, chưa hết hạn, đang active */
    public function availableBatches(): HasMany
    {
        return $this->hasMany(Batch::class)
            ->where('current_quantity', '>', 0)
            ->where('is_expired', false)
            ->where('is_active', true)
            ->orderBy('expiry_date')        // FEFO
            ->orderBy('created_at');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    /**
     * Tổng tồn kho = SUM(current_quantity) của tất cả batch còn active.
     *
     * Dùng:  $medicine->total_stock
     *
     * LƯU Ý: Nếu cần hiệu năng cao, eager load batches trước:
     *   Medicine::with('batches')->get()
     *   → $medicine->total_stock  (không tạo thêm query)
     */
    public function getTotalStockAttribute(): int
    {
        if ($this->relationLoaded('batches')) {
            return $this->batches
                ->where('is_active', true)
                ->where('is_expired', false)
                ->sum('current_quantity');
        }

        return (int) $this->batches()
            ->where('is_active', true)
            ->where('is_expired', false)
            ->sum('current_quantity');
    }

    /**
     * Lô có hạn dùng gần nhất (dùng để hiển thị cảnh báo ở POS).
     * Dùng: $medicine->nearest_expiry_date  → '15/03/2025' | null
     */
    public function getNearestExpiryDateAttribute(): ?string
    {
        if ($this->relationLoaded('batches')) {
            $batch = $this->batches
                ->where('is_active', true)
                ->where('is_expired', false)
                ->where('current_quantity', '>', 0)
                ->sortBy('expiry_date')
                ->first();
            return $batch?->expiry_date?->format('d/m/Y');
        }

        $batch = $this->batches()
            ->where('is_active', true)
            ->where('is_expired', false)
            ->where('current_quantity', '>', 0)
            ->orderBy('expiry_date')
            ->first();
        return $batch?->expiry_date?->format('d/m/Y');
    }

    /**
     * Ngày hết hạn gần nhất dạng Carbon (dùng cho tính toán, so sánh).
     * Dùng: $medicine->nearest_expiry_carbon
     */
    public function getNearestExpiryCarbonAttribute(): ?\Carbon\Carbon
    {
        if ($this->relationLoaded('batches')) {
            return $this->batches
                ->where('is_active', true)
                ->where('is_expired', false)
                ->where('current_quantity', '>', 0)
                ->sortBy('expiry_date')
                ->first()?->expiry_date;
        }
        return $this->batches()
            ->where('is_active', true)
            ->where('is_expired', false)
            ->where('current_quantity', '>', 0)
            ->orderBy('expiry_date')
            ->first()?->expiry_date;
    }

    /**
     * Có đang thiếu hàng không (tồn <= min_stock)?
     * Dùng: $medicine->is_low_stock  → true/false
     */
    public function getIsLowStockAttribute(): bool
    {
        if ($this->min_stock <= 0)
            return false;
        return $this->total_stock <= $this->min_stock;
    }

    /**
     * Có đang hết hàng hoàn toàn không?
     * Dùng: $medicine->is_out_of_stock  → true/false
     */
    public function getIsOutOfStockAttribute(): bool
    {
        return $this->total_stock === 0;
    }

    /**
     * Nhãn GPP phân loại thuốc (dùng ở badge POS + danh sách).
     * Dùng: $medicine->gpp_label  → 'Kê đơn' | 'Kháng sinh' | 'Gây nghiện' | 'OTC'
     */
    public function getGppLabelAttribute(): string
    {
        if ($this->is_narcotic)
            return 'Gây nghiện';
        if ($this->requires_prescription)
            return 'Kê đơn';
        if ($this->is_antibiotic)
            return 'Kháng sinh';
        return 'OTC';
    }

    /**
     * Màu badge GPP.
     * Dùng: <span class="badge bg-{{ $medicine->gpp_color }}">
     */
    public function getGppColorAttribute(): string
    {
        if ($this->is_narcotic)
            return 'dark';
        if ($this->requires_prescription)
            return 'danger';
        if ($this->is_antibiotic)
            return 'warning';
        return 'success';
    }

    /**
     * Giá trị tồn kho (tính theo giá nhập trung bình).
     * Cần load batches trước.
     */
    public function getStockValueAttribute(): float
    {
        if (!$this->relationLoaded('batches'))
            return 0;
        return $this->batches
            ->where('is_active', true)
            ->where('is_expired', false)
            ->sum(fn($b) => $b->current_quantity * (float) $b->purchase_price);
    }

    /**
     * Số lô đang còn hàng và còn hạn.
     */
    public function getActiveBatchCountAttribute(): int
    {
        if ($this->relationLoaded('batches')) {
            return $this->batches
                ->where('is_active', true)
                ->where('is_expired', false)
                ->where('current_quantity', '>', 0)
                ->count();
        }
        return $this->batches()
            ->where('is_active', true)
            ->where('is_expired', false)
            ->where('current_quantity', '>', 0)
            ->count();
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    /** Chỉ lấy thuốc đang kinh doanh */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Tìm kiếm đa trường — dùng cho POS và danh sách.
     * Tìm theo: tên, tên hoạt chất, mã thuốc, mã vạch.
     *
     * Dùng: Medicine::search('para')->get()
     */
    public function scopeSearch($query, string $keyword)
    {
        $kw = '%' . $keyword . '%';
        return $query->where(function ($q) use ($kw) {
            $q->where('name', 'like', $kw)
                ->orWhere('generic_name', 'like', $kw)
                ->orWhere('code', 'like', $kw)
                ->orWhere('barcode', 'like', $kw);
        });
    }

    /** Lọc theo nhóm thuốc */
    public function scopeOfCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /** Thuốc kê đơn */
    public function scopePrescriptionOnly($query)
    {
        return $query->where('requires_prescription', true);
    }

    /** Thuốc gây nghiện */
    public function scopeNarcotic($query)
    {
        return $query->where('is_narcotic', true);
    }

    /**
     * Lọc thuốc đang cận kề hết hàng (tồn <= min_stock).
     * Dùng cho Dashboard cảnh báo.
     *
     * Dùng: Medicine::lowStock()->with('batches')->get()
     *
     * Dùng correlated subquery thay vì withSum + havingRaw để tránh lỗi
     * MySQL ONLY_FULL_GROUP_BY (non-grouping field in HAVING clause).
     */
    public function scopeLowStock($query)
    {
        return $query->where('min_stock', '>', 0)
            ->whereRaw('
                         min_stock >= COALESCE((
                             SELECT SUM(b.current_quantity)
                             FROM batches b
                             WHERE b.medicine_id = medicines.id
                               AND b.is_active   = 1
                               AND b.is_expired  = 0
                               AND b.deleted_at IS NULL
                         ), 0)
                     ');
    }

    /**
     * Eager load kèm tổng tồn kho bằng withSum — dùng cho trang index.
     * Hiệu năng cao hơn load('batches') khi chỉ cần số.
     */
    public function scopeWithStockInfo($query)
    {
        return $query->withSum([
            'batches as total_stock' => fn($q) =>
                $q->where('is_active', true)->where('is_expired', false)
        ], 'current_quantity')
            ->withCount([
                'batches as active_batch_count' => fn($q) =>
                    $q->where('is_active', true)->where('is_expired', false)
                        ->where('current_quantity', '>', 0)
            ]);
    }
}