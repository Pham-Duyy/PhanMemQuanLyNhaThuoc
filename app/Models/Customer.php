<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Scopes\PharmacyScope;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pharmacy_id',
        'code',
        'name',
        'phone',
        'email',
        'address',
        'date_of_birth',
        'gender',
        'id_card',
        'current_debt',
        'debt_limit',
        'loyalty_points',
        'medical_note',
        'note',
        'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'current_debt' => 'decimal:2',
        'debt_limit' => 'decimal:2',
        'is_active' => 'boolean',
        'loyalty_points' => 'integer',
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

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function debtTransactions(): MorphMany
    {
        return $this->morphMany(DebtTransaction::class, 'debtable');
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    /** Tổng tiền đã mua (completed invoices) */
    public function getTotalPurchasedAttribute(): float
    {
        return $this->invoices()
            ->where('status', 'completed')
            ->sum('total_amount');
    }

    /**
     * Label trạng thái công nợ.
     * Dùng: $customer->debt_status_label
     */
    public function getDebtStatusLabelAttribute(): string
    {
        if ($this->current_debt <= 0)
            return 'Không nợ';
        if ($this->debt_limit > 0 && $this->current_debt >= $this->debt_limit)
            return 'Vượt hạn mức';
        if ($this->debt_limit > 0 && $this->current_debt >= $this->debt_limit * 0.8)
            return 'Gần hạn mức';
        return 'Đang nợ';
    }

    /**
     * Màu badge trạng thái công nợ.
     * Dùng: <span class="badge bg-{{ $customer->debt_status_color }}">
     */
    public function getDebtStatusColorAttribute(): string
    {
        if ($this->current_debt <= 0)
            return 'success';
        if ($this->debt_limit > 0 && $this->current_debt >= $this->debt_limit)
            return 'danger';
        if ($this->debt_limit > 0 && $this->current_debt >= $this->debt_limit * 0.8)
            return 'warning';
        return 'warning';
    }

    /**
     * Phần trăm đã dùng hạn mức nợ.
     * Dùng hiển thị progress bar.
     */
    public function getDebtLimitPercentAttribute(): float
    {
        if ($this->debt_limit <= 0)
            return 0;
        return min(100, round($this->current_debt / $this->debt_limit * 100, 1));
    }

    /** Kiểm tra còn dư địa để ghi nợ không */
    public function canDebt(float $amount): bool
    {
        // debt_limit = 0 → không cho nợ
        if ($this->debt_limit <= 0)
            return false;
        return ($this->current_debt + $amount) <= $this->debt_limit;
    }

    public function getRemainingDebtLimitAttribute(): float
    {
        if ($this->debt_limit <= 0)
            return 0;
        return max(0, (float) $this->debt_limit - (float) $this->current_debt);
    }

    public function getGenderLabelAttribute(): string
    {
        return match ($this->gender) {
            'male' => 'Nam',
            'female' => 'Nữ',
            'other' => 'Khác',
            default => '—',
        };
    }

    /**
     * Tuổi khách hàng (nếu có ngày sinh).
     * Dùng: $customer->age  → 35 | null
     */
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Tìm KH theo tên, SĐT, mã.
     * Hay dùng nhất ở POS khi chọn khách hàng.
     */
    public function scopeSearch($query, string $keyword)
    {
        $kw = '%' . $keyword . '%';
        return $query->where(function ($q) use ($kw) {
            $q->where('name', 'like', $kw)
                ->orWhere('phone', 'like', $kw)
                ->orWhere('code', 'like', $kw);
        });
    }

    public function scopeHasDebt($query)
    {
        return $query->where('current_debt', '>', 0);
    }
}