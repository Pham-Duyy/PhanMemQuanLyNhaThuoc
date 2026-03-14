<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Scopes\PharmacyScope;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pharmacy_id',
        'code',
        'name',
        'tax_code',
        'phone',
        'email',
        'address',
        'province',
        'contact_person',
        'contact_phone',
        'bank_name',
        'bank_account',
        'bank_branch',
        'current_debt',
        'debt_limit',
        'payment_term_days',
        'is_active',
        'note',
    ];

    protected $casts = [
        'current_debt' => 'decimal:2',
        'debt_limit' => 'decimal:2',
        'is_active' => 'boolean',
        'payment_term_days' => 'integer',
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

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function debtTransactions(): MorphMany
    {
        return $this->morphMany(DebtTransaction::class, 'debtable');
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    /**
     * Kiểm tra có vượt hạn mức nợ không.
     * debt_limit = 0 nghĩa là không giới hạn.
     */
    public function getIsOverDebtLimitAttribute(): bool
    {
        if ($this->debt_limit <= 0)
            return false;
        return $this->current_debt >= $this->debt_limit;
    }

    /** Dư địa nợ còn lại */
    public function getRemainingDebtLimitAttribute(): float
    {
        if ($this->debt_limit <= 0)
            return PHP_FLOAT_MAX;
        return max(0, (float) $this->debt_limit - (float) $this->current_debt);
    }

    /**
     * Label trạng thái công nợ NCC.
     * Dùng: $supplier->debt_status_label
     */
    public function getDebtStatusLabelAttribute(): string
    {
        if ($this->current_debt <= 0)
            return 'Không nợ';
        if ($this->is_over_debt_limit)
            return 'Vượt hạn mức';
        if ($this->debt_limit > 0 && $this->current_debt >= $this->debt_limit * 0.8)
            return 'Gần hạn mức';
        return 'Đang nợ';
    }

    /**
     * Màu badge trạng thái công nợ NCC.
     * Dùng: <span class="badge bg-{{ $supplier->debt_status_color }}">
     */
    public function getDebtStatusColorAttribute(): string
    {
        if ($this->current_debt <= 0)
            return 'success';
        if ($this->is_over_debt_limit)
            return 'danger';
        if ($this->debt_limit > 0 && $this->current_debt >= $this->debt_limit * 0.8)
            return 'warning';
        return 'warning';
    }

    /**
     * Phần trăm hạn mức nợ đã dùng (cho progress bar).
     * Dùng: $supplier->debt_limit_percent
     */
    public function getDebtLimitPercentAttribute(): float
    {
        if ($this->debt_limit <= 0)
            return 0;
        return min(100, round($this->current_debt / $this->debt_limit * 100, 1));
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, string $keyword)
    {
        $kw = '%' . $keyword . '%';
        return $query->where(function ($q) use ($kw) {
            $q->where('name', 'like', $kw)
                ->orWhere('code', 'like', $kw)
                ->orWhere('phone', 'like', $kw);
        });
    }

    /** NCC đang có nợ chưa thanh toán */
    public function scopeHasDebt($query)
    {
        return $query->where('current_debt', '>', 0);
    }
}