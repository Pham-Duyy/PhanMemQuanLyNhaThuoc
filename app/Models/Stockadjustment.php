<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Scopes\PharmacyScope;

class StockAdjustment extends Model
{
    protected $fillable = [
        'pharmacy_id',
        'batch_id',
        'medicine_id',
        'created_by',
        'approved_by',
        'type',
        'quantity_before',
        'quantity_after',
        'quantity_change',
        'reason',
        'note',
        'status',
        'approved_at',
        'adjustment_date',
    ];

    protected $casts = [
        'quantity_before' => 'integer',
        'quantity_after' => 'integer',
        'quantity_change' => 'integer',
        'adjustment_date' => 'datetime',
        'approved_at' => 'datetime',
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

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'count' => 'Kiểm kê',
            'destroy' => 'Xuất hủy',
            'return' => 'Trả NCC',
            'correction' => 'Đính chính',
            'other' => 'Khác',
            default => $this->type,
        };
    }

    /** Tăng hay giảm tồn */
    public function getDirectionAttribute(): string
    {
        return $this->quantity_change >= 0 ? 'increase' : 'decrease';
    }

    public function getDirectionLabelAttribute(): string
    {
        return $this->quantity_change >= 0 ? 'Tăng' : 'Giảm';
    }
}